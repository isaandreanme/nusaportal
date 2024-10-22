<?php

namespace App\Filament\Resources\PendaftaranResource\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\ProsesCpmi;
use App\Models\Kantor;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class IDChart extends ApexChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $chartId = 'idBp2miChart';
    protected static ?string $heading = 'ID BP2MI';

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

        // Ambil semua kantor yang tersedia di database
        $kantorData = Kantor::all();

        // Siapkan series dan warna kantor
        $series = [];
        $colors = ['#f59e0b', '#1c64f2', '#10b981', '#ef4444', '#6b7280'];

        foreach ($kantorData as $index => $kantor) {
            $kantorId = $kantor->id;
            $kantorNama = $kantor->nama;

            // Ambil data proses untuk kantor tertentu
            $kantorJobData = $this->getKantorData($kantorId, $start, $end);

            // Jika ada data, tambahkan ke series
            if ($kantorJobData->isNotEmpty()) {
                $series[] = [
                    'name' => $kantorNama,
                    'data' => $kantorJobData->map(fn(TrendValue $value) => $value->aggregate),
                    'color' => $colors[$index % count($colors)] // Mengulang warna jika jumlah kantor lebih dari jumlah warna
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
                'type' => 'line', // Jenis grafik garis
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
                'curve' => 'smooth', // Garis halus
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
        $query = ProsesCpmi::query()
            ->join('pendaftarans', 'proses_cpmis.pendaftaran_id', '=', 'pendaftarans.id')
            ->where('pendaftarans.kantor_id', $kantorId);
        return Trend::query($query)
            ->dateColumn('proses_cpmis.tgl_bp2mi')
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
        $query = ProsesCpmi::query()
            ->join('pendaftarans', 'proses_cpmis.pendaftaran_id', '=', 'pendaftarans.id');
        return Trend::query($query)
            ->dateColumn('proses_cpmis.tgl_bp2mi')
            ->between(
                start: $start ? Carbon::parse($start) : now()->subMonths(6),
                end: $end ? Carbon::parse($end) : now()
            )
            ->perMonth()
            ->count();
    }
}
