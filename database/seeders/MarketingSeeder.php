<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marketing;
use App\Models\Pendaftaran;
use App\Models\ProsesCpmi;
use App\Models\Sales;
use App\Models\Agency;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MarketingSeeder extends Seeder
{
    /**
     * Jalankan seeder database.
     */
    public function run(): void
    {
        // Inisialisasi Faker
        $faker = Faker::create();

        // Mengambil semua data dari tabel Pendaftaran, ProsesCpmi, Sales, dan Agency
        $pendaftarans = Pendaftaran::all();
        $prosesCpmis = ProsesCpmi::with('status')->get(); // Mengambil relasi status dengan ProsesCpmi
        $sales = Sales::all();
        $agencies = Agency::all();

        // Pastikan tabel Pendaftaran, ProsesCpmi, Sales, dan Agency tidak kosong
        if ($pendaftarans->isEmpty()) {
            Pendaftaran::factory()->count(5)->create();
            $pendaftarans = Pendaftaran::all();
        }

        if ($prosesCpmis->isEmpty()) {
            ProsesCpmi::factory()->count(5)->create();
            $prosesCpmis = ProsesCpmi::with('status')->get(); // Mengambil relasi status lagi jika data baru
        }

        if ($sales->isEmpty()) {
            $this->command->error('Tidak ada data di tabel Sales. Harap tambahkan data Sales terlebih dahulu.');
            return;
        }

        if ($agencies->isEmpty()) {
            $this->command->error('Tidak ada data di tabel Agencies. Harap tambahkan data Agencies terlebih dahulu.');
            return;
        }

        // Memastikan direktori penyimpanan untuk foto ada
        $namaFile = 'contohfotomaids.jpg';
        $direktoriFoto = 'biodata/foto';
        Storage::disk('public')->makeDirectory($direktoriFoto);
        $pathAsli = public_path("images/$namaFile");

        // Pastikan file gambar ada
        if (!file_exists($pathAsli)) {
            $this->command->error("File gambar $namaFile tidak ditemukan di public/images.");
            return;
        }

        // Salin file gambar ke direktori penyimpanan
        $pathTujuan = "$direktoriFoto/$namaFile";
        Storage::disk('public')->put($pathTujuan, file_get_contents($pathAsli));

        // Variabel untuk mengatur agency_id secara berurutan dalam rentang 3 hingga 12
        $agencyIdCounter = 3; // Mulai dari 3

        // Iterasi setiap pendaftaran dan buat data Marketing
        foreach ($pendaftarans as $pendaftaran) {
            $prosesCpmi = $prosesCpmis->random();
            $salesPerson = $sales->random();

            // Ambil nilai status_id dari relasi Status di ProsesCpmi
            $statusId = $prosesCpmi->status->id;

            // Tentukan agency_id berdasarkan status_id tanpa acak
            if ($statusId === 3) {
                // Jika status_id adalah 3, tetapkan agency_id secara berurutan antara 3 hingga 12
                $agencyId = $agencyIdCounter;

                // Naikkan agencyIdCounter dan reset jika sudah mencapai 12
                $agencyIdCounter++;
                if ($agencyIdCounter > 12) {
                    $agencyIdCounter = 3;
                }
            } elseif (in_array($statusId, [1, 4, 5, 6])) {
                // Jika status_id adalah 1, 4, 5, atau 6, agency_id harus 1
                $agencyId = 1;
            } elseif ($statusId === 2) {
                // Jika status_id adalah 2, agency_id harus 2
                $agencyId = 2;
            } else {
                $this->command->error("Status ID tidak valid: $statusId.");
                return;
            }

            // Set get_job true hanya jika status_id adalah 3
            $getJob = $statusId === 3;

            // Buat data Marketing
            Marketing::create([
                'pendaftaran_id' => $pendaftaran->id,
                'proses_cpmi_id' => $prosesCpmi->id,
                'sales_id' => $salesPerson->id,
                'agency_id' => $agencyId,
                'foto' => $pathTujuan,
                'code_hk' => $faker->randomNumber(5, true),
                'code_tw' => $faker->randomNumber(5, true),
                'code_sgp' => $faker->randomNumber(5, true),
                'code_my' => $faker->randomNumber(5, true),
                'nomor_hp' => $faker->phoneNumber,
                'get_job' => $getJob, // Set get_job true hanya jika status_id == 3
                'tgl_job' => $getJob ? Carbon::instance($faker->dateTimeBetween(now()->subMonths(6), now())) : null,
                'national' => $faker->country,
                'kelamin' => $faker->randomElement(['MALE', 'FEMALE']),
                'lulusan' => $faker->randomElement(['Elementary School', 'Junior High School', 'Senior Highschool', 'University']),
                'agama' => $faker->randomElement(['MOESLIM', 'CRISTIAN', 'HINDU', 'BOEDHA']),
                'anakke' => $faker->numberBetween(1, 5),
                'brother' => $faker->numberBetween(1, 5),
                'sister' => $faker->numberBetween(1, 5),
                'status_nikah' => $faker->randomElement(['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOW']),
                'tinggi_badan' => $faker->numberBetween(150, 200),
                'berat_badan' => $faker->numberBetween(50, 100),
                'son' => $faker->randomElement(['Yes', 'No']),
                'daughter' => $faker->randomElement(['Yes', 'No']),
                'careofbabies' => $faker->randomElement(['YES', 'NO']),
                'careoftoddler' => $faker->randomElement(['YES', 'NO']),
                'careofchildren' => $faker->randomElement(['YES', 'NO']),
                'careofelderly' => $faker->randomElement(['YES', 'NO']),
                'careofdisabled' => $faker->randomElement(['YES', 'NO']),
                'careofbedridden' => $faker->randomElement(['YES', 'NO']),
                'careofpet' => $faker->randomElement(['YES', 'NO']),
                'householdworks' => $faker->randomElement(['YES', 'NO']),
                'carwashing' => $faker->randomElement(['YES', 'NO']),
                'gardening' => $faker->randomElement(['YES', 'NO']),
                'cooking' => $faker->randomElement(['YES', 'NO']),
                'driving' => $faker->randomElement(['YES', 'NO']),
                'homecountry' => $faker->numberBetween(1, 4),
                'spokenenglish' => $faker->randomElement(['POOR', 'FAIR', 'GOOD']),
                'spokencantonese' => $faker->randomElement(['POOR', 'FAIR', 'GOOD']),
                'spokenmandarin' => $faker->randomElement(['POOR', 'FAIR', 'GOOD']),
                'remark' => $faker->sentence,
                'babi' => $faker->randomElement(['YES', 'NO']),
                'liburbukanhariminggu' => $faker->randomElement(['YES', 'NO']),
                'berbagikamar' => $faker->randomElement(['YES', 'NO']),
                'takutanjing' => $faker->randomElement(['YES', 'NO']),
                'merokok' => $faker->randomElement(['YES', 'NO']),
                'alkohol' => $faker->randomElement(['YES', 'NO']),
                'pernahsakit' => $faker->randomElement(['YES', 'NO']),
                'ketsakit' => $faker->word,
                'tujuan_id' => $faker->randomDigitNotNull,
                'kantor_id' => $faker->randomDigitNotNull,
                'marketing_id' => $faker->randomDigitNotNull,
                'pengalaman_id' => $faker->randomDigitNotNull,
                'dapatjob' => $faker->randomElement(['YES', 'NO']),
                'created_at' => $pendaftaran->created_at,
                'updated_at' => now(),
            ]);
        }
    }
}
