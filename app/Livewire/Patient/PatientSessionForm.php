<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use App\Models\TherapySession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class PatientSessionForm extends Component
{
    use Toast;

    public ?TherapySession $session = null;

    public Patient $patient;

    // Scheduling
    public $session_date;

    public $start_time = '09:00';

    public $duration_minutes = 45;

    public $therapist_user_id;

    public $is_recurring = false; // New feature placeholder

    // Clinical
    public $focus_area;

    public $notes;

    public $homework_assigned;

    // Options
    public $availableTherapists;

    public function mount(Patient $patient, ?TherapySession $session = null)
    {
        $this->patient = $patient;
        $this->session = $session;

        // Load Therapists (Doctors)
        $this->availableTherapists = User::role(config('constants.ROLES.DOCTOR'))->get();

        if ($session) {
            // 🟦 EDIT MODE
            $this->session_date = $session->scheduled_at->format('Y-m-d');
            $this->start_time = $session->scheduled_at->format('H:i');
            $this->duration_minutes = $session->duration_minutes;
            $this->therapist_user_id = $session->user_id;
            $this->focus_area = $session->focus_area;
            $this->notes = $session->notes;
            $this->homework_assigned = $session->homework_assigned;
        } else {
            // 🟩 CREATE MODE
            $this->session_date = now()->format('Y-m-d');
            $this->therapist_user_id = Auth::id(); // Default to current user if they are a therapist
        }
    }

    protected function rules()
    {
        return [
            'session_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'duration_minutes' => 'required|integer|min:15|max:180',
            'therapist_user_id' => 'required|exists:users,id',
            'focus_area' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'homework_assigned' => 'nullable|string',
        ];
    }

    public function setDuration($minutes)
    {
        $this->duration_minutes = $minutes;
    }

    public function save()
    {
        $this->validate();

        $scheduledDateTime = Carbon::createFromFormat('Y-m-d H:i', "$this->session_date $this->start_time");

        $data = [
            'patient_id' => $this->patient->id,
            'user_id' => $this->therapist_user_id,
            'scheduled_at' => $scheduledDateTime,
            'duration_minutes' => $this->duration_minutes,
            'focus_area' => $this->focus_area,
            'notes' => $this->notes,
            'homework_assigned' => $this->homework_assigned,
            'status' => $this->session->status ?? 'Scheduled',
            'billing_status' => $this->session->billing_status ?? 'Pending',
        ];

        if ($this->session) {
            $this->session->update($data);
            $message = __('Session updated successfully.');
        } else {
            TherapySession::create($data);
            $message = __('New session scheduled successfully.');
        }

        $this->success(__('Success'), $message);

        return redirect()->route('patient.session.list', ['patient' => $this->patient->id]);
    }

    public function render()
    {
        return view('livewire.session.patient-session-form');
    }
}
