<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    // --- Data Properties ---
    public $totalPatients;

    public $sessionsThisMonth;

    public $pendingInvoicesCount;

    public $revenueLast30Days;

    public $recentUnbilledSessions;

    public $absentPatientsThisMonth;

    public array $sessionStatusChart = []; // Renamed and structured for Pie/Doughnut

    public array $weeklyRevenueChart = []; // Renamed and structured for Line

    public function mount()
    {
        // Calculate the start and end dates for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        // Get current month/year for filtering
        $currentMonth = date('m');
        $currentYear = date('Y');

        // --- 1. Fetch Key Stats ---
        $this->totalPatients = Patient::count();

        $this->sessionsThisMonth = TherapySession::whereMonth('scheduled_at', $currentMonth)
            ->whereYear('scheduled_at', $currentYear)
            ->count();

        // ✅ NEW CALCULATION: Absent Patients (No Show Sessions) this Month
        // We count the number of sessions marked 'No Show' this month.
        $this->absentPatientsThisMonth = TherapySession::where('status', 'No Show')
            ->whereMonth('scheduled_at', $currentMonth)
            ->whereYear('scheduled_at', $currentYear)
            ->count();

        $this->pendingInvoicesCount = Invoice::where('status', 'Sent')
            ->where('amount_due', '>', 0)
            ->count();

        // Calculate revenue (total paid) in the last 30 days
        // NOTE: Uses placeholder logic from original code.
        $this->revenueLast30Days = Invoice::whereIn('status', ['Paid', 'Partially Paid'])
            ->whereBetween('issued_date', [$startDate, $endDate])
            ->sum(DB::raw('total_amount - amount_due')); // Total paid amount

        $this->recentUnbilledSessions = TherapySession::where('billing_status', 'Pending')
            ->where('status', 'Completed') // Only include completed sessions
            ->with('patient')
            ->orderBy('actual_end_at', 'desc') // Sort by when they actually finished
            ->take(5)
            ->get();

        // --- 2. Prepare Chart Data (Mock/Placeholder) ---
        $this->loadChartData();
    }

    private function loadChartData()
    {
        // ==========================================================
        // 1. Session Status Distribution Chart (Pie/Doughnut)
        // ==========================================================
        $sessionStatusCounts = TherapySession::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $labels = $sessionStatusCounts->keys()->toArray();
        $data = $sessionStatusCounts->values()->toArray();

        // Define colors for visual appeal (adjust as needed)
        $backgroundColors = [
            'Scheduled' => '#3b82f6', // Blue (Info)
            'Completed' => '#10b981', // Green (Success)
            'Cancelled' => '#ef4444', // Red (Error)
            'No Show' => '#f59e0b',   // Amber (Warning)
        ];

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Session Count',
                    'data' => $data,
                    // Map status labels to colors, defaulting to a gray color
                    'backgroundColor' => array_map(fn ($status) => $backgroundColors[$status] ?? '#9ca3af', $labels),
                ],
            ],
        ];

        $this->sessionStatusChart = [
            'type' => 'pie',
            'data' => $chartData,
            'options' => [
                'responsive' => true,
                'plugins' => ['legend' => ['position' => 'top']],
            ],
        ];

        // ==========================================================
        // 2. Weekly Revenue Trend Chart (Line) - ACTUAL DATA
        // ==========================================================

        // Define the period: Last 4 full weeks (28 days)
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(28)->startOfDay();

        // 1. Fetch data from the database
        // We group payments by week number and sum the amount paid.
        $weeklyRevenueData = Payment::select(
            // Cast the date to the week start date for better grouping/labeling
            DB::raw('DATE_FORMAT(payment_date, "%Y-%u") as week_group'),
            DB::raw('SUM(amount) as total_revenue')
        )
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->groupBy('week_group')
            ->orderBy('week_group')
            ->get();

        // 2. Prepare the chart labels and data arrays
        $labels = [];
        $revenueData = [];

        // Generate labels and data for the last 4 weeks dynamically
        $currentWeek = Carbon::parse($startDate);

        for ($i = 0; $i < 4; $i++) {
            $weekStart = $currentWeek->copy()->startOfWeek(Carbon::SUNDAY); // Assuming week starts Sunday
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SATURDAY);

            $weekFormat = $weekStart->format('Y-W'); // Get Year-Week format to match DB grouping

            // 3. Find the revenue for this week, or use 0 if no payment was made
            $revenueRow = $weeklyRevenueData->firstWhere('week_group', $weekStart->format('Y-u'));

            $labels[] = 'Week of '.$weekStart->format('M j');
            $revenueData[] = round($revenueRow->total_revenue ?? 0, 2);

            $currentWeek->addWeek();
        }

        // If you prefer 'Week 1, Week 2, Week 3, Week 4' simple labels:
        // $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];

        $this->weeklyRevenueChart = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Weekly Revenue ($)',
                        'data' => $revenueData, // ⬅️ NOW USING ACTUAL DATA
                        'borderColor' => '#10b981', // Green for revenue
                        'tension' => 0.3,
                        'fill' => false,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => ['display' => true, 'text' => 'Revenue ($)'],
                    ],
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
