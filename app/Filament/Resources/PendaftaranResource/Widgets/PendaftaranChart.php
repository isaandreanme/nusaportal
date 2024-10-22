<?php

namespace App\Filament\Resources\PendaftaranResource\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Pendaftaran;
use App\Models\ProsesCpmi;
use App\Models\Kantor;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PendaftaranChart extends ApexChartWidget
{
    use InteractsWithPageFilters; // Mengaktifkan filter halaman

    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'blogPostsChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'PENDAFTARAN';
    protected int | string | array $columnSpan = 'full';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Mengakses filter dari halaman (misalnya, startDate dan endDate)
        $start = $this->filters['startDate'] ?? null;
        $end = $this->filters['endDate'] ?? null;

        // Ambil data kantor yang tersedia
        $kantorData = Kantor::all(); // Mengambil semua kantor yang tersedia di database

        // Siapkan series dan warna untuk grafik
        $series = [];
        $colors = ['#f59e0b', '#1c64f2', '#10b981', '#ef4444', '#6b7280'];

        foreach ($kantorData as $index => $kantor) {
            $kantorId = $kantor->id;
            $kantorNama = $kantor->nama;

            // Ambil data untuk kantor tertentu
            $kantorPendaftaranData = $this->getKantorData($kantorId, $start, $end);

            // Jika ada data, tambahkan ke series
            if ($kantorPendaftaranData->isNotEmpty()) {
                $series[] = [
                    'name' => $kantorNama,
                    'data' => $kantorPendaftaranData->map(fn(TrendValue $value) => $value->aggregate),
                    'color' => $colors[$index % count($colors)], // Loop warna jika lebih dari jumlah warna
                ];
            }
        }

        // Ambil data total tanpa filter kantor_id
        $totalData = $this->getTotalData($start, $end);

        // Tambahkan data total ke series jika ada
        if ($totalData->isNotEmpty()) {
            $series[] = [
                'name' => 'TOTAL',
                'data' => $totalData->map(fn(TrendValue $value) => $value->aggregate),
                'color' => '#6b7280', // Warna untuk total
            ];
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => $series,
            'xaxis' => [
                'categories' => $totalData->map(fn(TrendValue $value) => Carbon::parse($value->date)->translatedFormat('M y')),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2, // Mengatur ketebalan garis menjadi 2
            ],

            // Grid-----------------------------------
            'grid' => [
                'show' => false,
                'borderColor' => '#e0e0e0',
                'strokeDashArray' => 1,
                'xaxis' => [
                    'lines' => [
                        'show' => true,
                    ],
                ],
                'yaxis' => [
                    'lines' => [
                        'show' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * Mengambil data per kantor berdasarkan ID
     */
    private function getKantorData(int $kantorId, $start, $end)
    {
        $query = Pendaftaran::query()->where('kantor_id', $kantorId);
        return Trend::query($query)
            ->dateColumn('created_at')
            ->between(
                start: $start ? Carbon::parse($start) : now()->subMonths(6),
                end: $end ? Carbon::parse($end) : now()
            )
            ->perMonth()
            ->count();
    }

    /**
     * Mengambil data total tanpa filter kantor
     */
    private function getTotalData($start, $end)
    {
        $query = Pendaftaran::query();
        return Trend::query($query)
            ->dateColumn('created_at')
            ->between(
                start: $start ? Carbon::parse($start) : now()->subMonths(6),
                end: $end ? Carbon::parse($end) : now()
            )
            ->perMonth()
            ->count();
    }
}
