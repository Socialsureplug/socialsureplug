<div class="sidebar">
    <div class="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('backend.dashboard') }}">
                @if (optional($settings)->logo_white)
                    <img src="{{ asset($settings->logo_white) }}" alt="" class="sidebar-logo"
                        style="max-height: 40px;">
                @else
                    {{ optional($settings)->website_name ?? config('app.name') }}
                @endif
            </a>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('backend.dashboard') }}" class="sidebar-link {{ menuActive('backend.dashboard') }}">
                    <i class="las la-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-menu-caption">Manage Account</li>

            <li class="sidebar-item">
                <a href="" class="sidebar-link has-dropdown">
                    <i class="las la-user-circle"></i>
                    <span>Manage Users</span>
                </a>
                <ul class="sidebar-dropdown {{ menuActivePrefix('backend.users') }}">
                    <li><a class="sub-sidebar-link {{ menuActive('backend.users.index') }}"
                            href="{{ route('backend.users.index') }}">Manage Users</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.users.status-filter', 'active') }}"
                            href="{{ route('backend.users.status-filter', 'active') }}">Active Users</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.users.status-filter', 'inactive') }}"
                            href="{{ route('backend.users.status-filter', 'inactive') }}">Inactive Users</a></li>
                </ul>
            </li>

            <li class="sidebar-item">
                <a href="{{ route('backend.tutorials.index') }}"
                    class="sidebar-link {{ menuActive('backend.tutorials.index') }}">
                    <i class="las la-play-circle"></i>
                    <span>Tutorials & Guides</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="" class="sidebar-link has-dropdown">
                    <i class="las la-credit-card"></i>
                    <span>Payment gateways</span>
                </a>
                <ul class="sidebar-dropdown {{ menuActivePrefix('backend.payment-gateway') }}">
                    <li><a class="sub-sidebar-link {{ menuActive('backend.payment-gateway.paystack') }}"
                            href="{{ route('backend.payment-gateway.paystack') }}">Paystack</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.payment-gateway.flutterwave') }}"
                            href="{{ route('backend.payment-gateway.flutterwave') }}">Flutterwave</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.payment-gateway.korapay') }}"
                            href="{{ route('backend.payment-gateway.korapay') }}">KoraPay</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.payment-gateway.bachs') }}"
                            href="{{ route('backend.payment-gateway.bachs') }}">Bachs</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.payment-gateway.bank') }}"
                            href="{{ route('backend.payment-gateway.bank') }}">Bank transfer</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-caption">Number setup</li>

            @if (optional($settings)->buy_number)
                <li class="sidebar-item">
                    <a href="" class="sidebar-link has-dropdown">
                        <i class="las la-sim-card"></i>
                        <span>Number Menu</span>
                    </a>
                    <ul class="sidebar-dropdown {{ menuActivePrefix('backend.numbers') }}">
                        <li><a class="sub-sidebar-link {{ menuActive('backend.numbers.api') }}"
                                href="{{ route('backend.numbers.api') }}">API</a></li>
                        <li><a class="sub-sidebar-link {{ menuActive('backend.numbers.settings') }}"
                                href="{{ route('backend.numbers.settings') }}">Settings</a></li>
                        <li><a class="sub-sidebar-link {{ menuActive('backend.numbers.orders') }}"
                                href="{{ route('backend.numbers.orders') }}">Orders</a></li>
                    </ul>
                </li>
            @endif

            <li class="sidebar-menu-caption">Renting setup</li>

            <li class="sidebar-item">
                <a href="" class="sidebar-link has-dropdown">
                    <i class="las la-phone-volume"></i>
                    <span>Renting Menu</span>
                </a>
                <ul class="sidebar-dropdown {{ menuActivePrefix('backend.rentings') }}">
                    <li><a class="sub-sidebar-link {{ menuActive('backend.rentings.api') }}"
                            href="{{ route('backend.rentings.api') }}">API</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.rentings.settings') }}"
                            href="{{ route('backend.rentings.settings') }}">Settings</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.rentings.orders') }}"
                            href="{{ route('backend.rentings.orders') }}">Orders</a></li>
                </ul>
            </li>

            @if (optional($settings)->boost_social)
                <li class="sidebar-menu-caption">SMM</li>
                <li class="sidebar-item">
                    <a href="" class="sidebar-link has-dropdown">
                        <i class="las la-cloud"></i>
                        <span>SMM</span>
                    </a>
                    <ul
                        class="sidebar-dropdown {{ menuActivePrefix('backend.smm-providers') ? 'active' : '' }} {{ menuActivePrefix('backend.smm-categories') ? 'active' : '' }} {{ menuActivePrefix('backend.smm-services') ? 'active' : '' }} {{ menuActivePrefix('backend.smm-orders') ? 'active' : '' }}">
                        <li><a class="sub-sidebar-link {{ menuActive('backend.smm-providers.index') }}"
                                href="{{ route('backend.smm-providers.index') }}">Providers</a></li>
                        <li><a class="sub-sidebar-link {{ menuActive('backend.smm-categories.index') }}"
                                href="{{ route('backend.smm-categories.index') }}">Categories</a></li>
                        <li><a class="sub-sidebar-link {{ menuActive('backend.smm-subcategories.index') }}"
                                href="{{ route('backend.smm-subcategories.index') }}">Subcategories</a></li>
                        <li><a class="sub-sidebar-link {{ menuActive('backend.smm-services.index') }}"
                                href="{{ route('backend.smm-services.index') }}">Services</a></li>
                        <li><a class="sub-sidebar-link {{ menuActive('backend.smm-orders.index') }}"
                                href="{{ route('backend.smm-orders.index') }}">Orders</a></li>
                    </ul>
                </li>
            @endif

            <li class="sidebar-menu-caption">Selling</li>
            <li class="sidebar-item">
                <a href="" class="sidebar-link has-dropdown">
                    <i class="las la-store"></i>
                    <span>Selling Menu</span>
                </a>
                <ul class="sidebar-dropdown {{ menuActivePrefix('backend.selling') }}">
                    <li><a class="sub-sidebar-link {{ menuActive('backend.selling.api') }}"
                            href="{{ route('backend.selling.api') }}">API Config</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.selling.categories') }}"
                            href="{{ route('backend.selling.categories') }}">Categories</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.selling.subcategories') }}"
                            href="{{ route('backend.selling.subcategories') }}">Subcategories</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive(['backend.selling.products', 'backend.selling.products.create', 'backend.selling.products.edit', 'backend.selling.products.accounts']) }}"
                            href="{{ route('backend.selling.products') }}">Products</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive(['backend.selling.orders', 'backend.selling.orders.accounts']) }}"
                            href="{{ route('backend.selling.orders') }}">Orders</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-caption">Reports</li>

            <li class="sidebar-item">
                <a href="{{ route('backend.payments.index') }}"
                    class="sidebar-link {{ menuActive('backend.payments.index') }}">
                    <i class="las la-receipt"></i>
                    <span>Payment Log</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('backend.logs.transactions') }}"
                    class="sidebar-link {{ menuActive('backend.logs.transactions') }}">
                    <i class="las la-history"></i>
                    <span>Transaction Log</span>
                </a>
            </li>
            @if (optional($settings)->boost_social)
                <li class="sidebar-item">
                    <a href="{{ route('backend.smm-orders.index') }}"
                        class="sidebar-link {{ menuActive('backend.smm-orders.index') }}">
                        <i class="las la-shopping-cart"></i>
                        <span>Order Log</span>
                    </a>
                </li>
            @endif

            <li class="sidebar-menu-caption">Settings</li>

            <li class="sidebar-item">
                <a href="" class="sidebar-link has-dropdown">
                    <i class="las la-money-bill"></i>
                    <span>Email</span>
                </a>
                <ul class="sidebar-dropdown {{ menuActivePrefix('backend.email') }}">
                    <li><a class="sub-sidebar-link {{ menuActive('backend.email.config') }}"
                            href="{{ route('backend.email.config') }}">Email Config</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.email.send') }}"
                            href="{{ route('backend.email.send') }}">Send Email</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.email.admin-template') }}"
                            href="{{ route('backend.email.admin-template') }}">Admin Template</a></li>
                    <li><a class="sub-sidebar-link {{ menuActive('backend.email.user-template') }}"
                            href="{{ route('backend.email.user-template') }}">User Template</a></li>
                </ul>
            </li>

            <li class="sidebar-item">
                <a href="{{ route('backend.settings.index') }}"
                    class="sidebar-link {{ menuActive('backend.settings.index') }}">
                    <i class="las la-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>
