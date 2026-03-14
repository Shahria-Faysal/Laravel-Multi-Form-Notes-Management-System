@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('dashboard') }}" class="btn btn-success">Dashboard</a>
    </div>
    <div class="container mb-2 d-flex justify-content-between">
        <a href="{{ route('notes.create') }}" class="btn btn-primary">
            Add New Notes
        </a>
        <div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button"
        data-bs-toggle="dropdown" {{ $backupCooldown ? 'disabled' : '' }}>
        {{ $backupCooldown ? "Backup on cooldown ({$remainingSeconds}s)..." : 'Take Database Backup' }}
    </button>

    <ul class="dropdown-menu">

        <li>
            <form method="POST" action="{{ route('notes.backup') }}">
                @csrf
                <input type="hidden" name="interval" value="0">
                <button class="dropdown-item" {{ $backupCooldown ? 'disabled' : '' }}>
                    {{ $backupCooldown ? "On cooldown ({$remainingSeconds}s)" : 'Instant' }}
                </button>
            </form>
        </li>

        <li>
            <form method="POST" action="{{ route('notes.backup') }}">
                @csrf
                <input type="hidden" name="interval" value="1">
                <button class="dropdown-item" {{ $backupCooldown ? 'disabled' : '' }}>
                    {{ $backupCooldown ? "On cooldown ({$remainingSeconds}s)" : 'Every minute' }}
                </button>
            </form>
        </li>

        <li>
            <form method="POST" action="{{ route('notes.backup') }}">
                @csrf
                <input type="hidden" name="interval" value="5">
                <button class="dropdown-item" {{ $backupCooldown ? 'disabled' : '' }}>
                    {{ $backupCooldown ? "On cooldown ({$remainingSeconds}s)" : 'Every 5 minutes' }}
                </button>
            </form>
        </li>

    </ul>
</div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div>
                <div class="card">
                    <div class="card-header">Manage Users</div>
                    <div class="card-body">
                        {{ $notesDataTable->table() }}
                        {{ $TrashTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $notesDataTable->scripts(attributes: ['type' => 'module']) }}
    {{ $TrashTable->scripts(attributes: ['type' => 'module']) }}

    <script type="module">
        document.addEventListener("DOMContentLoaded", function () {
            const table = window.LaravelDataTables['notes-table'];
            const trashTable = window.LaravelDataTables['trash-table'];

            function reloadBoth() {
                table.ajax.reload(null, false);
                trashTable.ajax.reload(null, false);
            }
            const editableColumns = [1, 2];
            const ColumnsNames = ['id', 'title', 'note'];
            let currentRow = null;

            function makeEditable(row) {
                row.find('td').each((i, td) => {
                    if (editableColumns.includes(i)) {
                        const val = $(td).text().trim();
                        $(td).html(`<input type="text" class="form-control editable-input" value="${val}">`);
                    }
                })
            }

            function resetRow(row) {
                row.find('td').each((i, td) => {
                    if (editableColumns.includes(i)) {
                        const val = $(td).find('input').val();
                        $(td).text(val);
                    }
                });
                const userId = row.find('.btn-update').data('id');
                row.find('td:last').html(`<button class="btn btn-primary btn-sm edit-user" data-id="${userId}">Edit</button>
                                                    <button class="btn btn-danger btn-sm delete-user" data-id="${userId}">Delete</button>
                                                    `);
            }

            $('table').on('click', '.edit-user', function () {
                const row = $(this).closest('tr');
                if (currentRow && !currentRow.is(row)) resetRow(currentRow);
                makeEditable(row);
                currentRow = row;

                const userId = $(this).data('id');
                row.find('td:last').html(`
                                                    <button class="btn btn-success btn-sm btn-update" data-id="${userId}">Update</button>
                                                    <button class="btn btn-danger btn-sm delete-user" data-id="${userId}">Delete</button>
                                                `);

                $('table').on('click', '.btn-update', function () {
                    const userId = $(this).data('id');
                    const row = $(this).closest('tr');
                    const data = {};
                    row.find('td').each((i, td) => {
                        if (editableColumns.includes(i)) {
                            data[ColumnsNames[i]] = $(td).find('input').val();
                        }
                    });

                    $.ajax({
                        url: "{{ route('notes.update', ':id') }}".replace(':id', userId),
                        type: 'PUT',
                        data: { ...data, _token: '{{ csrf_token() }}' },
                        success: res => {
                            if (res.status === 'success') reloadBoth();
                            else alert(res.message);
                        },
                        error: () => alert('Update Failed')
                    });
                });
            });

            $('table').on('click', '.delete-user', function () {
                const userId = $(this).data('id');
                if (!userId) return;
                if (!confirm('Are you sure?')) return;

                $.ajax({
                    url: "{{ route('notes.destroy', ':id') }}".replace(':id', userId),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: res => {
                        if (res.status === 'success') reloadBoth();
                        else alert(res.message);
                    },
                    error: () => alert('Delete Failed')
                });
            });

            $('#trash-table').on('click', '.force-delete', function () {
                const userId = $(this).data('id');
                if (!userId) return;
                if (!confirm('Delete permanently?')) return;

                $.ajax({
                    url: "{{ route('notes.force-delete', ':id') }}".replace(':id', userId),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: res => {
                        if (res.status === 'success') reloadBoth();
                        else alert(res.message);
                    },
                    error: () => alert('Delete Failed')
                });
            });

            $('#trash-table').on('click', '.restore-user', function () {
                const userId = $(this).data('id');
                if (!userId) return;
                if (!confirm('Restore?')) return;

                $.ajax({
                    url: "{{ route('notes.restore', ':id') }}".replace(':id', userId),
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: res => {
                        if (res.status === 'success') reloadBoth();
                        else alert(res.message);
                    },
                    error: () => alert('Restore Failed')
                });
            });

        });
    </script>
@endpush