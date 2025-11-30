<?php

namespace App\Livewire\Sessions;

use App\Models\TherapySession;
use Livewire\Component;
use Livewire\WithPagination;

class SessionsList extends Component
{
    use WithPagination;

    // --- Component State ---
    public $search = '';

    public $statusFilter = 'All'; // Filter for the 'status' column

    // Placeholder for the list of available statuses from the config
    // In a real application, ensure config('constants.THERAPY_SESSION_STATUS') is accessible
    protected $allStatuses = [
        'All',
        'Scheduled',
        'Completed',
        'Cancelled',
        'No Show',
    ];

    // --- Computed Property to Fetch Filtered Sessions ---

    public function getSessionsProperty()
    {
        $query = TherapySession::query()
            ->with(['patient', 'user']) // Eager load relationships
            ->latest('scheduled_at');

        // 1. Filter by Session Status
        if ($this->statusFilter !== 'All') {
            $query->where('status', $this->statusFilter);
        }

        // 2. Filter by Search (Patient Name or Therapist Name)
        if ($this->search) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                // Search by Patient name
                $q->whereHas('patient', function ($q_patient) use ($searchTerm) {
                    $q_patient->where('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm);
                })
                // Search by Therapist name
                    ->orWhereHas('user', function ($q_user) use ($searchTerm) {
                        $q_user->where('name', 'like', $searchTerm);
                    });
            });
        }

        return $query->paginate(15);
    }

    // --- Render Method ---

    public function render()
    {
        return view('livewire.sessions-list', [
            'sessions' => $this->sessions,
            'statuses' => $this->allStatuses,
        ]);
    }
}
