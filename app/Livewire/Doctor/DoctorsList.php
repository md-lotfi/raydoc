<?php

namespace App\Livewire\Doctor;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorsList extends Component
{
    use WithPagination;

    // Table properties for Mary UI Table
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public string $search = '';

    // Headers for the Mary UI Table
    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'class' => 'text-base'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'date_of_birth', 'label' => 'DOB'],
            ['key' => 'created_at', 'label' => 'Joined', 'sortBy' => 'created_at'],
            ['key' => 'actions', 'label' => 'Actions'],
        ];
    }

    /**
     * Retrieves the list of users with the 'doctor' role, applies search, and handles sorting.
     */
    public function doctors()
    {
        return User::role('doctor')
            ->when($this->search, function (Builder $query) {
                // Search across name, email, or phone
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function delete(User $user)
    {
        // 1. You should add confirmation via a modal or notification before running this.
        // 2. Ensure only users with appropriate permissions can delete doctors.

        // Example: Only delete if the user is a doctor
        if ($user->hasRole('doctor')) {
            $user->delete();
            session()->flash('success', "Doctor {$user->name} successfully removed.");
        } else {
            session()->flash('error', 'User not found or is not a doctor.');
        }
    }

    public function render()
    {
        return view('livewire.doctors-list', [
            'doctors' => $this->doctors(),
        ]);
    }
}
