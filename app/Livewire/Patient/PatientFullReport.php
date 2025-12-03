<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use App\Models\TherapySession;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PatientFullReport extends Component
{
    public Patient $patient;

    public $currentTab = 'clinical'; // Set default to clinical to see the timeline immediately

    // ... existing properties ...
    public $showSessionDrawer = false;

    public ?TherapySession $selectedSession = null;

    public array $sessionChart = [];

    public array $invoiceHeaders = [
        ['key' => 'invoice_number', 'label' => 'Invoice #'],
        ['key' => 'issued_date', 'label' => 'Date', 'class' => 'hidden lg:table-cell'],
        ['key' => 'total_amount', 'label' => 'Amount'],
        ['key' => 'amount_due', 'label' => 'Due'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'actions', 'label' => '', 'sortable' => false],
    ];

    public function mount(Patient $patient)
    {
        $this->patient = $patient->load([
            'user',
            'diagnoses.icdCode',
            'therapySessions.user',
            'invoices.lineItems',
        ]);

        $this->generateSessionChart();
    }

    // ... existing actions (previewSession, etc) ...

    public function previewSession($sessionId)
    {
        $this->selectedSession = TherapySession::find($sessionId);
        $this->showSessionDrawer = true;
    }

    public function generateSessionChart()
    {
        // ... existing chart logic ...
        $counts = $this->patient->therapySessions->groupBy('status')->map->count();
        $this->sessionChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => [__('Completed'), __('Scheduled'), __('Cancelled'), __('No Show')],
                'datasets' => [[
                    'data' => [$counts['Completed'] ?? 0, $counts['Scheduled'] ?? 0, $counts['Cancelled'] ?? 0, $counts['No Show'] ?? 0],
                    'backgroundColor' => ['#10b981', '#3b82f6', '#ef4444', '#f59e0b'],
                ]],
            ],
            'options' => ['cutout' => '70%', 'plugins' => ['legend' => ['position' => 'bottom']]],
        ];
    }

    #[Computed]
    public function clinicalTimeline()
    {
        // 1. Diagnoses (Add ->toBase())
        $diagnoses = $this->patient->diagnoses->toBase()->map(fn ($d) => [
            'id' => $d->id,
            'type' => 'diagnosis',
            'date' => $d->start_date,
            // Note: When using toBase(), relationships like 'icdCode' might need to be accessed differently
            // if not eager loaded or if they become arrays.
            // However, since we loaded them in mount(), the relations are attached to the model.
            // But toBase() converts the *list* to a support collection, the items are still Models until mapped.
            // Wait, actually toBase() on a relation query returns raw objects, but on a loaded collection it just changes the collection type.
            // Ideally, use the collection directly but cast to base to avoid the merge issue.
            'title' => 'Diagnosis: '.($d->icdCode->code ?? 'Unknown'),
            'subtitle' => $d->icdCode->description ?? '',
            'icon' => 'o-clipboard-document-check',
            'color' => 'bg-error text-white',
            'border' => 'border-error',
            'details' => __($d->condition_status),
        ]);

        // 2. Therapy Sessions (Add ->toBase())
        $sessions = $this->patient->therapySessions->toBase()->map(fn ($s) => [
            'id' => $s->id,
            'type' => 'session',
            'date' => $s->scheduled_at,
            'title' => __('Session: :session', ['session' => $s->focus_area]),
            'subtitle' => __($s->status),
            'icon' => 'o-calendar',
            'color' => $s->status === 'Completed' ? 'bg-primary text-white' : 'bg-base-200 text-gray-500',
            'border' => $s->status === 'Completed' ? 'border-primary' : 'border-base-300',
            'details' => $s->duration_minutes.__(' mins'),
        ]);

        // 3. Invoices (Add ->toBase())
        $invoices = $this->patient->invoices->toBase()->map(fn ($i) => [
            'id' => $i->id,
            'type' => 'invoice',
            'date' => $i->issued_date,
            'title' => __('Invoice Generated'),
            'subtitle' => '#'.$i->invoice_number,
            'icon' => 'o-banknotes',
            'color' => 'bg-success text-white',
            'border' => 'border-success',
            'details' => format_currency($i->total_amount),
        ]);

        // Merge, Sort, and Return
        return $diagnoses
            ->merge($sessions)
            ->merge($invoices)
            ->sortByDesc('date')
            ->values()
            ->all();
    }

    #[Computed]
    public function billingStats()
    {
        // ... existing billing logic ...
        $invoices = $this->patient->invoices;

        return [
            'total_billed' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->whereIn('status', ['Paid', 'Partially Paid'])->sum('total_amount') - $invoices->sum('amount_due'),
            'outstanding' => $invoices->sum('amount_due'),
            'count' => $invoices->count(),
        ];
    }

    public function render()
    {
        return view('livewire.patient.patient-full-report');
    }
}
