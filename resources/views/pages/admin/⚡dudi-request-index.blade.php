<?php

use App\Models\Dudi;
use App\Models\DudiRequest;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    public string $search = '';
    public string $filterStatus = '';

    public bool $showReviewModal = false;
    public ?int $reviewingId = null;
    public string $admin_feedback = '';

    public ?string $toast = null;
    public string $toastType = 'success';

    #[Computed]
    public function requestList()
    {
        return DudiRequest::query()
            ->with(['siswa.user:id,name', 'reviewer:id,name', 'approvedDudi:id,name'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhereHas('siswa.user', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->filterStatus !== '', fn($query) => $query->where('status', $this->filterStatus))
            ->latest()
            ->get();
    }

    #[Computed]
    public function reviewingRequest(): ?DudiRequest
    {
        if (!$this->reviewingId) {
            return null;
        }

        return DudiRequest::query()->with('siswa.user:id,name')->find($this->reviewingId);
    }

    public function openReview(int $id): void
    {
        $request = DudiRequest::query()->findOrFail($id);

        if ($request->status !== 'pending') {
            $this->notify('Request ini sudah divalidasi sebelumnya.', 'error');

            return;
        }

        $this->reviewingId = $id;
        $this->admin_feedback = '';
        $this->resetValidation();
        $this->showReviewModal = true;
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->reviewingId = null;
        $this->admin_feedback = '';
    }

    public function approve(): void
    {
        $this->validate([
            'admin_feedback' => 'nullable|string|max:1000',
        ]);

        $wasApproved = DB::transaction(function (): bool {
            $request = DudiRequest::query()->lockForUpdate()->findOrFail($this->reviewingId);

            if ($request->status !== 'pending') {
                return false;
            }

            $dudi = Dudi::create([
                'name' => $request->name,
                'address' => $request->address,
                'panggilan_pimpinan' => $request->panggilan_pimpinan,
                'kuota' => $request->kuota,
                'aktif' => true,
            ]);

            $request->update([
                'status' => 'approved',
                'admin_feedback' => $this->admin_feedback !== '' ? $this->admin_feedback : 'Request disetujui dan dimasukkan ke data DUDI.',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'approved_dudi_id' => $dudi->id,
            ]);

            return true;
        });

        if (!$wasApproved) {
            $this->closeReviewModal();
            $this->notify('Request ini sudah divalidasi sebelumnya.', 'error');

            return;
        }

        $this->closeReviewModal();
        $this->notify('Request DUDI berhasil di-approve.');
    }

    public function reject(): void
    {
        $this->validate([
            'admin_feedback' => 'required|string|max:1000',
        ]);

        $request = DudiRequest::query()->findOrFail($this->reviewingId);

        if ($request->status !== 'pending') {
            $this->closeReviewModal();
            $this->notify('Request ini sudah divalidasi sebelumnya.', 'error');

            return;
        }

        $request->update([
            'status' => 'rejected',
            'admin_feedback' => $this->admin_feedback,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->closeReviewModal();
        $this->notify('Request DUDI ditolak.');
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
        <p class="text-xs font-bold uppercase tracking-widest text-cyan-600">Review</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Request DUDI Siswa</h1>
        <p class="mt-1 text-sm text-slate-500">Validasi request DUDI dari siswa sebelum masuk ke data master DUDI.</p>
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
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama DUDI / siswa..."
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400 sm:col-span-2">
            <select wire:model.live="filterStatus"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approve</option>
                <option value="rejected">Tolak</option>
            </select>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="overflow-hidden rounded-xl border border-slate-200">
            <div
                class="grid grid-cols-12 bg-slate-100 px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 sm:px-4">
                <div class="col-span-2">Siswa</div>
                <div class="col-span-3">DUDI Request</div>
                <div class="col-span-1 text-center">Kuota</div>
                <div class="col-span-1 text-center">Status</div>
                <div class="col-span-3">Feedback</div>
                <div class="col-span-2 text-right">Aksi</div>
            </div>

            <div class="max-h-[560px] overflow-y-auto">
                @forelse ($this->requestList as $request)
                    <div wire:key="admin-request-{{ $request->id }}"
                        class="grid grid-cols-12 items-center border-t border-slate-100 px-3 py-2 text-sm text-slate-700 sm:px-4">
                        <div class="col-span-2 pr-2 font-semibold text-slate-900">
                            {{ $request->siswa?->user?->name ?? '-' }}</div>
                        <div class="col-span-3 pr-2">
                            <p class="font-semibold text-slate-900">{{ $request->name }}</p>
                            <p class="text-xs text-slate-500">{{ $request->panggilan_pimpinan }} •
                                {{ $request->address }}</p>
                        </div>
                        <div class="col-span-1 text-center font-semibold">{{ $request->kuota }}</div>
                        <div class="col-span-1 text-center">
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold
                                {{ $request->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($request->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $request->status === 'approved' ? 'Approve' : ($request->status === 'rejected' ? 'Tolak' : 'Pending') }}
                            </span>
                        </div>
                        <div class="col-span-3 text-xs text-slate-600">
                            {{ $request->admin_feedback ?: '-' }}
                            @if ($request->status === 'approved' && $request->approvedDudi)
                                <p class="mt-1 font-semibold text-emerald-700">Masuk DUDI:
                                    {{ $request->approvedDudi->name }}</p>
                            @endif
                        </div>
                        <div class="col-span-2 text-right">
                            @if ($request->status === 'pending')
                                <button wire:click="openReview({{ $request->id }})" wire:loading.attr="disabled"
                                    wire:target="openReview({{ $request->id }})"
                                    class="inline-flex items-center rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-700">
                                    Review
                                </button>
                            @else
                                <span class="text-xs font-semibold text-slate-400">Selesai</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-5 text-sm text-slate-500">Belum ada request DUDI dari siswa.</div>
                @endforelse
            </div>
        </div>
    </div>

    @if ($showReviewModal && $this->reviewingRequest)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-5 shadow-xl">
                <h2 class="text-lg font-extrabold text-slate-900">Review Request DUDI</h2>
                <p class="mt-1 text-sm text-slate-500">Cek detail request, lalu pilih approve atau tolak dengan feedback
                    admin.</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Siswa</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $this->reviewingRequest->siswa?->user?->name ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nama DUDI</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $this->reviewingRequest->name }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Panggilan Pimpinan</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $this->reviewingRequest->panggilan_pimpinan }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Kuota</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $this->reviewingRequest->kuota }} siswa
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 sm:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Alamat</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $this->reviewingRequest->address }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Feedback Admin</label>
                    <textarea wire:model="admin_feedback" rows="4" placeholder="Tulis feedback approval/penolakan..."
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400"></textarea>
                    <p class="mt-1 text-xs text-slate-500">Feedback wajib saat menolak, opsional saat approve.</p>
                    @error('admin_feedback')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="closeReviewModal" wire:loading.attr="disabled"
                        wire:target="closeReviewModal,approve,reject"
                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                        Tutup
                    </button>
                    <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject"
                        class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-70">
                        <span wire:loading.remove wire:target="reject">Tolak</span>
                        <span wire:loading wire:target="reject"
                            class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    </button>
                    <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-70">
                        <span wire:loading.remove wire:target="approve">Approve</span>
                        <span wire:loading wire:target="approve"
                            class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
