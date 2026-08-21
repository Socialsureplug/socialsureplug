<header class="header">
    <div class="header-row">

        <div class="header-left">
            <div class="menu-icon">
                <i class="ti ti-menu-2"></i>
            </div>
            <div class="header-logo">
                <a href="{{ route('user.dashboard') }}">
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
        </div>

        <div class="header-right">
            <div class="header-balance">Balance: {{ optional($settings)->website_currency ?? 'NGN' }}{{ number_format(auth()->user()->balance ?? 0, 2) }}</div>

            <div class="theme-toggle">
                <div class="toggle-switch">
                    <input type="checkbox" id="toggle" checked>
                    <label for="toggle" class="toggle-label">
                        <span class="toggle-text">On</span>
                    </label>
                </div>
            </div>

            <div class="header-profile">
                <div class="header-profile-top">
                    <div class="header-profile-image">
                        <img src="{{ auth()->user()->image ? asset(auth()->user()->image) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->fname . ' ' . auth()->user()->lname) . '&background=random' }}" alt="Profile">
                    </div>
                </div>
                <div class="header-dropdown">
                    <ul class="header-profile-menu">
                        <li class="header-profile-item">
                            <a href="{{ route('user.profile') }}" class="header-profile-link">
                                <i class="ti ti-user"></i>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li class="header-profile-item">
                            <form action="{{ route('user.logout') }}" method="POST" class="logout-form">
                                @csrf
                                <button type="submit" class="header-profile-link logout-btn">
                                    <i class="ti ti-logout"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>