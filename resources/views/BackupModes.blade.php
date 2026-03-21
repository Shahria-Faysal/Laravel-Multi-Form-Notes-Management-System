@extends('layouts.app')
 
@section('title', 'Backup Scheduler')
 
@section('content')
<div class="container py-4" style="max-width: 680px;">
 
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
 
    {{-- Page header --}}
    <div class="mb-4">
        <h1 class="h3 fw-semibold mb-1">Backup Schedule</h1>
        <p class="text-muted small mb-0">Set scheduled backups — they run automatically at the times you choose.</p>
    </div>
 
    {{-- Action bar --}}
    <div class="d-flex gap-2 mb-4">
        <button class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addSlotModal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add backup slot
        </button>
 
        <form method="POST" action="{{ route('backup.instant') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                    onclick="showToast('Taking backup now…', false)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/>
                </svg>
                Instant backup
            </button>
        </form>
    </div>
 
    {{-- Slot list --}}
    <div class="d-flex flex-column gap-3" id="slotList">
 
        @forelse($schedules ?? [] as $schedule)
        <div class="card border slot-card {{ $schedule->status ? 'border-primary border-opacity-25' : 'opacity-50' }}"
             id="slot-{{ $schedule->id }}">
            <div class="card-body d-flex align-items-center gap-3 py-3 px-3">
 
                {{-- Time --}}
                <div class="slot-time fw-semibold text-body me-1" style="font-family: monospace; font-size: 2rem; min-width: 100px; letter-spacing: -1px; line-height: 1;">
                    {{ \Carbon\Carbon::parse($schedule->time)->format('H:i') }}
                </div>
 
                {{-- Meta --}}
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-medium text-truncate small">{{ $schedule->label ?: 'Backup' }}</div>
                    @if($schedule->is_instant)
                        <span class="badge text-bg-warning mt-1" style="font-size: 10px;">Instant</span>
                    @endif
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $d)
                            <span class="badge rounded-pill {{ in_array($d, $schedule->days ?? []) ? 'text-bg-primary' : 'text-bg-secondary bg-opacity-25 text-secondary' }}"
                                  style="font-size: 10px;">
                                {{ $d }}
                            </span>
                        @endforeach
                    </div>
                </div>
 
                {{-- Right side: delete + toggle --}}
                <div class="d-flex align-items-center gap-3 flex-shrink-0">
 
                    {{-- Delete --}}
                    <form method="POST" action="{{ route('backup.schedule.destroy', $schedule->id) }}"
                          onsubmit="return confirm('Remove this backup slot?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-link text-secondary p-1 delete-btn" title="Remove"
                                style="line-height: 1;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                            </svg>
                        </button>
                    </form>
 
                    {{-- Toggle --}}
                    <div class="form-check form-switch mb-0" title="{{ $schedule->status ? 'Enabled' : 'Disabled' }}">
                        <input class="form-check-input" type="checkbox" role="switch"
                               style="width: 2.5em; height: 1.4em; cursor: pointer;"
                               {{ $schedule->status ? 'checked' : '' }}
                               onchange="toggleSchedule({{ $schedule->id }}, this)">
                    </div>
                </div>
            </div>
        </div>
 
        @empty
        <div class="text-center text-muted py-5" id="emptyState">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                 class="mb-3 opacity-25">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <p class="small mb-0">No backup slots yet.<br>Add one to get started.</p>
        </div>
        @endforelse
 
    </div>
</div>
 
