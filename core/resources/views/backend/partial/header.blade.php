
<header class="header">
    <div class="header-left">
        <div class="sidebar-menu-icon">
            <i class="las la-bars"></i>
        </div>

        <a href="#" class="visit-site-btn">
            <i class="las la-globe"></i>
            <span>Visit Site</span>
            <i class="las la-arrow-right"></i>
        </a>
    </div>
    <div class="header-right">

        <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" title="Switch theme" aria-label="Switch between light and dark theme">
            <i class="las la-moon theme-toggle-icon-dark"></i>
            <i class="las la-sun theme-toggle-icon-light"></i>
        </button>

        <div class="header-profile">
            <div class="header-profile-toggle header-has-dropdown">
                <span class="header-avatar">A</span>
                <div class="profile-greeting">Hi, Admin <i class="las la-angle-down"></i></div>
            </div>
            <div class="header-profile-dropdown header-dropdown">
                <a href="{{ route('backend.profile.index') }}" class="header-profile-item">
                    <i class="las la-user"></i> Profile
                </a>
                <form action="{{ route('backend.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="header-profile-item logout-btn">
                        <i class="las la-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
