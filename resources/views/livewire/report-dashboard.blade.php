<div class="space-y-8">

    {{-- 🟢 1. Header & Controls --}}
    <div class="flex flex-col lg:flex-row gap-6 justify-between items-start lg:items-center">
        <div>
            <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">
                {{ __('Analytics Dashboard') }}
            </h1>
            <p class="text-gray-500 mt-1">{{ __('Deep dive into your clinic performance metrics.') }}</p>
        </div>

        <x-mary-button label="{{ __('Export CSV') }}" icon="o-arrow-down-tray" class="btn-outline" wire:click="exportCsv"
            spinner :disabled="empty($tableRows)" />
    </div>

    {{-- 🎛️ 2. Filter Card --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 p-6">

        {{-- Report Types Tabs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @foreach ($availableReports as $report)
                <div wire:click="$set('reportType', '{{ $report['id'] }}')"
                    class="cursor-pointer rounded-xl p-4 border transition-all duration-200 flex flex-col items-center justify-center gap-2 text-center
                    {{ $reportType === $report['id']
                        ? 'bg-primary/5 border-primary text-primary ring-1 ring-primary/20'
                        : 'bg-base-100 border-base-200 hover:border-primary/50 hover:shadow-md' }}">
                    <x-mary-icon name="{{ $report['icon'] }}" class="w-6 h-6" />
                    <span class="font-semibold text-sm">{{ $report['name'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col md:flex-row gap-6 items-end border-t border-base-200 pt-6">
            {{-- Date Presets --}}
            <div class="flex-1 w-full">
                <label
                    class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 block">{{ __('Quick Range') }}</label>
                <div class="join w-full">
                    @foreach (['this_week' => __('Week'), 'this_month' => __('Month'), 'last_30_days' => __('30 Days'), 'last_month' => __('Last M')] as $key => $label)
                        <button
                            class="join-item btn btn-sm flex-1 {{ $activePreset === $key ? 'btn-active btn-primary' : 'btn-ghost bg-base-200' }}"
                            wire:click="setPeriod('{{ $key }}')">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Custom Date Inputs --}}
            <div class="flex gap-2 w-full md:w-auto">
                <x-mary-datepicker label="{{ __('From') }}" wire:model.live="startDate" icon="o-calendar"
                    class="w-full md:w-40" />
                <x-mary-datepicker label="{{ __('To') }}" wire:model.live="endDate" icon="o-calendar"
                    class="w-full md:w-40" />
            </div>
        </div>
    </div>

    {{-- 📊 3. Analytics Section --}}
    @if (!empty($reportData))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: KPIs --}}
            <div class="space-y-4 lg:col-span-1">
                @foreach ($reportData['kpi'] as $kpi)
                    <div
                        class="bg-base-100 p-6 rounded-2xl shadow-sm border border-base-200 flex items-center justify-between group hover:border-primary/30 transition-all">
                        <div>
                            <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">{{ $kpi['label'] }}
                            </div>
                            <div class="text-3xl font-black mt-1 {{ $kpi['color'] ?? '' }}">
                                @if (($kpi['format'] ?? '') == 'currency')
                                    {{ format_currency($kpi['value']) }}
                                @else
                                    {{ $kpi['value'] }}
                                @endif
                            </div>
                        </div>

                        {{-- Growth Indicator --}}
                        @if (isset($kpi['growth']))
                            <div
                                class="badge {{ $kpi['growth'] >= 0 ? 'badge-success/10 text-success' : 'badge-error/10 text-error' }} gap-1 font-bold p-3">
                                <x-mary-icon
                                    name="{{ $kpi['growth'] >= 0 ? 'o-arrow-trending-up' : 'o-arrow-trending-down' }}"
                                    class="w-4 h-4" />
                                {{ abs($kpi['growth']) }}%
                            </div>
                        @else
                            <div
                                class="p-3 bg-base-200 rounded-full group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                <x-mary-icon name="o-chart-bar" class="w-6 h-6 opacity-50" />
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- Context Note --}}
                <div class="alert alert-info bg-info/5 border-info/20 text-sm text-gray-600 shadow-none">
                    <x-mary-icon name="o-information-circle" />
                    <span>{{ __('Comparison vs previous period of same length.') }}</span>
                </div>
            </div>

            {{-- RIGHT: Chart --}}
            <div class="lg:col-span-2 bg-base-100 p-6 rounded-2xl shadow-sm border border-base-200">
                <h3 class="font-bold text-lg mb-4">{{ __('Visual Trend') }}</h3>
                <div class="h-72 relative w-full">
                    <x-mary-chart wire:model="chartData" class="w-full h-full" />
                </div>
            </div>
        </div>

        {{-- 📋 4. Detailed Data Table --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
            <div class="p-4 border-b border-base-200 bg-base-50 flex justify-between items-center">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <x-mary-icon name="o-table-cells" class="text-gray-400" />
                    {{ __('Detailed Breakdown') }}
                </h3>
                <span class="badge badge-ghost">{{ count($tableRows) }} {{ __('Records') }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            @foreach ($tableHeaders as $header)
                                <th class="uppercase text-xs text-gray-400 font-bold tracking-wider">
                                    {{ $header['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tableRows as $row)
                            <tr class="hover:bg-base-50 transition-colors">
                                @foreach ($tableHeaders as $header)
                                    <td class="font-medium text-gray-700">
                                        {{ is_array($row) ? $row[$header['key']] : $row->{$header['key']} }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($tableHeaders) }}" class="text-center py-8 text-gray-400">
                                    {{ __('No records found for this period.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-12">
            <div class="bg-base-200 inline-flex p-4 rounded-full mb-4">
                <x-mary-icon name="o-funnel" class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="font-bold text-lg">{{ __('Select parameters') }}</h3>
            <p class="text-gray-500">{{ __('Choose a report type and date range to analyze data.') }}</p>
        </div>
    @endif
</div>
