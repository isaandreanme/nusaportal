<?php

namespace App\Filament\Resources\PendaftaranResource\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Pendaftaran;
use App\Models\Kantor;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PraMedicalChart extends ApexChartWidget
{
    use InteractsWithPageFilters; // Untuk menangani filter halaman

    protected static ?string $chartId = 'praMedicalChart';
    protected static ?string $heading = 'PRA MEDICAL';
    // protected int | string | array $columnSpan = 'full';

    /**
     * Mengambil data untuk grafik
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Mengakses filter tanggal dari halaman
        $start = $this->filters['startDate'] ?? null;
        $end = $this->filters['endDate'] ?? null;

        // Ambil semua kantor yang tersedia
        $kantorData = Kantor::all();

        // Siapkan series dan warna grafik
        $series = [];
        $colors = ['#f59e0b', '#1c64f2', '#10b981', '#ef4444', '#6b7280'];

        foreach ($kantorData as $index => $kantor) {
            $kantorId = $kantor->id;
            $kantorNama = $kantor->nama;

            // Ambil data untuk kantor tertentu
            $kantorPraMedicalData = $this->getKantorData($kantorId, $start, $end);

            // Jika ada data pra-medical, tambahkan ke series
            if ($kantorPraMedicalData->isNotEmpty()) {
                $series[] = [
                    'name' => $kantorNama,
                    'data' => $kantorPraMedicalData->map(fn(TrendValue $value) => $value->aggregate),
                    'color' => $colors[$index % count($colors)], // Warna yang berulang
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
                'type' => 'line', // Jenis grafik garis
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
                'width' => 2, // Ketebalan garis
            ],
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
     * Fungsi untuk mengambil data per kantor
     */
    private function getKantorData(int $kantorId, $start, $end)
    {
        $query = Pendaftaran::query()->where('kantor_id', $kantorId);
        return Trend::query($query)
            ->dateColumn('tanggal_pra_medical')
            ->between(
                start: $start ? Carbon::parse($start) : now()->subMonths(6),
                end: $end ? Carbon::parse($end) : now()
            )
            ->perMonth()
            ->count();
    }

    /**
     * Fungsi untuk mengambil data total tanpa filter kantor
     */
    private function getTotalData($start, $end)
    {
        $query = Pendaftaran::query();
        return Trend::query($query)
            ->dateColumn('tanggal_pra_medical')
            ->between(
                start: $start ? Carbon::parse($start) : now()->subMonths(6),
                end: $end ? Carbon::parse($end) : now()
            )
            ->perMonth()
            ->count();
    }
}
