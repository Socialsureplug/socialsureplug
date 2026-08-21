<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') | {{ optional($settings)->website_name ?? config('app.name') }}</title>
    @if(optional($settings)->favicon_white)
    <link rel="shortcut icon" type="image/png" href="{{ asset($settings->favicon_white) }}">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/backend/css/auth.css') }}">
    <style>
        :root {
            --primary-color: {{ $settings->website_color ?? '#4c45e0' }};
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/backend/css/izitoast.min.css') }}">
    <link rel= "stylesheet" href= "https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css" >

    @stack('style')
</head>
<body>
    @yield('content')

    @stack('script')

    <script src="{{ asset('assets/backend/js/auth.js') }}"></script>
    <script src="{{ asset('assets/backend/js/izitoast.min.js') }}"></script>

    @if (Session::has('success'))
    <script>
        "use strict";
        iziToast.success({
            message: "{{ session('success') }}",
            position: 'topRight'
        });
    </script>
    @endif
    @if (Session::has('error'))
        <script>
            "use strict";
            iziToast.error({
                message: "{{ session('error') }}",
                position: 'topRight'
            });
        </script>
    @endif
    @if (session()->has('notify'))
        @forelse (session('notify') as $msg)
            <script>
                "use strict";
                iziToast.{{ $msg[0] }}({
                    message: "{{ trans($msg[1]) }}",
                    position: "topRight"
                });
            </script>
        @empty
        @endforelse
    @endif
    @if (@$errors->any())
        <script>
            "use strict";
            @forelse ($errors->all() as $error)
                iziToast.error({
                message: '{{ __($error) }}',
                position: "topRight"
                });
            @empty
            @endforelse
        </script>
    @endif
</body>
</html>