<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sponsors')->insert([
            [
                'nama' => 'OFFICE',
                'nomortelp' => '1234567890',
                'keterangan' => 'Office Address',
            ],
            [
                'nama' => 'SPONSOR 1',
                'nomortelp' => '0987654321',
                'keterangan' => 'Teman A',
            ],
            [
                'nama' => 'SPONSOR 2',
                'nomortelp' => '1122334455',
                'keterangan' => 'Saudara A',
            ],
            [
                'nama' => 'SPONSOR 3',
                'nomortelp' => '5566778899',
                'keterangan' => 'Keluarga B',
            ],
        ]);
    }
}
