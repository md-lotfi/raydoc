<?php

namespace App\Livewire\Patient;

use App\Models\Diagnosis;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class PatientDiagnosisList extends Component
{
    use Toast, WithPagination;

    public $patient;

    public $diagnosisToDeleteId;

    public $showDeleteModal;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($userId)
    {
        $this->diagnosisToDeleteId = $userId;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if (is_null($this->diagnosisToDeleteId)) {
            // Should not happen, but a safeguard
            $this->error(
                title: 'No diagnosis selected.',
                description: 'Please select a diagnosis before deleting',                  // optional (text)

                timeout: 3000,                      // optional (ms)
                redirectTo: null                    // optional (uri)
            );

            return;
        }

        $patient = Diagnosis::find($this->diagnosisToDeleteId);

        if (! $patient) {
            $this->error(
                title: 'No patient selected.',
                description: 'Please select a patient before deleting',                  // optional (text)

                timeout: 3000,                      // optional (ms)
                redirectTo: null                    // optional (uri)
            );
            // Close the modal and clear the ID
            $this->showDeleteModal = false;
            $this->diagnosisToDeleteId = null;

            return;
        }
        $patient->delete();

        // 3. Close modal, reset state, and notify
        $this->showDeleteModal = false;
        $this->diagnosisToDeleteId = null;

        $this->success(
            title: 'Patient saved.',
            description: 'Diagnosis deleted successfully!',                  // optional (text)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );

        $this->resetPage();
    }

    public function mount(Patient $patient)
    {
        $this->patient = $patient;
    }

    public function render()
    {
        $query = Diagnosis::query()->where('patient_id', $this->patient->id);
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('condition_status', 'like', '%'.$this->search.'%')
                    ->orWhere('type', 'like', '%'.$this->search.'%');
            });
        }

        $diagnoses = $query->paginate(5);

        return view('livewire.diagnosis.patient-diagnosis-list', compact('diagnoses'));
    }
}
