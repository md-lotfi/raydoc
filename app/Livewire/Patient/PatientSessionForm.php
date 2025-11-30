<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use App\Models\TherapySession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PatientSessionForm extends Component
{
    public ?int $sessionId = null; // used for edit

    // Patient Model Instance
    public Patient $patient;

    // Form Properties for Scheduling
    public $session_date;   // Input: Date (YYYY-MM-DD)

    public $start_time;     // Input: Time (HH:MM)

    public $duration_minutes; // Default to 60 minutes

    public $therapist_user_id; // Selected Therapist

    // Form Properties for Clinical/Details
    public $focus_area;

    public $notes;

    public $homework_assigned;

    // Data for select fields
    public $availableTherapists;

    public $sessionStatuses; // From config

    protected function rules()
    {
        return [
            'session_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:15|max:180',
            'therapist_user_id' => 'required|exists:users,id',
            'focus_area' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'homework_assigned' => 'nullable|string',
        ];
    }

    public function initProperties(?TherapySession $session)
    {
        $this->duration_minutes = config('constants.DEFAULT_THERAPY_SESSION_TIME');
        if ($session) {
            $this->sessionId = $session->id;
            $this->session_date = date('Y-m-d', strtotime($session->start_date));
            $this->start_time = $session->start_time;
            $this->duration_minutes = $session->duration_minutes;
            // $this->user_id = $session->user_id;
            $this->focus_area = $session->focus_area;
            $this->notes = $session->notes;
            $this->homework_assigned = $session->homework_assigned;
        }
        $this->session_date = date('Y-m-d', strtotime(now()));
        $this->start_time = '08:00';
        $this->duration_minutes = '45';
        $this->therapist_user_id = Auth::id();
        $this->availableTherapists = User::role(config('constants.ROLES.DOCTOR'))->get();
        $this->sessionStatuses = config('constants.THERAPY_SESSION_STATUS');
    }

    /**
     * Mounts the component, receiving the Patient model instance.
     */
    public function mount(Patient $patient, ?TherapySession $session)
    {

        $this->patient = $patient;
        $this->initProperties($session);
    }

    protected function getDataForSave()
    {
        $scheduledDateTime = Carbon::createFromFormat('Y-m-d H:i',
            $this->session_date.' '.$this->start_time
        );

        return [
            'patient_id' => $this->patient->id,
            'user_id' => $this->therapist_user_id,
            'scheduled_at' => $scheduledDateTime,
            'duration_minutes' => $this->duration_minutes,

            'focus_area' => $this->focus_area,
            'notes' => $this->notes,
            'homework_assigned' => $this->homework_assigned,

            // Default status for a new session
            'status' => 'Scheduled',
            'billing_status' => 'Pending',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = $this->getDataForSave();

        if ($this->sessionId) {
            $session = TherapySession::findOrFail($this->sessionId);
            $session->update($data);
            $action = 'Updated';
        } else {
            $session = TherapySession::create($data);
            $action = 'Added';
        }

        $this->dispatch('therapySession'.$action);

        // Clear the form fields after successful save
        $this->reset([
            'therapist_user_id',
            'session_date',
            'start_time',
            'duration_minutes',
            'focus_area',
            'notes',
            'homework_assigned', ]);

        // Optional: Send a success flash message
        session()->flash('success', 'Session scheduled successfully for '.$this->patient->full_name);

        $this->redirectRoute('patient.session.list', ['patient' => $this->patient->id]);
    }

    public function render()
    {
        return view('livewire.session.patient-session-form');
    }
}
