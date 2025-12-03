<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class ListPatients extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public int $perPage = 10;

    // Default Sort
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    // Filter States
    public bool $showFilterDrawer = false;

    public $statusFilter = null;

    public $genderFilter = null;

    // Detailed View & Deletion
    public bool $showDetailsDrawer = false;

    public ?Patient $selectedPatient = null;

    public $showDeleteModal = false;

    public $patientToDeleteId = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function sort($column)
    {
        if ($this->sortBy['column'] === $column) {
            $this->sortBy['direction'] = $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy['column'] = $column;
            $this->sortBy['direction'] = 'asc';
        }
    }

    public function togglePatientStatus($id)
    {
        $patient = Patient::find($id);
        if ($patient) {
            $patient->is_active = ! $patient->is_active;
            $patient->save();

            $statusMsg = $patient->is_active ? __('Active') : __('Inactive');
            $this->success(__('Status updated'), __('Patient is now :status', ['status' => $statusMsg]));
        }
    }

    public function showPatientDetails($id)
    {
        $this->selectedPatient = Patient::with(['user', 'diagnoses'])->find($id);
        $this->showDetailsDrawer = true;
    }

    public function confirmDelete($userId)
    {
        $this->patientToDeleteId = $userId;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->patientToDeleteId) {
            $patient = Patient::find($this->patientToDeleteId);
            if ($patient) {
                $patient->delete();
                $this->success(__('Deleted'), __('Patient :name has been deleted.', ['name' => $patient->first_name]));
            }
        }
        $this->showDeleteModal = false;
        $this->patientToDeleteId = null;
    }

    public function clearFilters()
    {
        $this->reset(['statusFilter', 'genderFilter', 'search']);
        $this->resetPage();
    }

    public function exportCsv()
    {
        $fileName = 'patients_export_'.date('Y-m-d_H-i').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Localized Headers
            fputcsv($handle, [
                __('ID'),
                __('First Name'),
                __('Last Name'),
                __('Email'),
                __('Phone'),
                __('Status'),
                __('Joined Date'),
            ]);

            Patient::query()->chunk(100, function ($patients) use ($handle) {
                foreach ($patients as $patient) {
                    fputcsv($handle, [
                        $patient->id,
                        $patient->first_name,
                        $patient->last_name,
                        $patient->email,
                        $patient->phone_number,
                        $patient->is_active ? __('Active') : __('Inactive'),
                        $patient->created_at->format('Y-m-d'),
                    ]);
                }
            });
            fclose($handle);
        }, $fileName);
    }

    public function render()
    {
        $query = Patient::query()->with('user');

        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone_number', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter !== null && $this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }
        if ($this->genderFilter) {
            $query->where('gender', $this->genderFilter);
        }

        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        $stats = [
            'total' => Patient::count(),
            'active' => Patient::where('is_active', 1)->count(),
            'inactive' => Patient::where('is_active', 0)->count(),
        ];

        return view('livewire.patient.list-patients', [
            'patients' => $query->paginate($this->perPage),
            'stats' => $stats,
        ]);
    }
}
