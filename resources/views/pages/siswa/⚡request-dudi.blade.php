<?php

use App\Models\DudiRequest;
use App\Models\Siswa;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.siswa')] class extends Component {
    public string $name = '';
    public string $address = '';
    public string $panggilan_pimpinan = 'Pimpinan';
    public int $kuota = 5;

    public ?string $toast = null;
    public string $toastType = 'success';

    #[Computed]
    public function currentSiswa(): ?Siswa
    {
        return auth()->user()?->siswa;
    }

    #[Computed]
    public function pendingCount(): int
    {
        if (!$this->currentSiswa) {
            return 0;
        }

        return (int) DudiRequest::query()->where('siswa_id', $this->currentSiswa->id)->where('status', 'pending')->count();
    }

    #[Computed]
    public function requestList()
    {
        if (!$this->currentSiswa) {
            return collect();
        }

        return DudiRequest::query()
            ->where('siswa_id', $this->currentSiswa->id)
            ->with(['reviewer:id,name', 'approvedDudi:id,name'])
            ->latest()
            ->get();
    }

    public function submit(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'panggilan_pimpinan' => 'required|string|max:50',
            'kuota' => 'required|integer|min:1|max:9999',
        ]);

        if (!$this->currentSiswa) {
            $this->notify('Profil siswa tidak ditemukan.', 'error');

            return;
        }

        if ($this->pendingCount >= 2) {
            $this->notify('Maksimal 2 request pending. Tunggu validasi admin terlebih dahulu.', 'error');

            return;
        }

        DudiRequest::create([
            'siswa_id' => $this->currentSiswa->id,
            'name' => $this->name,
            'address' => $this->address,
            'panggilan_pimpinan' => $this->panggilan_pimpinan,
            'kuota' => $this->kuota,
            'status' => 'pending',
        ]);

        $this->reset(['name', 'address']);
        $this->panggilan_pimpinan = 'Pimpinan';
        $this->kuota = 5;
        $this->notify('Request DUDI berhasil dikirim.');
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
        <p class="text-xs font-bold tracking-[0.22em] text-cyan-700">MENU SISWA</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Request DUDI</h1>
        <p class="mt-1 text-sm text-slate-500">Ajukan DUDI baru jika belum tersedia pada daftar pilihan.</p>
        <p class="mt-2 text-xs font-semibold text-slate-500">
            Batas request pending: <span class="font-bold text-slate-700">{{ $this->pendingCount }}/2</span>
        </p>
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

    <form wire:submit="submit" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama DUDI</label>
            <input wire:model="name" type="text" placeholder="PT Contoh Teknologi"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <p class="mt-1 text-xs text-slate-500">Isi nama instansi/perusahaan DUDI dengan jelas.</p>
            @error('name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Alamat DUDI</label>
            <textarea wire:model="address" rows="2" placeholder="Alamat lengkap DUDI"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400"></textarea>
            <p class="mt-1 text-xs text-slate-500">Contoh: Jl. Ahmad Yani No. 12, Pontianak.</p>
            @error('address')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Panggilan Pimpinan</label>
            <input wire:model="panggilan_pimpinan" type="text" placeholder="Manager"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <p class="mt-1 text-xs text-slate-500">Contoh: Manager, Ketua, Direktur, atau Pimpinan.</p>
            @error('panggilan_pimpinan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kuota Siswa</label>
            <input wire:model="kuota" type="number" min="1" max="9999"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <p class="mt-1 text-xs text-slate-500">Perkiraan jumlah siswa yang dapat diterima DUDI tersebut.</p>
            @error('kuota')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
            <span wire:loading.remove wire:target="submit">Kirim Request</span>
            <span wire:loading wire:target="submit"
                class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
        </button>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-extrabold text-slate-900">Riwayat Request DUDI</h2>
        <p class="mt-1 text-sm text-slate-500">Semua request Anda beserta status review admin.</p>

        <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
            <div
                class="grid grid-cols-12 bg-slate-100 px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 sm:px-4">
                <div class="col-span-3">DUDI</div>
                <div class="col-span-3">Panggilan</div>
                <div class="col-span-1 text-center">Kuota</div>
                <div class="col-span-2 text-center">Status</div>
                <div class="col-span-3">Feedback Admin</div>
            </div>

            <div class="max-h-96 overflow-y-auto">
                @forelse ($this->requestList as $request)
                    <div wire:key="request-{{ $request->id }}"
                        class="grid grid-cols-12 items-center border-t border-slate-100 px-3 py-2 text-sm text-slate-700 sm:px-4">
                        <div class="col-span-3 pr-2 font-semibold text-slate-900">{{ $request->name }}</div>
                        <div class="col-span-3 pr-2 text-slate-600">{{ $request->panggilan_pimpinan }}</div>
                        <div class="col-span-1 text-center font-semibold text-slate-800">{{ $request->kuota }}</div>
                        <div class="col-span-2 text-center">
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold
                                {{ $request->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($request->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $request->status === 'approved' ? 'Approve' : ($request->status === 'rejected' ? 'Tolak' : 'Pending') }}
                            </span>
                        </div>
                        <div class="col-span-3 text-xs text-slate-600">
                            {{ $request->admin_feedback ?: '-' }}
                            @if ($request->status === 'approved' && $request->approvedDudi)
                                <p class="mt-1 font-semibold text-emerald-700">Masuk sebagai:
                                    {{ $request->approvedDudi->name }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-5 text-sm text-slate-500">Belum ada request DUDI.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
