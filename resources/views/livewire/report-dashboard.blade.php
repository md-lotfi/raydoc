<div>
    <x-mary-header title="{{ __('Therapy Clinic Reports') }}"
        subtitle="{{ __('Generate custom reports based on operational and financial data.') }}" separator />

    <x-mary-card shadow separator>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">

            {{-- Report Type Selector --}}
            <x-mary-select label="{{ __('Report Type') }}" wire:model.live="reportType" :options="$availableReports"
                icon="o-document-chart-bar" select-class="bg-base-200" />

            {{-- Start Date Input --}}
            <x-mary-input label="{{ __('Start Date') }}" wire:model.live="startDate" type="date" icon="o-calendar"
                select-class="bg-base-200" />

            {{-- End Date Input --}}
            <x-mary-input label="{{ __('End Date') }}" wire:model.live="endDate" type="date" icon="o-calendar"
                select-class="bg-base-200" />

            {{-- Action Button (Auto-generates on input change, but included for manual refresh) --}}
            <x-mary-button label="{{ __('Generate Report') }}" wire:click="generateReport" class="btn-primary"
                icon="o-arrow-path" spinner />
        </div>

        @error(['startDate', 'endDate', 'reportType'])
            <x-mary-alert icon="o-exclamation-triangle" class="alert-error mt-4">
                {{ __('Please correct the following errors.') }}
            </x-mary-alert>
        @enderror
    </x-mary-card>

    <hr class="my-8" />

    {{-- 📊 REPORT OUTPUT AREA --}}
    @if (!empty($reportData))
        <x-mary-card :title="$reportTitle" shadow>

            {{-- 1. KPIs / Summary Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

                {{-- REVENUE SUMMARY KPIs --}}
                @if ($reportType === 'revenue_summary')
                    <x-mary-stat title="{{ __('Total Revenue') }}" :value="format_currency($reportData['totals']['total_revenue'])" icon="o-currency-dollar"
                        class="bg-base-100 shadow-lg" color="text-success" />
                    <x-mary-stat title="{{ __('Period Length') }}" :value="$reportData['totals']['period_days'] . ' Days'" icon="o-calendar"
                        class="bg-base-100 shadow-lg" />
                    <x-mary-stat title="{{ __('Avg. Daily Revenue') }}" :value="format_currency($reportData['totals']['average_daily'])" icon="o-chart-bar"
                        class="bg-base-100 shadow-lg" color="text-info" />
                @endif

                {{-- COMPLETION RATE KPIs --}}
                @if ($reportType === 'completion_rate')
                    <x-mary-stat title="{{ __('Total Sessions') }}" :value="$reportData['summary']['total_sessions']" icon="o-calendar-days"
                        class="bg-base-100 shadow-lg" />
                    <x-mary-stat title="{{ __('Completed') }}" :value="$reportData['summary']['completed']" icon="o-check-circle"
                        class="bg-base-100 shadow-lg" color="text-success" />
                    <x-mary-stat title="{{ __('Completion Rate') }}" :value="$reportData['summary']['completion_rate'] . '%'" icon="o-arrow-up-right"
                        class="bg-base-100 shadow-lg" color="text-primary" />
                    <x-mary-stat title="{{ __('Failure Rate') }}" :value="$reportData['summary']['failure_rate'] . '%'" icon="o-arrow-down-left"
                        class="bg-base-100 shadow-lg" color="text-error" />
                @endif

            </div>

            {{-- 2. Chart Visualization --}}
            @if (!empty($chartData))
                @if (!empty($chartData))
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-xl font-semibold mb-4">{{ __('Visualization') }}</h3>
                        {{-- Check for 'doughnut' or 'pie' for constrained sizing --}}
                        <div class="w-full @if ($chartData['type'] === 'doughnut' || $chartData['type'] === 'pie') max-w-lg mx-auto h-80 @else h-96 @endif">
                            <x-mary-chart wire:model="chartData" />
                        </div>
                    </div>
                @endif

                <!-- chart exceeds the heuight of the card -->
            @endif

        </x-mary-card>
    @elseif (empty($chartData) && !empty($startDate))
        <x-mary-alert icon="o-information-circle" class="alert-info mt-8">
            {{ __('No data found for the selected date range and report type.') }}
        </x-mary-alert>
    @endif
</div>
