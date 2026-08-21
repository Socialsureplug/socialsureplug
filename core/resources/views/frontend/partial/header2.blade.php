<header class="header">
    <div class="container">
        <div class="header-row">
            <div class="header-logo">
                <a href="{{ route('home') }}">
                    @if(optional($settings)->logo_white && optional($settings)->logo_dark)
                        <img src="{{ asset($settings->logo_dark) }}"
                             alt="{{ optional($settings)->website_name ?? 'Logo' }}"
                             class="logo logo-light">
                        <img src="{{ asset($settings->logo_white) }}"
                             alt="{{ optional($settings)->website_name ?? 'Logo' }}"
                             class="logo logo-dark">
                    @elseif(optional($settings)->logo_white)
                        <img src="{{ asset($settings->logo_white) }}"
                             alt="{{ optional($settings)->website_name ?? 'Logo' }}"
                             class="logo">
                    @elseif(optional($settings)->logo_dark)
                        <img src="{{ asset($settings->logo_dark) }}"
                             alt="{{ optional($settings)->website_name ?? 'Logo' }}"
                             class="logo">
                    @else
                        <span class="logo-text">{{ optional($settings)->website_name ?? config('app.name') }}</span>
                    @endif
                </a>
            </div>

            <ul class="header-link">
                <li><a href="#features">Features</a></li>
                <li><a href="#reviews">Reviews</a></li>
                <li><a href="{{ route('frontend.support') }}">Support</a></li>
            </ul>

            <div class="header-actions">
                <div class="theme-toggle">
                    <div class="toggle-switch">
                        <input type="checkbox" id="toggle" checked>
                        <label for="toggle" class="toggle-label">
                            <span class="toggle-text">On</span>
                        </label>
                    </div>
                </div>
                <a href="{{ route('user.login.page') }}" class="btn btn-secondary">Login</a>
                <a href="{{ route('user.register') }}" class="btn btn-primary">Sign Up</a>
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <i class="ti ti-menu-2"></i>
                </button>
            </div>
        </div>

        <div class="mobile-menu">
            <ul class="mobile-menu-list">
                <li class="mobile-menu-item">
                    <a href="#features">Features</a>
                </li>
                <li class="mobile-menu-item">
                    <a href="#reviews">Reviews</a>
                </li>
                <li class="mobile-menu-item">
                    <a href="{{ route('frontend.support') }}">Support</a>
                </li>
                <li class="mobile-menu-item">
                    <a href="{{ route('user.login.page') }}">Log In</a>
                </li>
                <li class="mobile-menu-item">
                    <a href="{{ route('user.register') }}">Sign Up</a>
                </li>
            </ul>
        </div>
    </div>
</header>