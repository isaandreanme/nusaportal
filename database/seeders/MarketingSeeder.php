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
        $prosesCpmis = ProsesCpmi::all();
        $sales = Sales::all();
        $agencies = Agency::all();

        // Jika tabel Pendaftaran kosong, buat beberapa data dummy
        if ($pendaftarans->isEmpty()) {
            Pendaftaran::factory()->count(5)->create();
            $pendaftarans = Pendaftaran::all();
        }

        // Jika tabel ProsesCpmi kosong, buat beberapa data dummy
        if ($prosesCpmis->isEmpty()) {
            ProsesCpmi::factory()->count(5)->create();
            $prosesCpmis = ProsesCpmi::all();
        }

        // Jika tabel Sales kosong, tampilkan pesan error
        if ($sales->isEmpty()) {
            $this->command->error('Tidak ada data di tabel Sales. Harap tambahkan data Sales terlebih dahulu.');
            return;
        }

        // Jika tabel Agency kosong, tampilkan pesan error
        if ($agencies->isEmpty()) {
            $this->command->error('Tidak ada data di tabel Agencies. Harap tambahkan data Agencies terlebih dahulu.');
            return;
        }

        // Nama file gambar yang digunakan
        $namaFile = 'contohfotomaids.jpg';
        $direktoriFoto = 'biodata/foto';

        // Buat direktori penyimpanan jika belum ada
        Storage::disk('public')->makeDirectory($direktoriFoto);
        $pathAsli = public_path("images/$namaFile");

        // Periksa apakah file gambar ada
        if (!file_exists($pathAsli)) {
            $this->command->error("File gambar $namaFile tidak ditemukan di public/images.");
            return;
        }

        // Salin file gambar ke direktori 'storage/public/biodata/foto'
        $pathTujuan = "$direktoriFoto/$namaFile";
        Storage::disk('public')->put($pathTujuan, file_get_contents($pathAsli));

        // Iterasi melalui setiap data pendaftaran untuk membuat data Marketing
        foreach ($pendaftarans as $pendaftaran) {
            $prosesCpmi = $prosesCpmis->random();
            $salesPerson = $sales->random();
            $statusId = $prosesCpmi->status_id;

            // Tentukan agency_id dan get_job berdasarkan status_id
            if (in_array($statusId, [1, 2])) {
                $agencyId = 2;
            } elseif (in_array($statusId, [4, 5, 6])) {
                $agencyId = 1;
            } elseif ($statusId === 3) {
                $availableAgencies = $agencies->whereNotIn('id', [1, 2]);
                $agencyId = $availableAgencies->isNotEmpty() ? $availableAgencies->random()->id : null;

                // Jika tidak ada agency selain 1 dan 2, tampilkan pesan error
                if (!$agencyId) {
                    $this->command->error('Tidak ada agency selain 1 dan 2 yang tersedia.');
                    return;
                }
            }

            // Tentukan get_job berdasarkan status_id
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
                'get_job' => $getJob,
                'tgl_job' => $getJob ? Carbon::instance($faker->dateTimeBetween(now()->subMonths(6), now())) : null, // Set tgl_job with Carbon if get_job is true
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
