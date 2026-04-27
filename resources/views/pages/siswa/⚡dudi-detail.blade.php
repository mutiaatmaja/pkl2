<?php

use App\Models\Dudi;
use App\Models\Siswa;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.siswa')] class extends Component {
    public Dudi $dudi;

    public bool $showConfirmModal = false;

    public ?string $toast = null;
    public string $toastType = 'success';

    public function mount(Dudi $dudi): void
    {
        if (!$dudi->aktif) {
            abort(404);
        }

        $this->dudi = $dudi;
    }

    #[Computed]
    public function currentSiswa(): ?Siswa
    {
        return auth()
            ->user()
            ?->siswa?->load(['user:id,name', 'kelas:id,name', 'dudi:id,name']);
    }

    #[Computed]
    public function pesertaPadaDudi()
    {
        return Siswa::query()
            ->with(['user:id,name', 'kelas:id,name'])
            ->where('dudi_id', $this->dudi->id)
            ->orderBy('nis')
            ->get();
    }

    #[Computed]
    public function pesertaCount(): int
    {
        return (int) Siswa::query()->where('dudi_id', $this->dudi->id)->count();
    }

    #[Computed]
    public function isCurrentSiswaMemilihDudiIni(): bool
    {
        return $this->currentSiswa && (int) $this->currentSiswa->dudi_id === (int) $this->dudi->id;
    }

    #[Computed]
    public function isCurrentSiswaSudahMemilihDudiLain(): bool
    {
        return $this->currentSiswa && $this->currentSiswa->dudi_id !== null && !$this->isCurrentSiswaMemilihDudiIni;
    }

    #[Computed]
    public function isDudiPenuhUntukPilihanBaru(): bool
    {
        return !$this->isCurrentSiswaMemilihDudiIni && $this->pesertaCount >= $this->dudi->kuota;
    }

    public function requestPilihDudi(): void
    {
        $siswa = $this->currentSiswa;

        if (!$siswa) {
            $this->notify('Profil siswa tidak ditemukan.', 'error');

            return;
        }

        if ($this->isCurrentSiswaSudahMemilihDudiLain) {
            $this->notify('Anda sudah memilih DUDI lain. Perubahan hanya bisa dilakukan oleh Admin.', 'error');

            return;
        }

        if ($this->isCurrentSiswaMemilihDudiIni) {
            $this->notify('DUDI ini sudah menjadi pilihan Anda.', 'success');

            return;
        }

        if ($this->isDudiPenuhUntukPilihanBaru) {
            $this->notify('DUDI ini sudah penuh, silakan pilih DUDI lain.', 'error');

            return;
        }

        $this->showConfirmModal = true;
    }

    public function cancelConfirm(): void
    {
        $this->showConfirmModal = false;
    }

    public function confirmPilihDudi(): void
    {
        $siswa = $this->currentSiswa;

        if (!$siswa) {
            $this->notify('Profil siswa tidak ditemukan.', 'error');

            return;
        }

        if ($this->isCurrentSiswaSudahMemilihDudiLain) {
            $this->showConfirmModal = false;
            $this->notify('Anda sudah memilih DUDI lain. Perubahan hanya bisa dilakukan oleh Admin.', 'error');

            return;
        }

        if ($this->isCurrentSiswaMemilihDudiIni) {
            $this->showConfirmModal = false;
            $this->notify('DUDI ini sudah menjadi pilihan Anda.', 'success');

            return;
        }

        if ($this->isDudiPenuhUntukPilihanBaru) {
            $this->showConfirmModal = false;
            $this->notify('DUDI ini sudah penuh, silakan pilih DUDI lain.', 'error');

            return;
        }

        $siswa->update([
            'dudi_id' => $this->dudi->id,
        ]);

        $this->showConfirmModal = false;
        $this->notify('Pilihan DUDI berhasil disimpan.');
    }

    public function dismissToast(): void
    {
        $this->toast = null;
    }

    private function notify(string $message, string $type = 'success'): void
    {
        $this->toast = $message;
        $this->toastType = $type;
    }
};
?>

