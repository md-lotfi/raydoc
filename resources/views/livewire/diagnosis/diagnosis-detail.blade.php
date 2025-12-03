<div class="space-y-8">

    <x-page-header title="{{ __('Diagnosis Record') }}" subtitle="{{ __('View clinical details for this condition.') }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Back to List') }}" icon="o-arrow-left" class="btn-ghost"
                link="{{ route('patient.diagnosis.list', $diagnosis->patient_id) }}" />
            <x-mary-button label="{{ __('Edit') }}" icon="o-pencil" class="btn-warning"
                link="{{ route('patient.diagnosis.edit', ['patient' => $diagnosis->patient->id, 'diagnosis' => $diagnosis->id]) }}" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 📝 LEFT: Diagnosis Card --}}
        <div class="lg:col-span-2 space-y-6">
            <x-mary-card shadow class="border-t-4 border-primary">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                            {{ __('ICD-10 Classification') }}</div>
                        <div class="text-3xl font-black text-primary">{{ $diagnosis->icdCode->code }}</div>
                        <div class="text-lg text-gray-700 font-medium mt-1">{{ $diagnosis->icdCode->description }}</div>
                    </div>
                    <x-mary-badge :value="__($diagnosis->condition_status)"
                        class="badge-lg font-bold
                        @if ($diagnosis->condition_status === 'Active')
badge-success/10 text-success
@elseif($diagnosis->condition_status === 'Resolved')
badge-neutral
@else
badge-warning/10 text-warning
@endif" />
                </div>

                <div class="bg-base-200/50 p-4 rounded-xl border border-base-200 mb-6">
                    <div class="text-xs font-bold text-gray-500 uppercase mb-2">{{ __('Clinical Notes') }}</div>
                    <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">
                        {{ $diagnosis->description ?? __('No additional notes recorded.') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <x-mary-icon name="o-tag" class="w-5 h-5 text-gray-400" />
                        <span class="text-gray-500">{{ __('Type:') }}</span>
                        <span class="font-semibold">{{ $diagnosis->type }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-mary-icon name="o-calendar" class="w-5 h-5 text-gray-400" />
                        <span class="text-gray-500">{{ __('Onset Date:') }}</span>
                        <span class="font-semibold">{{ $diagnosis->start_date }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-mary-icon name="o-user-circle" class="w-5 h-5 text-gray-400" />
                        <span class="text-gray-500">{{ __('Diagnosed By:') }}</span>
                        <span class="font-semibold">{{ $diagnosis->user->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-mary-icon name="o-clock" class="w-5 h-5 text-gray-400" />
                        <span class="text-gray-500">{{ __('Recorded:') }}</span>
                        <span class="font-semibold">{{ $diagnosis->created_at->translatedFormat('M d, Y') }}</span>
                    </div>
                </div>
            </x-mary-card>
        </div>

        {{-- 👤 RIGHT: Patient Context --}}
        <div class="lg:col-span-1 space-y-6">
            <x-mary-card title="{{ __('Patient Context') }}" shadow separator>
                <div class="flex items-center gap-4 mb-4">
                    <x-mary-avatar :image="$diagnosis->patient->avatar" :title="$diagnosis->patient->first_name" class="!w-12 !h-12" />
                    <div>
                        <div class="font-bold text-lg">{{ $diagnosis->patient->first_name }}
                            {{ $diagnosis->patient->last_name }}</div>
                        <div class="text-xs text-gray-500">{{ $diagnosis->patient->email }}</div>
                    </div>
                </div>

                <div class="space-y-3 text-sm border-t border-base-200 pt-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Gender') }}</span>
                        <span class="font-medium">{{ __($diagnosis->patient->gender) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Age') }}</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($diagnosis->patient->date_of_birth)->age }}
                            {{ __('Years') }}</span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-base-200">
                    <x-mary-button label="{{ __('View Full Record') }}" class="btn-outline w-full"
                        link="{{ route('patient.health.folder', $diagnosis->patient_id) }}" />
                </div>
            </x-mary-card>
        </div>

    </div>
</div>
