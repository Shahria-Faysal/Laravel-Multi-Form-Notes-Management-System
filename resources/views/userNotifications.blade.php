@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="container py-4" style="max-width: 680px;">

        {{-- Page header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 fw-semibold mb-1">Notifications</h1>
                <p class="text-muted small mb-0">Your recent activity and alerts.</p>
            </div>

            @if(auth()->user()->unreadNotifications->count() > 0)
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        {{-- Filter tabs --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ request('filter') !== 'unread' ? 'active' : '' }}"
                    href="{{ route('notifications.index') }}">
                    All
                    <span class="badge text-bg-secondary ms-1">{{ auth()->user()->notifications->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('filter') === 'unread' ? 'active' : '' }}"
                    href="{{ route('notifications.index', ['filter' => 'unread']) }}">
                    Unread
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="badge text-bg-primary ms-1">{{ auth()->user()->unreadNotifications->count() }}</span>
                    @endif
                </a>
            </li>
        </ul>

        {{-- Notification list --}}
        <div class="d-flex flex-column gap-2">

            @forelse($notifications as $notification)
                <div class="card border notification-card {{ is_null($notification->read_at) ? 'border-primary border-opacity-25 bg-primary bg-opacity-10' : '' }}"
                    data-id="{{ $notification->id }}">

                    <div class="card-body d-flex align-items-start gap-3 py-3 px-3">

                        @if(isset($notification->data['note_id']))
                            <a href="{{ route('note.show', $notification->data['note_id']) }}" class="stretched-link"></a>
                        @endif

                        {{-- Icon --}}
                        <div class="notification-icon flex-shrink-0 mt-1">
                            @if(isset($notification->data['type']) && $notification->data['type'] === 'backup')
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                                    style="width: 36px; height: 36px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#198754" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="1 4 1 10 7 10" />
                                        <path d="M3.51 15a9 9 0 1 0 .49-3.5" />
                                    </svg>
                                </div>
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                                    style="width: 36px; height: 36px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0d6efd" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-medium small">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </div>
                            <div class="text-muted small mt-1">
                                {{ $notification->data['message'] ?? '' }}
                            </div>
                            <div class="text-muted mt-1" style="font-size: 11px;">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>

                        {{-- Right side --}}
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">

                            {{-- Unread dot --}}
                            @if(is_null($notification->read_at))
                                <span class="rounded-circle bg-primary flex-shrink-0"
                                    style="width: 8px; height: 8px; display: inline-block;"></span>
                            @endif

                            {{-- Mark as read --}}
                            @if(is_null($notification->read_at))
                                <form method="POST" action="{{ route('notifications.markRead', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-secondary p-1" title="Mark as read"
                                        style="line-height: 1;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </button>
                                </form>
                            @endif

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}"
                                onsubmit="return confirm('Delete this notification?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-secondary p-1 delete-btn" title="Delete"
                                    style="line-height: 1;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14H6L5 6" />
                                        <path d="M10 11v6" />
                                        <path d="M14 11v6" />
                                        <path d="M9 6V4h6v2" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            @empty
                <div class="text-center text-muted py-5">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        class="mb-3 opacity-25">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <p class="small mb-0">No notifications yet.</p>
                </div>
            @endforelse

        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="mt-4">
                {{ $notifications->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.notification-card').forEach(card => {

            card.addEventListener('click', function () {

                let notificationId = this.dataset.id;

                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });

                // Update UI instantly
                this.classList.remove(
                    'border-primary',
                    'border-opacity-25',
                    'bg-primary',
                    'bg-opacity-10'
                );

            });

        });
    </script>
@endpush
<style>
    .notification-card {
        transition: opacity 0.2s, border-color 0.2s;
    }

    .delete-btn:hover {
        color: var(--bs-danger) !important;
    }
</style>