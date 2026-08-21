@extends('frontend.layout.app')

@section('content')
<div class="container content-page">
    <div class="legal-page">
        <h1 class="page-title">Privacy Policy</h1>
        <p class="page-date">Last updated: {{ date('F d, Y') }}</p>

        <div class="page-body">

            <section>
                <h2>1. Introduction</h2>
                <p>
                    {{ optional($settings)->website_name ?? config('app.name') }} ("we", "our", "us") values your privacy 
                    and is committed to protecting your personal information. This Privacy Policy explains how we collect, 
                    use, and safeguard your data when you use our Service.
                </p>
            </section>

            <section>
                <h2>2. Information We Collect</h2>

                <h3>2.1 Information You Provide</h3>
                <ul>
                    <li>Name, email address, and account credentials</li>
                    <li>Billing and payment details (processed via third-party providers)</li>
                    <li>Support messages and communication with us</li>
                </ul>

                <h3>2.2 Automatically Collected Data</h3>
                <ul>
                    <li>IP address, browser type, and device information</li>
                    <li>Usage activity (OTP requests, virtual numbers, SMM orders)</li>
                    <li>Log data (timestamps, actions, login attempts)</li>
                    <li>Cookies and tracking technologies</li>
                </ul>

                <h3>2.3 Sensitive Service Data</h3>
                <p>
                    We may process SMS/OTP-related data strictly for the purpose of delivering the Service. 
                    We do not guarantee confidentiality of OTP messages due to the nature of shared or temporary numbers.
                </p>
            </section>

            <section>
                <h2>3. How We Use Your Information</h2>
                <ul>
                    <li>Provide and maintain the Service</li>
                    <li>Process transactions and payments</li>
                    <li>Deliver OTP messages and SMM services</li>
                    <li>Detect fraud, abuse, or suspicious activity</li>
                    <li>Improve system performance and user experience</li>
                    <li>Comply with legal obligations</li>
                </ul>
            </section>

            <section>
                <h2>4. Data Sharing and Disclosure</h2>
                <p>We do not sell your personal data. However, we may share data in the following cases:</p>
                <ul>
                    <li><strong>Service Providers:</strong> Payment processors, SMS gateways, and SMM infrastructure providers</li>
                    <li><strong>Legal Compliance:</strong> If required by law or legal process</li>
                    <li><strong>Fraud Prevention:</strong> To detect or prevent illegal activity or abuse</li>
                    <li><strong>Business Transfers:</strong> In case of merger, acquisition, or asset sale</li>
                </ul>
            </section>

            <section>
                <h2>5. Data Retention</h2>
                <p>
                    We retain your data only as long as necessary to provide the Service, comply with legal obligations, 
                    and resolve disputes. OTP and SMS data may be stored temporarily for operational and security purposes.
                </p>
            </section>

            <section>
                <h2>6. Data Security</h2>
                <p>
                    We implement reasonable technical and organizational safeguards to protect your data. 
                    However, no system is completely secure, and we cannot guarantee absolute security.
                </p>
            </section>

            <section>
                <h2>7. Your Rights</h2>
                <p>You may have the following rights depending on your location:</p>
                <ul>
                    <li>Access your personal data</li>
                    <li>Request correction of inaccurate data</li>
                    <li>Request deletion of your data</li>
                    <li>Object to or restrict processing</li>
                </ul>
                <p class="page-contact-detail">
                    To exercise these rights, contact: support@website.com
                </p>
            </section>

            <section>
                <h2>8. Cookies and Tracking</h2>
                <p>
                    We use cookies and similar technologies to improve user experience, analyze usage, 
                    and enhance security. You can disable cookies in your browser settings.
                </p>
            </section>

            <section>
                <h2>9. Third-Party Services</h2>
                <p>
                    Our Service relies on third-party providers (payment gateways, SMS providers, SMM APIs). 
                    We are not responsible for their privacy practices or data handling.
                </p>
            </section>

            <section>
                <h2>10. Service Risk Disclosure</h2>
                <ul>
                    <li>Virtual numbers may be shared or reused</li>
                    <li>OTP confidentiality cannot be guaranteed</li>
                    <li>SMM services may involve third-party systems outside our control</li>
                </ul>
            </section>

            <section>
                <h2>11. Children's Privacy</h2>
                <p>
                    Our Service is not intended for individuals under 18. We do not knowingly collect data from minors.
                </p>
            </section>

            <section>
                <h2>12. International Data Transfers</h2>
                <p>
                    Your data may be processed in different countries where data protection laws may differ.
                </p>
            </section>

            <section>
                <h2>13. Changes to This Policy</h2>
                <p>
                    We may update this Privacy Policy at any time. Continued use of the Service indicates acceptance 
                    of the updated policy.
                </p>
            </section>

            <section>
                <h2>14. Contact Us</h2>
                <p>If you have questions, contact us:</p>
                <p class="page-contact-detail">
                    <strong>Email:</strong> support@socialsureplug.com<br>
                    <strong>Website:</strong> <a href="{{ route('home') }}">{{ config('app.url') }}</a>
                </p>
            </section>

        </div>
    </div>
</div>
@endsection