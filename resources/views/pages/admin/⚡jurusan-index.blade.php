<?php

use App\Models\Jurusan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $code = '';

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public ?string $toast = null;

    public string $toastType = 'success';

    #[Computed]
    public function jurusanList()
    {
        return Jurusan::query()->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))->withCount('kelas')->orderBy('name')->paginate(10);
    }

    public function updatedSearch(): void
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
        $jurusan = Jurusan::findOrFail($id);
        $this->editingId = $id;
        $this->name = $jurusan->name;
        $this->code = $jurusan->code;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:jurusans,code' . ($this->editingId ? ",{$this->editingId}" : ''),
        ]);

        if ($this->editingId) {
            Jurusan::findOrFail($this->editingId)->update([
                'name' => $this->name,
                'code' => strtoupper($this->code),
            ]);
            $this->notify('Jurusan berhasil diperbarui!');
        } else {
            Jurusan::create([
                'name' => $this->name,
                'code' => strtoupper($this->code),
            ]);
            $this->notify('Jurusan berhasil ditambahkan!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Jurusan::findOrFail($this->deletingId)->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->notify('Jurusan berhasil dihapus!');
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
        $this->code = '';
        $this->resetValidation();
    }
};
?>

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-cyan-600">Data Master</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Jurusan</h1>
        </div>
        <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700 disabled:opacity-70">
            <span wire:loading.remove wire:target="openCreate">+ Tambah Jurusan</span>
            <span wire:loading wire:target="openCreate" class="inline-flex items-center gap-2">
                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                Memuat...
            </span>
        </button>
    </div>

    {{-- Toast Notification --}}
    @if ($toast)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false;
            setTimeout(() => $wire.dismissToast(), 300) }, 3000)"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed right-5 top-5 z-50 flex items-center gap-3 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg
                   {{ $toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500' }}">
            <span>{{ $toastType === 'success' ? '✓' : '✕' }}</span>
            {{ $toast }}
        </div>
    @endif

    {{-- Search --}}
    <div class="flex items-center gap-3">
        <div class="relative">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari jurusan..."
                class="w-72 rounded-xl border border-slate-200 bg-white py-2 pl-4 pr-10 text-sm shadow-sm transition focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Loading overlay --}}
        <div wire:loading wire:target="search, save, delete, openEdit, confirmDelete"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-2">
                <div class="h-7 w-7 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="save">Menyimpan...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="delete">Menghapus...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="search">Mencari...</span>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-12">No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-28">Kode
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama
                        Jurusan</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-32">
                        Jumlah Kelas</th>
                    <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-36">Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($this->jurusanList as $index => $jurusan)
                    <tr wire:key="{{ $jurusan->id }}" class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm text-slate-400">
                            {{ $this->jurusanList->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-lg bg-cyan-50 px-2.5 py-1 text-xs font-bold text-cyan-700">
                                {{ $jurusan->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                            {{ $jurusan->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ $jurusan->kelas_count }} kelas
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $jurusan->id }})" wire:loading.attr="disabled"
                                    wire:target="openEdit({{ $jurusan->id }})"
                                    class="inline-flex min-w-[56px] items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="openEdit({{ $jurusan->id }})">Edit</span>
                                    <span wire:loading wire:target="openEdit({{ $jurusan->id }})"
                                        class="inline-flex items-center gap-1">
                                        <span
                                            class="h-3 w-3 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></span>
                                    </span>
                                </button>
                                <button wire:click="confirmDelete({{ $jurusan->id }})" wire:loading.attr="disabled"
                                    wire:target="confirmDelete({{ $jurusan->id }})"
                                    class="inline-flex min-w-[56px] items-center justify-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100 disabled:opacity-50">
                                    <span wire:loading.remove
                                        wire:target="confirmDelete({{ $jurusan->id }})">Hapus</span>
                                    <span wire:loading wire:target="confirmDelete({{ $jurusan->id }})"
                                        class="inline-flex items-center gap-1">
                                        <span
                                            class="h-3 w-3 animate-spin rounded-full border-2 border-red-400 border-t-transparent"></span>
                                    </span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-sm text-slate-400">
                            Belum ada data jurusan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($this->jurusanList->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $this->jurusanList->links() }}
            </div>
        @endif

    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-slate-900">
                        {{ $editingId ? 'Edit Jurusan' : 'Tambah Jurusan' }}
                    </h2>
                    <button wire:click="$set('showModal', false)"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        ✕
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Kode
                            Jurusan</label>
                        <input wire:model="code" type="text" placeholder="Contoh: RPL"
                            class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                   {{ $errors->has('code') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                        @error('code')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama
                            Jurusan</label>
                        <input wire:model="name" type="text" placeholder="Contoh: Rekayasa Perangkat Lunak"
                            class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                   {{ $errors->has('name') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="flex-1 rounded-xl bg-cyan-600 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
                            <span wire:loading wire:target="save">Menyimpan...</span>
                            <span wire:loading.remove wire:target="save">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-1 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-xl">🗑️</span>
                    <h2 class="text-lg font-extrabold text-slate-900">Hapus Jurusan?</h2>
                </div>
                <p class="mb-6 mt-2 text-sm text-slate-500">Tindakan ini tidak dapat dibatalkan. Data jurusan akan
                    dihapus permanen.</p>
                <div class="flex gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                        class="flex-1 rounded-xl bg-red-500 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600 disabled:opacity-70">
                        <span wire:loading wire:target="delete">Menghapus...</span>
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
