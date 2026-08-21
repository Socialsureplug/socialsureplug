@extends('frontend.layout.app')

@section('content')
<div class="container content-page">
    <div class="legal-page">
        <h1 class="page-title">Terms of Service</h1>
        <p class="page-date">Last updated: {{ date('F d, Y') }}</p>

        <div class="page-body">

            <section>
                <h2>1. Acceptance of Terms</h2>
                <p>
                    By accessing or using {{ optional($settings)->website_name ?? config('app.name') }} ("Service"), 
                    you confirm that you have read, understood, and agreed to these Terms of Service. 
                    If you do not agree, you must immediately discontinue use of the Service.
                </p>
            </section>

            <section>
                <h2>2. Description of Service</h2>
                <p>
                    The Service provides virtual phone numbers for SMS and OTP verification, as well as 
                    social media marketing ("SMM") services including followers, likes, views, and related engagement tools.
                </p>
                <p>
                    We do not guarantee that all numbers will work with every platform, and we are not affiliated with 
                    any third-party platforms such as Instagram, TikTok, or YouTube.
                </p>
            </section>

            <section>
                <h2>3. Eligibility</h2>
                <p>
                    You must be at least 18 years old to use this Service. By using the Service, you confirm that 
                    you have the legal capacity to enter into this agreement.
                </p>
            </section>

            <section>
                <h2>4. User Accounts</h2>
                <ul>
                    <li>You must provide accurate and complete information</li>
                    <li>You are responsible for all activities under your account</li>
                    <li>You must keep your login credentials secure</li>
                    <li>We are not liable for unauthorized access caused by your negligence</li>
                </ul>
            </section>

            <section>
                <h2>5. Payments & Billing</h2>
                <ul>
                    <li>All payments must be made in advance</li>
                    <li>All sales are final unless explicitly stated otherwise</li>
                    <li>No refunds will be issued for completed or partially delivered services</li>
                    <li>Chargebacks or payment disputes may result in immediate account suspension</li>
                </ul>
            </section>

            <section>
                <h2>6. Acceptable Use</h2>
                <p>You agree NOT to use the Service for:</p>
                <ul>
                    <li>Fraud, scams, or illegal account verification</li>
                    <li>Identity theft or impersonation</li>
                    <li>Creating accounts in violation of platform policies</li>
                    <li>Spamming, phishing, or abusive activities</li>
                </ul>
                <p>
                    Any violation may result in immediate suspension or permanent termination without refund.
                </p>
            </section>

            <section>
                <h2>7. OTP & Virtual Number Disclaimer</h2>
                <ul>
                    <li>We do not guarantee OTP delivery for all services or regions</li>
                    <li>Numbers are temporary and may be reused or recycled</li>
                    <li>We are not responsible for failed verifications due to third-party restrictions</li>
                </ul>
            </section>

            <section>
                <h2>8. SMM Services Disclaimer</h2>
                <ul>
                    <li>We do not guarantee permanent followers, likes, or engagement</li>
                    <li>Engagement may drop over time</li>
                    <li>We are not responsible for account bans, restrictions, or penalties</li>
                </ul>
            </section>

            <section>
                <h2>9. Service Availability</h2>
                <p>
                    We reserve the right to modify, suspend, or discontinue any part of the Service at any time 
                    without prior notice.
                </p>
            </section>

            <section>
                <h2>10. Intellectual Property</h2>
                <p>
                    All content, trademarks, and technology associated with the Service remain the property of 
                    {{ optional($settings)->website_name ?? config('app.name') }}.
                </p>
            </section>

            <section>
                <h2>11. Limitation of Liability</h2>
                <p>
                    To the fullest extent permitted by law, we shall not be liable for any direct, indirect, 
                    incidental, or consequential damages, including loss of data, profits, or accounts.
                </p>
            </section>

            <section>
                <h2>12. Indemnification</h2>
                <p>
                    You agree to indemnify and hold harmless {{ optional($settings)->website_name ?? config('app.name') }} 
                    from any claims, damages, or legal actions resulting from your use or misuse of the Service.
                </p>
            </section>

            <section>
                <h2>13. Termination</h2>
                <p>
                    We may suspend or terminate your account at any time, without notice, if you violate these Terms 
                    or engage in harmful activities.
                </p>
            </section>

            <section>
                <h2>14. Changes to Terms</h2>
                <p>
                    We reserve the right to update these Terms at any time. Continued use of the Service constitutes 
                    acceptance of the updated Terms.
                </p>
            </section>

            <section>
                <h2>15. Governing Law</h2>
                <p>
                    These Terms shall be governed and interpreted in accordance with the laws of your operating jurisdiction.
                </p>
            </section>

            <section>
                <h2>16. Contact</h2>
                <p>If you have any questions, contact us at:</p>
                <p class="page-contact-detail">
                    <strong>Email:</strong> support@socialsureplug.com
                </p>
            </section>

        </div>
    </div>
</div>
@endsection