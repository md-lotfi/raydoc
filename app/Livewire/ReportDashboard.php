<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportDashboard extends Component
{
    public string $reportType = 'revenue_summary';

    public string $startDate;

    public string $endDate;

    public string $activePreset = 'last_30_days'; // Track active preset for UI styling

    // --- Report Data ---
    public $reportData = [];

    public array $chartData = [];

    public array $tableHeaders = []; // For the detailed table

    public $tableRows = [];          // For the detailed table

    public $reportTitle = '';

    public array $availableReports = [];

    public function mount()
    {
        $this->availableReports = [
            ['name' => __('Revenue Summary'), 'id' => 'revenue_summary', 'icon' => 'o-currency-dollar'],
            ['name' => __('Session Completion'), 'id' => 'completion_rate', 'icon' => 'o-check-circle'],
            ['name' => __('Patient Growth'), 'id' => 'patient_growth', 'icon' => 'o-user-group'],
            ['name' => __('Diagnosis Trends'), 'id' => 'diagnosis_frequency', 'icon' => 'o-clipboard-document-list'],
        ];
        $this->setPeriod('last_30_days');
    }

    // --- Actions ---

    public function setPeriod($preset)
    {
        $this->activePreset = $preset;

        switch ($preset) {
            case 'today':
                $this->startDate = Carbon::today()->toDateString();
                $this->endDate = Carbon::today()->toDateString();
                break;
            case 'this_week':
                $this->startDate = Carbon::now()->startOfWeek()->toDateString();
                $this->endDate = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'this_month':
                $this->startDate = Carbon::now()->startOfMonth()->toDateString();
                $this->endDate = Carbon::now()->endOfMonth()->toDateString();
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'last_30_days':
            default:
                $this->startDate = Carbon::now()->subDays(29)->toDateString();
                $this->endDate = Carbon::now()->toDateString();
                break;
        }

        $this->generateReport();
    }

    public function updated($field)
    {
        // If user manually changes date, clear preset
        if (in_array($field, ['startDate', 'endDate'])) {
            $this->activePreset = 'custom';
            $this->generateReport();
        }

        if ($field === 'reportType') {
            $this->generateReport();
        }
    }

    public function generateReport()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $reportInfo = collect($this->availableReports)->firstWhere('id', $this->reportType);
        $this->reportTitle = $reportInfo['name'] ?? __('Report');

        // Reset
        $this->reportData = [];
        $this->chartData = [];
        $this->tableRows = [];

        switch ($this->reportType) {
            case 'revenue_summary':
                $this->runRevenueSummaryReport();
                break;
            case 'completion_rate':
                $this->runSessionCompletionRateReport();
                break;
            case 'patient_growth':
                $this->runPatientGrowthReport();
                break;
            case 'diagnosis_frequency':
                $this->runDiagnosisFrequencyReport();
                break;
        }
    }

    public function exportCsv()
    {
        $fileName = $this->reportType.'_'.$this->startDate.'.csv';
        $rows = $this->tableRows;
        $headers = collect($this->tableHeaders)->pluck('label')->toArray();

        return response()->streamDownload(function () use ($rows, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                // Extract only values corresponding to headers
                fputcsv($file, (array) $row);
            }
            fclose($file);
        }, $fileName);
    }

    // --- Helper for Comparison % ---
    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    // --- Report Logics ---

    protected function runRevenueSummaryReport()
    {
        // 1. Chart Data (Grouped by Date)
        $revenue = Invoice::whereIn('status', ['Paid', 'Partially Paid'])
            ->whereBetween('issued_date', [$this->startDate, $this->endDate])
            ->selectRaw('DATE(issued_date) as date, SUM(total_amount - amount_due) as daily_total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 2. KPIs with Comparison
        $currentTotal = $revenue->sum('daily_total');

        // Previous Period Calculation
        $daysDiff = Carbon::parse($this->startDate)->diffInDays($this->endDate) + 1;
        $prevStart = Carbon::parse($this->startDate)->subDays($daysDiff);
        $prevEnd = Carbon::parse($this->startDate)->subDay();

        $prevTotal = Invoice::whereIn('status', ['Paid', 'Partially Paid'])
            ->whereBetween('issued_date', [$prevStart, $prevEnd])
            ->sum(DB::raw('total_amount - amount_due'));

        $this->reportData = [
            'kpi' => [
                [
                    'label' => __('Total Revenue'),
                    'value' => $currentTotal,
                    'format' => 'currency',
                    'growth' => $this->calculateGrowth($currentTotal, $prevTotal),
                ],
                [
                    'label' => __('Invoices Count'),
                    'value' => $revenue->count(),
                    'format' => 'number',
                ],
            ],
        ];

        // 3. Chart
        $this->chartData = [
            'type' => 'line',
            'data' => [
                'labels' => $revenue->pluck('date'),
                'datasets' => [[
                    'label' => __('Revenue ($)'),
                    'data' => $revenue->pluck('daily_total'),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ]],
            ],
        ];

        // 4. Detailed Table Data
        $this->tableHeaders = [
            ['key' => 'invoice_number', 'label' => __('Invoice #')],
            ['key' => 'patient_name', 'label' => __('Patient')],
            ['key' => 'date', 'label' => __('Date')],
            ['key' => 'amount', 'label' => __('Amount Paid')],
        ];

        $this->tableRows = Invoice::with('patient')
            ->whereIn('status', ['Paid', 'Partially Paid'])
            ->whereBetween('issued_date', [$this->startDate, $this->endDate])
            ->latest('issued_date')
            ->get()
            ->map(fn ($inv) => [
                'invoice_number' => $inv->invoice_number,
                'patient_name' => $inv->patient->first_name.' '.$inv->patient->last_name,
                'date' => $inv->issued_date->format('Y-m-d'),
                'amount' => '$'.number_format($inv->total_amount - $inv->amount_due, 2),
            ]);
    }

    protected function runPatientGrowthReport()
    {
        $newPatients = Patient::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalNew = $newPatients->sum('count');

        // Comparison
        $daysDiff = Carbon::parse($this->startDate)->diffInDays($this->endDate) + 1;
        $prevNew = Patient::whereBetween('created_at', [
            Carbon::parse($this->startDate)->subDays($daysDiff),
            Carbon::parse($this->startDate)->subDay(),
        ])->count();

        $this->reportData = [
            'kpi' => [
                ['label' => __('New Patients'), 'value' => $totalNew, 'growth' => $this->calculateGrowth($totalNew, $prevNew)],
                ['label' => __('Avg per Day'), 'value' => round($totalNew / $daysDiff, 1)],
            ],
        ];

        $this->chartData = [
            'type' => 'bar',
            'data' => [
                'labels' => $newPatients->pluck('date'),
                'datasets' => [[
                    'label' => __('New Registrations'),
                    'data' => $newPatients->pluck('count'),
                    'backgroundColor' => '#3b82f6',
                    'borderRadius' => 4,
                ]],
            ],
        ];

        $this->tableHeaders = [
            ['key' => 'name', 'label' => __('Name')],
            ['key' => 'email', 'label' => __('Email')],
            ['key' => 'joined', 'label' => __('Joined Date')],
        ];

        $this->tableRows = Patient::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'name' => $p->first_name.' '.$p->last_name,
                'email' => $p->email,
                'joined' => $p->created_at->format('Y-m-d H:i'),
            ]);
    }

    protected function runSessionCompletionRateReport()
    {
        $sessions = TherapySession::whereBetween('scheduled_at', [$this->startDate, $this->endDate])->get();

        $counts = $sessions->groupBy('status')->map->count();
        $total = $sessions->count();
        $completed = $counts['Completed'] ?? 0;

        $rate = $total > 0 ? ($completed / $total) * 100 : 0;

        $this->reportData = [
            'kpi' => [
                ['label' => __('Total Sessions'), 'value' => $total],
                ['label' => __('Completion Rate'), 'value' => round($rate, 1).'%', 'color' => $rate > 80 ? 'text-success' : 'text-warning'],
            ],
        ];

        $this->chartData = [
            'type' => 'doughnut',
            'data' => [
                'labels' => [__('Completed'), __('Scheduled'), __('Cancelled'), __('No Show')],
                'datasets' => [[
                    'data' => [
                        $counts['Completed'] ?? 0,
                        $counts['Scheduled'] ?? 0,
                        $counts['Cancelled'] ?? 0,
                        $counts['No Show'] ?? 0,
                    ],
                    'backgroundColor' => ['#10b981', '#3b82f6', '#ef4444', '#f59e0b'],
                ]],
            ],
        ];

        $this->tableHeaders = [
            ['key' => 'date', 'label' => __('Date')],
            ['key' => 'patient', 'label' => __('Patient')],
            ['key' => 'status', 'label' => __('Status')],
        ];

        $this->tableRows = $sessions->map(fn ($s) => [
            'date' => Carbon::parse($s->scheduled_at)->format('Y-m-d H:i'),
            'patient' => $s->patient->first_name ?? 'Unknown',
            'status' => $s->status,
        ]);
    }

    protected function runDiagnosisFrequencyReport()
    {
        // Group by focus_area
        $data = TherapySession::whereBetween('scheduled_at', [$this->startDate, $this->endDate])
            ->whereNotNull('focus_area')
            ->select('focus_area', DB::raw('count(*) as count'))
            ->groupBy('focus_area')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $this->reportData = ['kpi' => [['label' => __('Unique Diagnoses'), 'value' => $data->count()]]];

        $this->chartData = [
            'type' => 'pie',
            'data' => [
                'labels' => $data->pluck('focus_area'),
                'datasets' => [[
                    'data' => $data->pluck('count'),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                ]],
            ],
        ];

        $this->tableHeaders = [['key' => 'name', 'label' => __('Diagnosis')], ['key' => 'count', 'label' => __('Frequency')]];
        $this->tableRows = $data->map(fn ($d) => ['name' => $d->focus_area, 'count' => $d->count]);
    }

    public function render()
    {
        return view('livewire.report-dashboard');
    }
}
