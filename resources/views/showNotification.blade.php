@extends('layouts.app')

@section('content')
<div class="container">

    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title">{{ $note->title ?? 'Untitled' }}</h3>
            <p class="card-text">{{ $note->note }}</p>

            <!-- Edit Button (optional) -->
            <a href="{{ route('notes.edit', $note) }}" class="btn btn-primary">Edit</a>
        </div>
        <div class="card-footer text-muted">
            Created at: {{ $note->created_at->format('d M Y, H:i') }}
            @if($note->updated_at != $note->created_at)
                | Updated at: {{ $note->updated_at->format('d M Y, H:i') }}
            @endif
        </div>
    </div>
</div>
@endsection