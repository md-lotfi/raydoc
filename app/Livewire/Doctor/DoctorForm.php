<?php

namespace App\Livewire\Doctor;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DoctorForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?string $dateOfBirth = null;

    public ?string $gender = null;

    public ?string $address = null;

    public ?string $city = null;

    public ?string $phone = null;

    // Permission properties
    public array $permissions = []; // Array to hold selected permissions

    // Define the list of permissions a doctor can be granted
    public array $doctorPermissions;

    public function mount()
    {
        $this->doctorPermissions = getDoctorPermissions();
    }

    /**
     * Validation rules for the form.
     */
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'dateOfBirth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female,Other',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'permissions' => 'nullable|array',
        ];
    }

    /**
     * Creates the user, assigns the 'doctor' role, and syncs permissions.
     */
    public function save()
    {
        $this->validate();

        // 1. Create the User
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'date_of_birth' => $this->dateOfBirth,
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
        ]);

        // 2. Assign the 'doctor' role
        $user->assignRole(config('constants.ROLES.DOCTOR'));

        // 3. Assign specific permissions selected by the admin
        if (! empty($this->permissions)) {
            // Ensure all selected permissions actually exist in the database
            $validPermissions = Permission::whereIn('name', $this->permissions)->pluck('name');
            $user->givePermissionTo($validPermissions);
        }

        // Emit success message and reset form
        session()->flash('success', 'Doctor user created and permissions assigned successfully.');
        $this->reset();
    }

    public function render()
    {
        // Ensure all required permissions exist for the checklist
        // NOTE: In a production app, permissions should be seeded, not created here.
        // This is included only to ensure the checklist works during development.
        /*foreach ($this->doctorPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }*/

        return view('livewire.doctor-form');
    }
}
