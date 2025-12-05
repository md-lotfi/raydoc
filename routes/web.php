<?php

use App\Livewire\Activation\ActivateApp;
use App\Livewire\Assistant\AssistantForm;
use App\Livewire\Assistant\AssistantsList;
use App\Livewire\Billing\BillingCodesForm;
use App\Livewire\Billing\BillingCodesList;
use App\Livewire\Dashboard;
use App\Livewire\Diagnosis\GlobalDiagnosesList;
use App\Livewire\Doctor\DoctorForm;
use App\Livewire\Doctor\DoctorsList;
use App\Livewire\Invoices\EditInvoice;
use App\Livewire\Invoices\GenerateInvoice;
use App\Livewire\Invoices\InvoicesList;
use App\Livewire\Invoices\ShowInvoice;
use App\Livewire\Patient\DiagnosisDetail;
use App\Livewire\Patient\ListPatients;
use App\Livewire\Patient\PatientDiagnosisForm;
use App\Livewire\Patient\PatientDiagnosisList;
use App\Livewire\Patient\PatientForm;
use App\Livewire\Patient\PatientFullReport;
use App\Livewire\Patient\PatientSessionForm;
use App\Livewire\Patient\PatientSessionList;
use App\Livewire\ReportDashboard;
use App\Livewire\Sessions\Schedule;
use App\Livewire\Sessions\SessionDetail;
use App\Livewire\Sessions\SessionsList;
use App\Livewire\Sessions\WaitingRoom;
use App\Livewire\Settings\AppSettings;
use App\Livewire\Settings\CurrencyForm;
use App\Livewire\Settings\CurrencyList;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

/*Route::get('/', function () {
    return view('welcome');
})->name('home');*/

Route::get('activate', ActivateApp::class)->name('activation.form');

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fr', 'ar'])) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('switch-language');

Route::middleware(['auth'])->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::get('report', ReportDashboard::class)->name('dashboard.report');
    });
    Route::prefix('settings')->group(function () {
        Route::prefix('app')->group(function () {
            Route::get('edit', AppSettings::class)->name('settings.app.edit');
        });
        Route::prefix('currency')->group(function () {
            Route::get('list', CurrencyList::class)->name('settings.currency.list');
            Route::get('create', CurrencyForm::class)->name('settings.currency.create');
            Route::get('/{currency}/edit', CurrencyForm::class)->name('settings.currency.edit');
        });
    });

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::prefix('patient')->group(function () {
        Route::get('list', ListPatients::class)
            ->middleware(['permission:view patients'])
            ->name('patient.list');
        Route::get('create', PatientForm::class)
            ->middleware(['permission:create patients'])
            ->name('patient.create');
        Route::get('/{patient}/health/folder', PatientFullReport::class)
            ->middleware(['permission:show patients health folder'])
            ->name('patient.health.folder');
        Route::get('patients/{id}/edit', PatientForm::class)
            ->middleware(['permission:edit patients'])
            ->name('patient.edit');

        Route::prefix('diagnosis')->group(function () {
            Route::get('/{patient}', PatientDiagnosisList::class)->name('patient.diagnosis.list');
            Route::get('/{patient}/create', PatientDiagnosisForm::class)->name('patient.diagnosis.create');
            Route::get('/{patient}/{diagnosis}/edit', PatientDiagnosisForm::class)->name('patient.diagnosis.edit');
            Route::get('/{diagnosis}/detail', DiagnosisDetail::class)->name('patient.diagnosis.detail');
        });

        Route::prefix('session')->group(function () {
            Route::get('/{patient}', PatientSessionList::class)->name('patient.session.list');
            Route::get('/{patient}/create', PatientSessionForm::class)->name('patient.session.create');
            Route::get('/{patient}/{session}/edit', PatientSessionForm::class)->name('patient.session.edit');
        });

    });

    Route::prefix('billing')->group(function () {
        Route::prefix('codes')->group(function () {
            Route::get('/create', BillingCodesForm::class)->name('billing.codes.create');
            Route::get('/{billingCode}/edit', BillingCodesForm::class)->name('billing.codes.edit');
            Route::get('/list', BillingCodesList::class)->name('billing.codes.list');
        });
    });

    Route::prefix('invoice')->group(function () {
        Route::get('patient/{patient}/generate', GenerateInvoice::class)->name('invoice.generate');
        Route::get('list', InvoicesList::class)->name('invoice.list');
        Route::get('/{invoice}/show', ShowInvoice::class)->name('invoice.show');
        Route::get('/{invoice}/edit', EditInvoice::class)->name('invoice.edit');
    });
    Route::prefix('sessions')->group(function () {
        Route::get('schedule', Schedule::class)->name('sessions.schedule');
        Route::get('list', SessionsList::class)->name('sessions.list');
        Route::get('waiting-room', WaitingRoom::class)->name('sessions.waiting-room');
        Route::get('/{session}/detail', SessionDetail::class)->name('sessions.detail');
    });

    Route::prefix('doctors')->group(function () {
        Route::get('create', DoctorForm::class)->name('doctors.create');
        Route::get('list', DoctorsList::class)->name('doctors.list');
        Route::get('/{doctor}/detail', DoctorForm::class)->name('doctors.detail');
        Route::get('/{doctor}/edit', DoctorForm::class)->name('doctors.edit');
    });

    Route::prefix('assistants')->group(function () {
        Route::get('create', AssistantForm::class)->name('assistants.create');
        Route::get('list', AssistantsList::class)->name('assistants.list');
        Route::get('/{assistant}/detail', AssistantForm::class)->name('assistants.detail');
        Route::get('/{assistant}/edit', AssistantForm::class)->name('assistants.edit');
    });

    Route::prefix('clinical')->group(function () {
        Route::get('diagnoses', GlobalDiagnosesList::class)->name('clinical.diagnoses.list');
    });

});
