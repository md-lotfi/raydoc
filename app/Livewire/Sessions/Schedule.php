<?php

namespace App\Livewire\Sessions;

use App\Models\Patient;
use App\Models\TherapySession;
use Carbon\Carbon; //
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Schedule extends Component
{
    use WithPagination;

    #[Url]
    public $date;

    // Session Detail Drawer
    public $selectedSession = null;

    public $showDrawer = false;

    // New Session Modal
    public $showCreateModal = false;

    public $patientSearch = '';

    public $foundPatients = [];

    public function mount()
    {
        $this->date = $this->date ?? now()->translatedFormat('Y-m-d');
    }

    // --- Search Logic for New Session ---

    // Triggered when user types in the modal
    public function updatedPatientSearch($value)
    {
        if (strlen($value) < 2) {
            $this->foundPatients = [];

            return;
        }

        $this->foundPatients = Patient::query()
            ->where('first_name', 'like', "%{$value}%")
            ->orWhere('last_name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->limit(5)
            ->get();
    }

    public function createSessionForPatient($patientId)
    {
        // Redirect to the existing route that you have in web.php
        return redirect()->route('patient.session.create', ['patient' => $patientId]);
    }

    public function updatedDate($value)
    {
        if (empty($value)) {
            $this->date = now()->translatedFormat('Y-m-d');
        }
    }

    // --- Existing Schedule Logic ---

    public function previousWeek()
    {
        $this->date = Carbon::parse($this->date)->subWeek()->translatedFormat('Y-m-d');
    }

    public function nextWeek()
    {
        $this->date = Carbon::parse($this->date)->addWeek()->translatedFormat('Y-m-d');
    }

    public function today()
    {
        $this->date = now()->translatedFormat('Y-m-d');
    }

    public function selectSession($sessionId)
    {
        $this->selectedSession = TherapySession::with('patient', 'user')->find($sessionId);
        $this->showDrawer = true;
    }

    public function render()
    {
        $anchorDate = Carbon::parse($this->date);
        // 1. Determine Start/End days based on Locale
        // Arabic: Saturday -> Friday
        // Others: Monday -> Sunday (or adjust to Carbon::SUNDAY for US English)
        $startDay = app()->getLocale() === 'ar' ? Carbon::SATURDAY : Carbon::MONDAY;
        $endDay = app()->getLocale() === 'ar' ? Carbon::FRIDAY : Carbon::SUNDAY;
        $startOfWeek = $anchorDate->copy()->startOfWeek($startDay);
        $endOfWeek = $anchorDate->copy()->endOfWeek($endDay);

        $sessions = TherapySession::query()
            ->with(['patient', 'user'])
            ->whereBetween('scheduled_at', [$startOfWeek, $endOfWeek])
            ->orderBy('scheduled_at')
            ->get();

        $weekGrid = [];
        $currentDay = $startOfWeek->copy();

        for ($i = 0; $i < 7; $i++) {
            $dateString = $currentDay->format('Y-m-d');
            $weekGrid[$dateString] = [
                'day_name' => $currentDay->translatedFormat('D'),
                'day_number' => $currentDay->translatedFormat('d'),
                'is_today' => $currentDay->isToday(),
                'sessions' => $sessions->filter(fn ($s) => $currentDay->isSameDay($s->scheduled_at)),
            ];
            $currentDay->addDay();
        }

        return view('livewire.schedule', [
            'weekGrid' => $weekGrid,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }
}
