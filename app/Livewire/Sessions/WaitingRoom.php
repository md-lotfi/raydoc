<?php

namespace App\Livewire\Sessions;

use App\Models\TherapySession;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class WaitingRoom extends Component
{
    use Toast;

    public $date;

    public $refreshKey = 0;

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
    }

    /**
     * ⚡️ Triggered automatically when $date is updated via the date picker
     */
    public function updatedDate()
    {
        // Dispatch event to JS to re-initialize SortableJS
        // because the DOM elements will be replaced.
        $this->dispatch('kanban-refresh');
    }

    public function getBoardDataProperty()
    {
        $sessions = TherapySession::query()
            ->with(['patient', 'user'])
            ->whereDate('scheduled_at', $this->date)
            ->where('status', '!=', 'Cancelled')
            ->get();

        $smartSort = function ($a, $b) {
            $now = now();

            // Check if A or B are "Due Appointments"
            $aIsDue = $a->session_type === 'appointment' && $a->scheduled_at <= $now;
            $bIsDue = $b->session_type === 'appointment' && $b->scheduled_at <= $now;

            // If both are due, sort by schedule time (Earlier appointment wins)
            if ($aIsDue && $bIsDue) {
                return $a->scheduled_at <=> $b->scheduled_at;
            }

            // If only A is due, A wins (returns -1)
            if ($aIsDue) {
                return -1;
            }

            // If only B is due, B wins (returns 1)
            if ($bIsDue) {
                return 1;
            }

            // Otherwise (both walk-ins or early), sort by arrival time (Longest wait wins)
            return $a->checked_in_at <=> $b->checked_in_at;
        };

        return [
            [
                'id' => 'Scheduled',
                'title' => __('Upcoming Appointments'),
                'color' => 'border-info/50',
                'bg' => 'bg-info/5',
                'items' => $sessions->where('status', 'Scheduled')
                    ->where('session_type', 'appointment') // Only fixed times
                    ->sortBy('scheduled_at'),
            ],
            [
                'id' => 'Checked In',
                'title' => __('Waiting Room'),
                'color' => 'border-warning/50',
                'bg' => 'bg-warning/5',
                'items' => $sessions->where('status', 'Checked In')->sort($smartSort),
            ],
            [
                'id' => 'In Session',
                'title' => __('In Session'),
                'color' => 'border-primary/50',
                'bg' => 'bg-primary/5',
                'items' => $sessions->where('status', 'In Session')->sortBy('scheduled_at'),
            ],
            [
                'id' => 'Completed',
                'title' => __('Completed'),
                'color' => 'border-success/50',
                'bg' => 'bg-success/5',
                'items' => $sessions->where('status', 'Completed')->sortByDesc('actual_end_at'),
            ],
            [
                'id' => 'No Show',
                'title' => __('Absent / No Show'),
                'color' => 'border-error/50',
                'bg' => 'bg-error/5',
                // Sort by who was marked absent most recently
                'items' => $sessions->where('status', 'No Show')->sortByDesc('updated_at'),
            ],
        ];
    }

    #[On('status-changed')]
    public function updateStatus($sessionId, $newStatus)
    {
        $session = TherapySession::find($sessionId);
        if ($session) {
            $updateData = ['status' => $newStatus];

            // ⚡️ AUTO-TIMESTAMPING
            if ($newStatus === 'Checked In' && ! $session->checked_in_at) {
                $updateData['checked_in_at'] = now(); // Mark arrival time
            }
            if ($newStatus === 'In Session' && ! $session->actual_start_at) {
                $updateData['actual_start_at'] = now();
            }
            if ($newStatus === 'Completed' && ! $session->actual_end_at) {
                $updateData['actual_end_at'] = now();
                $updateData['billing_status'] = 'Pending';
            }

            $session->update($updateData);

            $this->refreshKey++;
            $this->dispatch('kanban-refresh');
            $this->success(__('Updated'), __('Status changed to :status', ['status' => __($newStatus)]));
        }
    }

    public function callNextPatient()
    {
        // 1. Priority: Scheduled Appointments that are Checked In AND their time has passed (Overdue/On-time)
        $nextSession = TherapySession::query()
            ->whereDate('scheduled_at', $this->date)
            ->where('status', 'Checked In')
            ->where('session_type', 'appointment')
            ->where('scheduled_at', '<=', now()) // Only if appointment time has arrived
            ->orderBy('scheduled_at', 'asc') // Oldest appointment first
            ->first();

        // 2. Fallback: If no urgent appointments, take the person who has been waiting the longest (FIFO)
        // This covers Walk-ins AND Appointments that arrived super early
        if (! $nextSession) {
            $nextSession = TherapySession::query()
                ->whereDate('scheduled_at', $this->date)
                ->where('status', 'Checked In')
                ->orderBy('checked_in_at', 'asc') // First come, first served
                ->first();
        }

        if (! $nextSession) {
            $this->warning(__('Queue Empty'), __('No patients are currently waiting.'));

            return;
        }

        // ... (rest of the start session logic remains the same) ...
        $nextSession->update([
            'status' => 'In Session',
            'actual_start_at' => now(),
        ]);

        $this->refreshKey++;
        $this->dispatch('kanban-refresh');
        $this->success(__('Session Started'), __('Now seeing: ').$nextSession->patient->first_name);
    }

    public function toJSON()
    {
        return [];
    }

    public function render()
    {
        return view('livewire.session.waiting-room', [
            'board' => $this->boardData,
        ]);
    }
}
