@extends('frontend.layout.dash')

@section('content')
    <section class="welcome-banner" aria-label="{{ __('Welcome') }}">
        <div class="welcome-banner-inner">
            <div class="welcome-banner-text">
                <h1 class="welcome-banner-title">
                    <span class="welcome-banner-greet">{{ __('Welcome,') }}</span>
                    <span class="welcome-banner-name">{{ $displayName }}</span>
                    <span class="welcome-banner-wave" aria-hidden="true">👋</span>
                </h1>
                <p class="welcome-banner-sub">{{ __('Everything you need to grow your online presence is here.') }}</p>
            </div>
            <div class="welcome-banner-actions">
                @php
                    $quickOrderHref = route('user.payment.index');
                    $quickOrderLabel = __('Quick order');
                    if (optional($settings)->boost_social) {
                        $quickOrderHref = route('user.smm-order.index');
                    } elseif (optional($settings)->buy_number) {
                        $quickOrderHref = route('user.number.index');
                    }
                @endphp
                <a href="{{ $quickOrderHref }}" class="welcome-banner-btn welcome-banner-btn-primary">
                    <i class="ti ti-plus" aria-hidden="true"></i>
                    {{ $quickOrderLabel }}
                </a>
                <a href="{{ route('user.payment.index') }}" class="welcome-banner-btn welcome-banner-btn-secondary">
                    <i class="ti ti-wallet" aria-hidden="true"></i>
                    {{ __('Add fund') }}
                </a>
            </div>
        </div>
    </section>

    <div class="stats-grid">
        <div class="stat-card primary">
            <span class="stat-pill stat-pill--light">+12.5%</span>
            <div class="stat-card-header">
                <span class="stat-icon">
                    <i class="ti ti-wallet"></i>
                </span>
            </div>
            <div class="stat-card-body">
                <div class="value">{{ $currency }}{{ number_format($walletBalance, 2) }}</div>
                <div class="label">Wallet Balance</div>
            </div>
        </div>

        @if(optional($settings)->buy_number)
        <div class="stat-card">
            <span class="stat-pill">+8.2%</span>
            <div class="stat-card-header">
                <span class="stat-icon">
                    <i class="ti ti-device-mobile"></i>
                </span>
            </div>
            <div class="stat-card-body">
                <div class="value">{{ $numbersPurchased }}</div>
                <div class="label">Numbers Purchased</div>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-pill">+23.1%</span>
            <div class="stat-card-header">
                <span class="stat-icon">
                    <i class="ti ti-message-circle"></i>
                </span>
            </div>
            <div class="stat-card-body">
                <div class="value">{{ $smsServerStatus }}</div>
                <div class="label">SMS Server Status</div>
            </div>
        </div>
        @endif

        <div class="stat-card">
            <span class="stat-pill">+5.4%</span>
            <div class="stat-card-header">
                <span class="stat-icon">
                    <i class="ti ti-wave-sine"></i>
                </span>
            </div>
            <div class="stat-card-body">
                <div class="value">{{ $currency }}{{ number_format($totalFunded, 2) }}</div>
                <div class="label">Total Amount Funded</div>
            </div>
        </div>

        @if(optional($settings)->boost_social)
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-icon">
                    <i class="ti ti-brand-instagram"></i>
                </span>
            </div>
            <div class="stat-card-body">
                <div class="value">{{ number_format($smmOrdersCount) }}</div>
                <div class="label">SMM Orders</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-icon">
                    <i class="ti ti-shopping-cart"></i>
                </span>
            </div>
            <div class="stat-card-body">
                <div class="value">{{ $currency }}{{ number_format($smmTotalSpent, 2) }}</div>
                <div class="label">SMM Total Spent</div>
            </div>
        </div>
        @endif
    </div>

    <div class="card mp-20">
        <h2 class="title">Quick Actions</h2>

        <div class="actions-grid">
            @if(optional($settings)->buy_number)
            <a href="{{ route('user.number.index') }}" class="action-card">
                <span class="action-icon">
                    <i class="ti ti-phone-call"></i>
                </span>
                <div class="action-title">Buy Numbers</div>
                <div class="action-desc">Get virtual numbers</div>
            </a>
            @endif
            
            @if(optional($settings)->social_logs)
            <a href="{{ route('user.selling.index') }}" class="action-card" data-remove-start style="display:none!important">
                <span class="action-icon" style="display:none">
                    <i class="ti ti-shopping-bag"></i>
                </span>
                <div class="action-title">Buy Account</div>
                <div class="action-desc">Buy account credentials</div>
            </a>
            <a href="{{ route('user.selling.index') }}" class="action-card">
                <span class="action-icon">
                    <i class="ti ti-shopping-cart"></i>
                </span>
                <div class="action-title">Buy Accounts</div>
                <div class="action-desc">Browse and purchase accounts</div>
            </a>
            @endif
            
            @if(optional($settings)->boost_social)
            <a href="{{ route('user.smm-order.index') }}" class="action-card">
                <span class="action-icon">
                    <i class="ti ti-brand-instagram"></i>
                </span>
                <div class="action-title">Buy Followers</div>
                <div class="action-desc">Followers, likes & more</div>
            </a>
            @endif
            <a href="{{ route('user.payment.index') }}" class="action-card">
                <span class="action-icon">
                    <i class="ti ti-wallet"></i>
                </span>
                <div class="action-title">Fund Wallet</div>
                <div class="action-desc">Add funds instantly</div>
            </a>
            @if(optional($settings)->buy_number)
            <a href="{{ route('user.number.orders') }}" class="action-card">
                <span class="action-icon">
                    <i class="ti ti-chart-line"></i>
                </span>
                <div class="action-title">Number History</div>
                <div class="action-desc">View your codes</div>
            </a>
            @endif
            @if(optional($settings)->boost_social)
            <a href="{{ route('user.log.smm-orders') }}" class="action-card">
                <span class="action-icon">
                    <i class="ti ti-list"></i>
                </span>
                <div class="action-title">Followers History</div>
                <div class="action-desc">View SMM orders</div>
            </a>
            @endif
            <a href="https://wa.me/2349163323871" class="action-card">
                <span class="action-icon">
                    <i class="ti ti-headset"></i>
                </span>
                <div class="action-title">Support</div>
                <div class="action-desc">24/7 assistance</div>
            </a>
            <a href="https://t.me/+0ePevXngW0U2Nzk0" class="action-card">
                <span class="action-icon">
                    <i class="ti ti-brand-telegram"></i>
                </span>
                <div class="action-title">Join Telegram</div>
                <div class="action-desc">Join our Telegram channel for updates</div>
            </a>
        </div>
    </div>
     <!--
    @if(optional($settings)->buy_number)
    <div class="card mp-20">
        <h2 class="title">Top Services</h2>
        <div class="service-list">
            @forelse($topServices as $service)
                <div class="service-card">
                    <div class="service-left">
                        <span class="service-icon">
                            <i class="{{ $service['icon'] }}"></i>
                        </span>
                        <div class="service-info">
                            <div class="service-name">{{ $service['name'] }}</div>
                            <div class="service-detail">{{ $service['detail'] }}</div>
                        </div>
                    </div>
                    <a href="{{ route('user.number.index') }}" class="btn-buy">Buy</a>
                </div>
            @empty
                <div class="service-card">
                    <div class="service-left">
                        <div class="service-info">
                            <div class="service-name">No top services configured</div>
                            <div class="service-detail">Ask admin to add top services</div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
    @endif -->
    

@endsection
