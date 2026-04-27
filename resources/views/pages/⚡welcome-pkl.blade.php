<?php

use App\Models\Dudi;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $search = '';

    public int $totalSiswa = 0;

    public int $sudahPilihMagang = 0;

    public int $belumPilihMagang = 0;

    public int $totalDudi = 0;

    public function mount(): void
    {
        $this->totalSiswa = Siswa::query()->count();
        $this->sudahPilihMagang = Siswa::query()->whereNotNull('dudi_id')->count();
        $this->belumPilihMagang = Siswa::query()->whereNull('dudi_id')->count();
        $this->totalDudi = Dudi::query()->count();
    }

    public function getDudisProperty(): Collection
    {
        return Dudi::query()
            ->select(['id', 'name', 'address', 'aktif', 'kuota'])
            ->withCount('siswas')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery->where('name', 'like', '%' . $this->search . '%')->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('aktif')
            ->orderBy('name')
            ->limit(100)
            ->get();
    }
};
?>

<div class="relative overflow-hidden">
    <div class="pointer-events-none absolute -top-16 -right-20 h-64 w-64 rounded-full bg-cyan-300/35 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-20 h-72 w-72 rounded-full bg-emerald-300/35 blur-3xl">
    </div>

    <div class="relative mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <section class="rounded-3xl border border-white/60 bg-white/70 p-6 shadow-xl backdrop-blur sm:p-8">
            <p class="text-xs font-bold tracking-[0.24em] text-cyan-700">PORTAL PKL SMKN 7 PONTIANAK</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Landing Informasi PKL
                Siswa</h1>
            <p class="mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">
                Pantau ringkasan data siswa, progres pemilihan tempat magang, serta data DUDI aktif dalam satu halaman
                yang ringkas.
            </p>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                        Dashboard
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" wire:navigate
                            class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                            Login
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" wire:navigate
                            class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                            Claim Akun
                        </a>
                    @endif
                @endauth
            </div>
        </section>
        <section class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-cyan-100 bg-cyan-50/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">Total Siswa</p>
                <p class="mt-2 text-3xl font-extrabold text-cyan-900">{{ number_format($totalSiswa) }}</p>
            </article>

            <article class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Sudah Pilih Magang</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-900">{{ number_format($sudahPilihMagang) }}</p>
            </article>

            <article class="rounded-2xl border border-amber-100 bg-amber-50/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Belum Pilih Magang</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-900">{{ number_format($belumPilihMagang) }}</p>
            </article>

            <article class="rounded-2xl border border-indigo-100 bg-indigo-50/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700">Jumlah DUDI</p>
                <p class="mt-2 text-3xl font-extrabold text-indigo-900">{{ number_format($totalDudi) }}</p>
            </article>
        </section>

        <section class="mt-6 rounded-3xl border border-white/70 bg-white/80 p-4 shadow-xl backdrop-blur sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Daftar DUDI</h2>
                    <p class="text-sm text-slate-600">Pencarian cepat berdasarkan nama atau alamat.</p>
                </div>

                <div class="relative w-full sm:w-80">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari DUDI..."
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
                    <div wire:loading wire:target="search"
                        class="pointer-events-none absolute top-2.5 right-3 text-xs font-semibold text-cyan-700">
                        mencari...
                    </div>
                </div>
            </div>

            <div class="mt-4">
                @if ($this->dudis->isEmpty())
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white px-4 py-5 text-sm text-slate-500">
                        Data DUDI tidak ditemukan.
                    </div>
                @else
                    <div class="space-y-3 md:hidden">
                        @foreach ($this->dudis as $dudi)
                            <article wire:key="dudi-mobile-{{ $dudi->id }}"
                                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-sm font-bold text-slate-900">{{ $dudi->name }}</h3>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold {{ $dudi->aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $dudi->aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>

                                <p class="mt-2 text-xs leading-relaxed text-slate-600">{{ $dudi->address }}</p>

                                <div class="mt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">Kuota: <span
                                                class="font-bold text-slate-800">{{ $dudi->kuota }}</span></p>
                                        <p class="mt-0.5 text-xs font-semibold {{ $dudi->siswas_count >= $dudi->kuota ? 'text-rose-600' : 'text-emerald-700' }}">
                                            Terisi: {{ $dudi->siswas_count }}/{{ $dudi->kuota }}
                                            ({{ $dudi->siswas_count >= $dudi->kuota ? 'Penuh' : 'Tersedia' }})
                                        </p>
                                    </div>
                                    <a href="{{ route('dudi.show', $dudi) }}" wire:navigate
                                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                                        Detail
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="hidden overflow-hidden rounded-2xl border border-slate-200 md:block">
                        <div
                            class="grid grid-cols-12 bg-slate-100/80 px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 sm:px-4">
                            <div class="col-span-3">DUDI</div>
                            <div class="col-span-3">Alamat</div>
                            <div class="col-span-1 text-center">Aktif</div>
                            <div class="col-span-2 text-center">Status Terisi</div>
                            <div class="col-span-1 text-right">Kuota</div>
                            <div class="col-span-2 text-right">Aksi</div>
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            @foreach ($this->dudis as $dudi)
                                <div wire:key="dudi-desktop-{{ $dudi->id }}"
                                    class="grid grid-cols-12 items-center border-t border-slate-100 px-3 py-2 text-sm text-slate-700 sm:px-4">
                                    <div class="col-span-3 pr-2 font-semibold text-slate-900">{{ $dudi->name }}</div>
                                    <div class="col-span-3 truncate pr-2 text-slate-600">{{ $dudi->address }}</div>
                                    <div class="col-span-1 text-center">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold {{ $dudi->aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ $dudi->aktif ? 'YA' : 'NO' }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 text-center">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold {{ $dudi->siswas_count >= $dudi->kuota ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $dudi->siswas_count }}/{{ $dudi->kuota }}
                                            {{ $dudi->siswas_count >= $dudi->kuota ? '(Penuh)' : '(Tersedia)' }}
                                        </span>
                                    </div>
                                    <div class="col-span-1 text-right font-bold text-slate-800">{{ $dudi->kuota }}</div>
                                    <div class="col-span-2 text-right">
                                        <a href="{{ route('dudi.show', $dudi) }}" wire:navigate
                                            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 whitespace-nowrap">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
