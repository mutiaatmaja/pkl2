<?php

use App\Models\Dudi;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.siswa')] class extends Component {
    public string $search = '';

    #[Computed]
    public function dudis()
    {
        return Dudi::query()
            ->withCount('siswas')
            ->where('aktif', true)
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery->where('name', 'like', '%' . $this->search . '%')->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->limit(100)
            ->get();
    }
};
?>

<div class="space-y-6">
    <div>
        <p class="text-xs font-bold tracking-[0.22em] text-cyan-700">MENU SISWA</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Pilih DUDI</h1>
        <p class="mt-1 text-sm text-slate-500">Siswa wajib membuka detail DUDI terlebih dahulu untuk melihat profil DUDI
            dan peserta lain sebelum memilih.</p>
        <p class="mt-2 text-xs font-semibold text-amber-700">DUDI yang status suratnya sudah dicetak atau sudah diterima
            tetap ditampilkan, tetapi akan dinonaktifkan sehingga tidak bisa dipilih lagi.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari DUDI aktif..."
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @forelse ($this->dudis as $dudi)
            @php($isFull = $dudi->siswas_count >= $dudi->kuota)
            @php($isLocked = $dudi->sudah_cetak_surat || $dudi->diterima)

            <div wire:key="dudi-siswa-{{ $dudi->id }}"
                class="relative rounded-2xl border p-5 shadow-sm transition {{ $isLocked ? 'border-slate-200 bg-slate-50/90 opacity-75' : 'border-slate-200 bg-white' }}">
                @if ($isLocked)
                    <div class="pointer-events-none absolute inset-0 flex items-start justify-end p-3">
                        <span
                            class="rounded-full bg-slate-900/80 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-white">
                            Terkunci
                        </span>
                    </div>
                @endif

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">{{ $dudi->name }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold {{ $dudi->sudah_cetak_surat ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $dudi->sudah_cetak_surat ? 'Sudah Dicetak' : 'Belum Dicetak' }}
                            </span>
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold {{ $dudi->diterima ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $dudi->diterima ? 'Diterima' : 'Belum Diterima' }}
                            </span>
                        </div>
                    </div>
                    <span
                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold {{ $isFull ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $dudi->siswas_count }}/{{ $dudi->kuota }}
                    </span>
                </div>

                <p class="mt-2 text-sm text-slate-600">{{ $dudi->address }}</p>

                <div class="mt-4 flex items-center justify-between">
                    <p
                        class="text-xs font-semibold {{ $isLocked ? 'text-slate-500' : ($isFull ? 'text-rose-600' : 'text-emerald-700') }}">
                        {{ $isLocked ? 'Tidak bisa dipilih lagi' : ($isFull ? 'Kuota penuh' : 'Kuota tersedia') }}
                    </p>
                    <a href="{{ route('siswa.pilih-dudi.detail', $dudi) }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition {{ $isLocked ? 'bg-slate-500 hover:bg-slate-600' : 'bg-cyan-600 hover:bg-cyan-700' }}">
                        Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-500 lg:col-span-2">
                DUDI aktif tidak ditemukan.
            </div>
        @endforelse
    </div>
</div>
