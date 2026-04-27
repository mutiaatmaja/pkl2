<?php

use App\Models\Dudi;
use App\Models\Siswa;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;

    public Dudi $dudi;

    public string $searchPeserta = '';
    public string $searchCalon = '';

    public ?int $selectedSiswaId = null;

    public ?string $toast = null;
    public string $toastType = 'success';

    public function mount(Dudi $dudi): void
    {
        $this->dudi = $dudi;
    }

    #[Computed]
    public function pesertaList()
    {
        return Siswa::query()
            ->with(['user:id,name,email', 'kelas:id,name', 'jurusan:id,name'])
            ->where('dudi_id', $this->dudi->id)
            ->when($this->searchPeserta, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->whereHas('user', fn($userQuery) => $userQuery->where('name', 'like', "%{$this->searchPeserta}%"))
                        ->orWhere('nis', 'like', "%{$this->searchPeserta}%")
                        ->orWhere('nisn', 'like', "%{$this->searchPeserta}%");
                });
            })
            ->join('users', 'users.id', '=', 'siswas.user_id')
            ->orderBy('users.name')
            ->select('siswas.*')
            ->paginate(10);
    }

    #[Computed]
    public function calonSiswaOptions()
    {
        return Siswa::query()
            ->with('user:id,name')
            ->whereNull('dudi_id')
            ->when($this->searchCalon, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->whereHas('user', fn($userQuery) => $userQuery->where('name', 'like', "%{$this->searchCalon}%"))
                        ->orWhere('nis', 'like', "%{$this->searchCalon}%")
                        ->orWhere('nisn', 'like', "%{$this->searchCalon}%");
                });
            })
            ->join('users', 'users.id', '=', 'siswas.user_id')
            ->orderBy('users.name')
            ->select('siswas.*')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function pesertaCount(): int
    {
        return (int) Siswa::query()->where('dudi_id', $this->dudi->id)->count();
    }

    public function updatedSearchPeserta(): void
    {
        $this->resetPage();
    }

    public function addPeserta(): void
    {
        $this->validate([
            'selectedSiswaId' => ['required', Rule::exists('siswas', 'id')->where(fn($query) => $query->whereNull('dudi_id'))],
        ]);

        if ($this->pesertaCount >= $this->dudi->kuota) {
            $this->notify('Kuota DUDI sudah penuh. Tingkatkan kuota terlebih dahulu.', 'error');

            return;
        }

        $siswa = Siswa::query()->whereNull('dudi_id')->find($this->selectedSiswaId);

        if (!$siswa) {
            $this->notify('Siswa tidak ditemukan atau sudah terdaftar di DUDI lain.', 'error');

            return;
        }

        $siswa->update([
            'dudi_id' => $this->dudi->id,
        ]);

        $this->selectedSiswaId = null;
        $this->notify('Peserta berhasil ditambahkan ke DUDI.');
    }

    public function removePeserta(int $siswaId): void
    {
        $siswa = Siswa::query()->where('dudi_id', $this->dudi->id)->find($siswaId);

        if (!$siswa) {
            $this->notify('Peserta tidak ditemukan pada DUDI ini.', 'error');

            return;
        }

        $siswa->update([
            'dudi_id' => null,
        ]);

        $this->notify('Peserta berhasil dihapus dari DUDI.');
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
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.dudi.index') }}" wire:navigate
                class="text-sm font-semibold text-cyan-700 hover:text-cyan-600">Kembali ke daftar DUDI</a>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Detail DUDI</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $dudi->name }} • {{ $dudi->address }}</p>
        </div>
        <a href="{{ route('admin.dudi.surat-permohonan', $dudi) }}" target="_blank"
            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
            Cetak Surat Permohonan
        </a>
    </div>

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
        <h2 class="text-base font-extrabold text-slate-900">Tambah Peserta Manual</h2>
        <p class="mt-1 text-sm text-slate-500">Pilih siswa yang belum terdaftar di DUDI lain.</p>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <input wire:model.live.debounce.300ms="searchCalon" type="text" placeholder="Cari calon siswa..."
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <select wire:model="selectedSiswaId"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                <option value="">-- Pilih Siswa --</option>
                @foreach ($this->calonSiswaOptions as $siswa)
                    <option value="{{ $siswa->id }}">
                        {{ $siswa->user?->name ?? '-' }} - {{ $siswa->nis }}
                    </option>
                @endforeach
            </select>
            <button wire:click="addPeserta" wire:loading.attr="disabled" wire:target="addPeserta"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
                <span wire:loading.remove wire:target="addPeserta">Tambah Peserta</span>
                <span wire:loading wire:target="addPeserta" class="inline-flex items-center gap-2">
                    <span
                        class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Menambahkan...
                </span>
            </button>
        </div>
        @error('selectedSiswaId')
            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div wire:loading wire:target="searchPeserta, addPeserta, removePeserta"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-2">
                <div class="h-7 w-7 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="addPeserta">Menambahkan
                    peserta...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="removePeserta">Menghapus
                    peserta...</span>
                <span class="text-xs font-semibold text-slate-500" wire:loading wire:target="searchPeserta">Memfilter
                    peserta...</span>
            </div>
        </div>

        <div class="border-b border-slate-100 px-5 py-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-extrabold text-slate-900">Daftar Peserta DUDI</h2>
                <input wire:model.live.debounce.300ms="searchPeserta" type="text" placeholder="Cari peserta..."
                    class="w-64 rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="w-12 px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">NIS / NISN
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kelas</th>
                    <th class="w-36 px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($this->pesertaList as $index => $siswa)
                    <tr wire:key="peserta-{{ $siswa->id }}" class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm text-slate-400">{{ $this->pesertaList->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $siswa->user?->name ?? '-' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $siswa->user?->email ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <p>{{ $siswa->nis }}</p>
                            <p class="text-xs text-slate-400">{{ $siswa->nisn }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $siswa->kelas?->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end">
                                <button wire:click="removePeserta({{ $siswa->id }})" wire:loading.attr="disabled"
                                    wire:target="removePeserta({{ $siswa->id }})"
                                    class="inline-flex items-center gap-2 rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100 disabled:opacity-60">
                                    <span wire:loading.remove wire:target="removePeserta({{ $siswa->id }})">Hapus
                                        Peserta</span>
                                    <span wire:loading wire:target="removePeserta({{ $siswa->id }})"
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
                        <td colspan="5" class="px-6 py-14 text-center text-sm text-slate-400">Belum ada peserta pada
                            DUDI ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($this->pesertaList->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $this->pesertaList->links() }}
            </div>
        @endif
    </div>
</div>
