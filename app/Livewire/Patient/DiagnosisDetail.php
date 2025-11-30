<?php

namespace App\Livewire\Patient;

use App\Models\Diagnosis;
use Livewire\Component;

class DiagnosisDetail extends Component
{
    public Diagnosis $diagnosis;

    public function mount(Diagnosis $diagnosis)
    {
        $this->diagnosis = $diagnosis->load(['patient', 'icdCode', 'user']);
    }

    public function render()
    {
        return view('livewire.diagnosis.diagnosis-detail', [
            'diagnosis' => $this->diagnosis,
        ]);
    }
}
