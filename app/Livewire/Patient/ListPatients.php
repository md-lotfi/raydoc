<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class ListPatients extends Component
{
    use Toast, WithPagination;

    public $showDeleteModal = false;

    public $patientToDeleteId = null;

    public string $search = '';

    public function confirmDelete($userId)
    {
        $this->patientToDeleteId = $userId;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if (is_null($this->patientToDeleteId)) {
            // Should not happen, but a safeguard
            $this->error(
                title: 'No patient selected.',
                description: 'Please select a patient before deleting',                  // optional (text)

                timeout: 3000,                      // optional (ms)
                redirectTo: null                    // optional (uri)
            );

            return;
        }

        $patient = Patient::find($this->patientToDeleteId);

        if (! $patient) {
            $this->error(
                title: 'No patient selected.',
                description: 'Please select a patient before deleting',                  // optional (text)

                timeout: 3000,                      // optional (ms)
                redirectTo: null                    // optional (uri)
            );
            // Close the modal and clear the ID
            $this->showDeleteModal = false;
            $this->patientToDeleteId = null;

            return;
        }

        $patientName = $patient->first_name; // Store name before deletion
        $patient->delete();

        // 3. Close modal, reset state, and notify
        $this->showDeleteModal = false;
        $this->patientToDeleteId = null;

        $this->success(
            title: 'Patient saved.',
            description: 'Patient '.$patientName.' deleted successfully!',                  // optional (text)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );

        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Patient::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone_number', 'like', '%'.$this->search.'%');
            });
        }
        $patients = $query->with('user')->paginate(5);

        // Log::debug(json_encode($patients));

        return view('livewire.patient.list-patients', compact('patients'));
    }

    public function togglePatientStatus($id)
    {
        $patient = Patient::find($id);
        $patient->is_active = $patient->is_active ? 0 : 1;
        $patient->save();
    }

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function mount() {}
}
