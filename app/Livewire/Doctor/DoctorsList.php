<?php

namespace App\Livewire\Doctor;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class DoctorsList extends Component
{
    use Toast, WithPagination;

    // --- State ---
    public string $search = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    // Delete Confirmation
    public $doctorToDeleteId = null;

    public $showDeleteModal = false;

    // --- Data Definition ---
    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => __('Doctor Name'), 'class' => 'w-1/3'],
            ['key' => 'email', 'label' => __('Contact Info')],
            ['key' => 'created_at', 'label' => __('Joined'), 'class' => 'hidden md:table-cell'],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ];
    }

    // --- Computed Data ---

    #[Computed]
    public function doctors()
    {
        return User::role(config('constants.ROLES.DOCTOR')) // Use constant for safety
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
        // Prevent deleting yourself if you happen to be a doctor
        if ($id === Auth::id()) {
            $this->error(__('Action Denied'), __('You cannot delete your own account.'));

            return;
        }

        $this->doctorToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->doctorToDeleteId) {
            $user = User::find($this->doctorToDeleteId);

            if ($user && $user->hasRole(config('constants.ROLES.DOCTOR'))) {
                $user->delete();
                $this->success(__('Deleted'), __('Doctor account removed successfully.'));
            } else {
                $this->error(__('Error'), __('User not found or permission denied.'));
            }
        }

        $this->showDeleteModal = false;
        $this->doctorToDeleteId = null;
    }

    public function render()
    {
        return view('livewire.doctors-list');
    }
}
