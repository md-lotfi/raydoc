<?php

namespace App\Livewire\Sessions;

use App\Models\TherapySession;
use Carbon\Carbon;
use Livewire\Component;

class Schedule extends Component
{
    public string $currentDate;

    public $weekStart;

    public $weekEnd;

    public $scheduleTitle;

    public function mount()
    {
        $this->currentDate = Carbon::now()->toDateString();
    }

    public function updatedCurrentDate()
    {
        // keep behaviour if you want to respond immediately
        // but render() will re-run anyway on property update
    }

    public function navigate($direction)
    {
        $date = Carbon::parse($this->currentDate);
        $date->{$direction === 'next' ? 'addWeek' : 'subWeek'}();
        $this->currentDate = $date->toDateString();
    }

    /**
     * Render — compute sessions here and pass to view.
     */
    public function render()
    {
        $date = Carbon::parse($this->currentDate);
        $this->weekStart = $date->copy()->startOfWeek(Carbon::SUNDAY);
        $this->weekEnd = $date->copy()->endOfWeek(Carbon::SATURDAY);

        $this->scheduleTitle = $this->weekStart->format('M j').' - '.$this->weekEnd->format('M j, Y');

        // Fetch sessions (an Eloquent collection of models)
        $sessions = TherapySession::whereBetween('scheduled_at', [$this->weekStart, $this->weekEnd])
            ->with('patient')
            ->orderBy('scheduled_at')
            ->get();

        // Group by day (this stays a Collection of Collections — but it's only passed to the view, not stored as a public prop)
        $sessionsByDay = $sessions->groupBy(function ($session) {
            return $session->scheduled_at->format('Y-m-d');
        });

        // Dispatch a browser event for the title (use dispatchBrowserEvent)
        // $this->dispatchBrowserEvent('schedule-updated', ['title' => $this->scheduleTitle]);

        return view('livewire.schedule', [
            'sessionsByDay' => $sessionsByDay,
        ]);
    }
}
