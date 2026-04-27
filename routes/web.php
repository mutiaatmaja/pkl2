<?php

use App\Http\Controllers\Admin\DudiController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\AuthController;
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
	Route::view('/dashboard', 'dashboard')->name('dashboard');
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

	Route::prefix('admin')->name('admin.')->group(function () {
		Route::livewire('/jurusan', 'pages::admin.jurusan-index')->name('jurusan.index');
		Route::livewire('/kelas', 'pages::admin.kelas-index')->name('kelas.index');
		Route::livewire('/siswa', 'pages::admin.siswa-index')->name('siswa.index');
		Route::get('/siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
		Route::livewire('/dudi', 'pages::admin.dudi-index')->name('dudi.index');
		Route::livewire('/dudi/{dudi}', 'pages::admin.dudi-detail')->name('dudi.show');
		Route::get('/dudi/{dudi}/surat-permohonan', [DudiController::class, 'suratPermohonan'])->name('dudi.surat-permohonan');
		Route::get('/dudi/template', [DudiController::class, 'downloadTemplate'])->name('dudi.template');
		Route::livewire('/pengaturan', 'pages::admin.pengaturan')->name('pengaturan');
	});
});
