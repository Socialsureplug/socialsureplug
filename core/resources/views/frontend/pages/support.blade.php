@extends('frontend.layout.app')

@section('content')
<style>
    /* Support Page Styles - Socialsureplug Brand */
    :root {
        --sp-primary: #2DBDB4;
        --sp-primary-dark: #25a099;
        --sp-secondary: #F97316;
        --sp-dark: #0f172a;
        --sp-darker: #020617;
        --sp-light: #f8fafc;
        --sp-gray: #64748b;
        --sp-gray-light: #e2e8f0;
        --whatsapp: #25D366;
        --telegram: #0088cc;
    }

    /* Support Hero */
    .support-hero {
        background: linear-gradient(135deg, var(--sp-darker) 0%, var(--sp-dark) 100%);
        padding: 80px 0 60px;
        position: relative;
        overflow: hidden;
    }

    .support-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(45, 189, 180, 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    .support-hero-content {
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .support-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(45, 189, 180, 0.15);
        border: 1px solid rgba(45, 189, 180, 0.3);
        color: var(--sp-primary);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .support-hero h1 {
        font-size: 3rem;
        font-weight: 800;
        color: white;
        margin-bottom: 16px;
    }

    .support-hero p {
        font-size: 1.2rem;
        color: #94a3b8;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Contact Cards Section */
    .contact-section {
        padding: 60px 0;
        background: var(--sp-light);
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-top: -40px;
        position: relative;
        z-index: 10;
    }

    .contact-card {
        background: white;
        border-radius: 24px;
        padding: 40px 30px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .contact-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .contact-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    }

    .contact-card.whatsapp {
        border-color: var(--whatsapp);
        transform: scale(1.05);
    }

    .contact-card.whatsapp::before {
        background: var(--whatsapp);
        transform: scaleX(1);
    }

    .contact-card.telegram::before {
        background: var(--telegram);
    }

    .contact-card.email::before {
        background: var(--sp-primary);
    }

    .contact-card:hover::before {
        transform: scaleX(1);
    }

    .contact-icon-wrap {
        width: 80px;
        height: 80px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 40px;
        position: relative;
    }

    .contact-card.whatsapp .contact-icon-wrap {
        background: linear-gradient(135deg, #25D366, #128C7E);
        color: white;
        box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
        animation: pulse-whatsapp 2s infinite;
    }

    .contact-card.telegram .contact-icon-wrap {
        background: linear-gradient(135deg, #0088cc, #00a8e6);
        color: white;
        box-shadow: 0 10px 30px rgba(0, 136, 204, 0.3);
    }

    .contact-card.email .contact-icon-wrap {
        background: linear-gradient(135deg, var(--sp-primary), var(--sp-secondary));
        color: white;
        box-shadow: 0 10px 30px rgba(45, 189, 180, 0.3);
    }

    @keyframes pulse-whatsapp {
        0%, 100% { box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3); }
        50% { box-shadow: 0 10px 40px rgba(37, 211, 102, 0.5); }
    }

    .contact-card h3 {
        font-size: 1.5rem;
        color: var(--sp-dark);
        margin-bottom: 12px;
    }

    .contact-card p {
        color: var(--sp-gray);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 24px;
    }

    .btn-contact {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .btn-whatsapp {
        background: linear-gradient(135deg, #25D366, #128C7E);
        color: white;
        box-shadow: 0 4px 20px rgba(37, 211, 102, 0.3);
    }

    .btn-whatsapp:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(37, 211, 102, 0.4);
        color: white;
    }

    .btn-telegram {
        background: linear-gradient(135deg, #0088cc, #00a8e6);
        color: white;
        box-shadow: 0 4px 20px rgba(0, 136, 204, 0.3);
    }

    .btn-telegram:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 136, 204, 0.4);
        color: white;
    }

    .btn-email {
        background: linear-gradient(135deg, var(--sp-primary), var(--sp-secondary));
        color: white;
        box-shadow: 0 4px 20px rgba(45, 189, 180, 0.3);
    }

    .btn-email:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(45, 189, 180, 0.4);
        color: white;
    }

    .response-time {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 12px;
        font-size: 0.85rem;
        color: var(--sp-gray);
    }

    .response-time i {
        color: var(--sp-secondary);
    }

    .contact-card.whatsapp .response-time i {
        color: var(--whatsapp);
    }

    .contact-card.telegram .response-time i {
        color: var(--telegram);
    }

    /* Popular Badge */
    .popular-badge {
        position: absolute;
        top: 20px;
        right: -35px;
        background: var(--sp-secondary);
        color: white;
        padding: 5px 40px;
        font-size: 12px;
        font-weight: 700;
        transform: rotate(45deg);
    }

    /* FAQ Section */
    .faq-section {
        padding: 80px 0;
        background: white;
    }

    .section-header {
        text-align: center;
        max-width: 700px;
        margin: 0 auto 50px;
    }

    .section-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--sp-secondary);
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 16px;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--sp-dark);
        margin-bottom: 16px;
    }

    .section-desc {
        color: var(--sp-gray);
        font-size: 1.1rem;
    }

    .faq-wrapper {
        max-width: 800px;
        margin: 0 auto;
    }

    .faq-item {
        background: var(--sp-light);
        border-radius: 16px;
        margin-bottom: 16px;
        overflow: hidden;
        border: 1px solid var(--sp-gray-light);
        transition: all 0.3s ease;
    }

    .faq-item:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .faq-question {
        padding: 24px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 600;
        color: var(--sp-dark);
        transition: all 0.3s ease;
        user-select: none;
    }

    .faq-question:hover {
        color: var(--sp-primary);
        background: rgba(45, 189, 180, 0.02);
    }

    .faq-question i {
        transition: transform 0.3s ease;
        color: var(--sp-primary);
        font-size: 20px;
    }

    .faq-item.active .faq-question i {
        transform: rotate(180deg);
    }

    .faq-answer {
        padding: 0 30px;
        max-height: 0;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        color: var(--sp-gray);
        line-height: 1.7;
        opacity: 0;
    }

    .faq-item.active .faq-answer {
        padding: 0 30px 24px;
        max-height: 500px;
        opacity: 1;
    }

    .faq-answer a {
        color: var(--sp-primary);
        text-decoration: none;
        font-weight: 600;
    }

    .faq-answer a:hover {
        text-decoration: underline;
    }

    .faq-answer ol, .faq-answer ul {
        margin: 12px 0;
        padding-left: 20px;
    }

    .faq-answer li {
        margin-bottom: 8px;
    }

    /* Resources & Report Section */
    .info-section {
        padding: 80px 0;
        background: linear-gradient(to bottom, var(--sp-light), white);
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .info-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--sp-gray-light);
    }

    .info-card h2 {
        font-size: 1.75rem;
        color: var(--sp-dark);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-card h2 i {
        color: var(--sp-primary);
    }

    .info-card p {
        color: var(--sp-gray);
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .info-card ul {
        list-style: none;
        padding: 0;
    }

    .info-card li {
        padding: 10px 0;
        border-bottom: 1px solid var(--sp-gray-light);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-card li:last-child {
        border-bottom: none;
    }

    .info-card li i {
        color: var(--sp-primary);
    }

    .info-card a {
        color: var(--sp-primary);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .info-card a:hover {
        color: var(--sp-secondary);
    }

    /* Business Hours Box */
    .hours-box {
        background: linear-gradient(135deg, rgba(45, 189, 180, 0.1), rgba(249, 115, 22, 0.1));
        border-radius: 16px;
        padding: 24px;
        margin-top: 24px;
        border: 1px solid rgba(45, 189, 180, 0.2);
    }

    .hours-box h4 {
        color: var(--sp-dark);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hours-box p {
        margin: 0;
        font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .contact-card.whatsapp {
            transform: scale(1);
            order: -1;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .support-hero h1 {
            font-size: 2.2rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .contact-card {
            padding: 30px 20px;
        }

        .info-card {
            padding: 30px 20px;
        }

        .faq-question {
            padding: 20px;
            font-size: 0.95rem;
        }

        .faq-answer {
            padding: 0 20px;
        }

        .faq-item.active .faq-answer {
            padding: 0 20px 20px;
        }
    }
</style>

<!-- Support Hero -->
<section class="support-hero">
    <div class="container">
        <div class="support-hero-content">
            <div class="support-badge">
                <i class="ti ti-headset"></i>
                24/7 Customer Support
            </div>
            <h1>How Can We Help You?</h1>
            <p>Get instant support via WhatsApp, join our Telegram community, or send us an email. We're here to help with virtual numbers, OTP verification, and SMM services.</p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- WhatsApp - Primary Contact -->
            <div class="contact-card whatsapp">
                <div class="popular-badge">FASTEST</div>
                <div class="contact-icon-wrap">
                    <i class="ti ti-brand-whatsapp"></i>
                </div>
                <h3>WhatsApp Support</h3>
                <p>Get instant help directly on WhatsApp. Our team typically responds within minutes during business hours.</p>
                <a href="https://wa.me/2349163323871" target="_blank" class="btn-contact btn-whatsapp">
                    <i class="ti ti-brand-whatsapp"></i>
                    Chat on WhatsApp
                </a>
                <div class="response-time">
                    <i class="ti ti-clock"></i>
                    Response time: 5-15 minutes
                </div>
            </div>

            <!-- Telegram -->
            <div class="contact-card telegram">
                <div class="contact-icon-wrap">
                    <i class="ti ti-brand-telegram"></i>
                </div>
                <h3>Join Telegram</h3>
                <p>Join our Telegram channel for updates, exclusive deals, community support, and instant notifications.</p>
                <a href="https://t.me/+0ePevXngW0U2Nzk0" target="_blank" class="btn-contact btn-telegram">
                    <i class="ti ti-brand-telegram"></i>
                    Join Channel
                </a>
                <div class="response-time">
                    <i class="ti ti-users"></i>
                    5,000+ members active
                </div>
            </div>

            <!-- Email -->
            <div class="contact-card email">
                <div class="contact-icon-wrap">
                    <i class="ti ti-mail"></i>
                </div>
                <h3>Email Support</h3>
                <p>For detailed inquiries or documentation requests, send us an email and we'll respond promptly.</p>
                <a href="mailto:support@socialsureplug.com" class="btn-contact btn-email">
                    <i class="ti ti-send"></i>
                    Send Email
                </a>
                <div class="response-time">
                    <i class="ti ti-clock"></i>
                    Response time: 24-48 hours
                </div>
            </div>
        </div>

        <!-- Business Hours -->
        <div class="hours-box" style="max-width: 600px; margin: 40px auto 0;">
            <h4><i class="ti ti-clock-hour-4" style="color: var(--sp-primary);"></i> Business Hours</h4>
            <p><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM (EST)<br>
            <strong>Saturday:</strong> 10:00 AM - 4:00 PM (EST)<br>
            <strong>Sunday:</strong> Closed (Emergency WhatsApp support available)</p>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <i class="ti ti-sparkles"></i>
                FAQ
            </div>
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-desc">Quick answers to common questions about our services</p>
        </div>

        <div class="faq-wrapper">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I get started?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>After signing up:</p>
                    <ol>
                        <li>Add balance to your account (pay-as-you-go or choose a plan)</li>
                        <li><strong>Virtual numbers:</strong> Select a country and get a virtual number; use it when an app or website asks for phone verification. Receive OTPs and SMS in your dashboard.</li>
                        <li><strong>Boost Socials (SMM):</strong> Go to Boost Socials, choose a category and service (e.g. followers, likes, views), enter your link and quantity, and place your order. Same wallet for both services.</li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I order followers, likes, or views?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Go to <strong>Boost Socials</strong> in your dashboard. Choose a category (e.g. Instagram, TikTok), select a service (followers, likes, views, etc.), enter your profile or post link and quantity, then place your order. You can also use the Mass order tab to submit multiple lines (service_id|quantity|link). Payment is deducted from the same wallet you use for virtual numbers.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Which countries can I get virtual numbers for?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>We offer virtual numbers in 50+ countries. Log in to your dashboard to see the full list of available countries and get a number in the region you need.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How fast do I receive OTPs and SMS?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Messages are delivered to your dashboard within seconds. As soon as a service sends an SMS or OTP to your virtual number, it appears in your account.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I cancel my subscription?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes, you can cancel your subscription at any time. To cancel, please contact our support team via WhatsApp or email at <a href="mailto:support@website.com">support@website.com</a> and we'll help you cancel your subscription. Your access will continue until the end of your current billing period after cancellation.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Is there a free trial?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes! New users get a free trial period to explore all features. Check your dashboard for trial details or contact us on WhatsApp to learn more about our trial offers.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Resources & Report Section -->
<section class="info-section">
    <div class="container">
        <div class="info-grid">
            <!-- Resources -->
            <div class="info-card">
                <h2><i class="ti ti-book"></i> Resources</h2>
                <p>Quick links to important pages and information:</p>
                <ul>
                    <li><i class="ti ti-chevron-right"></i> <a href="{{ route('home') }}">Home & Features</a></li>
                    <li><i class="ti ti-chevron-right"></i> <a href="{{ route('frontend.terms') }}">Terms of Service</a></li>
                    <li><i class="ti ti-chevron-right"></i> <a href="{{ route('frontend.privacy') }}">Privacy Policy</a></li>
                    <li><i class="ti ti-chevron-right"></i> <a href="https://t.me/+0ePevXngW0U2Nzk0" target="_blank">Join Telegram Community</a></li>
                </ul>
            </div>

            <!-- Report Issue -->
            <div class="info-card">
                <h2><i class="ti ti-bug"></i> Report an Issue</h2>
                <p>Found a bug or experiencing technical difficulties? Please contact us via WhatsApp for fastest resolution, or email us with the following details:</p>
                <ul>
                    <li><i class="ti ti-check"></i> Detailed description of the issue</li>
                    <li><i class="ti ti-check"></i> Steps to reproduce the problem</li>
                    <li><i class="ti ti-check"></i> Screenshots (if applicable)</li>
                    <li><i class="ti ti-check"></i> Your browser and device info</li>
                </ul>
                <div style="margin-top: 20px;">
                    <a href="https://wa.me/2349163323871" target="_blank" class="btn-contact btn-whatsapp" style="width: auto; display: inline-flex;">
                        <i class="ti ti-brand-whatsapp"></i>
                        Report via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // FAQ Accordion Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const faqQuestions = document.querySelectorAll('.faq-question');
        
        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const faqItem = this.parentElement;
                const isActive = faqItem.classList.contains('active');
                
                // Close all others
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Toggle current
                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
        });
    });
</script>
@endsection