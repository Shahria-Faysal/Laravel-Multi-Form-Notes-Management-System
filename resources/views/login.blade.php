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
            <div class="col-5 mt-5">
                <div class="card">
                    <div class="card-header">
                        Login
                    </div>
                    <div class="card-body">
                        <form action="{{ route('loginMatch') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="useremail" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="useremail"
                                    aria-describedby="emailHelp" name="email">
                            </div>
                            <div class="mb-3">
                                <label for="userpass" class="form-label">Password</label>
                                <input type="password" class="form-control" id="userpass" name="password">
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                            <a href="{{ route('register') }}" class="btn btn-secondary">Register</a>
                        </form>
                    </div>
                    @if ($errors->any())
                        <div class="card-footer text-body-secondary">
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>

</html>