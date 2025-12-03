<?php

namespace App\Livewire\Doctor;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads; // ✅ Import Flux
use Mary\Traits\Toast;

class DoctorForm extends Component
{
    use Toast, WithFileUploads;

    public ?User $doctor = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?string $dateOfBirth = null;

    public ?string $gender = null;

    public ?string $address = null;

    public ?string $city = null;

    public ?string $phone = null;

    public $avatar;

    public $existingAvatar;

    public array $permissions = [];

    public array $availablePermissions = [];

    public bool $selectAllPermissions = false;

    public bool $showPassword = false;

    public function mount(?User $doctor = null)
    {
        $this->availablePermissions = getDoctorPermissions();

        if (! empty($doctor)) {
            $this->doctor = $doctor;
            $this->name = $this->doctor->name;
            $this->email = $this->doctor->email;
            $this->dateOfBirth = $this->doctor->date_of_birth?->format('Y-m-d');
            $this->gender = $this->doctor->gender;
            $this->address = $this->doctor->address;
            $this->city = $this->doctor->city;
            $this->phone = $this->doctor->phone;
            $this->existingAvatar = $this->doctor->avatar;
            $this->permissions = $this->doctor->getAllPermissions()->pluck('name')->toArray();
            $this->selectAllPermissions = count($this->permissions) === count($this->availablePermissions);
        } else {
            $this->permissions = $this->availablePermissions;
            $this->selectAllPermissions = true;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->doctor?->id)],
            'password' => [$this->doctor ? 'nullable' : 'required', 'string', 'min:8', 'same:passwordConfirmation'],
            'dateOfBirth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female,Other',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'permissions' => 'nullable|array',
            'avatar' => 'nullable|image|max:2048',
        ];
    }

    public function generatePassword(): void
    {
        $randomPass = Str::random(12);
        $this->password = $randomPass;
        $this->passwordConfirmation = $randomPass;
        $this->showPassword = true;
        $this->success(__('Generated'), __('Password generated: ').$randomPass);
    }

    public function updatedSelectAllPermissions($value)
    {
        $this->permissions = $value ? $this->availablePermissions : [];
    }

    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'date_of_birth' => $this->dateOfBirth,
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
        ];

        if (! empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->avatar) {
            if ($this->doctor && $this->doctor->avatar) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->doctor->avatar));
            }
            $path = $this->avatar->store('avatars', 'public');
            $userData['avatar'] = Storage::url($path);
        }

        if ($this->doctor) {
            $this->doctor->update($userData);
            $user = $this->doctor;
            $message = __('Doctor profile updated successfully.');
        } else {
            $user = User::create($userData);
            $user->assignRole(config('constants.ROLES.DOCTOR'));
            $message = __('Doctor created successfully.');
        }

        if (! empty($this->permissions)) {
            $user->syncPermissions($this->permissions);
        }

        $this->success(__('Success'), __('Success: ').$message);

        return redirect()->route('doctors.list');
    }

    public function render()
    {
        return view('livewire.doctor-form');
    }
}
