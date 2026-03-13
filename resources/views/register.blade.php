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
                        Register
                    </div>
                    <div class="card-body">
                        <form action="{{ route('registerSave') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="username" class="form-label">Name</label>
                                <input type="name" class="form-control" id="username" name="name">
                            </div>
                            <div class="mb-3">
                                <label for="useremail" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="useremail"
                                    aria-describedby="emailHelp" name="email">
                            </div>
                            <div class="mb-3">
                                <label for="userpass" class="form-label">Password</label>
                                <input type="password" class="form-control" id="userpass" name="password">
                            </div>
                            <div class="mb-3">
                                <label for="userpass-confirm" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="userpass" name="password_confirmation">
                            </div>
                            <div class="mb-3">
                                <label for="admincode-confirm" class="form-label">Put Admin Code (Optional)</label>
                                <input type="number" class="form-control" id="admincode" name="admincode">
                            </div>
                            <button type="submit" class="btn btn-primary">Register</button>
                            <a href="/" class="btn btn-secondary">Back</a>
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