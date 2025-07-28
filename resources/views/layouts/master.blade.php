<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'eSIM Satın Al')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
.navbar {
    padding: 0.8rem 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.navbar-brand {
    font-size: 1.4rem;
}
.nav-link {
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: all 0.3s;
}
.nav-link:hover {
    background-color: rgba(255,255,255,0.15);
}
.nav-link.active {
    font-weight: 600;
    background-color: rgba(255,255,255,0.1);
}
.btn-outline-light:hover {
    color: #0d6efd !important;
}
</style>
</head>
<body>
    <div id="app">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/vue@3.4.15/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @yield('scripts')
</body>
</html>
