<?php

use App\Models\Siswa;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $kategori = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedKategori(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function totalSudahMagang(): int
    {
        return (int) Siswa::query()->whereNotNull('dudi_id')->count();
    }

    #[Computed]
    public function totalBelumMagang(): int
    {
        return (int) Siswa::query()->whereNull('dudi_id')->count();
    }

    #[Computed]
    public function totalSudahCetak(): int
    {
        return (int) Siswa::query()->whereHas('dudi', fn($query) => $query->where('sudah_cetak_surat', true))->count();
    }

    #[Computed]
    public function totalSudahDiterima(): int
    {
        return (int) Siswa::query()->whereHas('dudi', fn($query) => $query->where('diterima', true))->count();
    }

    #[Computed]
    public function siswaList()
    {
        return Siswa::query()
            ->with(['user:id,name,email', 'kelas:id,name', 'jurusan:id,name', 'dudi:id,name,sudah_cetak_surat,diterima'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($subQuery): void {
                    $subQuery
                        ->whereHas('user', fn($userQuery) => $userQuery->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhere('nis', 'like', '%' . $this->search . '%')
                        ->orWhere('nisn', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->kategori === 'sudah_magang', fn($query) => $query->whereNotNull('dudi_id'))
            ->when($this->kategori === 'belum_magang', fn($query) => $query->whereNull('dudi_id'))
            ->when($this->kategori === 'sudah_cetak', fn($query) => $query->whereHas('dudi', fn($dudiQuery) => $dudiQuery->where('sudah_cetak_surat', true)))
            ->when($this->kategori === 'sudah_diterima', fn($query) => $query->whereHas('dudi', fn($dudiQuery) => $dudiQuery->where('diterima', true)))
            ->join('users', 'users.id', '=', 'siswas.user_id')
            ->orderBy('users.name')
            ->select('siswas.*')
            ->paginate(15);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-cyan-600">Monitoring</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Status Siswa Magang</h1>
            <p class="mt-1 text-sm text-slate-500">Pantau kategori siswa berdasarkan progres magang dan status surat
                DUDI.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Sudah Magang</p>
            <p class="mt-2 text-3xl font-extrabold text-emerald-900">{{ $this->totalSudahMagang }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Belum Magang</p>
            <p class="mt-2 text-3xl font-extrabold text-amber-900">{{ $this->totalBelumMagang }}</p>
        </div>
        <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">Sudah Cetak Surat</p>
            <p class="mt-2 text-3xl font-extrabold text-cyan-900">{{ $this->totalSudahCetak }}</p>
        </div>
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">Sudah Diterima</p>
            <p class="mt-2 text-3xl font-extrabold text-indigo-900">{{ $this->totalSudahDiterima }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, NIS, NISN..."
                class="w-72 rounded-xl border border-slate-200 bg-white py-2 pl-4 pr-10 text-sm shadow-sm transition focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
            </div>
        </div>

        <select wire:model.live="kategori"
            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <option value="">Semua Kategori</option>
            <option value="sudah_magang">Sudah Magang</option>
            <option value="belum_magang">Belum Magang</option>
            <option value="sudah_cetak">Sudah Cetak Surat</option>
            <option value="sudah_diterima">Sudah Diterima</option>
        </select>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div wire:loading wire:target="search,kategori"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-2">
                <div class="h-7 w-7 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
                <span class="text-xs font-semibold text-slate-500">Memfilter...</span>
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
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">DUDI</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kategori
                        Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($this->siswaList as $index => $siswa)
                    <tr wire:key="monitoring-siswa-{{ $siswa->id }}" class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm text-slate-400">{{ $this->siswaList->firstItem() + $index }}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $siswa->user?->name ?? '-' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $siswa->kelas?->name ?? '-' }} •
                                {{ $siswa->jurusan?->name ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <p>{{ $siswa->nis }}</p>
                            <p class="text-xs text-slate-400">{{ $siswa->nisn }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            @if ($siswa->dudi)
                                <a href="{{ route('admin.dudi.show', $siswa->dudi_id) }}" wire:navigate
                                    class="font-semibold text-cyan-700 hover:text-cyan-600">
                                    {{ $siswa->dudi->name }}
                                </a>
                            @else
                                <span class="text-slate-400">Belum memilih DUDI</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold {{ $siswa->dudi_id ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $siswa->dudi_id ? 'Sudah Magang' : 'Belum Magang' }}
                                </span>
                                @if ($siswa->dudi_id)
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold {{ $siswa->dudi?->sudah_cetak_surat ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $siswa->dudi?->sudah_cetak_surat ? 'Sudah Cetak' : 'Belum Cetak' }}
                                    </span>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold {{ $siswa->dudi?->diterima ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $siswa->dudi?->diterima ? 'Sudah Diterima' : 'Belum Diterima' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-sm text-slate-400">Tidak ada data siswa
                            sesuai kategori.</td>
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
</div>
