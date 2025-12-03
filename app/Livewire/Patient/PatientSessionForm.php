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
    public $session_type = 'appointment'; // 'appointment' or 'queue'

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
            $this->session_type = $session->session_type;
            $this->session_date = $session->scheduled_at->format('Y-m-d');
            $this->start_time = $session->scheduled_at->format('H:i');
            $this->duration_minutes = $session->duration_minutes;
            $this->therapist_user_id = $session->user_id;
            $this->focus_area = $session->focus_area;
            $this->notes = $session->notes;
            $this->homework_assigned = $session->homework_assigned;
        } else {
            // 🟩 CREATE MODE
            $this->session_type = settings()->metadata['system']['default_session_type'] ?? 'appointment';
            $this->session_date = now()->format('Y-m-d');
            $this->therapist_user_id = Auth::id(); // Default to current user if they are a therapist
        }
    }

    protected function rules()
    {
        return [
            'session_type' => 'required|in:'.implode(',', config('constants.SESSION_TYPE')),
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

        if ($this->session_type === 'queue') {
            $existingAppointment = TherapySession::where('patient_id', $this->patient->id)
                ->whereDate('scheduled_at', $this->session_date)
                ->where('session_type', 'appointment')
                ->exists();

            if ($existingAppointment) {
                $this->warning(__('Duplicate Visit'), __('This patient already has a scheduled appointment today.'));
                // You can choose to return here or just warn and proceed
            }
        }

        // For Queue, default time to NOW (arrival time)
        $timeString = ($this->session_type === 'queue') ? now()->format('H:i') : $this->start_time;
        $scheduledDateTime = Carbon::createFromFormat('Y-m-d H:i', "$this->session_date $timeString");

        $data = [
            'patient_id' => $this->patient->id,
            'user_id' => $this->therapist_user_id,
            'session_type' => $this->session_type,
            'scheduled_at' => $scheduledDateTime,
            'duration_minutes' => $this->duration_minutes,
            'focus_area' => $this->focus_area,
            'notes' => $this->notes,
            'homework_assigned' => $this->homework_assigned,
            'status' => $this->session->status ?? ($this->session_type === 'queue' ? 'Checked In' : 'Scheduled'),
            'billing_status' => $this->session->billing_status ?? 'Pending',
        ];

        if ($this->session_type === 'queue' && ! isset($this->session)) {
            $data['checked_in_at'] = now();
        }

        if ($this->session) {
            $this->session->update($data);
            $message = __('Session updated successfully.');
        } else {
            TherapySession::create($data);
            $message = __('New session scheduled successfully.');
        }

        $this->success(__('Success'), $message);

        /*if ($this->session_type === 'appointment' && settings()->shouldNotify('patient_booking')) {
    // Send Email
}*/

        return redirect()->route('patient.session.list', ['patient' => $this->patient->id]);
    }

    public function render()
    {
        return view('livewire.session.patient-session-form');
    }
}
