<?php

use App\Http\Controllers\Admin\DudiController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Siswa\SuratController as SiswaSuratController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::welcome-pkl')->name('home');
Route::livewire('/dudi/{dudi}', 'pages::dudi-detail')->name('dudi.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showClaimForm'])->name('register');
    Route::post('/register/check-nisn', [AuthController::class, 'checkNisn'])->name('register.check-nisn');
    Route::post('/register', [AuthController::class, 'registerClaim'])->name('register.claim');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user?->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user?->hasRole('siswa')) {
            return redirect()->route('siswa.dashboard');
        }

        abort(403);
    })->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
        Route::livewire('/users', 'pages::admin.user-index')->name('users.index');
        Route::livewire('/jurusan', 'pages::admin.jurusan-index')->name('jurusan.index');
        Route::livewire('/kelas', 'pages::admin.kelas-index')->name('kelas.index');
        Route::livewire('/siswa', 'pages::admin.siswa-index')->name('siswa.index');
        Route::get('/siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
        Route::livewire('/dudi', 'pages::admin.dudi-index')->name('dudi.index');
        Route::get('/dudi/template', [DudiController::class, 'downloadTemplate'])->name('dudi.template');
        Route::livewire('/dudi/{dudi}', 'pages::admin.dudi-detail')->name('dudi.show');
        Route::get('/dudi/{dudi}/surat-permohonan', [DudiController::class, 'suratPermohonan'])->name('dudi.surat-permohonan');
        Route::livewire('/request-dudi', 'pages::admin.dudi-request-index')->name('dudi-request.index');
        Route::livewire('/pengaturan', 'pages::admin.pengaturan')->name('pengaturan');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::view('/dashboard', 'dashboard-siswa')->name('dashboard');
        Route::livewire('/profil', 'pages::siswa.profil')->name('profile');
        Route::middleware('siswa.profile.completed')->group(function () {
            Route::livewire('/pilih-dudi', 'pages::siswa.pilih-dudi')->name('pilih-dudi');
            Route::livewire('/pilih-dudi/{dudi}', 'pages::siswa.dudi-detail')->name('pilih-dudi.detail');
        });
        Route::livewire('/request-dudi', 'pages::siswa.request-dudi')->name('request-dudi');
        Route::livewire('/cetak-surat', 'pages::siswa.cetak-surat')->name('cetak-surat');
        Route::get('/surat-permohonan/{dudi}', [SiswaSuratController::class, 'suratPermohonan'])->name('surat-permohonan');
    });
});
