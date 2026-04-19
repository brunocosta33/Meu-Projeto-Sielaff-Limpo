<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1456b8">

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    @yield('head-meta')

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/boostrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    {{-- Livewire Styles --}}
    @livewireStyles

    @yield('head-scripts')
</head>

<body>
    <div id="app">
        <div class="wrapper">
            <main id="content">
                @yield('content')
                <footer class="text-center text-muted small py-3">
                    Desenvolvido por Bruno Costa &middot; &copy; 2026 Todos os direitos reservados.
                </footer>
            </main>
        </div>

        {{-- Scripts --}}
        <script src="{{ asset('js/app.js') }}"></script>

        {{-- Livewire Scripts --}}
        @livewireScripts

        @yield('foot-script')
    </div>
</body>
</html>
<!--
Developed by d2y - develop2you.com
Support support@develop2you.com | +351 220 502 129
version 1.0 - 2019
-->
