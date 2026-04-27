<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Layout('layouts.siswa')] class extends Component {
    #[Computed]
    public function siswa()
    {
        return auth()->user()?->siswa?->load('dudi');
    }
};
?>

<div class="space-y-6">
    <div>
        <p class="text-xs font-bold tracking-[0.22em] text-cyan-700">MENU SISWA</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Cetak Surat</h1>
        <p class="mt-1 text-sm text-slate-500">Cetak surat permohonan sesuai DUDI yang sudah Anda pilih.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">DUDI terpilih:</p>
        <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $this->siswa?->dudi?->name ?? 'Belum memilih DUDI' }}
        </p>

        @if ($this->siswa?->dudi)
            <a href="{{ route('siswa.surat-permohonan', $this->siswa->dudi) }}" target="_blank"
                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Cetak Surat Permohonan
            </a>
        @else
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                Anda belum memilih DUDI. Pilih DUDI terlebih dahulu sebelum mencetak surat.
            </div>
            <a href="{{ route('siswa.pilih-dudi') }}" wire:navigate
                class="mt-3 inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                Ke Halaman Pilih DUDI
            </a>
        @endif
    </div>
</div>
