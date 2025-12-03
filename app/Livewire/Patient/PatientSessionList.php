<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use App\Models\TherapySession;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;

class PatientSessionList extends Component
{
    use Toast, WithPagination;

    public Patient $patient;

    // --- Filters & Sort ---
    public string $search = '';
    public string $statusFilter = ''; // Empty = All
    public array $sortBy = ['column' => 'scheduled_at', 'direction' => 'desc'];

    // --- Modal States ---
    public $showCancelModal = false;
    public $showNoShowModal = false;
    public $showDuplicateModal = false;

    // --- Action Data ---
    public $selectedSessionId;
    public $reasonText = '';
    
    // Duplicate Form Data
    public $dupDate;
    public $dupTime;
    public $dupDuration;

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

    public function confirmAction($id, $action)
    {
        $this->selectedSessionId = $id;
        $this->reasonText = '';

        if ($action === 'cancel') {
            $this->showCancelModal = true;
        } elseif ($action === 'noshow') {
            $this->showNoShowModal = true;
        } elseif ($action === 'duplicate') {
            $session = TherapySession::find($id);
            $this->dupDate = now()->format('Y-m-d');
            $this->dupTime = $session->scheduled_at->format('H:i');
            $this->dupDuration = $session->duration_minutes;
            $this->showDuplicateModal = true;
        }
    }

    public function updateStatus($status)
    {
        $this->validate(['reasonText' => 'required|string|min:5']);

        $session = TherapySession::find($this->selectedSessionId);
        
        if ($session) {
            $session->update([
                'status' => $status,
                'cancellation_reason' => $this->reasonText,
                'cancelled_at' => $status === 'Cancelled' ? now() : null,
                'billing_status' => 'Not Applicable'
            ]);
            
            $this->success(__('Updated'), __("Session marked as $status."));
        }

        $this->showCancelModal = false;
        $this->showNoShowModal = false;
    }

    public function markCompleted($id)
    {
        $session = TherapySession::find($id);
        if ($session && $session->status === 'Scheduled') {
            $session->update(['status' => 'Completed', 'billing_status' => 'Pending']);
            $this->success(__('Success'), __('Session marked as completed.'));
        }
    }

    public function duplicateSession()
    {
        $this->validate([
            'dupDate' => 'required|date|after_or_equal:today',
            'dupTime' => 'required',
            'dupDuration' => 'required|integer|min:15'
        ]);

        $original = TherapySession::find($this->selectedSessionId);
        
        if ($original) {
            $newDate = Carbon::parse("$this->dupDate $this->dupTime");

            TherapySession::create([
                'patient_id' => $original->patient_id,
                'user_id' => $original->user_id,
                'scheduled_at' => $newDate,
                'duration_minutes' => $this->dupDuration,
                'focus_area' => $original->focus_area,
                'status' => 'Scheduled',
                'billing_status' => 'Pending'
            ]);

            $this->success(__('Duplicated'), __('New session scheduled successfully.'));
        }

        $this->showDuplicateModal = false;
    }

    public function render()
    {
        $query = TherapySession::query()
            ->where('patient_id', $this->patient->id)
            ->with('user');

        if ($this->search) {
            $query->where('focus_area', 'like', "%$this->search%");
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return view('livewire.session.patient-session-list', [
            'sessions' => $query->paginate(10)
        ]);
    }
}