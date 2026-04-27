@extends('layouts.app')

@section('title', 'Login - PKL SMKN 7 Pontianak')

@section('content')
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-4 py-8">
        <section class="w-full rounded-3xl border border-white/70 bg-white/85 p-6 shadow-xl backdrop-blur sm:p-8">
            <h1 class="text-2xl font-extrabold text-slate-900">Login</h1>
            <p class="mt-2 text-sm text-slate-600">Masuk dengan email dan password yang sudah kamu claim.</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
                    @error('email')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember"
                        class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                    Ingat saya
                </label>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Masuk
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-slate-600">
                Belum claim akun?
                <a href="{{ route('register') }}" wire:navigate
                    class="font-semibold text-cyan-700 hover:text-cyan-600">Claim via NISN</a>
            </p>
        </section>
    </main>
@endsection
