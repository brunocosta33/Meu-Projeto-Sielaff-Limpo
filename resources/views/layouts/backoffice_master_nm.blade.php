<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('head-meta')

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    
    <link href="{{ asset('css/boostrap-select.min.css') }}" rel="stylesheet">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">


    @yield('head-scripts')

</head>

<body>
    <div id="app">
        <div class="wrapper">
        <main id="content">
            @yield('content')
        </main>
        </div>
        {{-- Scripts --}}
       
        @yield('foot-script')
</body>
</div>

</html>
<!--
Developed by d2y - develop2you.com
Support support@develop2you.com | +351 220 502 129
version 1.0 - 2019
-->