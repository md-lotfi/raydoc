<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use App\Models\TherapySession;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class PatientSessionList extends Component
{
    use Toast, WithPagination;

    public $patient;

    public $sessionToAbscentId;

    public $sessionToCancelId;

    public $sessionToDuplicateId;

    public $showDuplicateSchedule;

    public $showCancelModal;

    public $cancellationReason = '';

    public $showAbscentModal;

    public $abscentReason = '';

    public string $search = '';

    public $session_date;   // Input: Date (YYYY-MM-DD)

    public $start_time;     // Input: Time (HH:MM)

    public $duration_minutes; // Default to 60 minutes

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function completeSession($sessionId)
    {
        $session = TherapySession::find($sessionId);
        $session->status = 'Completed';
        $session->save();
    }

    public function duplicateSession()
    {
        $this->validate([
            'session_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:15|max:180',
        ]);
        $session = TherapySession::findOrFail($this->sessionToDuplicateId);

        $scheduledDateTime = Carbon::createFromFormat('Y-m-d H:i',
            $this->session_date.' '.$this->start_time
        );
        TherapySession::create([
            'patient_id' => $session->patient_id,
            'user_id' => $session->user_id,
            'scheduled_at' => $scheduledDateTime,
            'duration_minutes' => $this->duration_minutes,

            'focus_area' => $session->focus_area,
            'notes' => $session->notes,
            'homework_assigned' => $session->homework_assigned,

            // Default status for a new session
            'status' => 'Scheduled',
            'billing_status' => 'Pending',
        ]);
        $this->showDuplicateSchedule = false;
    }

    public function cancelSession()
    {
        $this->validate(['cancellationReason' => 'required|string|min:10']);
        $session = TherapySession::findOrFail($this->sessionToCancelId);
        $session->update([
            'status' => 'Cancelled',
            'billing_status' => 'Not Applicable',
            'cancelled_at' => now(),
            'cancellation_reason' => $this->cancellationReason,
        ]);

        // Close modal and clean up
        $this->showCancelModal = false;
        session()->flash('success', 'Therapy session successfully cancelled.');

        // Refresh the table/component view
        $this->dispatch('$refresh');
    }

    public function confirmCancel($sessionId)
    {
        $this->sessionToCancelId = $sessionId;
        $this->cancellationReason = '';
        $this->showCancelModal = true;
    }

    public function confirmAbscent($sessionId)
    {
        $this->sessionToAbscentId = $sessionId;
        $this->abscentReason = '';
        $this->showAbscentModal = true;
    }

    public function markSessionAsNoShow()
    {
        $session = TherapySession::find($this->sessionToAbscentId);
        $session->status = 'No Show';
        $session->cancellation_reason = $this->abscentReason; // Reuse the field for notes
        $session->save();
        $this->dispatch('session-updated');
    }

    public function showDuplicateSessionModal($sessionId)
    {
        $this->sessionToDuplicateId = $sessionId;
        $this->session_date = date('Y-m-d', strtotime(now()));
        $this->start_time = '08:00';
        $this->duration_minutes = config('constants.DEFAULT_THERAPY_SESSION_TIME');
        $this->showDuplicateSchedule = true;
    }

    public function delete()
    {
        if (is_null($this->sessionToCancelId)) {
            // Should not happen, but a safeguard
            $this->error(
                title: 'No therapy session selected.',
                description: 'Please select a therapy session before deleting',                  // optional (text)

                timeout: 3000,                      // optional (ms)
                redirectTo: null                    // optional (uri)
            );

            return;
        }

        $session = TherapySession::find($this->diagnosisToDeleteId);

        if (! $session) {
            $this->error(
                title: 'No patient selected.',
                description: 'Please select a patient before deleting',                  // optional (text)

                timeout: 3000,                      // optional (ms)
                redirectTo: null                    // optional (uri)
            );
            // Close the modal and clear the ID
            $this->showCancelModal = false;
            $this->sessionToCancelId = null;

            return;
        }
        $session->delete();

        // 3. Close modal, reset state, and notify
        $this->showCancelModal = false;
        $this->sessionToCancelId = null;

        $this->success(
            title: 'Session saved.',
            description: 'Session deleted successfully!',                  // optional (text)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );

        $this->resetPage();
    }

    public function mount(Patient $patient)
    {
        $this->patient = $patient;
    }

    public function render()
    {
        $query = TherapySession::query()->where('patient_id', $this->patient->id);
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('condition_status', 'like', '%'.$this->search.'%')
                    ->orWhere('type', 'like', '%'.$this->search.'%');
            });
        }

        $sessions = $query->paginate(15);

        return view('livewire.session.patient-session-list', compact('sessions'));
    }
}
