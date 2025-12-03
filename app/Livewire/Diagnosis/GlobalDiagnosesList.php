<?php

namespace App\Livewire\Diagnosis;

use App\Models\Diagnosis;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Mary\Traits\Toast;

class GlobalDiagnosesList extends Component
{
    use WithPagination, Toast;

    // --- Filters & Sort ---
    public string $search = '';
    public string $statusFilter = ''; 
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    // --- Modal & Delete ---
    public $showDeleteModal = false;
    public $diagnosisToDeleteId = null;

    // --- Headers ---
    public function headers(): array
    {
        return [
            ['key' => 'icdCode.code', 'label' => __('ICD Code'), 'class' => 'font-mono font-bold w-32'],
            ['key' => 'patient.last_name', 'label' => __('Patient')],
            ['key' => 'icdCode.description', 'label' => __('Condition'), 'class' => 'hidden md:table-cell w-1/3'],
            ['key' => 'type', 'label' => __('Type'), 'class' => 'text-center'],
            ['key' => 'condition_status', 'label' => __('Status'), 'class' => 'text-center'],
            ['key' => 'start_date', 'label' => __('Onset Date'), 'class' => 'hidden lg:table-cell'],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ];
    }

    // --- Actions ---

    public function sort($column)
    {
        // Toggle direction if same column, otherwise reset to asc
        $this->sortBy['direction'] = ($this->sortBy['column'] === $column && $this->sortBy['direction'] === 'asc') ? 'desc' : 'asc';
        $this->sortBy['column'] = $column;
    }

    public function confirmDelete($id)
    {
        $this->diagnosisToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->diagnosisToDeleteId) {
            Diagnosis::destroy($this->diagnosisToDeleteId);
            $this->success(__('Deleted'), __('Diagnosis record removed.'));
        }
        $this->showDeleteModal = false;
        $this->diagnosisToDeleteId = null;
    }

    public function render()
    {
        $query = Diagnosis::query()
            ->with(['patient', 'icdCode', 'user']);

        // 1. Search
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->whereHas('patient', function ($p) {
                    $p->where('first_name', 'like', "%$this->search%")
                      ->orWhere('last_name', 'like', "%$this->search%");
                })
                ->orWhereHas('icdCode', function ($i) {
                    $i->where('code', 'like', "%$this->search%")
                      ->orWhere('description', 'like', "%$this->search%");
                });
            });
        }

        // 2. Status Filter
        if ($this->statusFilter) {
            $query->where('condition_status', $this->statusFilter);
        }

        // 3. Sorting (Handle Relationships)
        $sortCol = $this->sortBy['column'];
        $sortDir = $this->sortBy['direction'];

        if ($sortCol === 'patient.last_name') {
            $query->join('patients', 'diagnoses.patient_id', '=', 'patients.id')
                  ->orderBy('patients.last_name', $sortDir)
                  ->select('diagnoses.*');
        } elseif ($sortCol === 'icdCode.code') {
             $query->join('icd_codes', 'diagnoses.icd_code_id', '=', 'icd_codes.id')
                  ->orderBy('icd_codes.code', $sortDir)
                  ->select('diagnoses.*');
        } else {
            $query->orderBy($sortCol, $sortDir);
        }

        // 4. Stats
        $stats = [
            'total' => Diagnosis::count(),
            'active' => Diagnosis::where('condition_status', 'Active')->count(),
            'resolved' => Diagnosis::where('condition_status', 'Resolved')->count(),
        ];

        return view('livewire.diagnosis.global-diagnoses-list', [
            'diagnoses' => $query->paginate(15),
            'stats' => $stats
        ]);
    }
}