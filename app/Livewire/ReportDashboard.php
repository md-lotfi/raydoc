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
    public string $reportType = 'revenue_summary'; // Default report

    public string $startDate;

    public string $endDate;

    // --- Report Data ---
    public $reportData = [];

    public array $chartData = [];

    public $reportTitle = '';

    // Define available reports
    public array $availableReports = [
        ['name' => 'Revenue Summary', 'id' => 'revenue_summary'],
        ['name' => 'Session Completion Rate', 'id' => 'completion_rate'],
        ['name' => 'Patient Growth', 'id' => 'patient_growth'],
        ['name' => 'Diagnosis Frequency', 'id' => 'diagnosis_frequency'],
    ];

    public function mount()
    {
        // Set default date range: Last 30 days
        $this->endDate = Carbon::now()->toDateString();
        $this->startDate = Carbon::now()->subDays(30)->toDateString();
        $this->generateReport();
    }

    // Listeners to regenerate the report when inputs change
    public function updated($field)
    {
        if (in_array($field, ['reportType', 'startDate', 'endDate'])) {
            $this->generateReport();
        }
    }

    /**
     * Main method to generate the selected report.
     */
    public function generateReport()
    {
        $reportIds = collect($this->availableReports)->pluck('id')->implode(',');

        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'reportType' => 'required|in:'.$reportIds, // Validate using the extracted IDs
        ]);

        // Find the friendly name for the title
        $reportName = collect($this->availableReports)->firstWhere('id', $this->reportType)['name'] ?? 'Unknown Report';

        $this->reportTitle = $reportName.
                             ' (From '.Carbon::parse($this->startDate)->format('M j, Y').
                             ' to '.Carbon::parse($this->endDate)->format('M j, Y').')';

        // Reset data before running a new report
        $this->reportData = [];
        $this->chartData = [];

        switch ($this->reportType) {
            case 'revenue_summary':
                $this->runRevenueSummaryReport();
                break;
            case 'completion_rate':
                $this->runSessionCompletionRateReport();
                break;
                // ✅ NEW CASES
            case 'patient_growth':
                $this->runPatientGrowthReport();
                break;
            case 'diagnosis_frequency':
                $this->runDiagnosisFrequencyReport();
                break;
        }
    }

    protected function runPatientGrowthReport()
    {
        // Get the number of patients created per day/week in the range
        $newPatients = Patient::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select(
                DB::raw('count(id) as count'),
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date_group')
            )
            ->groupBy('date_group')
            ->orderBy('date_group')
            ->get();

        $totalNew = $newPatients->sum('count');

        // Calculate the total number of patients BEFORE the start date
        $totalPatientsBeforePeriod = Patient::where('created_at', '<', $this->startDate)->count();
        $totalPatientsAfterPeriod = $totalPatientsBeforePeriod + Patient::where('created_at', '<=', $this->endDate)->count();

        $this->reportData['summary'] = [
            'total_new_patients' => $totalNew,
            'starting_count' => $totalPatientsBeforePeriod,
            'ending_count' => $totalPatientsAfterPeriod,
        ];

        // Prepare chart data (Bar/Line chart showing new patient trend)
        $this->chartData = [
            'type' => 'bar',
            'data' => [
                'labels' => $newPatients->pluck('date_group'),
                'datasets' => [[
                    'label' => 'New Patients Registered',
                    'data' => $newPatients->pluck('count'),
                    'backgroundColor' => '#3b82f6', // Info blue
                ]],
            ],
            'options' => [
                'scales' => [
                    'y' => ['beginAtZero' => true, 'stepSize' => 1],
                ],
                'responsive' => true,
                'maintainAspectRatio' => false, // THIS IS KEY: Allows chart to fit parent height
                'plugins' => [
                    'legend' => ['display' => true],
                ],
            ],

        ];
    }

    // ------------------------------------------------------------------
    // ✅ NEW REPORT: DIAGNOSIS FREQUENCY
    // ------------------------------------------------------------------

    protected function runDiagnosisFrequencyReport()
    {
        // This assumes you have a relationship or a field on TherapySession or a linked model
        // that stores the main diagnosis/focus area (e.g., 'focus_area' or 'diagnosis_id')

        // ASSUMPTION: The primary diagnosis/focus area is stored in `focus_area` on the TherapySession model.

        $diagnosisCounts = TherapySession::whereBetween('scheduled_at', [$this->startDate, $this->endDate])
            ->whereNotNull('focus_area')
            ->select('focus_area', DB::raw('count(*) as count'))
            ->groupBy('focus_area')
            ->orderByDesc('count')
            ->get();

        $this->reportData['counts'] = $diagnosisCounts->map(function ($item) {
            return [
                'diagnosis' => $item->focus_area,
                'count' => $item->count,
            ];
        })->toArray();

        // Prepare chart data (Pie/Doughnut chart for frequency)
        $labels = $diagnosisCounts->pluck('focus_area')->toArray();
        $data = $diagnosisCounts->pluck('count')->toArray();

        // Define a set of appealing colors for the chart
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316'];

        $this->chartData = [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Session Count',
                    'data' => $data,
                    // Map diagnosis to colors (cycle through available colors)
                    'backgroundColor' => array_map(fn ($index) => $colors[$index % count($colors)], array_keys($labels)),
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false, // THIS IS KEY: Allows chart to fit parent height
                'plugins' => [
                    'legend' => ['display' => true],
                ],
            ],
        ];
    }

    /**
     * Calculates total revenue collected within the date range, grouped by week.
     */
    protected function runRevenueSummaryReport()
    {
        // Calculate total amount paid (assuming 'Payment' model or using invoice status)
        // We will sum the paid amount for invoices issued within the period.
        $revenue = Invoice::whereIn('status', ['Paid', 'Partially Paid'])
            ->whereBetween('issued_date', [$this->startDate, $this->endDate])
            ->select(
                DB::raw('SUM(total_amount - amount_due) as total_paid'),
                DB::raw('DATE_FORMAT(issued_date, "%Y-%m-%d") as date_group')
            )
            ->groupBy('date_group')
            ->orderBy('date_group')
            ->get();

        $totalRevenue = $revenue->sum('total_paid');

        $this->reportData['totals'] = [
            'total_revenue' => $totalRevenue,
            'period_days' => Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1,
            'average_daily' => $totalRevenue / (Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1),
        ];

        // Prepare chart data (Line chart showing revenue trend)
        $this->chartData = [
            'type' => 'line',
            'data' => [
                'labels' => $revenue->pluck('date_group'),
                'datasets' => [[
                    'label' => 'Revenue Collected ($)',
                    'data' => $revenue->pluck('total_paid'),
                    'borderColor' => '#10b981', // Green
                    'tension' => 0.3,
                    'fill' => false,
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false, // Prevents vertical overflow
                'plugins' => [
                    'legend' => ['display' => true],
                ],
                // Ensure scales also fit well
                'scales' => [
                    'x' => ['display' => true],
                    'y' => ['display' => true],
                ],

            ],
        ];
    }

    /**
     * Calculates the success/failure rate of sessions.
     */
    protected function runSessionCompletionRateReport()
    {
        $sessions = TherapySession::whereBetween('scheduled_at', [$this->startDate, $this->endDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalSessions = $sessions->sum();

        $completed = $sessions['Completed'] ?? 0;
        $failed = ($sessions['Cancelled'] ?? 0) + ($sessions['No Show'] ?? 0);
        $completionRate = $totalSessions > 0 ? round(($completed / $totalSessions) * 100, 2) : 0;

        $this->reportData['summary'] = [
            'total_sessions' => $totalSessions,
            'completed' => $completed,
            'failed' => $failed,
            'completion_rate' => $completionRate,
            'failure_rate' => 100 - $completionRate,
        ];

        // Prepare chart data (Doughnut chart for status distribution)
        $labels = $sessions->keys()->toArray();
        $data = $sessions->values()->toArray();
        $backgroundColors = [
            'Completed' => '#10b981', // Success
            'Scheduled' => '#3b82f6', // Info
            'Cancelled' => '#ef4444', // Error
            'No Show' => '#f59e0b',   // Warning
        ];

        $this->chartData = [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Session Status',
                    'data' => $data,
                    'backgroundColor' => array_map(fn ($status) => $backgroundColors[$status] ?? '#9ca3af', $labels),
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false, // THIS IS KEY: Allows chart to fit parent height
                'plugins' => [
                    'legend' => ['display' => true],
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.report-dashboard');
    }
}
