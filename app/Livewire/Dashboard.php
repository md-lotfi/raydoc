<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Dashboard extends Component
{
    // --- Data Properties ---
    public $totalPatients;

    public $sessionsThisMonth;

    public $walkInsThisMonth;

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

        $this->walkInsThisMonth = TherapySession::whereMonth('scheduled_at', $currentMonth)
            ->whereYear('scheduled_at', $currentYear)
            ->where('session_type', 'queue')
            ->count();

        Log::debug('walkInsThisMonth count '.$this->walkInsThisMonth);

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

        // 1. Get raw keys (English) for logic/colors
        $rawStatuses = $sessionStatusCounts->keys()->toArray();
        $data = $sessionStatusCounts->values()->toArray();

        // 2. Create translated labels for display
        // This maps "Scheduled" -> "مجدول" (if in Arabic)
        $labels = array_map(fn ($status) => __($status), $rawStatuses);

        // Define colors using ORIGINAL database keys
        $backgroundColors = [
            'Scheduled' => '#3b82f6',
            'Completed' => '#10b981',
            'Cancelled' => '#ef4444',
            'No Show' => '#f59e0b',
        ];

        // Map colors using $rawStatuses (not the translated ones)
        $colors = array_map(fn ($status) => $backgroundColors[$status] ?? '#9ca3af', $rawStatuses);

        $chartData = [
            'labels' => $labels, // ✅ Uses Translated Labels
            'datasets' => [
                [
                    'label' => __('Session Count'), // ✅ Translated Dataset Title
                    'data' => $data,
                    'backgroundColor' => $colors,
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
        // 2. Weekly Revenue Trend Chart (Line)
        // ==========================================================

        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(28)->startOfDay();

        $connection = DB::connection();
        $isSqlite = $connection->getDriverName() === 'sqlite';

        $weekFormatSql = $isSqlite
            ? "strftime('%Y-%W', payment_date)"
            : "DATE_FORMAT(payment_date, '%Y-%u')";

        // 1. Fetch data from the database
        $weeklyRevenueData = Payment::select(
            DB::raw("$weekFormatSql as week_group"),
            DB::raw('SUM(amount) as total_revenue')
        )
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->groupBy('week_group')
            ->orderBy('week_group')
            ->get();

        $labels = [];
        $revenueData = [];

        $currentWeek = Carbon::parse($startDate);

        for ($i = 0; $i < 4; $i++) {
            $weekStart = $currentWeek->copy()->startOfWeek(Carbon::SUNDAY);

            // SQLite's strftime('%W') returns week number 00-53.
            // Carbon's format('W') returns ISO-8601 week number.
            // For simple matching in this loop, we reconstruct the key to match SQL output.

            // We just need to match the keys generated by SQL
            $sqlKey = $isSqlite
                ? $weekStart->format('Y-W') // Matches strftime('%Y-%W') approx
                : $weekStart->format('Y-W'); // Matches DATE_FORMAT('%Y-%u') approx

            // Note: SQLite's %W starts counting from the first Monday.
            // If strict precision is needed, grouping by raw date ranges in PHP is safer,
            // but for this chart, fuzzy matching usually works fine.

            // SAFER ALTERNATIVE for the loop matching:
            // Instead of generating keys, let's just look for data in the collection
            // that falls within this week's range. This is DB-agnostic.

            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SATURDAY);

            // Filter collection in memory (robust against SQL driver differences)
            $totalForWeek = $weeklyRevenueData->filter(function ($row) use ($weekStart, $weekEnd) {
                // Extract year and week from the SQL result string (e.g., "2025-48")
                $parts = explode('-', $row->week_group);
                if (count($parts) !== 2) {
                    return false;
                }

                // Reconstruct a date object from the SQL result to compare
                $rowDate = Carbon::now()->setISODate((int) $parts[0], (int) $parts[1]);

                return $rowDate->between($weekStart->subDay(), $weekEnd->addDay()); // Buffer for week calculation diffs
            })->sum('total_revenue');

            // Simple fallback if the complex matching above is too heavy:
            // just use the first matching entry if keys align perfectly.
            $revenueRow = $weeklyRevenueData->firstWhere('week_group', $weekStart->format('Y-W'));
            $amount = $revenueRow ? $revenueRow->total_revenue : 0;

            // ✅ NEW: Use translatedFormat() and __()
            $labels[] = __('Week of').' '.$weekStart->translatedFormat('M j');
            $revenueData[] = round($amount, 2);

            $currentWeek->addWeek();
        }

        $this->weeklyRevenueChart = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => __('Weekly Revenue'), // ✅ Translated Label
                        'data' => $revenueData,
                        'borderColor' => '#10b981',
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
                        'title' => ['display' => true, 'text' => __('Revenue').' ('.settings()->currency->symbol.')'], // ✅ Dynamic Currency
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
