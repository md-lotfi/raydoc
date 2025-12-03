<?php

namespace App\Livewire\Sessions;

use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

class SessionDetail extends Component
{
    use Toast;

    public TherapySession $session;

    // Navigation
    public ?TherapySession $previousSession = null;
    public ?TherapySession $nextSession = null;

    // Editable Properties
    public $actualStartAt;
    public $actualEndAt;
    public $notes;
    public $homeworkAssigned;
    
    // Cancellation
    public $showCancelModal = false;
    public $cancellationReason;

    public function mount(TherapySession $session)
    {
        $this->session = $session->load(['patient', 'user']);
        
        // Load navigation links
        $this->previousSession = TherapySession::where('patient_id', $this->session->patient_id)
            ->where('scheduled_at', '<', $this->session->scheduled_at)
            ->orderByDesc('scheduled_at')
            ->first();

        $this->nextSession = TherapySession::where('patient_id', $this->session->patient_id)
            ->where('scheduled_at', '>', $this->session->scheduled_at)
            ->orderBy('scheduled_at')
            ->first();

        // Initialize Form
        $this->notes = $session->notes;
        $this->homeworkAssigned = $session->homework_assigned;
        $this->actualStartAt = $session->actual_start_at ? $session->actual_start_at->format('Y-m-d\TH:i') : null;
        $this->actualEndAt = $session->actual_end_at ? $session->actual_end_at->format('Y-m-d\TH:i') : null;
    }

    protected function rules()
    {
        return [
            'notes' => 'nullable|string',
            'homeworkAssigned' => 'nullable|string',
            'actualStartAt' => 'nullable|date',
            'actualEndAt' => 'nullable|date|after_or_equal:actualStartAt',
        ];
    }

    public function updateClinicalDetails()
    {
        $this->validate();

        $this->session->update([
            'notes' => $this->notes,
            'homework_assigned' => $this->homeworkAssigned,
            'actual_start_at' => $this->actualStartAt,
            'actual_end_at' => $this->actualEndAt,
        ]);

        $this->success(__('Saved'), __('Clinical details updated successfully.'));
    }

    public function markAsCompleted()
    {
        $this->validate([
            'actualStartAt' => 'required',
            'actualEndAt' => 'required|after_or_equal:actualStartAt',
        ]);

        $this->session->update([
            'status' => 'Completed',
            'billing_status' => 'Pending',
            'actual_start_at' => $this->actualStartAt,
            'actual_end_at' => $this->actualEndAt,
        ]);

        $this->success(__('Completed'), __('Session marked as completed.'));
    }

    public function cancelSession()
    {
        $this->validate(['cancellationReason' => 'required|string|min:5']);

        try {
            DB::transaction(function () {
                $this->session->update([
                    'status' => 'Cancelled',
                    'billing_status' => 'Not Applicable',
                    'cancelled_at' => Carbon::now(),
                    'cancellation_reason' => $this->cancellationReason,
                ]);
            });

            $this->showCancelModal = false;
            $this->warning(__('Cancelled'), __('Session has been cancelled.'));
            
        } catch (\Exception $e) {
            $this->error(__('Error'), $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.session.session-detail');
    }
}