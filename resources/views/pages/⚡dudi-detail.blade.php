<?php

use App\Models\Dudi;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public Dudi $dudi;

    public function mount(Dudi $dudi): void
    {
        $this->dudi = $dudi;
    }

    public function getPesertaProperty(): Collection
    {
        return Siswa::query()
            ->with(['user:id,name', 'kelas:id,name'])
            ->where('dudi_id', $this->dudi->id)
            ->orderBy('nis')
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
            <a href="{{ route('home') }}" wire:navigate
                class="inline-flex items-center text-sm font-semibold text-cyan-700 transition hover:text-cyan-600">
                Kembali ke daftar DUDI
            </a>

            <p class="mt-4 text-xs font-bold tracking-[0.24em] text-cyan-700">DETAIL DUDI</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $dudi->name }}</h1>
            <p class="mt-3 max-w-3xl text-sm text-slate-600 sm:text-base">{{ $dudi->address }}</p>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/80 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700">Status</p>
                    <p class="mt-1 text-lg font-bold text-indigo-900">{{ $dudi->aktif ? 'Aktif' : 'Nonaktif' }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Kuota</p>
                    <p class="mt-1 text-lg font-bold text-amber-900">{{ $dudi->kuota }} siswa</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Peserta Memilih</p>
                    <p class="mt-1 text-lg font-bold text-emerald-900">{{ $this->peserta->count() }} siswa</p>
                </div>
            </div>
        </section>

        <section class="mt-6 rounded-3xl border border-white/70 bg-white/80 p-4 shadow-xl backdrop-blur sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">Daftar Peserta yang Memilih DUDI Ini</h2>
            <p class="mt-1 text-sm text-slate-600">Berikut siswa yang sudah memilih {{ $dudi->name }} sebagai tempat
                PKL.</p>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <div
                    class="grid grid-cols-12 bg-slate-100/80 px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 sm:px-4">
                    <div class="col-span-2">NIS</div>
                    <div class="col-span-4">Nama Siswa</div>
                    <div class="col-span-3">Kelas</div>
                    <div class="col-span-3">NISN</div>
                </div>

                <div class="max-h-96 overflow-y-auto">
                    @forelse ($this->peserta as $peserta)
                        <div wire:key="peserta-{{ $peserta->id }}"
                            class="grid grid-cols-12 items-center border-t border-slate-100 px-3 py-2 text-sm text-slate-700 sm:px-4">
                            <div class="col-span-2 font-semibold text-slate-900">{{ $peserta->nis }}</div>
                            <div class="col-span-4 pr-2 text-slate-800">{{ $peserta->user?->name ?? '-' }}</div>
                            <div class="col-span-3 text-slate-600">{{ $peserta->kelas?->name ?? '-' }}</div>
                            <div class="col-span-3 text-slate-600">{{ $peserta->nisn }}</div>
                        </div>
                    @empty
                        <div class="px-4 py-5 text-sm text-slate-500">Belum ada peserta yang memilih DUDI ini.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
