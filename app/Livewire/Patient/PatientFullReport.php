<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed; // Use computed properties
use Livewire\Component;

class PatientFullReport extends Component
{
    // Patient model instance passed via mount()
    public Patient $patient;

    // UI state for tabs
    public $currentTab = 'profile';

    // Table headers properties for sessions and billing
    public array $sessionHeaders;

    public array $invoiceHeaders;

    public array $diagnosisHeaders;

    public function mount(Patient $patient)
    {
        // Eager load all necessary relationships for the report
        // Added 'appointments' for clarity if different from TherapySessions
        $this->patient = $patient->load([
            'user',
            'diagnoses.icdCode',
            'therapySessions.user',
            'invoices.lineItems',
        ]);

        // Define static table headers here
        $this->sessionHeaders = [
            ['key' => 'scheduled_at', 'label' => 'Date & Time', 'sortBy' => 'scheduled_at'],
            ['key' => 'user.name', 'label' => 'Therapist'],
            ['key' => 'duration_minutes', 'label' => 'Duration (min)'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'billing_status', 'label' => 'Billing Status'],
            ['key' => 'actions', 'label' => 'Notes'],
        ];

        $this->invoiceHeaders = [
            ['key' => 'invoice_number', 'label' => 'Invoice #', 'sortBy' => 'invoice_number'],
            ['key' => 'issued_date', 'label' => 'Issued Date', 'sortBy' => 'issued_date'],
            ['key' => 'total_amount', 'label' => 'Total Amount'],
            ['key' => 'amount_due', 'label' => 'Amount Due'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'actions', 'label' => 'Actions'],
        ];

        $this->diagnosisHeaders = [
            ['key' => 'icd_code_info', 'label' => 'ICD Code & Description'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'start_date', 'label' => 'Start Date'],
            ['key' => 'condition_status', 'label' => 'Status'],
        ];
    }

    public function setTab($tabName)
    {
        $this->currentTab = $tabName;
    }

    // --- Computed Properties for Billing Summary ---

    #[Computed]
    public function billingSummary(): array
    {
        // Ensure to use the collection from the eagerly loaded patient model
        $invoices = $this->patient->invoices;

        $totalBilled = $invoices->sum('total_amount');
        $totalDue = $invoices->sum('amount_due');
        $totalPaid = $totalBilled - $totalDue;

        return [
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
            'invoices' => $invoices,
        ];
    }

    // --- Computed Properties for Clinical Summary ---

    #[Computed]
    public function clinicalSummary(): array
    {
        $totalSessions = $this->patient->therapySessions->count();
        $completedSessions = $this->patient->therapySessions->where('status', 'Completed')->count();
        $noShowOrCancelled = $this->patient->therapySessions->whereIn('status', ['No Show', 'Cancelled'])->count();

        return [
            'total_sessions' => $totalSessions,
            'completed' => $completedSessions,
            'no_show_cancelled' => $noShowOrCancelled,
        ];
    }

    #[Computed]
    public function diagnosisSummary(): array
    {
        $allDiagnoses = $this->patient->diagnoses;

        $activeDiagnoses = $allDiagnoses->filter(fn ($d) => $d->condition_status === 'Active');
        $pastDiagnoses = $allDiagnoses->filter(fn ($d) => $d->condition_status !== 'Active');

        return [
            'total_diagnoses' => $allDiagnoses->count(),
            'active_count' => $activeDiagnoses->count(),
            'past_count' => $pastDiagnoses->count(),
            'active' => $activeDiagnoses->sortByDesc('start_date'),
            'past' => $pastDiagnoses->sortByDesc('start_date'),
        ];
    }

    public function render()
    {
        return view('livewire.patient.patient-full-report');
    }
}
