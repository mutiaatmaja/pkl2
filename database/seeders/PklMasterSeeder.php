<?php

namespace Database\Seeders;

use App\Models\Dudi;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PklMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Admin',
                'description' => 'Administrator aplikasi PKL',
            ]
        );

        $siswaRole = Role::query()->firstOrCreate(
            ['name' => 'siswa'],
            [
                'display_name' => 'Siswa',
                'description' => 'Siswa peserta PKL',
            ]
        );

        $adminUser = User::query()->updateOrCreate(
            ['email' => 'admin@smkn7-pkl.test'],
            [
                'name' => 'Admin PKL SMKN 7 Pontianak',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles([$adminRole]);

        $jurusanRpl = Jurusan::query()->firstOrCreate(
            ['code' => 'RPL'],
            ['name' => 'Rekayasa Perangkat Lunak']
        );

        $kelasA = Kelas::query()->firstOrCreate([
            'jurusan_id' => $jurusanRpl->id,
            'name' => 'XI-RPL-A',
        ]);

        $kelasB = Kelas::query()->firstOrCreate([
            'jurusan_id' => $jurusanRpl->id,
            'name' => 'XI-RPL-B',
        ]);

        $sampleSiswa = [
            ['name' => 'Andi Saputra', 'email' => 'andi.saputra@siswa.smkn7.test', 'nis' => '2407001', 'nisn' => '007001001'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@siswa.smkn7.test', 'nis' => '2407002', 'nisn' => '007001002'],
            ['name' => 'Citra Maharani', 'email' => 'citra.maharani@siswa.smkn7.test', 'nis' => '2407003', 'nisn' => '007001003'],
            ['name' => 'Dimas Pratama', 'email' => 'dimas.pratama@siswa.smkn7.test', 'nis' => '2407004', 'nisn' => '007001004'],
            ['name' => 'Eka Putri', 'email' => 'eka.putri@siswa.smkn7.test', 'nis' => '2407005', 'nisn' => '007001005'],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@siswa.smkn7.test', 'nis' => '2407006', 'nisn' => '007001006'],
            ['name' => 'Gina Lestari', 'email' => 'gina.lestari@siswa.smkn7.test', 'nis' => '2407007', 'nisn' => '007001007'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra.wijaya@siswa.smkn7.test', 'nis' => '2407008', 'nisn' => '007001008'],
            ['name' => 'Intan Permata', 'email' => 'intan.permata@siswa.smkn7.test', 'nis' => '2407009', 'nisn' => '007001009'],
            ['name' => 'Joko Prabowo', 'email' => 'joko.prabowo@siswa.smkn7.test', 'nis' => '2407010', 'nisn' => '007001010'],
        ];

        foreach ($sampleSiswa as $index => $data) {
            $claimEmail = $data['nisn'].'@claim.smkn7.local';

            $user = User::query()->updateOrCreate(
                ['email' => $claimEmail],
                [
                    'name' => $data['name'],
                    'email' => $data['nisn'].'@claim.smkn7.local',
                    'password' => Hash::make(Str::password(20)),
                    'email_verified_at' => null,
                ]
            );
            $user->syncRoles([$siswaRole]);

            $kelas = $index % 2 === 0 ? $kelasA : $kelasB;

            Siswa::query()->updateOrCreate(
                ['nis' => $data['nis']],
                [
                    'user_id' => $user->id,
                    'jurusan_id' => $jurusanRpl->id,
                    'kelas_id' => $kelas->id,
                    'nisn' => $data['nisn'],
                ]
            );
        }

        $sampleDudi = [
            'PT Pontianak Digital Solusi',
            'CV Kalbar Teknologi',
            'PT Borneo Inovasi Nusantara',
            'PT Nusantara Data Center',
            'CV Mitra Jaringan Pontianak',
            'PT Sarana Sistem Informasi',
            'PT Khatulistiwa Media Digital',
            'CV Solusi Web Kalimantan',
            'PT Lintas Teknologi Pontianak',
            'CV Aplikasi Cerdas Indonesia',
            'PT Borneo Cloud Computing',
            'CV Mandiri Software',
            'PT Integrasi Sistem Kalbar',
            'CV Kreatif Digital Pontianak',
            'PT Solusi Infrastruktur TI',
            'CV Cipta Inovasi Teknologi',
            'PT Sentra Aplikasi Daerah',
            'CV Mitra Startup Pontianak',
            'PT Teknologi Edukasi Khatulistiwa',
            'CV Digitalisasi Usaha Kalbar',
        ];

        $panggilanPimpinan = ['Pimpinan', 'Ketua', 'Dekan', 'Direktur', 'Manajer'];

        $dudiIds = [];

        foreach ($sampleDudi as $index => $dudi) {
            $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            $dudiRecord = Dudi::query()->updateOrCreate(
                ['name' => $dudi],
                [
                    'panggilan_pimpinan' => $panggilanPimpinan[$index % count($panggilanPimpinan)],
                    'address' => 'Jl. Contoh Alamat No. '.$number.', Pontianak',
                    'aktif' => true,
                    'kuota' => 3 + ($index % 4),
                ]
            );

            $dudiIds[] = $dudiRecord->id;
        }

        $sampleSiswaNis = collect($sampleSiswa)->pluck('nis')->values();
        $selectedSiswaNis = $sampleSiswaNis->take(6);

        foreach ($selectedSiswaNis as $index => $nis) {
            Siswa::query()
                ->where('nis', $nis)
                ->update([
                    'dudi_id' => $dudiIds[$index] ?? null,
                ]);
        }
    }
}
