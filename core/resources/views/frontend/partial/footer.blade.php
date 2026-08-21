<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-brand">
                <div class="footer-logo-wrapper">
                    @if(optional($settings)->logo_white && optional($settings)->logo_dark)
                        <img src="{{ asset($settings->logo_dark) }}"
                             alt="{{ optional($settings)->website_name ?? 'Logo' }}"
                             class="footer-logo logo logo-light">
                        <img src="{{ asset($settings->logo_white) }}"
                             alt="{{ optional($settings)->website_name ?? 'Logo' }}"
                             class="footer-logo logo logo-dark">
                    @elseif(optional($settings)->logo_white)
                        <img src="{{ asset($settings->logo_white) }}"
                             alt="{{ optional($settings)->website_name ?? 'Logo' }}"
                             class="footer-logo logo">
                    @elseif(optional($settings)->logo_dark)
                        <img src="{{ asset($settings->logo_dark) }}"
                             alt="{{ optional($settings)->website_name ?? 'Logo' }}"
                             class="footer-logo logo">
                    @else
                        <span class="logo-text">{{ optional($settings)->website_name ?? config('app.name') }}</span>
                    @endif
                </div>
                <p class="footer-tagline">{{ optional($settings)->website_name ?? config('app.name') }}  sell virtual numbers for OTP & eSIM verification, buy social accounts, and boost your growth — fast, secure, and private.</p>
            </div>

            <div class="footer-links">
                <div class="footer-column">
                    <h4 class="footer-column-title">Info</h4>
                    <ul class="footer-link-list">
                        <li><a href="{{ route('home') }}#about">About Us</a></li>
                        <li><a href="{{ route('home') }}#features">Features</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4 class="footer-column-title">Resources</h4>
                    <ul class="footer-link-list">
                        <li><a href="{{ route('frontend.support') }}">Help Center</a></li>
                        <li><a href="{{ route('home') }}#reviews">Reviews</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4 class="footer-column-title">Company</h4>
                    <ul class="footer-link-list">
                        <li><a href="{{ route('frontend.privacy') }}">Privacy policy</a></li>
                        <li><a href="{{ route('frontend.terms') }}">Terms conditions</a></li>
                        <li><a href="{{ route('frontend.support') }}">Support</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="footer-copyright">
                © 2026 {{ optional($settings)->website_name ?? config('app.name') }}, Ltd.</a>
            </p>
            <div class="footer-social">
                <a href="https://x.com/SocialSurePlug" class="social-icon">
                    <i class="ti ti-brand-twitter"></i>
                </a>
               
                <a href="https://www.instagram.com/socialsureplug" class="social-icon">
                    <i class="ti ti-brand-instagram"></i>
                </a>
                
                <a href="https://www.tiktok.com/@socialsureplughq" class="social-icon">
                    <i class="ti ti-brand-tiktok"></i>
                </a>
                
            </div>
        </div>
    </div>
</footer>