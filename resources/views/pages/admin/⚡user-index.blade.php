<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filterRole = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $role = 'siswa';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $email_verified = true;

    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public ?string $toast = null;
    public string $toastType = 'success';

    #[Computed]
    public function roleOptions()
    {
        return Role::query()
            ->whereIn('name', ['admin', 'siswa'])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function userList()
    {
        return User::query()
            ->with(['roles:id,name,display_name', 'siswa:id,user_id,nis'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterRole !== '', function ($query): void {
                $query->whereHas('roles', function ($roleQuery): void {
                    $roleQuery->where('name', $this->filterRole);
                });
            })
            ->orderBy('name')
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
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
        $user = User::query()->with('roles:id,name')->findOrFail($id);

        $this->editingId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? 'siswa';
        $this->email_verified = $user->email_verified_at !== null;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', 'in:admin,siswa'],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'email_verified' => ['boolean'],
        ]);

        $role = Role::query()->where('name', $this->role)->firstOrFail();

        if ($this->editingId) {
            $user = User::query()->findOrFail($this->editingId);

            $payload = [
                'name' => $this->name,
                'email' => $this->email,
                'email_verified_at' => $this->email_verified ? $user->email_verified_at ?? now() : null,
            ];

            if ($this->password !== '') {
                $payload['password'] = Hash::make($this->password);
            }

            $user->update($payload);
            $user->syncRoles([$role]);

            $this->notify('Akun user berhasil diperbarui.');
        } else {
            $user = User::query()->create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => $this->email_verified ? now() : null,
            ]);

            $user->syncRoles([$role]);

            $this->notify('Akun user berhasil ditambahkan.');
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
        $user = User::query()->with('roles:id,name')->findOrFail($this->deletingId);

        if ((int) $user->id === (int) auth()->id()) {
            $this->showDeleteModal = false;
            $this->deletingId = null;
            $this->notify('Akun yang sedang digunakan tidak bisa dihapus.', 'error');

            return;
        }

        if ($user->hasRole('admin')) {
            $adminCount = User::query()->whereHas('roles', fn($query) => $query->where('name', 'admin'))->count();

            if ($adminCount <= 1) {
                $this->showDeleteModal = false;
                $this->deletingId = null;
                $this->notify('Minimal harus ada 1 akun admin aktif.', 'error');

                return;
            }
        }

        $user->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->notify('Akun user berhasil dihapus.');
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
        $this->email = '';
        $this->role = 'siswa';
        $this->password = '';
        $this->password_confirmation = '';
        $this->email_verified = true;
        $this->resetValidation();
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-cyan-600">Manajemen Akun</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">User</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola akun admin dan siswa pada sistem PKL.</p>
        </div>
        <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700 disabled:opacity-70">
            <span wire:loading.remove wire:target="openCreate">+ Tambah User</span>
            <span wire:loading wire:target="openCreate"
                class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
        </button>
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

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama / email user..."
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400 sm:col-span-2">
            <select wire:model.live="filterRole"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                <option value="">Semua Role</option>
                @foreach ($this->roleOptions as $roleOption)
                    <option value="{{ $roleOption->name }}">
                        {{ $roleOption->display_name ?: ucfirst($roleOption->name) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="overflow-hidden rounded-xl border border-slate-200">
            <div
                class="grid grid-cols-12 bg-slate-100 px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 sm:px-4">
                <div class="col-span-3">Nama</div>
                <div class="col-span-3">Email</div>
                <div class="col-span-2 text-center">Role</div>
                <div class="col-span-2 text-center">Status</div>
                <div class="col-span-2 text-right">Aksi</div>
            </div>

            <div class="max-h-[560px] overflow-y-auto">
                @forelse ($this->userList as $user)
                    @php($mainRole = $user->roles->first())
                    <div wire:key="user-row-{{ $user->id }}"
                        class="grid grid-cols-12 items-center border-t border-slate-100 px-3 py-2 text-sm text-slate-700 sm:px-4">
                        <div class="col-span-3 pr-2">
                            <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                            @if ($user->siswa)
                                <p class="text-xs text-slate-500">NIS: {{ $user->siswa->nis }}</p>
                            @endif
                        </div>
                        <div class="col-span-3 pr-2 text-slate-600">{{ $user->email }}</div>
                        <div class="col-span-2 text-center">
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold
                                {{ $mainRole?->name === 'admin' ? 'bg-cyan-100 text-cyan-700' : 'bg-indigo-100 text-indigo-700' }}">
                                {{ $mainRole?->display_name ?: ucfirst($mainRole?->name ?? '-') }}
                            </span>
                        </div>
                        <div class="col-span-2 text-center">
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold {{ $user->email_verified_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $user->email_verified_at ? 'Terverifikasi' : 'Belum Verifikasi' }}
                            </span>
                        </div>
                        <div class="col-span-2 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <button wire:click="openEdit({{ $user->id }})" wire:loading.attr="disabled"
                                    wire:target="openEdit({{ $user->id }})"
                                    class="inline-flex min-w-14 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 disabled:opacity-70">
                                    <span wire:loading.remove wire:target="openEdit({{ $user->id }})">Edit</span>
                                    <span wire:loading wire:target="openEdit({{ $user->id }})"
                                        class="h-3 w-3 animate-spin rounded-full border-2 border-slate-400 border-t-transparent"></span>
                                </button>
                                <button wire:click="confirmDelete({{ $user->id }})" wire:loading.attr="disabled"
                                    wire:target="confirmDelete({{ $user->id }})"
                                    class="inline-flex min-w-14 items-center justify-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100 disabled:opacity-70">
                                    <span wire:loading.remove
                                        wire:target="confirmDelete({{ $user->id }})">Hapus</span>
                                    <span wire:loading wire:target="confirmDelete({{ $user->id }})"
                                        class="h-3 w-3 animate-spin rounded-full border-2 border-red-300 border-t-transparent"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-5 text-sm text-slate-500">Data user tidak ditemukan.</div>
                @endforelse
            </div>
        </div>

        <div class="mt-4">
            {{ $this->userList->links() }}
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-slate-900">{{ $editingId ? 'Edit User' : 'Tambah User' }}
                    </h2>
                    <button wire:click="$set('showModal', false)"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">✕</button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Nama</label>
                        <input wire:model="name" type="text"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Email</label>
                        <input wire:model="email" type="email"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Role</label>
                            <select wire:model="role"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                                @foreach ($this->roleOptions as $roleOption)
                                    <option value="{{ $roleOption->name }}">
                                        {{ $roleOption->display_name ?: ucfirst($roleOption->name) }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Status
                                Email</label>
                            <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
                                <input wire:model="email_verified" type="checkbox"
                                    class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                Email terverifikasi
                            </label>
                            @error('email_verified')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">
                                {{ $editingId ? 'Password Baru (opsional)' : 'Password' }}
                            </label>
                            <input wire:model="password" type="password"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                            @error('password')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-600">Konfirmasi
                                Password</label>
                            <input wire:model="password_confirmation" type="password"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-70">
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save"
                                class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="$set('showDeleteModal', false)">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <h2 class="text-lg font-extrabold text-slate-900">Konfirmasi Hapus</h2>
                <p class="mt-2 text-sm text-slate-600">Akun user yang dihapus tidak dapat dikembalikan. Lanjutkan?</p>

                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-70">
                        <span wire:loading.remove wire:target="delete">Hapus</span>
                        <span wire:loading wire:target="delete"
                            class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
