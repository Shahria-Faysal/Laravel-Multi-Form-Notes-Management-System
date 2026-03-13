<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-6 mb-3">
                <h1>Welcome {{ Auth::user()->name }}</h1>
                {{ Auth::user() }}
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                @if (auth()->user()->is_admin)
                    <a href="{{ route('notes.index') }}" class="btn btn-primary">Go to All notes</a>
                @else
                    <a href="{{ route('notes.index') }}" class="btn btn-primary">Go to my notes</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>