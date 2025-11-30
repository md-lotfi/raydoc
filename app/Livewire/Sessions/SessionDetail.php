<?php

namespace App\Livewire\Sessions;

use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SessionDetail extends Component
{
    public TherapySession $session;

    // Properties for editing/updating status
    public $newStatus;

    public $cancellationReason;

    public $actualStartAt;

    public $actualEndAt;

    public $notes;

    public $homeworkAssigned;

    // Flag for displaying the cancellation modal
    public $showCancelModal = false;

    public function mount(TherapySession $session)
    {
        // Eager load necessary relationships for display
        $this->session = $session->load(['patient', 'user']);

        // Initialize editable properties
        $this->newStatus = $session->status;
        $this->notes = $session->notes;
        $this->homeworkAssigned = $session->homework_assigned;

        // Format dates for input fields
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

    // --- Actions ---

    public function updateClinicalDetails()
    {
        $this->validate();

        $this->session->update([
            'notes' => $this->notes,
            'homework_assigned' => $this->homeworkAssigned,
            'actual_start_at' => $this->actualStartAt,
            'actual_end_at' => $this->actualEndAt,
        ]);

        session()->flash('success', 'Clinical details updated successfully.');
    }

    public function markAsCompleted()
    {
        // Validation: Ensure actual times are set before marking as completed
        $this->validate([
            'actualStartAt' => 'required',
            'actualEndAt' => 'required|after_or_equal:actualStartAt',
        ]);

        $this->session->update([
            'status' => 'Completed',
            'billing_status' => 'Pending', // Ready to be billed
            'actual_start_at' => $this->actualStartAt,
            'actual_end_at' => $this->actualEndAt,
        ]);

        session()->flash('success', 'Session marked as **Completed** and is now pending billing.');
    }

    public function openCancelModal()
    {
        $this->showCancelModal = true;
    }

    public function cancelSession()
    {
        $this->validate([
            'cancellationReason' => 'required|string|min:10',
        ]);

        try {
            DB::transaction(function () {
                $this->session->update([
                    'status' => 'Cancelled',
                    'billing_status' => 'Not Applicable', // Not billable
                    'cancelled_at' => Carbon::now(),
                    'cancellation_reason' => $this->cancellationReason,
                ]);
            });

            $this->showCancelModal = false;
            session()->flash('warning', 'Session has been successfully **Cancelled**.');
            $this->session->refresh();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to cancel session: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.session.session-detail');
    }
}
