<?php

namespace App\Livewire\Assistant;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class AssistantForm extends Component
{
    use Toast, WithFileUploads;

    public ?User $assistant = null; // Model if editing

    // Form Properties
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?string $dateOfBirth = null;

    public ?string $gender = null;

    public ?string $address = null;

    public ?string $city = null;

    public ?string $phone = null;

    // Avatar
    public $avatar;

    public $existingAvatar;

    // Permissions
    public array $permissions = [];

    public array $availablePermissions = [];

    public bool $selectAllPermissions = false;

    // UI State
    public bool $showPassword = false;

    public function mount(?User $assistant = null)
    {
        $this->availablePermissions = getAssistantPermissions();
        if (! empty($assistant)) {
            // 🟦 EDIT MODE
            $this->assistant = $assistant; // User::findOrFail($id);

            // Fill form
            $this->name = $this->assistant->name;
            $this->email = $this->assistant->email;
            $this->dateOfBirth = $this->assistant->date_of_birth?->format('Y-m-d');
            $this->gender = $this->assistant->gender;
            $this->address = $this->assistant->address;
            $this->city = $this->assistant->city;
            $this->phone = $this->assistant->phone;
            $this->existingAvatar = $this->assistant->avatar;

            // Load existing permissions
            $this->permissions = $this->assistant->getAllPermissions()->pluck('name')->toArray();

            // Check "Select All" state
            $this->selectAllPermissions = count($this->permissions) === count($this->availablePermissions);

        } else {
            // 🟩 CREATE MODE
            // Default to all permissions selected
            $this->permissions = $this->availablePermissions;
            $this->selectAllPermissions = true;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->assistant?->id)],
            'password' => [$this->assistant ? 'nullable' : 'required', 'string', 'min:8', 'same:passwordConfirmation'],
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

        // Only update password if provided
        if (! empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        // Handle Avatar
        if ($this->avatar) {
            if ($this->assistant && $this->assistant->avatar) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->assistant->avatar));
            }
            $path = $this->avatar->store('avatars', 'public');
            $userData['avatar'] = Storage::url($path);
        }

        if ($this->assistant) {
            // Update
            $this->assistant->update($userData);
            $user = $this->assistant;
            $message = __('Assistant profile updated successfully.');
        } else {
            // Create
            $user = User::create($userData);
            $user->assignRole(config('constants.ROLES.ASSISTANT'));
            $message = __('Assistant created successfully.');
        }

        // Sync Permissions
        if (! empty($this->permissions)) {
            $user->syncPermissions($this->permissions);
        }

        $this->success(__('Success'), $message);

        return redirect()->route('assistants.list');
    }

    public function render()
    {
        return view('livewire.assistant-form');
    }
}
