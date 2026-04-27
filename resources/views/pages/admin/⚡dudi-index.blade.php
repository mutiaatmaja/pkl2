<?php

use App\Imports\DudiImport;
use App\Models\Dudi;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;
    use WithFileUploads;

    // Search
    public string $search = '';
    public string $filterAktif = '';

    // Create/Edit modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $panggilan_pimpinan = 'Pimpinan';
    public string $address = '';
    public bool $aktif = true;
    public int $kuota = 5;

    // Delete modal
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    // Import modal
    public bool $showImportModal = false;
    public $importFile = null;

    // Toast
    public ?string $toast = null;
    public string $toastType = 'success';

    #[Computed]
    public function dudiList()
    {
        return Dudi::query()
            ->withCount('siswas')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('address', 'like', "%{$this->search}%"))
            ->when($this->filterAktif !== '', fn($q) => $q->where('aktif', (bool) $this->filterAktif))
            ->orderBy('name')
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAktif(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $dudi = Dudi::findOrFail($id);
        $this->editingId = $id;
        $this->name = $dudi->name;
        $this->panggilan_pimpinan = $dudi->panggilan_pimpinan;
        $this->address = $dudi->address;
        $this->aktif = (bool) $dudi->aktif;
        $this->kuota = $dudi->kuota;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:dudis,name' . ($this->editingId ? ",{$this->editingId}" : ''),
            'panggilan_pimpinan' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'kuota' => 'required|integer|min:1|max:9999',
        ]);

        if ($this->editingId) {
            Dudi::findOrFail($this->editingId)->update([
                'name' => $this->name,
                'panggilan_pimpinan' => $this->panggilan_pimpinan,
                'address' => $this->address,
                'aktif' => $this->aktif,
                'kuota' => $this->kuota,
            ]);
            $this->notify('Data DUDI berhasil diperbarui!');
        } else {
            Dudi::create([
                'name' => $this->name,
                'panggilan_pimpinan' => $this->panggilan_pimpinan,
                'address' => $this->address,
                'aktif' => $this->aktif,
                'kuota' => $this->kuota,
            ]);
            $this->notify('DUDI berhasil ditambahkan!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleAktif(int $id): void
    {
        $dudi = Dudi::findOrFail($id);
        $dudi->update(['aktif' => !$dudi->aktif]);
        $this->notify($dudi->aktif ? 'DUDI diaktifkan.' : 'DUDI dinonaktifkan.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $dudi = Dudi::findOrFail($this->deletingId);
        $dudi->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->notify('DUDI berhasil dihapus!');
    }

    public function openImport(): void
    {
        $this->importFile = null;
        $this->showImportModal = true;
    }

    public function processImport(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new DudiImport();
        Excel::import($import, $this->importFile->getRealPath());

        $totalSkipped = $import->totalSkipped();
        $message = "Import selesai: {$import->importedCount} DUDI berhasil ditambahkan.";
        if ($totalSkipped > 0) {
            $message .= " {$totalSkipped} baris dilewati (duplikat / data tidak valid)";
        }

        $this->importFile = null;
        $this->notify($message);
        $this->showImportModal = false;
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

    private function resetForm(): void
    {
        $this->name = '';
        $this->panggilan_pimpinan = 'Pimpinan';
        $this->address = '';
        $this->aktif = true;
        $this->kuota = 5;
        $this->resetValidation();
    }
};
?>

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-cyan-600">Data Master</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">DUDI</h1>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openImport" wire:loading.attr="disabled" wire:target="openImport"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:opacity-70">
                <span wire:loading.remove wire:target="openImport">⬆ Import</span>
                <span wire:loading wire:target="openImport" class="inline-flex items-center gap-2">
                    <span
                        class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-400 border-t-transparent"></span>
                    Memuat...
                </span>
            </button>
            <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700 disabled:opacity-70">
                <span wire:loading.remove wire:target="openCreate">+ Tambah DUDI</span>
                <span wire:loading wire:target="openCreate" class="inline-flex items-center gap-2">
                    <span
                        class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Memuat...
                </span>
            </button>
        </div>
    </div>

    {{-- Toast Notification --}}
    @if ($toast)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            setTimeout(() => $wire.dismissToast(), 300)
        }, 3000)"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed right-5 top-5 z-50 flex items-center gap-3 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg
                   {{ $toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500' }}">
            <span>{{ $toastType === 'success' ? '✓' : '✕' }}</span>
            {{ $toast }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau alamat..."
                class="w-72 rounded-xl border border-slate-200 bg-white py-2 pl-4 pr-10 text-sm shadow-sm transition focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
            </div>
        </div>

        <select wire:model.live="filterAktif"
            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <option value="">Semua Status</option>
            <option value="1">Aktif</option>
            <option value="0">Tidak Aktif</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Loading overlay --}}
        <div wire:loading
            wire:target="search, filterAktif, save, delete, toggleAktif, openEdit, confirmDelete, processImport"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-2">
                <div class="h-7 w-7 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="save">Menyimpan...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="delete">Menghapus...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading
                    wire:target="toggleAktif">Memperbarui...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="processImport">Mengimpor
                    data...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading
                    wire:target="search,filterAktif">Memfilter...</span>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="w-12 px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama DUDI
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Alamat
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kuota</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status
                    </th>
                    <th class="w-44 px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($this->dudiList as $index => $dudi)
                    <tr wire:key="{{ $dudi->id }}" class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm text-slate-400">
                            {{ $this->dudiList->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $dudi->name }}</p>

                        </td>
                        <td class="px-6 py-4">
                            <p class="max-w-xs text-sm text-slate-600">{{ $dudi->address }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                            <p> {{ $dudi->siswas_count }}/{{ $dudi->kuota }}</p>

                        </td>
                        <td class="px-6 py-4">
                            @if ($dudi->aktif)
                                <span
                                    class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Aktif</span>
                            @else
                                <span
                                    class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Tidak
                                    Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="toggleAktif({{ $dudi->id }})" wire:loading.attr="disabled"
                                    wire:target="toggleAktif({{ $dudi->id }})"
                                    class="inline-flex min-w-14 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold transition hover:border-amber-300 hover:text-amber-700 disabled:opacity-50
                                           {{ $dudi->aktif ? 'text-slate-600' : 'text-slate-400' }}">
                                    <span wire:loading.remove
                                        wire:target="toggleAktif({{ $dudi->id }})">{{ $dudi->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                    <span wire:loading wire:target="toggleAktif({{ $dudi->id }})"
                                        class="inline-flex items-center">
                                        <span
                                            class="h-3 w-3 animate-spin rounded-full border-2 border-amber-500 border-t-transparent"></span>
                                    </span>
                                </button>
                                <button wire:click="openEdit({{ $dudi->id }})" wire:loading.attr="disabled"
                                    wire:target="openEdit({{ $dudi->id }})"
                                    class="inline-flex min-w-14 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="openEdit({{ $dudi->id }})">Edit</span>
                                    <span wire:loading wire:target="openEdit({{ $dudi->id }})"
                                        class="inline-flex items-center">
                                        <span
                                            class="h-3 w-3 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></span>
                                    </span>
                                </button>
                                <a href="{{ route('admin.dudi.show', $dudi) }}" wire:navigate
                                    class="inline-flex min-w-14 items-center justify-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                    Detail
                                </a>
                                <button wire:click="confirmDelete({{ $dudi->id }})" wire:loading.attr="disabled"
                                    wire:target="confirmDelete({{ $dudi->id }})"
                                    class="inline-flex min-w-14 items-center justify-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100 disabled:opacity-50">
                                    <span wire:loading.remove
                                        wire:target="confirmDelete({{ $dudi->id }})">Hapus</span>
                                    <span wire:loading wire:target="confirmDelete({{ $dudi->id }})"
                                        class="inline-flex items-center">
                                        <span
                                            class="h-3 w-3 animate-spin rounded-full border-2 border-red-400 border-t-transparent"></span>
                                    </span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">
                            Belum ada data DUDI.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($this->dudiList->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $this->dudiList->links() }}
            </div>
        @endif

    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-slate-900">
                        {{ $editingId ? 'Edit DUDI' : 'Tambah DUDI' }}
                    </h2>
                    <button wire:click="$set('showModal', false)"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        ✕
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama
                                DUDI</label>
                            <input wire:model="name" type="text" placeholder="Nama perusahaan / industri"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                       {{ $errors->has('name') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Panggilan
                                Pimpinan</label>
                            <input wire:model="panggilan_pimpinan" type="text"
                                placeholder="Contoh: Pimpinan / Ketua / Dekan"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                       {{ $errors->has('panggilan_pimpinan') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                            @error('panggilan_pimpinan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Alamat</label>
                        <textarea wire:model="address" rows="3" placeholder="Alamat lengkap"
                            class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                   {{ $errors->has('address') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}"></textarea>
                        @error('address')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Kuota
                                Siswa</label>
                            <input wire:model="kuota" type="number" min="1" placeholder="Jumlah kuota"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                       {{ $errors->has('kuota') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                            @error('kuota')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Status</label>
                            <label class="mt-2.5 flex cursor-pointer items-center gap-3">
                                <input wire:model="aktif" type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-400">
                                <span class="text-sm text-slate-700">Aktif menerima siswa</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                <span
                                    class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirm Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="$set('showDeleteModal', false)">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50">
                    <span class="text-xl">🗑️</span>
                </div>
                <h2 class="text-lg font-extrabold text-slate-900">Hapus DUDI?</h2>
                <p class="mt-1 text-sm text-slate-500">Data DUDI ini akan dihapus secara permanen dan tidak dapat
                    dikembalikan.</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                        class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-70">
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <span wire:loading wire:target="delete" class="inline-flex items-center gap-2">
                            <span
                                class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            Menghapus...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Import Modal --}}
    @if ($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="$set('showImportModal', false)">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-slate-900">Import DUDI</h2>
                    <button wire:click="$set('showImportModal', false)"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        ✕
                    </button>
                </div>

                <div class="mb-4 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-700">Format kolom yang diperlukan:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-xs">
                        <li><span class="font-mono font-bold">nama</span> — Nama perusahaan (wajib, unik)</li>
                        <li><span class="font-mono font-bold">alamat</span> — Alamat lengkap (wajib)</li>
                        <li><span class="font-mono font-bold">aktif</span> — Status: <span
                                class="font-mono">ya</span>, <span class="font-mono">1</span>, atau <span
                                class="font-mono">tidak</span>, <span class="font-mono">0</span></li>
                        <li><span class="font-mono font-bold">kuota</span> — Jumlah kuota siswa (angka, min. 1)</li>
                    </ul>
                    <a href="{{ route('admin.dudi.template') }}"
                        class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-cyan-600 hover:underline">
                        ⬇ Unduh Format CSV
                    </a>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-600">File Excel /
                        CSV</label>
                    <div
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 px-6 py-8 text-center transition hover:border-cyan-300">
                        @if ($importFile)
                            <p class="text-sm font-semibold text-slate-700">{{ $importFile->getClientOriginalName() }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ number_format($importFile->getSize() / 1024, 1) }} KB</p>
                            <button wire:click="$set('importFile', null)"
                                class="mt-2 text-xs text-red-500 hover:underline">Hapus</button>
                        @else
                            <span class="text-2xl">📂</span>
                            <p class="mt-2 text-sm text-slate-500">Klik untuk pilih file</p>
                            <p class="text-xs text-slate-400">xlsx, xls, csv (maks. 5 MB)</p>
                            <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv"
                                class="mt-3 text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-cyan-700 hover:file:bg-cyan-100">
                        @endif
                        <div wire:loading wire:target="importFile" class="mt-2">
                            <div
                                class="h-4 w-4 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent">
                            </div>
                        </div>
                    </div>
                    @error('importFile')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showImportModal', false)"
                        class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button wire:click="processImport" wire:loading.attr="disabled" wire:target="processImport"
                        class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
                        <span wire:loading.remove wire:target="processImport">Import</span>
                        <span wire:loading wire:target="processImport" class="inline-flex items-center gap-2">
                            <span
                                class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            Mengimpor...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
