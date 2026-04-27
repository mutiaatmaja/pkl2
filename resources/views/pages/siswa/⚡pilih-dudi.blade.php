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
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari DUDI aktif..."
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-cyan-300 transition focus:border-cyan-400 focus:ring">
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @forelse ($this->dudis as $dudi)
            @php($isFull = $dudi->siswas_count >= $dudi->kuota)

            <div wire:key="dudi-siswa-{{ $dudi->id }}"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-extrabold text-slate-900">{{ $dudi->name }}</h2>
                    <span
                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold {{ $isFull ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $dudi->siswas_count }}/{{ $dudi->kuota }}
                    </span>
                </div>

                <p class="mt-2 text-sm text-slate-600">{{ $dudi->address }}</p>

                <div class="mt-4 flex items-center justify-between">
                    <p class="text-xs font-semibold {{ $isFull ? 'text-rose-600' : 'text-emerald-700' }}">
                        {{ $isFull ? 'Kuota penuh' : 'Kuota tersedia' }}
                    </p>
                    <a href="{{ route('siswa.pilih-dudi.detail', $dudi) }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
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
