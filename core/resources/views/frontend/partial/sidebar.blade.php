<div class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('user.dashboard') }}">
            @if(optional($settings)->logo_white)
            <img src="{{ asset($settings->logo_white) }}" alt="{{ optional($settings)->website_name ?? 'Logo' }}" class="logo">
            @else
            <span class="logo-text">{{ optional($settings)->website_name ?? config('app.name') }}</span>
            @endif
        </a>
    </div>

    <div class="sidebar-wrapper">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('user.dashboard') }}" class="sidebar-link {{ menuActive('user.dashboard') }}">
                    <i class="ti ti-layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-title">Order</li>

            @if(optional($settings)->buy_number)
            <li class="sidebar-item">
                <a href="{{ route('user.number.index') }}" class="sidebar-link {{ menuActive('user.number.index') }}">
                    <i class="ti ti-server"></i>
                    <span>Buy Number</span>
                </a>
            </li>
            @endif

            @if(optional($settings)->buy_rent)
            <li class="sidebar-item">
                <a href="{{ route('user.renting.index') }}" class="sidebar-link {{ menuActive('user.renting.index') }}">
                    <i class="ti ti-phone"></i>
                    <span>Rent Number</span>
                </a>
            </li>
            @endif

            @if(optional($settings)->boost_social)
            <li class="sidebar-item">
                <a href="{{ route('user.smm-order.index') }}" class="sidebar-link {{ menuActive('user.smm-order.index') }}">
                    <i class="ti ti-chart-line"></i>
                    <span>Buy Followers</span>
                </a>
            </li>
            @endif

            @if(optional($settings)->social_logs)
            <li class="sidebar-item">
                <a href="{{ route('user.selling.index') }}" class="sidebar-link {{ menuActive(['user.selling.index', 'user.selling.product']) }}">
                    <i class="ti ti-shopping-cart"></i>
                    <span>Buy Accounts</span>
                </a>
            </li>
            @endif

            <li class="sidebar-title">Payment</li>

            <li class="sidebar-item">
                <a href="{{ route('user.payment.index') }}" class="sidebar-link {{ menuActive('user.payment.index') }}">
                    <i class="ti ti-wallet"></i>
                    <span>Fund Wallet</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('user.referral.index') }}" class="sidebar-link {{ menuActive('user.referral.index') }}">
                    <i class="ti ti-users"></i>
                    <span>Referral</span>
                </a>
            </li>

            <li class="sidebar-title">History</li>

            @if(optional($settings)->buy_number)
            <li class="sidebar-item">
                <a href="{{ route('user.number.orders') }}" class="sidebar-link {{ menuActive('user.number.orders') }}">
                    <i class="ti ti-history"></i>
                    <span>Number History</span>
                </a>
            </li>
            @endif

            @if(optional($settings)->buy_rent)
            <li class="sidebar-item">
                <a href="{{ route('user.renting.orders') }}" class="sidebar-link {{ menuActive('user.renting.orders') }}">
                    <i class="ti ti-history"></i>
                    <span>Renting History</span>
                </a>
            </li>
            @endif

            @if(optional($settings)->boost_social)
            <li class="sidebar-item">
                <a href="{{ route('user.log.smm-orders') }}" class="sidebar-link {{ menuActive('user.log.smm-orders') }}">
                    <i class="ti ti-history"></i>
                    <span>Followers History</span>
                </a>
            </li>
            @endif
            @if(optional($settings)->social_logs)
            <li class="sidebar-item">
                <a href="{{ route('user.log.selling-orders') }}" class="sidebar-link {{ menuActive(['user.log.selling-orders', 'user.log.selling-orders.accounts']) }}">
                    <i class="ti ti-history"></i>
                    <span>Account Orders</span>
                </a>
            </li>
            @endif
            <li class="sidebar-item">
                <a href="{{ route('user.log.transaction') }}" class="sidebar-link {{ menuActive('user.log.transaction') }}">
                    <i class="ti ti-history"></i>
                    <span>Transaction History</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('user.log.payments') }}" class="sidebar-link {{ menuActive('user.log.payments') }}">
                    <i class="ti ti-history"></i>
                    <span>Payment History</span>
                </a>
            </li>

            <li class="sidebar-title">API</li>

            @if(optional($settings)->buy_number)
            <li class="sidebar-item">
                <a href="{{ route('user.api.index') }}" class="sidebar-link {{ menuActive('user.api.index') }}">
                    <i class="ti ti-message"></i>
                    <span>SMS Documentation</span>
                </a>
            </li>
            @endif

            @if(optional($settings)->boost_social)
            <li class="sidebar-item">
                <a href="{{ route('user.api.smm-docs') }}" class="sidebar-link {{ menuActive('user.api.smm-docs') }}">
                    <i class="ti ti-api"></i>
                    <span>SMM Documentation</span>
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>