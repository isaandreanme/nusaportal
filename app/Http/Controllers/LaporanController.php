<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProsesCpmi;
use App\Models\Status;
use App\Models\Kantor;
use App\Models\Pendaftaran;
use App\Models\Marketing;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use PDF;

class LaporanController extends Controller
{
    use InteractsWithPageFilters;

    public function index(Request $request)
    {
        // Ambil filter tanggal dari request (bila ada)
        $start = $request->input('startDate');
        $end = $request->input('endDate');

        // Ambil status dan kantor terkait
        $statuses = Status::whereIn('id', [1, 2, 3, 4, 5, 6])->get();
        $kantors = Kantor::whereIn('id', [1, 2, 3, 4])->get();

        // Inisialisasi variabel data yang akan ditampilkan
        $jumlahData = [];
        $totalPerStatus = array_fill(1, 6, 0); // Total per status_id (1-6)
        $grandTotal = 0;

        // Inisialisasi variabel untuk kolom tambahan seperti Pra Medical, Siap Kerja, dll.
        $jumlahPraMedical = [];
        $jumlahSiapKerja = [];
        $jumlahBp2mi = [];
        $jumlahDapatJob = [];
        $jumlahPenerbangan = [];

        foreach ($kantors as $kantor) {
            $totalPerKantor = 0;

            foreach ($statuses as $status) {
                // Hitung jumlah data berdasarkan status dan kantor
                $count = ProsesCpmi::when($start, function ($query) use ($start) {
                    return $query->whereDate('created_at', '>=', $start);
                })
                    ->when($end, function ($query) use ($end) {
                        return $query->whereDate('created_at', '<=', $end);
                    })
                    ->where('status_id', $status->id)
                    ->whereHas('pendaftaran', function ($query) use ($kantor) {
                        $query->where('kantor_id', $kantor->id);
                    })
                    ->count();

                // Simpan jumlah data per kantor dan status
                $jumlahData[$kantor->nama][$status->nama] = $count;
                $totalPerKantor += $count;
                $totalPerStatus[$status->id] += $count;
            }

            // Hitung Pra Medical, Siap Kerja, dll. (sama seperti kode yang ada sebelumnya)
            $countPraMedical = ProsesCpmi::when($start, function ($query) use ($start) {
                return $query->whereHas('pendaftaran', function ($query) use ($start) {
                    return $query->whereDate('tanggal_pra_medical', '>=', $start);
                });
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereHas('pendaftaran', function ($query) use ($end) {
                        return $query->whereDate('tanggal_pra_medical', '<=', $end);
                    });
                })
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->whereNotNull('tanggal_pra_medical')
                        ->where('kantor_id', $kantor->id);
                })
                ->whereIn('status_id', [1, 2])
                ->count();

