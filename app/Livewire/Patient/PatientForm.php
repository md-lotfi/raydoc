<?php

namespace App\Livewire\Patient;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast; // Added Toast trait for cleaner notifications

class PatientForm extends Component
{
    use Toast, WithFileUploads;

    public ?Patient $patient = null; // Store the model if editing

    // Form Fields
    public $first_name;

    public $last_name;

    public $date_of_birth;

    public $gender;

    public $phone_number;

    public $address;

    public $email;

    public $city;

    public $is_active = true; // Default to active for new patients

    // Avatar Management
    public $avatar;

    public $existingAvatar;

    // Options
    public $genderOptions;

    public function toJSON()
    {
        return [];
    }

    public function mount($id = null)
    {
        $this->genderOptions = collect(config('constants.GENDERS'))->map(fn ($g) => ['id' => $g, 'name' => $g]);

        if ($id) {
            $this->patient = Patient::findOrFail($id);
            $this->fill($this->patient->toArray());

            // ✅ FIX: Reset avatar so it doesn't hold the DB string path
            $this->avatar = null;

            // Map the DB path to the separate property for display
            $this->existingAvatar = $this->patient->avatar;

            // Format date for the input
            $this->date_of_birth = $this->patient->date_of_birth->format('Y-m-d');
        }
    }

    protected function rules()
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'date_of_birth' => 'required|date|before:today',
            'gender' => ['required', Rule::in(config('constants.GENDERS'))],
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|max:2048', // Increased limit to 2MB
        ];
    }

    public function save()
    {
        $this->validate();
        DB::beginTransaction();
        try {
            $data = [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'date_of_birth' => $this->date_of_birth,
                'gender' => $this->gender,
                'phone_number' => $this->phone_number,
                'address' => $this->address,
                'city' => $this->city,
                'is_active' => $this->is_active,
                'user_id' => Auth::id(), // Always track who touched it last
            ];

            // 1. Handle Avatar Upload
            if ($this->avatar) {
                // Delete old avatar if updating
                if ($this->patient && $this->patient->avatar) {
                    Storage::disk(config('app.default_disk'))->delete(str_replace('/storage/', '', $this->patient->avatar)); // public
                }

                $path = $this->avatar->store('avatars', config('app.default_disk'));

                Log::debug('storing avatar image to local '.$path);
                $data['avatar'] = Storage::url($path);
            }

            // 2. Create or Update
            if ($this->patient) {
                $this->patient->update($data);
                $message = __('Patient profile updated successfully.');
            } else {
                $this->patient = Patient::create($data);
                $message = __('New patient registered successfully.');
            }

            DB::commit();
            $this->success(__('Success'), $message);

            // 3. Redirect to List (or Detail view)
            return redirect()->route('patient.list');
        } catch (\Throwable $th) {
            Log::debug('save patient form error: '.$th->getMessage());
            Log::debug('save patient form statck trace: '.$th->getTraceAsString());
            DB::rollBack();
            $this->error(__('Error'), $th->getMessage());

            // return redirect()->route('patient.list');
        }
    }

    public function cancel()
    {
        return redirect()->route('patient.list');
    }

    public function render()
    {
        return view('livewire.patient.patient-form');
    }
}
