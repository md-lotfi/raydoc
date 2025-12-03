<?php

namespace App\Livewire\Assistant;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\Builder;
use Mary\Traits\Toast;

class AssistantsList extends Component
{
    use WithPagination, Toast;

    // --- State ---
    public string $search = '';
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    // Delete Confirmation
    public $assistantToDeleteId = null;
    public $showDeleteModal = false;

    // --- Data Definition ---
    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => __('Name'), 'class' => 'w-1/3'],
            ['key' => 'email', 'label' => __('Contact Info')],
            ['key' => 'created_at', 'label' => __('Joined'), 'class' => 'hidden md:table-cell'],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ];
    }

    // --- Computed Data ---
    
    #[Computed]
    public function assistants()
    {
        return User::role(config('constants.ROLES.ASSISTANT')) // Ensure constant exists
            ->when($this->search, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);
    }

    // --- Actions ---

    public function sort($column)
    {
        if ($this->sortBy['column'] === $column) {
            $this->sortBy['direction'] = $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy['column'] = $column;
            $this->sortBy['direction'] = 'asc';
        }
    }

    public function confirmDelete($id)
    {
        $this->assistantToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->assistantToDeleteId) {
            $user = User::find($this->assistantToDeleteId);
            
            if ($user && $user->hasRole(config('constants.ROLES.ASSISTANT'))) {
                $user->delete();
                $this->success(__('Deleted'), __('Assistant account removed successfully.'));
            } else {
                $this->error(__('Error'), __('User not found or permission denied.'));
            }
        }

        $this->showDeleteModal = false;
        $this->assistantToDeleteId = null;
    }

    public function render()
    {
        return view('livewire.assistants-list');
    }
}