<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Inventory System')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @stack('head')
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">

            <a class="navbar-brand fw-bold" href="/">Inventory System</a>

            <div class="d-flex gap-2">
                <a href="/" class="btn btn-outline-light">Dashboard</a>
                <a href="/inventory" class="btn btn-outline-light">Inventory</a>
                <button type="button" id="logoutButton" class="btn btn-danger d-none">Logout</button>
            </div>

        </div>
    </nav>

    @yield('content')

    <footer class="py-4 mt-5 border-top bg-white">
        <div class="container text-center">
            <p class="text-muted small mb-0">Inventory System</p>
        </div>
    </footer>

    {{-- Bootstrap JS (classic script) runs before the deferred Vite modules. --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @vite('resources/js/app.js')

    @stack('scripts')

</body>

</html>
