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

    <link href="{{ asset('css/boostrap-select.min.css') }}" rel="stylesheet">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/summernote.css') }}" rel="stylesheet">
    @stack('styles')



    @yield('head-scripts')

</head>

<body>

    <div id="app">
        <div class="wrapper">
            @if( (session('ViewAs') == null || session('ViewAs') == "")
            && Auth::user()->hasAnyPermissionsOrRole(['view_leftmenu',]))
            @include('backoffice.includes.aside_left')
            @endif
            <main id="content">
                @include('backoffice.includes.navbar')
                <div class="mt-2"></div>
                @include('flash::message')
                @yield('content')
            </main>
        </div>
    </div>
    {{-- Scripts --}}
    <script>
        window.translations = @json(__('messages'))
    </script>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    <script>
        function media(x) {
            if (window.matchMedia("(max-width: 768px)").matches) {
                if ($('#sidebarCollapse').hasClass('active')) {
                    $('#img_logo').removeClass('visibile').addClass('invisible');
                } else {
                    $('#img_logo').removeClass('invisible').addClass('visible');
                }
            } else {
                if ($('#sidebarCollapse').hasClass('active')) {
                    $('#img_logo').removeClass('invisible').addClass('visibile');
                } else {
                    $('#img_logo').removeClass('visibile').addClass('invisible');
                }
            }
        }


        $(document).ready(function() {

            if (window.matchMedia("(max-width: 768px)").matches) {
                if ($('#sidebarCollapse').hasClass('active')) {
                    $('#img_logo').removeClass('visibile').addClass('invisible');
                } else {
                    $('#img_logo').removeClass('invisible').addClass('visible');
                }
            }
            var x = window.matchMedia("(max-width: 768px)")
            media(x) // Call listener function at run time
            x.addListener(media)

        });
    </script>

    @yield('foot-scripts')
</body>

</html>
<!--
Developed by d2y - develop2you.com
Support support@develop2you.com | +351 220 502 129
version 1.0 - 2019
-->
