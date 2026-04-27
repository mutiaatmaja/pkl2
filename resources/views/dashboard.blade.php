@extends('layouts.admin')

@section('title', 'Dashboard - PKL SMKN 7 Pontianak')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-xs font-bold tracking-[0.22em] text-cyan-700">PKL SMKN 7 PONTIANAK</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Siswa</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">
                    {{ \App\Models\Siswa::count() }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sudah Magang</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-600">
                    {{ \App\Models\Siswa::whereNotNull('dudi_id')->count() }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Belum Magang</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-500">
                    {{ \App\Models\Siswa::whereNull('dudi_id')->count() }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total DUDI</p>
                <p class="mt-2 text-3xl font-extrabold text-cyan-600">
                    {{ \App\Models\Dudi::count() }}
                </p>
            </div>
        </div>
    </div>
@endsection
