<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $pageTitle ?? $title ?? 'Admin') | {{ optional($settings)->website_name ?? config('app.name') }}</title>
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

    <link rel="stylesheet" href="{{ asset('assets/backend/css/style.css') }}?v={{ file_exists(base_path('../assets/backend/css/style.css')) ? filemtime(base_path('../assets/backend/css/style.css')) : time() }}">
    <style>
        :root {
            --primary-color: {{ $settings->website_color ?? '#4c45e0' }};

            /* Dark theme (default) */
            --fd-bg: #0f172a;
            --fd-bg-deep: #020617;
            --fd-surface: #1e293b;
            --fd-border: #334155;
            --fd-text: #ffffff;
            --fd-text-secondary: #e2e8f0;
            --fd-muted: #94a3b8;
            --fd-danger: #dc2626;
            --fd-success: #10b981;
            --fd-on-accent: #ffffff;
        }

        [data-theme="light"] {
            --fd-bg: #f1f5f9;
            --fd-bg-deep: #e2e8f0;
            --fd-surface: #ffffff;
            --fd-border: #e2e8f0;
            --fd-text: #0f172a;
            --fd-text-secondary: #334155;
            --fd-muted: #64748b;
            --fd-danger: #dc2626;
            --fd-success: #059669;
            --fd-on-accent: #ffffff;
        }

        body {
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .card, .sidebar, .form-control, table.table thead th, .fd-chart-canvas-wrap canvas {
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }
    </style>
    <script>
        // Applied before first paint so the page never flashes the wrong theme.
        (function () {
            var saved = localStorage.getItem('ssp-theme');
            var theme = saved === 'light' || saved === 'dark' ? saved : 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/backend/css/responsive.css') }}?v={{ file_exists(base_path('../assets/backend/css/responsive.css')) ? filemtime(base_path('../assets/backend/css/responsive.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('assets/backend/css/izitoast.min.css') }}?v={{ file_exists(base_path('../assets/backend/css/izitoast.min.css')) ? filemtime(base_path('../assets/backend/css/izitoast.min.css')) : time() }}">
    <link rel= "stylesheet" href= "https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css" >

    @stack('style')
</head>
<body>
    @include('backend.partial.sidebar')
    <div class="main-content">
        @include('backend.partial.header')
        @yield('content')
    </div>

    <div class="modal" id="confirm-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="confirm-modal-title">Please confirm</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <div class="modal-body">
                <p id="confirm-modal-message">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="confirm-modal-cancel">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-modal-ok">Confirm</button>
            </div>
        </div>
    </div>

    @stack('script')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/backend/js/script.js') }}?v={{ file_exists(base_path('../assets/backend/js/script.js')) ? filemtime(base_path('../assets/backend/js/script.js')) : time() }}"></script>
    <script src="{{ asset('assets/backend/js/theme-toggle.js') }}?v={{ file_exists(base_path('../assets/backend/js/theme-toggle.js')) ? filemtime(base_path('../assets/backend/js/theme-toggle.js')) : time() }}"></script>
    <script src="{{ asset('assets/backend/js/izitoast.min.js') }}?v={{ file_exists(base_path('../assets/backend/js/izitoast.min.js')) ? filemtime(base_path('../assets/backend/js/izitoast.min.js')) : time() }}"></script>

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
    @if(optional($settings)->live_chat_code)
    {!! $settings->live_chat_code !!}
    @endif
</body>
</html>