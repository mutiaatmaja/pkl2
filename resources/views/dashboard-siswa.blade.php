@extends('layouts.siswa')

@section('title', 'Dashboard Siswa - PKL SMKN 7 Pontianak')

@section('content')
    @php($siswa = auth()->user()?->siswa)

    <div class="space-y-6">
        <div>
            <p class="text-xs font-bold tracking-[0.22em] text-cyan-700">PORTAL SISWA</p>
            <h1 class="mt-1 text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">Dashboard Siswa</h1>
            <p class="mt-1 text-sm text-slate-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">NIS</p>
                <p class="mt-2 text-2xl font-extrabold text-cyan-900">{{ $siswa?->nis ?? '-' }}</p>
            </div>
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">Kelas</p>
                <p class="mt-2 text-2xl font-extrabold text-indigo-900">{{ $siswa?->kelas?->name ?? '-' }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Status DUDI</p>
                <p class="mt-2 text-lg font-extrabold text-emerald-900">
                    {{ $siswa?->dudi ? 'Sudah memilih DUDI' : 'Belum memilih DUDI' }}
                </p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-extrabold text-slate-900">DUDI Pilihan Saat Ini</h2>
            <p class="mt-2 text-sm text-slate-600">
                {{ $siswa?->dudi?->name ?? 'Belum ada DUDI terpilih.' }}
            </p>
            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                <a href="{{ route('siswa.pilih-dudi') }}" wire:navigate
                    class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700 sm:w-auto">
                    Pilih / Ganti DUDI
                </a>
                <a href="{{ route('siswa.cetak-surat') }}" wire:navigate
                    class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 sm:w-auto">
                    Cetak Surat
                </a>
            </div>
        </div>
    </div>
@endsection
