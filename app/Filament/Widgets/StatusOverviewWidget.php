<?php

namespace App\Filament\Widgets;

use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\ProsesCpmi;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat as AdvancedStatsOverviewWidgetStat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class StatusOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    // Fungsi untuk menghasilkan data acak untuk chart kecil
    function generateRandomChartData($count)
    {
        $data = [];
        $startTime = strtotime('00:00'); // Mulai dari jam 00:00

        for ($i = 0; $i < $count; $i++) {
            $hour = date('H:00', strtotime("+{$i} hours", $startTime)); // Mengatur format jam
            $randomValue = rand(5, 96); // Menghasilkan angka acak antara 5 dan 96
            $data[] = $randomValue; // Hanya mengambil nilai acak untuk chart
        }

        return $data;
    }


    protected int | string | array $columnSpan = 2;


    protected function getStats(): array
    {
        // Mengambil filter tanggal dan kantor dari halaman
        $start = $this->filters['startDate'] ?? null;
        $end = $this->filters['endDate'] ?? null;
        $kantor = $this->filters['kantor'] ?? null;

        return [
            AdvancedStatsOverviewWidgetStat::make(
                'BARU',
                ProsesCpmi::when($start, fn($query) => $query->whereDate('created_at', '>=', $start))
                    ->when($end, fn($query) => $query->whereDate('created_at', '<=', $end))
                    ->when($kantor, fn($query) => $query->whereHas('pendaftaran', function ($query) use ($kantor) {
                        $query->where('kantor_id', $kantor);
                    }))
                    ->where('status_id', 1)
                    ->count()
            )
                ->description('Total CPMI Baru')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->icon('heroicon-o-bell')
                ->iconColor('primary')
                ->chartColor('primary')
                ->progress(100)
                ->progressBarColor('primary')
                ->chart($this->generateRandomChartData(9))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.open('/admin/proses-cpmis?tableFilters[Status][values][0]=1')",
                ]),

            AdvancedStatsOverviewWidgetStat::make(
                'PROSES',
                ProsesCpmi::when($start, fn($query) => $query->whereDate('created_at', '>=', $start))
                    ->when($end, fn($query) => $query->whereDate('created_at', '<=', $end))
                    ->when($kantor, fn($query) => $query->whereHas('pendaftaran', function ($query) use ($kantor) {
                        $query->where('kantor_id', $kantor);
                    }))
                    ->where('status_id', 2)
                    ->count()
            )
                ->description('Total CPMI On Proses')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->iconColor('primary')
                ->chartColor('primary')
                ->progress(100)
                ->progressBarColor('primary')
                ->chart($this->generateRandomChartData(9))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.open('/admin/proses-cpmis?tableFilters[Status][values][0]=2')",
                ]),

            AdvancedStatsOverviewWidgetStat::make(
                'TERBANG',
                ProsesCpmi::when($start, fn($query) => $query->whereDate('created_at', '>=', $start))
                    ->when($end, fn($query) => $query->whereDate('created_at', '<=', $end))
                    ->when($kantor, fn($query) => $query->whereHas('pendaftaran', function ($query) use ($kantor) {
                        $query->where('kantor_id', $kantor);
                    }))
                    ->where('status_id', 3)
                    ->count()
            )
                ->description('Total CPMI Terbang')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->icon('heroicon-o-paper-airplane')
                ->iconColor('primary')
                ->chartColor('primary')
                ->progress(100)
                ->progressBarColor('primary')
                ->chart($this->generateRandomChartData(9))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.open('/admin/proses-cpmis?tableFilters[Status][values][0]=3')",
                ]),

            AdvancedStatsOverviewWidgetStat::make(
                'PENDING',
                ProsesCpmi::when($start, fn($query) => $query->whereDate('created_at', '>=', $start))
                    ->when($end, fn($query) => $query->whereDate('created_at', '<=', $end))
                    ->when($kantor, fn($query) => $query->whereHas('pendaftaran', function ($query) use ($kantor) {
                        $query->where('kantor_id', $kantor);
                    }))
                    ->where('status_id', 4)
                    ->count()
            )
                ->description('Total CPMI Pending')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->icon('heroicon-o-clock')
                ->iconColor('primary')
                ->chartColor('primary')
                ->progress(100)
                ->progressBarColor('warning')
                ->chart($this->generateRandomChartData(9))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.open('/admin/proses-cpmis?tableFilters[Status][values][0]=4')",
                ]),

            AdvancedStatsOverviewWidgetStat::make(
                'UNFIT',
                ProsesCpmi::when($start, fn($query) => $query->whereDate('created_at', '>=', $start))
                    ->when($end, fn($query) => $query->whereDate('created_at', '<=', $end))
                    ->when($kantor, fn($query) => $query->whereHas('pendaftaran', function ($query) use ($kantor) {
                        $query->where('kantor_id', $kantor);
                    }))
                    ->where('status_id', 5)
                    ->count()
            )
                ->description('Total CPMI UNFIT')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->icon('heroicon-o-beaker')
                ->iconColor('primary')
                ->chartColor('primary')
                ->progress(100)
                ->progressBarColor('warning')
                ->chart($this->generateRandomChartData(9))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.open('/admin/proses-cpmis?tableFilters[Status][values][0]=5')",
                ]),

            AdvancedStatsOverviewWidgetStat::make(
                'MD',
                ProsesCpmi::when($start, fn($query) => $query->whereDate('created_at', '>=', $start))
                    ->when($end, fn($query) => $query->whereDate('created_at', '<=', $end))
                    ->when($kantor, fn($query) => $query->whereHas('pendaftaran', function ($query) use ($kantor) {
                        $query->where('kantor_id', $kantor);
                    }))
                    ->where('status_id', 6)
                    ->count()
            )
                ->description('Total CPMI Mengundurkan Diri')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->icon('heroicon-o-x-circle')
                ->iconColor('primary')
                ->chartColor('primary')
                ->progress(100)
                ->progressBarColor('danger')
                ->chart($this->generateRandomChartData(9))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.open('/admin/proses-cpmis?tableFilters[Status][values][0]=6')",
                ]),
        ];
    }
}
