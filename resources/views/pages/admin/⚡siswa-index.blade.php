<?php

use App\Imports\SiswaImport;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;
    use WithFileUploads;

    // Search & filter
    public string $search = '';
    public string $filterJurusan = '';
    public string $filterKelas = '';

    // Create/Edit modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $nama = '';
    public string $nis = '';
    public string $nisn = '';
    public string $jenis_kelamin = '';
    public string $alamat = '';
    public string $no_hp = '';
    public ?int $jurusan_id = null;
    public ?int $kelas_id = null;

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
    public function siswaList()
    {
        return Siswa::query()
            ->with(['user', 'jurusan', 'kelas', 'dudi'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"))
                    ->orWhere('nis', 'like', "%{$this->search}%")
                    ->orWhere('nisn', 'like', "%{$this->search}%");
            })
            ->when($this->filterJurusan, fn($q) => $q->where('jurusan_id', $this->filterJurusan))
            ->when($this->filterKelas, fn($q) => $q->where('kelas_id', $this->filterKelas))
            ->join('users', 'users.id', '=', 'siswas.user_id')
            ->orderBy('users.name')
            ->select('siswas.*')
            ->paginate(15);
    }

    #[Computed]
    public function jurusanOptions()
    {
        return Jurusan::orderBy('name')->get();
    }

    #[Computed]
    public function kelasOptions()
    {
        if (!$this->jurusan_id) {
            return collect();
        }

        return Kelas::where('jurusan_id', $this->jurusan_id)->orderBy('name')->get();
    }

    #[Computed]
    public function kelasFilterOptions()
    {
        if (!$this->filterJurusan) {
            return collect();
        }

        return Kelas::where('jurusan_id', $this->filterJurusan)->orderBy('name')->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterJurusan(): void
    {
        $this->filterKelas = '';
        $this->resetPage();
    }

    public function updatedJurusanId(): void
    {
        $this->kelas_id = null;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $siswa = Siswa::with('user')->findOrFail($id);
        $this->editingId = $id;
        $this->nama = $siswa->user?->name ?? '';
        $this->nis = $siswa->nis;
        $this->nisn = $siswa->nisn;
        $this->jenis_kelamin = (string) ($siswa->jenis_kelamin ?? '');
        $this->alamat = (string) ($siswa->alamat ?? '');
        $this->no_hp = (string) ($siswa->no_hp ?? '');
        $this->jurusan_id = $siswa->jurusan_id;
        $this->kelas_id = $siswa->kelas_id;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama' => 'required|string|max:255',
            'nis' => 'required|string|max:30|unique:siswas,nis' . ($this->editingId ? ",{$this->editingId}" : ''),
            'nisn' => 'required|string|max:30|unique:siswas,nisn' . ($this->editingId ? ",{$this->editingId}" : ''),
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string|max:1000',
            'no_hp' => 'required|string|max:30',
            'jurusan_id' => 'required|exists:jurusans,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        if ($this->editingId) {
            $siswa = Siswa::findOrFail($this->editingId);
            $siswa->update([
                'nis' => $this->nis,
                'nisn' => $this->nisn,
                'jenis_kelamin' => $this->jenis_kelamin,
                'alamat' => $this->alamat,
                'no_hp' => $this->no_hp,
                'jurusan_id' => $this->jurusan_id,
                'kelas_id' => $this->kelas_id,
            ]);
            $siswa->user?->update(['name' => $this->nama]);
            $this->notify('Data siswa berhasil diperbarui!');
        } else {
            $siswaRole = Role::where('name', 'siswa')->first();
            $user = User::create([
                'name' => $this->nama,
                'email' => $this->nisn . '@claim.smkn7.local',
                'password' => Hash::make(Str::password(20)),
                'email_verified_at' => null,
            ]);
            if ($siswaRole) {
                $user->syncRoles([$siswaRole]);
            }
            Siswa::create([
                'user_id' => $user->id,
                'jurusan_id' => $this->jurusan_id,
                'kelas_id' => $this->kelas_id,
                'nis' => $this->nis,
                'nisn' => $this->nisn,
                'jenis_kelamin' => $this->jenis_kelamin,
                'alamat' => $this->alamat,
                'no_hp' => $this->no_hp,
            ]);
            $this->notify('Siswa berhasil ditambahkan!');
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
        $siswa = Siswa::findOrFail($this->deletingId);
        $user = $siswa->user;
        $siswa->delete();
        // Only delete placeholder (unclaimed) user
        if ($user && str_ends_with($user->email, '@claim.smkn7.local')) {
            $user->delete();
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->notify('Siswa berhasil dihapus!');
    }

    public function openImport(): void
    {
        $this->importFile = null;
        $this->importResult = null;
        $this->showImportModal = true;
    }

    public function processImport(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new SiswaImport();
        Excel::import($import, $this->importFile->getRealPath());

        $totalSkipped = $import->totalSkipped();
        $message = "Import selesai: {$import->importedCount} siswa berhasil ditambahkan.";
        if ($totalSkipped > 0) {
            $message .= " {$totalSkipped} baris dilewati (duplikat / data tidak valid)";
        }

        if (!empty($import->skipReasons)) {
            $message .= ' Detail: ' . implode(' | ', $import->skipReasons);
        }

        $validationFailureMessages = collect($import->failures())
            ->take(3)
            ->map(function ($failure): string {
                $errors = implode(', ', $failure->errors());

                return "Baris {$failure->row()} ({$failure->attribute()}): {$errors}";
            })
            ->values()
            ->all();

        if (!empty($validationFailureMessages)) {
            $message .= ' Validasi: ' . implode(' | ', $validationFailureMessages);
        }

        $this->importFile = null;
        $this->notify($message, $import->importedCount > 0 ? 'success' : 'error');
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
        $this->nama = '';
        $this->nis = '';
        $this->nisn = '';
        $this->jenis_kelamin = '';
        $this->alamat = '';
        $this->no_hp = '';
        $this->jurusan_id = null;
        $this->kelas_id = null;
        $this->resetValidation();
    }
};
?>

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-cyan-600">Data Master</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Siswa</h1>
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
                <span wire:loading.remove wire:target="openCreate">+ Tambah Siswa</span>
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
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, NIS, NISN..."
                class="w-72 rounded-xl border border-slate-200 bg-white py-2 pl-4 pr-10 text-sm shadow-sm transition focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
            </div>
        </div>

        <select wire:model.live="filterJurusan"
            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <option value="">Semua Jurusan</option>
            @foreach ($this->jurusanOptions as $jurusan)
                <option value="{{ $jurusan->id }}">{{ $jurusan->code }} - {{ $jurusan->name }}</option>
            @endforeach
        </select>

        @if ($filterJurusan)
            <select wire:model.live="filterKelas"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                <option value="">Semua Kelas</option>
                @foreach ($this->kelasFilterOptions as $kelas)
                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Table --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Loading overlay --}}
        <div wire:loading
            wire:target="search, filterJurusan, filterKelas, save, delete, openEdit, confirmDelete, processImport"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-2">
                <div class="h-7 w-7 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="save">Menyimpan...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="delete">Menghapus...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="processImport">Mengimpor
                    data...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading
                    wire:target="search,filterJurusan,filterKelas">Memfilter...</span>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="w-12 px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama Siswa
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">NIS / NISN
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status
                    </th>
                    <th class="w-36 px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($this->siswaList as $index => $siswa)
                    <tr wire:key="{{ $siswa->id }}" class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm text-slate-400">
                            {{ $this->siswaList->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $siswa->user?->name ?? '-' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $siswa->user?->email }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs font-medium text-slate-700">{{ $siswa->nis }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $siswa->nisn }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-700">{{ $siswa->kelas?->name ?? '-' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $siswa->jurusan?->name ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if ($siswa->dudi_id)
                                <a href="{{ route('admin.dudi.show', $siswa->dudi_id) }}" wire:navigate
                                    class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100 hover:text-emerald-800">
                                    Sudah Magang
                                </a>
                            @elseif (str_ends_with($siswa->user?->email ?? '', '@claim.smkn7.local'))
                                <span
                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-600">Belum
                                    Claim</span>
                            @else
                                <span
                                    class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Belum
                                    Magang</span>
                            @endif
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                                • {{ $siswa->no_hp ?: '-' }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $siswa->id }})" wire:loading.attr="disabled"
                                    wire:target="openEdit({{ $siswa->id }})"
                                    class="inline-flex min-w-[56px] items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="openEdit({{ $siswa->id }})">Edit</span>
                                    <span wire:loading wire:target="openEdit({{ $siswa->id }})"
                                        class="inline-flex items-center">
                                        <span
                                            class="h-3 w-3 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></span>
                                    </span>
                                </button>
                                <button wire:click="confirmDelete({{ $siswa->id }})" wire:loading.attr="disabled"
                                    wire:target="confirmDelete({{ $siswa->id }})"
                                    class="inline-flex min-w-[56px] items-center justify-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100 disabled:opacity-50">
                                    <span wire:loading.remove
                                        wire:target="confirmDelete({{ $siswa->id }})">Hapus</span>
                                    <span wire:loading wire:target="confirmDelete({{ $siswa->id }})"
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
                            Belum ada data siswa.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($this->siswaList->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $this->siswaList->links() }}
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
                        {{ $editingId ? 'Edit Siswa' : 'Tambah Siswa' }}
                    </h2>
                    <button wire:click="$set('showModal', false)"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        ✕
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama
                            Lengkap</label>
                        <input wire:model="nama" type="text" placeholder="Nama lengkap siswa"
                            class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                   {{ $errors->has('nama') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                        @error('nama')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">NIS</label>
                            <input wire:model="nis" type="text" placeholder="Nomor Induk Siswa"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                       {{ $errors->has('nis') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                            @error('nis')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">NISN</label>
                            <input wire:model="nisn" type="text" placeholder="Nomor Induk Siswa Nasional"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                       {{ $errors->has('nisn') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                            @error('nisn')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Jenis
                                Kelamin</label>
                            <select wire:model="jenis_kelamin"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                   {{ $errors->has('jenis_kelamin') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">No
                                HP</label>
                            <input wire:model="no_hp" type="text" placeholder="081234567890"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                       {{ $errors->has('no_hp') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                            @error('no_hp')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Alamat</label>
                        <textarea wire:model="alamat" rows="2" placeholder="Alamat lengkap siswa"
                            class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                   {{ $errors->has('alamat') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}"></textarea>
                        @error('alamat')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Jurusan</label>
                        <select wire:model.live="jurusan_id"
                            class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                   {{ $errors->has('jurusan_id') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}">
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach ($this->jurusanOptions as $jurusan)
                                <option value="{{ $jurusan->id }}">{{ $jurusan->code }} - {{ $jurusan->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('jurusan_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Kelas</label>
                        <select wire:model="kelas_id"
                            class="w-full rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus:ring-1
                                   {{ $errors->has('kelas_id') ? 'border-red-300 focus:border-red-400 focus:ring-red-400' : 'border-slate-200 focus:border-cyan-400 focus:ring-cyan-400' }}"
                            @disabled(!$jurusan_id)>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($this->kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                        @error('kelas_id')
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
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
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
                    <h2 class="text-lg font-extrabold text-slate-900">Hapus Siswa?</h2>
                </div>
                <p class="mb-6 mt-2 text-sm text-slate-500">Data siswa dan akun placeholder akan dihapus permanen (jika
                    siswa belum claim akun).</p>
                <div class="flex gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                        class="flex-1 rounded-xl bg-red-500 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600 disabled:opacity-70">
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <span wire:loading wire:target="delete">Menghapus...</span>
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
                    <h2 class="text-lg font-extrabold text-slate-900">Import Siswa</h2>
                    <button wire:click="$set('showImportModal', false)"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        ✕
                    </button>
                </div>

                {{-- Download template --}}
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-cyan-100 bg-cyan-50 p-4">
                    <span class="mt-0.5 text-lg">📋</span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-cyan-800">Format Import</p>
                        <p class="mt-0.5 text-xs text-cyan-700">Kolom yang diperlukan: <strong>nis, nisn, nama,
                                kode_jurusan, nama_kelas</strong></p>
                        <a href="{{ route('admin.siswa.template') }}"
                            class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-700">
                            ⬇ Unduh Format CSV
                        </a>
                    </div>
                </div>

                <form wire:submit="processImport" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">File Excel /
                            CSV</label>
                        <div
                            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-8 transition
                                   {{ $errors->has('importFile') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-slate-50 hover:border-cyan-300' }}">
                            <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv" class="hidden"
                                id="import-file-input">
                            @if ($importFile)
                                <p class="text-sm font-semibold text-emerald-600">✓
                                    {{ $importFile->getClientOriginalName() }}</p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ number_format($importFile->getSize() / 1024, 1) }} KB</p>
                                <button type="button" wire:click="$set('importFile', null)"
                                    class="mt-2 text-xs text-red-500 hover:underline">Ganti file</button>
                            @else
                                <div wire:loading wire:target="importFile" class="flex flex-col items-center gap-2">
                                    <div
                                        class="h-6 w-6 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent">
                                    </div>
                                    <p class="text-xs text-slate-500">Mengunggah file...</p>
                                </div>
                                <div wire:loading.remove wire:target="importFile">
                                    <label for="import-file-input" class="cursor-pointer text-center">
                                        <p class="text-sm font-semibold text-slate-600">Klik untuk pilih file</p>
                                        <p class="mt-1 text-xs text-slate-400">Format: .xlsx, .xls, .csv · Maks 5 MB
                                        </p>
                                    </label>
                                </div>
                            @endif
                        </div>
                        @error('importFile')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="$set('showImportModal', false)"
                            class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="processImport"
                            class="flex-1 rounded-xl bg-cyan-600 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
                            <span wire:loading.remove wire:target="processImport">Proses Import</span>
                            <span wire:loading wire:target="processImport"
                                class="inline-flex items-center justify-center gap-2">
                                <span
                                    class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                Mengimpor...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
