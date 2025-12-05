<?php

namespace App\Livewire\Activation;

use App\Services\LicenseService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.guest')]
class ActivateApp extends Component
{
    use Toast; // Optional: for success/error notifications

    public $licenseKey = '';

    public $error = '';

    public function toJSON()
    {
        return [];
    }

    public function activate(LicenseService $service)
    {
        $this->validate(['licenseKey' => 'required|string']);

        // Resolve the service manually here
        // $service = app(LicenseService::class);

        if ($service->activate($this->licenseKey)) {
            return redirect()->route('dashboard');
        }

        $this->error = __('Invalid License Key or No Internet Connection.');

        // Optional: Show a toast as well
        $this->warning($this->error);
    }

    public function render()
    {
        Log::debug('ready to render');

        return view('livewire.activation.activate-app');
    }
}
