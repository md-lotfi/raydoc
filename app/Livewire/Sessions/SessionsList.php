<?php

namespace App\Livewire\Sessions;

use App\Models\TherapySession;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class SessionsList extends Component
{
    use Toast, WithPagination;

    // --- State ---
    public string $search = '';

    public string $statusFilter = ''; // Empty = All

    // Sorting
    public array $sortBy = ['column' => 'scheduled_at', 'direction' => 'desc'];

    // Delete Confirmation
    public $showDeleteModal = false;

    public $sessionToDeleteId = null;

    // --- Actions ---

    public function sort($column)
    {
        if ($this->sortBy['column'] === $column) {
            $this->sortBy['direction'] = $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy['column'] = $column;
            $this->sortBy['direction'] = 'asc';
        }
    }

    public function updateStatus($sessionId, $newStatus)
    {
        $session = TherapySession::find($sessionId);
        if ($session) {
            $session->update(['status' => $newStatus]);

            // ✅ FIX: Use placeholder :status for dynamic values
            $this->success(
                __('Status Updated'),
                __('Session marked as :status.', ['status' => __($newStatus)])
            );
        }
    }

    public function confirmDelete($id)
    {
        $this->sessionToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->sessionToDeleteId) {
            TherapySession::destroy($this->sessionToDeleteId);
            // ✅ ALREADY GOOD: Uses translation keys
            $this->success(__('Deleted'), __('Session record removed successfully.'));
        }
        $this->showDeleteModal = false;
        $this->sessionToDeleteId = null;
    }

    // --- Render ---

    public function render()
    {
        // 1. Base Query
        $query = TherapySession::query()->with(['patient', 'user']);

        // 2. Search
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->whereHas('patient', fn ($p) => $p->where('first_name', 'like', "%$this->search%")->orWhere('last_name', 'like', "%$this->search%"))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$this->search%"));
            });
        }

        // 3. Filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // 4. Sort
        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        // 5. Statistics
        $stats = [
            'total' => TherapySession::count(),
            'scheduled' => TherapySession::where('status', 'Scheduled')->count(),
            'completed' => TherapySession::where('status', 'Completed')->count(),
            'pending_billing' => TherapySession::where('billing_status', 'Pending')->count(),
        ];

        return view('livewire.sessions-list', [
            'sessions' => $query->paginate(10),
            'stats' => $stats,
            // Keep these as raw keys for DB logic; translate them in the Blade view using {{ __($status) }}
            'statuses' => ['Scheduled', 'Completed', 'Cancelled', 'No Show'],
        ]);
    }
}
