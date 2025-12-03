<?php

namespace App\Livewire\Patient;

use App\Models\Diagnosis;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\Builder;
use Mary\Traits\Toast;

class PatientDiagnosisList extends Component
{
    use Toast, WithPagination;

    public Patient $patient;

    // --- Filters & Sort ---
    public string $search = '';
    public string $statusFilter = ''; // '' = All, 'Active', 'Resolved', etc.
    public string $typeFilter = '';   // '' = All, 'Primary', 'Secondary'
    public array $sortBy = ['column' => 'start_date', 'direction' => 'desc'];

    // --- Delete State ---
    public $diagnosisToDeleteId;
    public $showDeleteModal = false;

    // --- Options (from constants) ---
    public $diagnosisTypes;
    public $conditionStatuses;

    public function mount(Patient $patient)
    {
        $this->patient = $patient;
        $this->diagnosisTypes = config('constants.DIAGNOSIS_TYPE');
        $this->conditionStatuses = config('constants.DIAGNOSIS_CONDITION_STATUS');
    }

    // --- Headers ---
    public function headers(): array
    {
        return [
            ['key' => 'icdCode.code', 'label' => __('ICD Code'), 'class' => 'font-mono font-bold w-24'],
            ['key' => 'icdCode.description', 'label' => __('Diagnosis'), 'class' => 'w-1/3'],
            ['key' => 'type', 'label' => __('Type'), 'class' => 'text-center'],
            ['key' => 'condition_status', 'label' => __('Status'), 'class' => 'text-center'],
            ['key' => 'start_date', 'label' => __('Onset Date'), 'class' => 'hidden md:table-cell'],
            ['key' => 'user.name', 'label' => __('Physician'), 'class' => 'hidden lg:table-cell text-xs'],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ];
    }

    // --- Actions ---

    public function sort($column)
    {
        if ($this->sortBy['column'] === $column) {
            $this->sortBy['direction'] = $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy['column'] = $column;
            $this->sortBy['direction'] = 'asc';
        }
    }

    public function confirmDelete($id)
    {
        $this->diagnosisToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->diagnosisToDeleteId) {
            $diagnosis = Diagnosis::find($this->diagnosisToDeleteId);
            $diagnosis?->delete();
            $this->success(__('Deleted'), __('Diagnosis record removed.'));
        }
        
        $this->showDeleteModal = false;
        $this->diagnosisToDeleteId = null;
    }

    // --- Computed Data ---

    #[Computed]
    public function diagnoses()
    {
        return Diagnosis::query()
            ->where('patient_id', $this->patient->id)
            ->with(['icdCode', 'user'])
            ->when($this->search, function (Builder $q) {
                $q->whereHas('icdCode', function ($i) {
                    $i->where('code', 'like', "%$this->search%")
                      ->orWhere('description', 'like', "%$this->search%");
                })->orWhere('description', 'like', "%$this->search%");
            })
            ->when($this->statusFilter, fn($q) => $q->where('condition_status', $this->statusFilter))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);
    }

    #[Computed]
    public function stats()
    {
        // Efficiently count without loading all models
        $base = Diagnosis::where('patient_id', $this->patient->id);
        
        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('condition_status', 'Active')->count(),
            'primary' => (clone $base)->where('type', 'Primary')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.diagnosis.patient-diagnosis-list');
    }
}