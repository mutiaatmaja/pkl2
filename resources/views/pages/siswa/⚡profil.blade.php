<?php

use App\Models\Siswa;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.siswa')] class extends Component {
    public string $jenis_kelamin = '';
    public string $alamat = '';
    public string $no_hp = '';

    public ?string $toast = null;
    public string $toastType = 'success';

    public function mount(): void
    {
        $siswa = auth()->user()?->siswa;

        if (!$siswa) {
            abort(404);
        }

        $this->jenis_kelamin = (string) ($siswa->jenis_kelamin ?? '');
        $this->alamat = (string) ($siswa->alamat ?? '');
        $this->no_hp = (string) ($siswa->no_hp ?? '');
    }

    public function save(): void
    {
        $this->validate([
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string|max:1000',
            'no_hp' => 'required|string|max:30',
        ]);

        $siswa = auth()->user()?->siswa;

        if (!$siswa) {
            $this->notify('Profil siswa tidak ditemukan.', 'error');

            return;
        }

        $siswa->update([
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
        ]);

        $this->notify('Profil berhasil diperbarui.');
    }

    public function dismissToast(): void
    {
        $this->toast = null;
    }

    public function isComplete(): bool
    {
        $siswa = auth()->user()?->siswa;

        return $siswa instanceof Siswa && $siswa->isProfileComplete();
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
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Profil Saya</h1>
        <p class="mt-1 text-sm text-slate-500">Lengkapi data profil agar bisa memilih DUDI.</p>
    </div>

    @if (session('profile_required_message'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
            {{ session('profile_required_message') }}
        </div>
    @endif

    @if ($toast)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false;
            $wire.dismissToast(); }, 3500)"
            class="fixed right-5 top-5 z-50 flex items-center gap-3 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg
                   {{ $toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500' }}">
            <span>{{ $toastType === 'success' ? '✓' : '✕' }}</span>
            {{ $toast }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm text-slate-500">Status profil:</p>
            <span
                class="inline-flex rounded-full px-2 py-1 text-xs font-bold {{ $this->isComplete() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $this->isComplete() ? 'Lengkap' : 'Belum Lengkap' }}
            </span>
        </div>

        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jenis Kelamin</label>
                <select wire:model="jenis_kelamin"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                    <option value="">Pilih jenis kelamin</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
                @error('jenis_kelamin')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Alamat</label>
                <textarea wire:model="alamat" rows="3" placeholder="Alamat lengkap"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400"></textarea>
                @error('alamat')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">No HP</label>
                <input wire:model="no_hp" type="text" placeholder="Contoh: 081234567890"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                @error('no_hp')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-1">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
                    <span wire:loading.remove wire:target="save">Simpan Profil</span>
                    <span wire:loading wire:target="save"
                        class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                </button>

                @if ($this->isComplete())
                    <a href="{{ route('siswa.pilih-dudi') }}" wire:navigate
                        class="ml-2 inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                        Lanjut Pilih DUDI
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
