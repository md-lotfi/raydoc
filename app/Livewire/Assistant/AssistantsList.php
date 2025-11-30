<?php

namespace App\Livewire\Assistant;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class AssistantsList extends Component
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
     * Retrieves the list of users with the 'assistant' role, applies search, and handles sorting.
     */
    public function assistants()
    {
        return User::role('assistant')
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
        // Add safety checks, such as ensuring the currently logged-in user has permissions
        // and that the target user is indeed an assistant.
        if ($user->hasRole('assistant')) {
            $user->delete();
            session()->flash('success', "Assistant {$user->name} successfully removed.");
        } else {
            session()->flash('error', 'User not found or is not an assistant.');
        }
    }

    public function render()
    {
        return view('livewire.assistants-list', [
            'assistants' => $this->assistants(),
        ]);
    }
}
