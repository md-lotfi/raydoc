<?php

namespace App\Livewire\Patient;

use App\Models\Diagnosis;
use App\Models\IcdCode;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Mary\Traits\Toast;

class PatientDiagnosisForm extends Component
{
    use Toast;

    public ?int $diagnosisId = null; // Edit mode ID

    public Patient $patient;

    public ?Diagnosis $diagnosis = null;

    // Form Properties
    public $icd_searchable_id;

    public $description;

    public $start_date;

    public $type;

    public $condition_status = 'Active';

    // Options
    public $icdCodes = [];

    public $diagnosisTypes = [];

    public $conditionStatuses = [];

    public function mount(Patient $patient, ?Diagnosis $diagnosis = null)
    {
        $this->patient = $patient;
        $this->diagnosis = $diagnosis;

        // ✅ FIX: Translate the 'name' for display, keep 'id' for database
        $this->diagnosisTypes = collect(config('constants.DIAGNOSIS_TYPE', ['Primary', 'Secondary', 'Historical']))
            ->map(fn ($i) => ['id' => $i, 'name' => __($i)]);

        $this->conditionStatuses = collect(config('constants.DIAGNOSIS_CONDITION_STATUS', ['Active', 'Resolved', 'Chronic', 'In Remission']))
            ->map(fn ($i) => ['id' => $i, 'name' => __($i)]);

        if ($diagnosis) {
            $this->diagnosisId = $diagnosis->id;
            $this->icd_searchable_id = $diagnosis->icd_code_id;
            $this->description = $diagnosis->description;
            $this->start_date = $diagnosis->start_date?->format('Y-m-d');
            $this->type = $diagnosis->type;
            $this->condition_status = $diagnosis->condition_status;
        } else {
            $this->start_date = now()->format('Y-m-d');
        }

        $this->searchIcd();
    }

    // Search logic for MaryUI Choices
    public function searchIcd(string $value = '')
    {
        $selectedOption = IcdCode::where('id', $this->icd_searchable_id)->get();

        $this->icdCodes = IcdCode::query()
            ->where('code', 'like', "%$value%")
            ->orWhere('description', 'like', "%$value%")
            ->take(10)
            ->orderBy('code')
            ->get()
            ->merge($selectedOption);
    }

    protected function rules()
    {
        // Flatten arrays for validation rules
        $types = collect($this->diagnosisTypes)->pluck('id')->toArray();
        $statuses = collect($this->conditionStatuses)->pluck('id')->toArray();

        return [
            'icd_searchable_id' => ['required', 'integer', 'exists:icd_codes,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date', 'before_or_equal:today'],
            'type' => ['required', 'string', Rule::in($types)],
            'condition_status' => ['required', 'string', Rule::in($statuses)],
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'patient_id' => $this->patient->id,
            'icd_code_id' => $this->icd_searchable_id,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'type' => $this->type,
            'condition_status' => $this->condition_status,
            'user_id' => Auth::id(),
        ];

        if ($this->diagnosisId) {
            $this->diagnosis->update($data);
            $message = __('Diagnosis updated successfully.');
        } else {
            Diagnosis::create($data);
            $message = __('New diagnosis added to patient record.');
        }

        $this->success(__('Saved'), $message);

        return redirect()->route('patient.diagnosis.list', ['patient' => $this->patient->id]);
    }

    public function render()
    {
        return view('livewire.diagnosis.patient-diagnosis-form');
    }
}