{{-- Add slot modal --}}
<div class="modal fade" id="addSlotModal" tabindex="-1" aria-labelledby="addSlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-semibold" id="addSlotModalLabel">New backup slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form method="POST" action="{{ route('backup.schedule.store') }}" id="addSlotForm">
                    @csrf
 
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-uppercase text-muted" style="letter-spacing: .5px;">Time</label>
                        <input type="time" name="time" id="timeInput" class="form-control form-control-lg" required
                               style="font-family: monospace; font-size: 1.25rem;">
                    </div>

                   <div class="mb-3 d-flex align-items-center justify-content-between">
                        <label class="form-label fw-semibold text-muted mb-0">Take Continuous Backup (Optional)</label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                style="width: 2.5em; height: 1.4em; cursor: pointer;"
                                onchange="toggleIntervalInput(this)">
                        </div>
                    </div>

                    <div class="mb-3" id="intervalInput" style="display: none;">
                        <label class="form-label small fw-semibold text-uppercase text-muted" style="letter-spacing: .5px;">
                            Interval (minutes)
                        </label>
                        <input type="number" name="is_continuous" class="form-control" min="1" placeholder="e.g. 5">
                    </div>
 
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-uppercase text-muted" style="letter-spacing: .5px;">Label <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" name="label" class="form-control" placeholder="e.g. Daily midnight backup">
                    </div>
 
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-uppercase text-muted" style="letter-spacing: .5px;">Repeat on</label>
                        <div class="d-flex flex-wrap gap-2" id="dayPicker">
                            @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $d)
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary day-btn"
                                        style="width: 42px; height: 42px;"
                                        data-day="{{ $d }}"
                                        onclick="toggleDay(this)">
                                    {{ $d }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="days" id="daysInput">
                    </div>
 
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary flex-fill">Save slot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
 
{{-- Toast --}}
<div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 1100;">
    <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <span id="toastDot" class="rounded-circle bg-success flex-shrink-0" style="width:8px;height:8px;display:inline-block;"></span>
                <span id="toastMsg">Done</span>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
 
@endsection
 
@push('scripts')
<script>

function toggleIntervalInput(checkbox) {
    const intervalDiv = document.getElementById('intervalInput');
    intervalDiv.style.display = checkbox.checked ? 'block' : 'none';
}

// ── Day picker ───────────────────────────────────────────────────────────────
function toggleDay(btn) {
    btn.classList.toggle('btn-outline-secondary');
    btn.classList.toggle('btn-primary');
    syncDays();
}
 
function syncDays() {
    const selected = [...document.querySelectorAll('#dayPicker .btn-primary')].map(b => b.dataset.day);
    document.getElementById('daysInput').value = JSON.stringify(selected);
}
 
// ── Toggle schedule on/off via fetch ────────────────────────────────────────
function toggleSchedule(id, checkbox) {
    const card    = document.getElementById('slot-' + id);
    const status = checkbox.checked;
 
    fetch(`/backup/schedule/${id}/toggle`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            card.classList.toggle('opacity-50', !status);
            card.classList.toggle('border-primary', status);
            card.classList.toggle('border-opacity-25', status);
            showToast(status ? 'Backup slot status' : 'Backup slot disabled');
        } else {
            checkbox.checked = !status;
            showToast('Could not update slot', true);
        }
    })
    .catch(() => {
        checkbox.checked = !status;
        showToast('Network error', true);
    });
}
 
    fetch(`/backup/schedule/continuous/${id}/toggle`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            card.classList.toggle('opacity-50', !status);
            card.classList.toggle('border-primary', status);
            card.classList.toggle('border-opacity-25', status);
            showToast(status ? 'Backup slot status' : 'Backup slot disabled');
        } else {
            checkbox.checked = !status;
            showToast('Could not update slot', true);
        }
    })
    .catch(() => {
        checkbox.checked = !status;
        showToast('Network error', true);
    });
 
// ── Toast ────────────────────────────────────────────────────────────────────
function showToast(msg, isError = false) {
    const toastEl = document.getElementById('liveToast');
    const dot     = document.getElementById('toastDot');
    document.getElementById('toastMsg').textContent = msg;
    dot.className = `rounded-circle flex-shrink-0 ${isError ? 'bg-danger' : 'bg-success'}`;
    dot.style.cssText = 'width:8px;height:8px;display:inline-block;';
    bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 }).show();
}
 
// ── Flash success after instant backup ──────────────────────────────────────
@if(session('backup_taken'))
    document.addEventListener('DOMContentLoaded', () => showToast('Backup completed successfully'));
@endif
 
// Reset modal on close
document.getElementById('addSlotModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('addSlotForm').reset();
    document.querySelectorAll('#dayPicker .btn-primary').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-secondary');
    });
    document.getElementById('daysInput').value = '';
});
</script>
 
<style>
.delete-btn:hover { color: var(--bs-danger) !important; }
.slot-card { transition: opacity 0.2s, border-color 0.2s; }
</style>
@endpush