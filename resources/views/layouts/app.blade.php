<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Document Summarizer')</title>
    <link href="{{ asset('build/assets/app-DKQt_pIM.css') }}" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">DocSum</a>
            <a class="nav-link" href="/summarize">Summarize</a>
        </div>
    </nav>
    <main class="container">
        @yield('content')
    </main>
    @livewireScripts
    <script src="{{ asset('build/assets/app-Ijw946WK.js') }}"></script>
</body>
</html>