            $countSiapKerja = ProsesCpmi::when($start, function ($query) use ($start) {
                return $query->whereDate('tglsiapkerja', '>=', $start);
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereDate('tglsiapkerja', '<=', $end);
                })
                ->whereNotNull('tglsiapkerja')
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->where('kantor_id', $kantor->id);
                })
                ->whereIn('status_id', [1, 2])
                ->count();

            $countBp2mi = ProsesCpmi::when($start, function ($query) use ($start) {
                return $query->whereDate('tgl_bp2mi', '>=', $start);
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereDate('tgl_bp2mi', '<=', $end);
                })
                ->whereNotNull('tgl_bp2mi')
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->where('kantor_id', $kantor->id);
                })
                ->whereIn('status_id', [1, 2])
                ->count();

            $countDapatJob = Marketing::when($start, function ($query) use ($start) {
                return $query->whereDate('tgl_job', '>=', $start);
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereDate('tgl_job', '<=', $end);
                })
                ->whereNotNull('tgl_job')
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->where('kantor_id', $kantor->id);
                })
                ->whereHas('prosesCpmi', function ($query) {
                    $query->whereIn('status_id', [1, 2]);
                })
                ->count();

            $countPenerbangan = ProsesCpmi::when($start, function ($query) use ($start) {
                return $query->whereDate('tanggal_penerbangan', '>=', $start);
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereDate('tanggal_penerbangan', '<=', $end);
                })
                ->whereNotNull('tanggal_penerbangan')
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->where('kantor_id', $kantor->id);
                })
                ->count();

            // Simpan hasil ke dalam array
            $jumlahPraMedical[$kantor->nama] = $countPraMedical;
            $jumlahSiapKerja[$kantor->nama] = $countSiapKerja;
            $jumlahBp2mi[$kantor->nama] = $countBp2mi;
            $jumlahDapatJob[$kantor->nama] = $countDapatJob;
            $jumlahPenerbangan[$kantor->nama] = $countPenerbangan;

            // Total per kantor
            $jumlahData[$kantor->nama]['total'] = $totalPerKantor;
            $grandTotal += $totalPerKantor;
        }
        // Inisialisasi array kosong untuk menyimpan data berdasarkan status
        $dataByStatus = [];

        // Definisikan logika query umum untuk rentang tanggal
        $baseQuery = ProsesCpmi::when($start, function ($query) use ($start) {
            return $query->whereDate('created_at', '>=', $start);
        })->when($end, function ($query) use ($end) {
            return $query->whereDate('created_at', '<=', $end);
        });
        
        foreach ($statuses as $status) {
            $dataByStatus[$status->nama] = $baseQuery->clone() 
                ->where('status_id', $status->id)
                ->with([
                    'pendaftaran' => function ($query) {
                        // Ambil field spesifik dari Pendaftaran termasuk kantor_id
                        $query->select('id', 'nama', 'created_at', 'kantor_id')
                            ->with(['kantor', 'marketing.agency' => function ($query) {
                                $query->select('id', 'nama');
                            }]);
                    },
                    'tujuan', // Memuat relasi 'tujuan'
                    'pelatihan' // Memuat relasi 'pelatihan'
                ])
                ->select('id', 'pendaftaran_id', 'tujuan_id', 'pelatihan_id', 'status_id', 'created_at', 'tanggal_penerbangan') // Hilangkan kantor_id di sini
                ->get();
        }


        // Kirim data ke view laporan.blade.php
        return view('laporan', compact(
            'jumlahData',
            'statuses',
            'totalPerStatus',
            'grandTotal',
            'jumlahPraMedical',
            'jumlahSiapKerja',
            'jumlahBp2mi',
            'jumlahDapatJob',
            'jumlahPenerbangan',
            'kantors',
            'start',
            'end',
            'dataByStatus' // Tambahan data untuk tabel baru
        ));
    }

    public function generatePdf(Request $request)
    {
        // Sama dengan metode index, tetapi hasilnya di-generate sebagai PDF
        $start = $request->input('startDate');
        $end = $request->input('endDate');

        $statuses = Status::whereIn('id', [1, 2, 3, 4, 5, 6])->get();
        $kantors = Kantor::whereIn('id', [1, 2, 3, 4])->get();

        $jumlahData = [];
        $totalPerStatus = array_fill(1, 6, 0);
        $grandTotal = 0;

        $jumlahPraMedical = [];
        $jumlahSiapKerja = [];
        $jumlahBp2mi = [];
        $jumlahDapatJob = [];
        $jumlahPenerbangan = [];

        foreach ($kantors as $kantor) {
            $totalPerKantor = 0;

            foreach ($statuses as $status) {
                $count = ProsesCpmi::when($start, function ($query) use ($start) {
                    return $query->whereDate('created_at', '>=', $start);
                })
                    ->when($end, function ($query) use ($end) {
                        return $query->whereDate('created_at', '<=', $end);
                    })
                    ->where('status_id', $status->id)
                    ->whereHas('pendaftaran', function ($query) use ($kantor) {
                        $query->where('kantor_id', $kantor->id);
                    })
                    ->count();

                $jumlahData[$kantor->nama][$status->nama] = $count;
                $totalPerKantor += $count;
                $totalPerStatus[$status->id] += $count;
            }

            $countPraMedical = ProsesCpmi::when($start, function ($query) use ($start) {
                return $query->whereHas('pendaftaran', function ($query) use ($start) {
                    return $query->whereDate('tanggal_pra_medical', '>=', $start);
                });
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereHas('pendaftaran', function ($query) use ($end) {
                        return $query->whereDate('tanggal_pra_medical', '<=', $end);
                    });
                })
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->whereNotNull('tanggal_pra_medical')
                        ->where('kantor_id', $kantor->id);
                })
                ->whereIn('status_id', [1, 2])
                ->count();

            $countSiapKerja = ProsesCpmi::when($start, function ($query) use ($start) {
                return $query->whereDate('tglsiapkerja', '>=', $start);
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereDate('tglsiapkerja', '<=', $end);
                })
                ->whereNotNull('tglsiapkerja')
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->where('kantor_id', $kantor->id);
                })
                ->whereIn('status_id', [1, 2])
                ->count();

            $countBp2mi = ProsesCpmi::when($start, function ($query) use ($start) {
                return $query->whereDate('tgl_bp2mi', '>=', $start);
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereDate('tgl_bp2mi', '<=', $end);
                })
                ->whereNotNull('tgl_bp2mi')
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->where('kantor_id', $kantor->id);
                })
                ->whereIn('status_id', [1, 2])
                ->count();

            $countDapatJob = Marketing::when($start, function ($query) use ($start) {
                return $query->whereDate('tgl_job', '>=', $start);
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereDate('tgl_job', '<=', $end);
                })
                ->whereNotNull('tgl_job')
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->where('kantor_id', $kantor->id);
                })
                ->whereHas('prosesCpmi', function ($query) {
                    $query->whereIn('status_id', [1, 2]);
                })
                ->count();

            $countPenerbangan = ProsesCpmi::when($start, function ($query) use ($start) {
                return $query->whereDate('tanggal_penerbangan', '>=', $start);
            })
                ->when($end, function ($query) use ($end) {
                    return $query->whereDate('tanggal_penerbangan', '<=', $end);
                })
                ->whereNotNull('tanggal_penerbangan')
                ->whereHas('pendaftaran', function ($query) use ($kantor) {
                    $query->where('kantor_id', $kantor->id);
                })
                ->count();

            $jumlahPraMedical[$kantor->nama] = $countPraMedical;
            $jumlahSiapKerja[$kantor->nama] = $countSiapKerja;
            $jumlahBp2mi[$kantor->nama] = $countBp2mi;
            $jumlahDapatJob[$kantor->nama] = $countDapatJob;
            $jumlahPenerbangan[$kantor->nama] = $countPenerbangan;

            $jumlahData[$kantor->nama]['total'] = $totalPerKantor;
            $grandTotal += $totalPerKantor;
        }

        // Inisialisasi array kosong untuk menyimpan data berdasarkan status
        $dataByStatus = [];

        // Definisikan logika query umum untuk rentang tanggal
        $baseQuery = ProsesCpmi::when($start, function ($query) use ($start) {
            return $query->whereDate('created_at', '>=', $start);
        })->when($end, function ($query) use ($end) {
            return $query->whereDate('created_at', '<=', $end);
        });
        
        foreach ($statuses as $status) {
            $dataByStatus[$status->nama] = $baseQuery->clone() 
                ->where('status_id', $status->id)
                ->with([
                    'pendaftaran' => function ($query) {
                        // Ambil field spesifik dari Pendaftaran termasuk kantor_id
                        $query->select('id', 'nama', 'created_at', 'kantor_id')
                            ->with(['kantor', 'marketing.agency' => function ($query) {
                                $query->select('id', 'nama');
                            }]);
                    },
                    'tujuan', // Memuat relasi 'tujuan'
                    'pelatihan' // Memuat relasi 'pelatihan'
                ])
                ->select('id', 'pendaftaran_id', 'tujuan_id', 'pelatihan_id', 'status_id', 'created_at', 'tanggal_penerbangan') // Hilangkan kantor_id di sini
                ->get();
        }




        $pdf = FacadePdf::loadView('laporan', compact(
            'jumlahData',
            'statuses',
            'totalPerStatus',
            'grandTotal',
            'jumlahPraMedical',
            'jumlahSiapKerja',
            'jumlahBp2mi',
            'jumlahDapatJob',
            'jumlahPenerbangan',
            'kantors',
            'start',
            'end',
            'dataByStatus' // Tambahan data untuk tabel baru
        ));

        $appName = env('COMPANY_NAME', 'DefaultApp');

        // Get current date and time
        $timestamp = date('Ymd_His'); // Format: YYYYMMDD_HHMMSS

        // Generate filename with app name and timestamp
        $fileName = $appName . '_laporan_' . $timestamp . '.pdf';

        // Return the PDF as a downloadable file with the new filename
        return $pdf->download($fileName);
    }
}
