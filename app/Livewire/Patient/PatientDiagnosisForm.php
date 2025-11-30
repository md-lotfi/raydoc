<?php

namespace App\Livewire\Patient;

use App\Models\Diagnosis;
use App\Models\IcdCode;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PatientDiagnosisForm extends Component
{
    public ?int $diagnosisId = null; // used for edit

    public ?int $icd_searchable_id = null;

    // Patient Model Instance
    public Patient $patient;

    public $description;

    public $start_date;

    public $type;

    public $condition_status = 'Active'; // Default status

    // Data for select fields
    public $icdCodes;

    public $diagnosisTypes;

    public $conditionStatuses;

    public function searchIcd(string $value = '')
    {
        // Besides the search results, you must include on demand selected option
        $selectedOption = IcdCode::where('id', $this->icd_searchable_id)->get();

        $this->icdCodes = IcdCode::query()
            ->where('code', 'like', "%$value%")
            ->orWhere('description', 'like', "%$value%")
            ->take(5)
            ->orderBy('code')
            ->get()
            ->merge($selectedOption);     // <-- Adds selected option
    }

    protected function rules()
    {
        return [
            'icd_searchable_id' => ['required', 'integer', 'exists:icd_codes,id'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'type' => ['required', 'string', Rule::in($this->diagnosisTypes)],
            'condition_status' => ['required', 'string', Rule::in($this->conditionStatuses)],
        ];
    }

    /**
     * Mounts the component, receiving the Patient model instance.
     */
    public function mount(Patient $patient, ?Diagnosis $diagnosis)
    {
        $this->patient = $patient;
        if ($diagnosis) {
            $this->diagnosisId = $diagnosis->id;
            $this->icd_searchable_id = $diagnosis->icd_code_id;
            $this->description = $diagnosis->description;
            $this->start_date = date('Y-m-d', strtotime($diagnosis->start_date));
            $this->type = $diagnosis->type;
            $this->condition_status = $diagnosis->condition_status;

        }

        $this->searchIcd();
        // $this->icdCodes = IcdCode::select('id', 'code', 'description')->take(10)->get();
        $this->diagnosisTypes = config('constants.DIAGNOSIS_TYPE');
        $this->conditionStatuses = config('constants.DIAGNOSIS_CONDITION_STATUS');
    }

    protected function getDataForSave()
    {
        return [
            'patient_id' => $this->patient->id,
            'icd_code_id' => $this->icd_searchable_id,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'type' => $this->type,
            'condition_status' => $this->condition_status,
            'user_id' => Auth::id(),
        ];
    }

    public function save()
    {
        $this->validate();

        $data = $this->getDataForSave();

        if ($this->diagnosisId) {
            $diagnosis = Diagnosis::findOrFail($this->diagnosisId);
            $diagnosis->update($data);
            $action = 'Updated';
        } else {
            $diagnosis = Diagnosis::create($data);
            $action = 'Added';
        }

        $this->dispatch('diagnosis'.$action);

        // Clear the form fields after successful save
        $this->reset(['icd_searchable_id', 'description', 'start_date', 'type', 'condition_status']);

        // Optional: Send a success flash message
        session()->flash('success', 'Diagnosis recorded successfully for '.$this->patient->full_name);

        $this->redirectRoute('patient.diagnosis.list', ['patient' => $this->patient->id]);
    }

    public function render()
    {
        return view('livewire.diagnosis.patient-diagnosis-form');
    }
}
