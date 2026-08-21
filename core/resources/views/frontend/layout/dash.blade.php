<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ optional($settings)->website_name ?? config('app.name') }}</title>
    @if(optional($settings)->favicon_white)
    <link rel="shortcut icon" type="image/png" href="{{ asset($settings->favicon_white) }}">
    @endif
    @if(optional($settings)->seo_description)
    @php $seoDesc = Str::limit(strip_tags($settings->seo_description), 160); @endphp
    <meta name="description" content="{{ $seoDesc }}">
    <meta property="og:title" content="{{ optional($settings)->website_name ?? config('app.name') }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ optional($settings)->website_name ?? config('app.name') }}">
    @if(optional($settings)->favicon_white)
    <meta property="og:image" content="{{ asset($settings->favicon_white) }}">
    @endif
    @endif

    <link rel="stylesheet" href="{{ asset('assets/frontend/css/dash.css') }}?v={{ file_exists(base_path('../assets/frontend/css/dash.css')) ? filemtime(base_path('../assets/frontend/css/dash.css')) : time() }}">
    <style>
        :root {
            --primary-color: {{ optional($settings)->website_color ?? '#ff8432' }};
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/izitoast.min.css') }}?v={{ file_exists(base_path('../assets/frontend/css/izitoast.min.css')) ? filemtime(base_path('../assets/frontend/css/izitoast.min.css')) : time() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
    @stack('style')
    @if(optional($settings)->header_code)
    {!! $settings->header_code !!}
    @endif
</head>
<body>
    @include('frontend.partial.sidebar')
    <div class="main-content">
        @include('frontend.partial.header')
        <div class="container">
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('assets/frontend/js/dash.js') }}?v={{ file_exists(base_path('../assets/frontend/js/dash.js')) ? filemtime(base_path('../assets/frontend/js/dash.js')) : time() }}"></script>
    <script src="{{ asset('assets/frontend/js/izitoast.min.js') }}?v={{ file_exists(base_path('../assets/frontend/js/izitoast.min.js')) ? filemtime(base_path('../assets/frontend/js/izitoast.min.js')) : time() }}"></script>

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
    @stack('script')
    @if(optional($settings)->live_chat_code)
    {!! $settings->live_chat_code !!}
    @endif
</body>
</html>