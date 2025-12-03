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

        return [
            [
                'id' => 'Scheduled',
                'title' => __('Scheduled'),
                'color' => 'border-info/50',
                'bg' => 'bg-info/5',
                'items' => $sessions->where('status', 'Scheduled')->sortBy('scheduled_at'),
            ],
            [
                'id' => 'Checked In',
                'title' => __('Checked In'),
                'color' => 'border-warning/50',
                'bg' => 'bg-warning/5',
                'items' => $sessions->where('status', 'Checked In')->sortBy('scheduled_at'),
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
        ];
    }

    #[On('status-changed')]
    public function updateStatus($sessionId, $newStatus)
    {
        $session = TherapySession::find($sessionId);

        if ($session) {
            $updateData = ['status' => $newStatus];

            // Auto-timestamp logic
            if ($newStatus === 'In Session' && ! $session->actual_start_at) {
                $updateData['actual_start_at'] = now();
            }
            if ($newStatus === 'Completed' && ! $session->actual_end_at) {
                $updateData['actual_end_at'] = now();
                $updateData['billing_status'] = 'Pending';
            }

            $session->update($updateData);

            $this->success(__('Updated'), "Moved to $newStatus");
        }
    }

    public function render()
    {
        return view('livewire.session.waiting-room', [
            'board' => $this->boardData,
        ]);
    }
}
