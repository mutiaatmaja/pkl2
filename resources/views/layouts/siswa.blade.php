<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $title ?? 'Siswa - PKL SMKN 7 Pontianak')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>

    @livewireStyles
</head>

<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <input id="siswa-sidebar-toggle" type="checkbox" class="peer sr-only">

        <label for="siswa-sidebar-toggle"
            class="fixed inset-0 z-30 hidden bg-slate-900/45 backdrop-blur-sm peer-checked:block md:hidden"></label>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-white shadow-md transition-transform duration-200 peer-checked:translate-x-0 md:translate-x-0">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                <div>
                    <p class="text-[10px] font-bold tracking-[0.22em] text-cyan-600">PKL SMKN 7 PONTIANAK</p>
                    <h1 class="text-base font-extrabold text-slate-900">Portal Siswa</h1>
                </div>
                <label for="siswa-sidebar-toggle"
                    class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 md:hidden">
                    ✕
                </label>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
                <a href="{{ route('siswa.dashboard') }}" wire:navigate
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors
                           {{ request()->routeIs('siswa.dashboard') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="text-base leading-none">🏠</span>
                    Dashboard
                </a>

                <p class="mt-5 mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Menu Siswa</p>

                <a href="{{ route('siswa.profile') }}" wire:navigate
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors
                           {{ request()->routeIs('siswa.profile') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="text-base leading-none">👤</span>
                    Profil Saya
                </a>

                <a href="{{ route('siswa.pilih-dudi') }}" wire:navigate
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors
                           {{ request()->routeIs('siswa.pilih-dudi*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="text-base leading-none">🏢</span>
                    Pilih DUDI
                </a>

                <a href="{{ route('siswa.request-dudi') }}" wire:navigate
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors
                           {{ request()->routeIs('siswa.request-dudi') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="text-base leading-none">📝</span>
                    Request DUDI
                </a>

                <a href="{{ route('siswa.cetak-surat') }}" wire:navigate
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors
                           {{ request()->routeIs('siswa.cetak-surat') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="text-base leading-none">📄</span>
                    Cetak Surat
                </a>
            </nav>

            <div class="border-t border-slate-100 px-5 py-4">
                <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()?->name ?? '-' }}</p>
                <p class="text-xs text-slate-400 truncate mt-0.5">{{ auth()->user()?->email ?? '' }}</p>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">
                        <span>↩</span> Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-h-screen md:ml-64">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur md:hidden">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-extrabold tracking-wide text-slate-900">Portal Siswa</p>
                    <label for="siswa-sidebar-toggle"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">
                        <span>☰</span> Menu
                    </label>
                </div>
            </header>

            <main class="p-4 sm:p-6 md:p-8">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </div>

    @livewireScripts
</body>

</html>
