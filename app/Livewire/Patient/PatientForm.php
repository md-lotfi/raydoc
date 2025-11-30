<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class PatientForm extends Component
{
    use WithFileUploads;

    public $patientId = null;

    public $first_name;

    public $last_name;

    public $date_of_birth;

    public $gender;

    public $phone_number;

    public $address;

    public $email;

    public $city;

    public $avatar;

    public $genderOptions;

    public $currentAvatarPath = null;

    protected function rules()
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            // Ensure gender is one of the valid options
            'gender' => ['required', Rule::in(config('constants.GENDERS'))],
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'city' => 'nullable|string',
            // Only validate if a new file is uploaded
            'avatar' => 'nullable|image|max:1024',
        ];
    }

    public function mount($id = null)
    {
        $this->genderOptions = config('constants.GENDERS');
        Log::debug("patient id is $id");
        if ($id) {
            $this->patientId = $id;
            $patient = Patient::findOrFail($id);

            // Load patient data into component properties
            $this->first_name = $patient->first_name;
            $this->last_name = $patient->last_name;
            $this->date_of_birth = date('Y-m-d', strtotime($patient->date_of_birth));
            $this->gender = $patient->gender;
            $this->phone_number = $patient->phone_number;
            $this->address = $patient->address;
            $this->email = $patient->email;
            $this->city = $patient->city;
            $this->currentAvatarPath = $patient->avatar; // Store existing avatar path
        }
    }

    // ⭐ KEY CHANGE: The save method now handles both Create and Update
    public function save()
    {
        $this->validate();

        try {
            $data = $this->getDataForSave();

            if ($this->patientId) {
                // UPDATE MODE
                $patient = Patient::findOrFail($this->patientId);
                $patient->update($data);
                $action = 'updated';
            } else {
                // CREATE MODE
                $patient = Patient::create($data);
                $action = 'added';
            }

            // Handle avatar after creation/update to get the correct path if new
            $this->handleAvatar($patient);

            // Reset form fields after creation, but not after updating
            if (! $this->patientId) {
                $this->reset(['first_name', 'last_name', 'date_of_birth', 'gender', 'phone_number', 'address', 'city', 'email', 'avatar']);
            }

            // Re-fetch the patient's name just in case
            $patient->refresh();

            session()->flash('success', 'Patient '.$patient->first_name.' '.$patient->last_name.' '.$action.' successfully.');

            $this->redirectRoute('patient.list', ['patientId' => $patient->id]);
        } catch (\Throwable $th) {
            Log::debug($th->getMessage());
            session()->flash('error', 'There was an error saving the patient: '.$th->getMessage());
        }
    }

    // Helper to gather data
    protected function getDataForSave()
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'city' => $this->city,
            'email' => $this->email,
            'user_id' => Auth::user()->id,
        ];
    }

    // Helper to handle avatar upload and path update
    protected function handleAvatar(Patient $patient)
    {
        if ($this->avatar) {
            // Delete old avatar if it exists
            if ($patient->avatar && str_starts_with($patient->avatar, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $patient->avatar);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new avatar
            $avatarPath = $this->avatar->store('avatars', 'public');
            $patient->avatar = Storage::url($avatarPath);
            $patient->save(); // Save the new avatar path
            $this->currentAvatarPath = $patient->avatar;
        }
        // Clear the temporary file upload
        $this->avatar = null;
    }

    // Updated method to handle preview logic for both new and existing avatars
    public function render()
    {
        return view('livewire.patient.patient-form');
    }
}
