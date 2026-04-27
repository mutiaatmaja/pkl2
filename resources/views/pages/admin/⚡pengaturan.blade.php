<?php

use App\Models\Pengaturan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts.admin')] class extends Component {
    use WithFileUploads;

    public string $nomor_surat = '';
    public string $pejabat_penandatangan = '';
    public string $jabatan_penandatangan = '';
    public ?string $nip_penandatangan = null;
    public ?string $tanggal_surat = null;
    public bool $enable_ttd_scan = false;
    public string $lokasi_penerbitan = 'Pontianak';

    // Current paths stored in DB
    public ?string $currentTtdPath = null;
    public ?string $currentKopSuratPath = null;

    // New uploads (temporary)
    public $ttdUpload = null;
    public $kopSuratUpload = null;

    // Toast
    public ?string $toast = null;
    public string $toastType = 'success';

    public function mount(): void
    {
        $p = Pengaturan::instance();

        $this->nomor_surat = $p->nomor_surat;
        $this->pejabat_penandatangan = $p->pejabat_penandatangan;
        $this->jabatan_penandatangan = $p->jabatan_penandatangan;
        $this->nip_penandatangan = $p->nip_penandatangan;
        $this->tanggal_surat = $p->tanggal_surat?->format('Y-m-d');
        $this->enable_ttd_scan = $p->enable_ttd_scan;
        $this->lokasi_penerbitan = $p->lokasi_penerbitan;
        $this->currentTtdPath = $p->ttd_pejabat;
        $this->currentKopSuratPath = $p->kop_surat;
    }

    public function save(): void
    {
        $this->validate([
            'nomor_surat' => 'required|string|max:255',
            'pejabat_penandatangan' => 'required|string|max:255',
            'jabatan_penandatangan' => 'required|string|max:255',
            'nip_penandatangan' => 'nullable|string|max:30',
            'tanggal_surat' => 'nullable|date',
            'lokasi_penerbitan' => 'required|string|max:100',
            'ttdUpload' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kopSuratUpload' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $p = Pengaturan::instance();

        $ttdPath = $p->ttd_pejabat;
        if ($this->ttdUpload) {
            if ($ttdPath && Storage::disk('public')->exists($ttdPath)) {
                Storage::disk('public')->delete($ttdPath);
            }
            $ttdPath = $this->ttdUpload->store('pengaturan/ttd', 'public');
        }

        $kopPath = $p->kop_surat;
        if ($this->kopSuratUpload) {
            if ($kopPath && Storage::disk('public')->exists($kopPath)) {
                Storage::disk('public')->delete($kopPath);
            }
            $kopPath = $this->kopSuratUpload->store('pengaturan/kop', 'public');
        }

        $p->update([
            'nomor_surat' => $this->nomor_surat,
            'pejabat_penandatangan' => $this->pejabat_penandatangan,
            'jabatan_penandatangan' => $this->jabatan_penandatangan,
            'nip_penandatangan' => $this->nip_penandatangan,
            'tanggal_surat' => $this->tanggal_surat ?: null,
            'enable_ttd_scan' => $this->enable_ttd_scan,
            'lokasi_penerbitan' => $this->lokasi_penerbitan,
            'ttd_pejabat' => $ttdPath,
            'kop_surat' => $kopPath,
        ]);

        $this->currentTtdPath = $ttdPath;
        $this->currentKopSuratPath = $kopPath;
        $this->ttdUpload = null;
        $this->kopSuratUpload = null;

        $this->notify('Pengaturan berhasil disimpan!');
    }

    public function removeTtd(): void
    {
        $p = Pengaturan::instance();
        if ($p->ttd_pejabat && Storage::disk('public')->exists($p->ttd_pejabat)) {
            Storage::disk('public')->delete($p->ttd_pejabat);
        }
        $p->update(['ttd_pejabat' => null, 'enable_ttd_scan' => false]);
        $this->currentTtdPath = null;
        $this->enable_ttd_scan = false;
        $this->ttdUpload = null;
        $this->notify('Tanda tangan dihapus.');
    }

    public function removeKopSurat(): void
    {
        $p = Pengaturan::instance();
        if ($p->kop_surat && Storage::disk('public')->exists($p->kop_surat)) {
            Storage::disk('public')->delete($p->kop_surat);
        }
        $p->update(['kop_surat' => null]);
        $this->currentKopSuratPath = null;
        $this->kopSuratUpload = null;
        $this->notify('Kop surat dihapus, akan menggunakan default.');
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

    {{-- Page Header --}}
    <div>
        <p class="text-xs font-bold uppercase tracking-widest text-cyan-600">Konfigurasi</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Pengaturan</h1>
    </div>

    {{-- Toast --}}
    @if ($toast)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.dismissToast();
        }, 4000)"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 text-sm font-semibold shadow-xl
                   {{ $toastType === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-500 text-white' }}">
            <span>{{ $toastType === 'success' ? '✓' : '✕' }}</span>
            {{ $toast }}
            <button wire:click="dismissToast" class="ml-2 opacity-70 hover:opacity-100">✕</button>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- Surat --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <h2 class="mb-5 text-base font-bold text-slate-800">Pengaturan Surat</h2>

            <div class="grid gap-5 sm:grid-cols-2">

                {{-- Nomor Surat --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Format Nomor Surat
                    </label>
                    <input wire:model="nomor_surat" type="text" placeholder="421.5/SMKN7-PKL/{tahun}/{nomor}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100 @error('nomor_surat') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400">Gunakan <code
                            class="rounded bg-slate-100 px-1">{tahun}</code> dan <code
                            class="rounded bg-slate-100 px-1">{nomor}</code> sebagai placeholder dinamis.</p>
                    @error('nomor_surat')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lokasi Penerbitan --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Lokasi Penerbitan Surat
                    </label>
                    <input wire:model="lokasi_penerbitan" type="text" placeholder="Pontianak"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100 @error('lokasi_penerbitan') border-red-400 @enderror">
                    @error('lokasi_penerbitan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Surat --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Tanggal Surat
                        <span class="ml-1 text-xs font-normal text-slate-400">(opsional, kosong = tanggal cetak)</span>
                    </label>
                    <input wire:model="tanggal_surat" type="date"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100 @error('tanggal_surat') border-red-400 @enderror">
                    @error('tanggal_surat')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Pejabat Penandatangan --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <h2 class="mb-5 text-base font-bold text-slate-800">Pejabat Penandatangan</h2>

            <div class="grid gap-5 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Pejabat</label>
                    <input wire:model="pejabat_penandatangan" type="text" placeholder="Kepala SMKN 7 Pontianak"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100 @error('pejabat_penandatangan') border-red-400 @enderror">
                    @error('pejabat_penandatangan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jabatan</label>
                    <input wire:model="jabatan_penandatangan" type="text" placeholder="Kepala Sekolah"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100 @error('jabatan_penandatangan') border-red-400 @enderror">
                    @error('jabatan_penandatangan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">NIP Penandatangan</label>
                    <input wire:model="nip_penandatangan" type="text" placeholder="198001012006041001"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100 @error('nip_penandatangan') border-red-400 @enderror">
                    @error('nip_penandatangan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Tanda Tangan --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-800">Tanda Tangan Pejabat</h2>

                {{-- Toggle enable TTD scan --}}
                <label class="flex cursor-pointer items-center gap-2.5">
                    <span class="text-sm font-semibold text-slate-600">Gunakan Tanda Tangan Scan</span>
                    <button type="button" wire:click="$toggle('enable_ttd_scan')"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                               {{ $enable_ttd_scan ? 'bg-cyan-500' : 'bg-slate-200' }}">
                        <span
                            class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                     {{ $enable_ttd_scan ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </label>
            </div>

            @if (!$enable_ttd_scan)
                <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    Tanda tangan scan <strong>tidak digunakan</strong>. Surat akan memiliki baris kosong untuk tanda
                    tangan manual.
                </p>
            @endif

            <div class="mt-4">
                @if ($currentTtdPath && !$ttdUpload)
                    <p class="mb-2 text-sm font-semibold text-slate-600">Tanda tangan saat ini:</p>
                    <div class="flex items-start gap-4">
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2">
                            <img src="{{ Storage::disk('public')->url($currentTtdPath) }}" alt="TTD Pejabat"
                                class="h-24 w-auto object-contain">
                        </div>
                        <button type="button" wire:click="removeTtd" wire:loading.attr="disabled"
                            wire:target="removeTtd"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 disabled:opacity-60">
                            <span wire:loading.remove wire:target="removeTtd">🗑 Hapus</span>
                            <span wire:loading wire:target="removeTtd"
                                class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-red-400 border-t-transparent"></span>
                        </button>
                    </div>
                @endif

                @if ($ttdUpload)
                    <div class="mb-3 flex items-start gap-4">
                        <div class="overflow-hidden rounded-xl border border-cyan-200 bg-cyan-50 p-2">
                            <img src="{{ $ttdUpload->temporaryUrl() }}" alt="Preview TTD"
                                class="h-24 w-auto object-contain">
                        </div>
                        <p class="text-xs text-cyan-700 font-semibold mt-1">Preview — belum disimpan</p>
                    </div>
                @endif

                <label class="mt-3 block">
                    <span class="mb-1.5 block text-sm font-semibold text-slate-700">
                        {{ $currentTtdPath ? 'Ganti' : 'Upload' }} Foto/Scan Tanda Tangan
                        <span class="ml-1 text-xs font-normal text-slate-400">JPG, PNG, maks. 2MB</span>
                    </span>
                    <input wire:model="ttdUpload" type="file" accept="image/jpeg,image/png"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-cyan-700 hover:file:bg-cyan-100">
                    <div wire:loading wire:target="ttdUpload"
                        class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                        <span
                            class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-400 border-t-transparent"></span>
                        Mengunggah...
                    </div>
                    @error('ttdUpload')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </label>
            </div>
        </div>

        {{-- Kop Surat --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <h2 class="mb-5 text-base font-bold text-slate-800">Kop Surat</h2>
            <p class="mb-4 text-sm text-slate-500">Upload gambar kop surat yang akan disisipkan di bagian atas setiap
                surat. Jika tidak diupload, kop surat default akan digunakan.</p>

            @if ($currentKopSuratPath && !$kopSuratUpload)
                <p class="mb-2 text-sm font-semibold text-slate-600">Kop surat saat ini:</p>
                <div class="flex items-start gap-4">
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 w-full max-w-md">
                        <img src="{{ Storage::disk('public')->url($currentKopSuratPath) }}" alt="Kop Surat"
                            class="h-24 w-full object-contain">
                    </div>
                    <button type="button" wire:click="removeKopSurat" wire:loading.attr="disabled"
                        wire:target="removeKopSurat"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 disabled:opacity-60">
                        <span wire:loading.remove wire:target="removeKopSurat">🗑 Hapus (gunakan default)</span>
                        <span wire:loading wire:target="removeKopSurat"
                            class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-red-400 border-t-transparent"></span>
                    </button>
                </div>
            @endif

            @if ($kopSuratUpload)
                <div class="mb-3 flex items-start gap-4">
                    <div class="overflow-hidden rounded-xl border border-cyan-200 bg-cyan-50 p-2 w-full max-w-md">
                        <img src="{{ $kopSuratUpload->temporaryUrl() }}" alt="Preview Kop Surat"
                            class="h-24 w-full object-contain">
                    </div>
                    <p class="text-xs text-cyan-700 font-semibold mt-1">Preview — belum disimpan</p>
                </div>
            @endif

            <label class="mt-3 block">
                <span class="mb-1.5 block text-sm font-semibold text-slate-700">
                    {{ $currentKopSuratPath ? 'Ganti' : 'Upload' }} Kop Surat
                    <span class="ml-1 text-xs font-normal text-slate-400">JPG, PNG, maks. 4MB</span>
                </span>
                <input wire:model="kopSuratUpload" type="file" accept="image/jpeg,image/png"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-cyan-700 hover:file:bg-cyan-100">
                <div wire:loading wire:target="kopSuratUpload"
                    class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                    <span
                        class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-400 border-t-transparent"></span>
                    Mengunggah...
                </div>
                @error('kopSuratUpload')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </label>
        </div>

        {{-- Save Button --}}
        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-cyan-700 active:bg-cyan-800 disabled:opacity-70 transition-colors">
                <span wire:loading.remove wire:target="save">💾 Simpan Pengaturan</span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Menyimpan...
                </span>
            </button>
        </div>

    </form>

</div>