<div class="space-y-6">
    <div>
        <a href="{{ route('siswa.pilih-dudi') }}" wire:navigate
            class="text-sm font-semibold text-cyan-700 hover:text-cyan-600">
            Kembali ke daftar DUDI
        </a>
        <p class="mt-3 text-xs font-bold tracking-[0.22em] text-cyan-700">DETAIL DUDI</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">{{ $dudi->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $dudi->address }}</p>
    </div>

    @if ($toast)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false;
            $wire.dismissToast(); }, 3500)"
            class="fixed right-5 top-5 z-50 flex items-center gap-3 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg
                   {{ $toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500' }}">
            <span>{{ $toastType === 'success' ? '✓' : '✕' }}</span>
            {{ $toast }}
        </div>
    @endif

    @if ($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl">
                <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Pilihan DUDI</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Setelah memilih DUDI ini, Anda <strong>tidak dapat mengganti atau membatalkan</strong> pilihan
                    secara mandiri.
                    Perubahan hanya bisa dilakukan oleh Admin. Lanjutkan?
                </p>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="cancelConfirm" wire:loading.attr="disabled"
                        wire:target="cancelConfirm,confirmPilihDudi"
                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" wire:click="confirmPilihDudi" wire:loading.attr="disabled"
                        wire:target="confirmPilihDudi"
                        class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
                        <span wire:loading.remove wire:target="confirmPilihDudi">Ya, Pilih DUDI</span>
                        <span wire:loading wire:target="confirmPilihDudi"
                            class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-extrabold text-slate-900">Profil Siswa</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nama Siswa</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $this->currentSiswa?->user?->name ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">NIS</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $this->currentSiswa?->nis ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Pilihan Saat Ini</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $this->currentSiswa?->dudi?->name ?? 'Belum memilih DUDI' }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">Status</p>
            <p class="mt-1 text-lg font-extrabold text-cyan-900">{{ $dudi->aktif ? 'Aktif' : 'Tidak Aktif' }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Kuota</p>
            <p class="mt-1 text-lg font-extrabold text-amber-900">{{ $dudi->kuota }} siswa</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Peserta/Kuota</p>
            <p class="mt-1 text-lg font-extrabold text-emerald-900">{{ $this->pesertaCount }}/{{ $dudi->kuota }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Aksi Pilih DUDI</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Setelah melihat detail dan peserta, Anda dapat memilih DUDI ini satu kali.
                    Perubahan pilihan hanya bisa dilakukan Admin.
                </p>
            </div>
            <button wire:click="requestPilihDudi" wire:loading.attr="disabled" wire:target="requestPilihDudi"
                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition disabled:opacity-70
                       {{ $this->isCurrentSiswaSudahMemilihDudiLain ? 'bg-slate-500 cursor-not-allowed' : ($this->isCurrentSiswaMemilihDudiIni ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-cyan-600 hover:bg-cyan-700') }}">
                <span wire:loading.remove wire:target="requestPilihDudi">
                    {{ $this->isCurrentSiswaMemilihDudiIni ? 'Sudah Terpilih' : 'Pilih DUDI Ini' }}
                </span>
                <span wire:loading wire:target="requestPilihDudi"
                    class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
            </button>
        </div>

        @if ($this->isCurrentSiswaSudahMemilihDudiLain)
            <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                Anda sudah memilih DUDI <strong>{{ $this->currentSiswa?->dudi?->name }}</strong>.
                Pergantian atau pembatalan hanya dapat dilakukan oleh Admin.
            </p>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-extrabold text-slate-900">Daftar Peserta pada DUDI Ini</h2>
        <p class="mt-1 text-sm text-slate-500">
            Total peserta {{ $this->pesertaCount }} siswa, dan jumlah nama di tabel ini sama dengan jumlah peserta
            tersebut.
        </p>

        <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
            <div
                class="grid grid-cols-12 bg-slate-100 px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 sm:px-4">
                <div class="col-span-3">NIS</div>
                <div class="col-span-5">Nama</div>
                <div class="col-span-4">Kelas</div>
            </div>

            <div class="max-h-80 overflow-y-auto">
                @forelse ($this->pesertaPadaDudi as $peserta)
                    <div wire:key="peserta-lain-{{ $peserta->id }}"
                        class="grid grid-cols-12 items-center border-t border-slate-100 px-3 py-2 text-sm text-slate-700 sm:px-4">
                        <div class="col-span-3 font-semibold text-slate-900">{{ $peserta->nis }}</div>
                        <div class="col-span-5 pr-2">
                            {{ $peserta->user?->name ?? '-' }}
                            @if ($this->currentSiswa && $peserta->id === $this->currentSiswa->id)
                                <span class="ml-1 text-xs font-semibold text-cyan-700">(Anda)</span>
                            @endif
                        </div>
                        <div class="col-span-4 text-slate-600">{{ $peserta->kelas?->name ?? '-' }}</div>
                    </div>
                @empty
                    <div class="px-4 py-5 text-sm text-slate-500">Belum ada peserta pada DUDI ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
