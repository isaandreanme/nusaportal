<?php

namespace App\Filament\Resources\PendaftaranResource\Widgets;

use App\Models\Marketing;
use App\Models\Pendaftaran;
use App\Models\Kantor;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class JobChart extends ApexChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $chartId = 'jobChart';
    protected static ?string $heading = 'DAPAT JOB';

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

        // Ambil data kantor yang tersedia
        $kantorData = Kantor::all(); // Mengambil semua kantor yang tersedia

        // Menyiapkan variabel series dan nama kantor
        $series = [];
        $colors = ['#f59e0b', '#1c64f2', '#10b981', '#ef4444', '#6b7280']; // Sesuaikan warna

        foreach ($kantorData as $index => $kantor) {
            $kantorId = $kantor->id;
            $kantorNama = $kantor->nama;
            
            // Ambil data untuk kantor tertentu
            $kantorJobData = $this->getKantorData($kantorId, $start, $end);

            // Jika ada data, tambahkan ke series
            if ($kantorJobData->isNotEmpty()) {
                $series[] = [
                    'name' => $kantorNama,
                    'data' => $kantorJobData->map(fn(TrendValue $value) => $value->aggregate),
                    'color' => $colors[$index % count($colors)] // Loop warna jika lebih dari jumlah warna
                ];
            }
        }

        // Ambil data total tanpa filter kantor_id
        $total = $this->getTotalData($start, $end);

        // Tambahkan data total ke series
        if ($total->isNotEmpty()) {
            $series[] = [
                'name' => 'TOTAL',
                'data' => $total->map(fn(TrendValue $value) => $value->aggregate),
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
                'categories' => $total->map(fn(TrendValue $value) => Carbon::parse($value->date)->translatedFormat('M y')),
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
                'width' => 2,
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
        $query = Marketing::query()
            ->join('pendaftarans', 'marketings.pendaftaran_id', '=', 'pendaftarans.id')
            ->where('pendaftarans.kantor_id', $kantorId);
        return Trend::query($query)
            ->dateColumn('marketings.tgl_job')
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
        $query = Marketing::query()
            ->join('pendaftarans', 'marketings.pendaftaran_id', '=', 'pendaftarans.id');
        return Trend::query($query)
            ->dateColumn('marketings.tgl_job')
            ->between(
                start: $start ? Carbon::parse($start) : now()->subMonths(6),
                end: $end ? Carbon::parse($end) : now()
            )
            ->perMonth()
            ->count();
    }
}
