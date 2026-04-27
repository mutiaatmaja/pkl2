<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaBelumClaimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswaRole = Role::query()->firstOrCreate(
            ['name' => 'siswa'],
            [
                'display_name' => 'Siswa',
                'description' => 'Siswa peserta PKL',
            ]
        );

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

        $sampleSiswaBelumClaim = [
            ['name' => 'Kevin Ardiansyah', 'nis' => '2407011', 'nisn' => '007001011'],
            ['name' => 'Lina Oktavia', 'nis' => '2407012', 'nisn' => '007001012'],
            ['name' => 'Miko Firmansyah', 'nis' => '2407013', 'nisn' => '007001013'],
            ['name' => 'Nadia Wulandari', 'nis' => '2407014', 'nisn' => '007001014'],
            ['name' => 'Oky Prasetyo', 'nis' => '2407015', 'nisn' => '007001015'],
        ];

        foreach ($sampleSiswaBelumClaim as $index => $data) {
            $claimEmail = $data['nisn'].'@claim.smkn7.local';

            $user = User::query()->firstOrCreate(
                ['email' => $claimEmail],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('unclaimed-account'),
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
                    'dudi_id' => null,
                ]
            );
        }
    }
}
