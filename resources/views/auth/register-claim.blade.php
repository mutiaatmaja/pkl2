@extends('layouts.app')

@section('title', 'Claim Akun Siswa - PKL SMKN 7 Pontianak')

@section('content')
    @php
        $claimNisn = session('claim_nisn', old('nisn'));
        $isClaimStep = filled($claimNisn) && !$errors->has('nisn');
    @endphp

    <main class="mx-auto flex min-h-screen w-full max-w-lg items-center px-4 py-8">
        <section class="w-full rounded-3xl border border-white/70 bg-white/85 p-6 shadow-xl backdrop-blur sm:p-8">
            <h1 class="text-2xl font-extrabold text-slate-900">Claim Akun Siswa</h1>
            <p class="mt-2 text-sm text-slate-600">Masukkan NISN untuk validasi data siswa. Jika valid, lanjutkan isi email
                dan password.</p>

            @if (session('claim_nisn'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    NISN ditemukan untuk siswa: <span class="font-semibold">{{ session('claim_name') }}</span>
                </div>
            @endif

            @if (!$isClaimStep)
                <form method="POST" action="{{ route('register.check-nisn') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="nisn" class="mb-1 block text-sm font-semibold text-slate-700">NISN</label>
                        <input id="nisn" name="nisn" type="text" value="{{ old('nisn') }}" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
                        @error('nisn')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                        Cek NISN
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('register.claim') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="nisn" value="{{ $claimNisn }}">

                    <div>
                        <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
                        @error('email')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Password</label>
                        <input id="password" name="password" type="password" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
                        @error('password')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation"
                            class="mb-1 block text-sm font-semibold text-slate-700">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
                    </div>

                    @error('nisn')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                        Claim Akun
                    </button>
                </form>
            @endif

            <p class="mt-4 text-center text-sm text-slate-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" wire:navigate
                    class="font-semibold text-cyan-700 hover:text-cyan-600">Login</a>
            </p>
        </section>
    </main>
@endsection
