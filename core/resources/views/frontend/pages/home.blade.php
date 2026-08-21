@extends('frontend.layout.app')

@section('content')
<style>
    /* Socialsureplug Brand Variables */
    :root {
        --sp-primary: #2DBDB4;
        --sp-primary-dark: #25a099;
        --sp-secondary: #F97316;
        --sp-secondary-light: #fb923c;
        --sp-dark: #0f172a;
        --sp-darker: #020617;
        --sp-light: #f8fafc;
        --sp-gray: #64748b;
        --sp-gray-light: #e2e8f0;
        --telegram: #0088cc;
    }

    /* Telegram Popup Modal */
    .telegram-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .telegram-modal.show {
        display: flex;
        opacity: 1;
    }

    .telegram-modal-content {
        background: white;
        border-radius: 24px;
        padding: 40px;
        max-width: 500px;
        width: 100%;
        text-align: center;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        transform: scale(0.9);
        transition: transform 0.3s ease;
        border: 2px solid var(--telegram);
    }

    .telegram-modal.show .telegram-modal-content {
        transform: scale(1);
    }

    .telegram-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--sp-gray-light);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--sp-gray);
        transition: all 0.3s ease;
        font-size: 20px;
    }

    .telegram-close:hover {
        background: var(--sp-secondary);
        color: white;
        transform: rotate(90deg);
    }

    .telegram-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #0088cc, #00a8e6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        animation: pulse 2s infinite;
    }

    .telegram-icon i {
        font-size: 40px;
        color: white;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 136, 204, 0.7); }
        50% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(0, 136, 204, 0); }
    }

    .telegram-modal h3 {
        font-size: 1.75rem;
        color: var(--sp-dark);
        margin-bottom: 12px;
        font-weight: 700;
    }

    .telegram-modal p {
        color: var(--sp-gray);
        margin-bottom: 24px;
        line-height: 1.6;
    }

    .btn-telegram {
        background: linear-gradient(135deg, #0088cc, #00a8e6);
        color: white;
        padding: 16px 32px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 136, 204, 0.3);
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .btn-telegram:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 136, 204, 0.4);
        color: white;
    }

    .telegram-note {
        margin-top: 16px;
        font-size: 0.875rem;
        color: var(--sp-gray);
    }

    /* Mobile Responsive for Popup */
    @media (max-width: 480px) {
        .telegram-modal-content {
            padding: 30px 20px;
            margin: 10px;
        }

        .telegram-modal h3 {
            font-size: 1.5rem;
        }

        .telegram-icon {
            width: 60px;
            height: 60px;
        }

        .telegram-icon i {
            font-size: 30px;
        }

        .btn-telegram {
            width: 100%;
            justify-content: center;
        }
    }

    /* Hero Section - Dark Theme like Bulkfollows */
    .sp-hero {
        background: linear-gradient(135deg, var(--sp-darker) 0%, var(--sp-dark) 50%, #1e293b 100%);
        position: relative;
        overflow: hidden;
        padding: 120px 0 80px;
    }

    .sp-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(45, 189, 180, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 1;
    }

    .sp-hero::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(249, 115, 22, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 1;
    }

    .sp-hero .container {
        position: relative;
        z-index: 2;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .hero-badge {
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
        margin-bottom: 24px;
        animation: fadeInDown 0.8s ease;
    }

    .hero-badge i {
        font-size: 16px;
    }

    .sp-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        color: white;
        line-height: 1.1;
        margin-bottom: 24px;
        animation: fadeInUp 0.8s ease 0.1s both;
    }

    .sp-hero h1 span {
        background: linear-gradient(135deg, var(--sp-primary) 0%, var(--sp-secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: #94a3b8;
        line-height: 1.7;
        margin-bottom: 32px;
        animation: fadeInUp 0.8s ease 0.2s both;
    }

    .hero-actions {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        animation: fadeInUp 0.8s ease 0.3s both;
    }

    .btn-sp-primary {
        background: linear-gradient(135deg, var(--sp-primary) 0%, var(--sp-primary-dark) 100%);
        color: white;
        padding: 16px 32px;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(45, 189, 180, 0.3);
        text-decoration: none;
    }

    .btn-sp-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(45, 189, 180, 0.4);
        color: white;
    }

    .btn-sp-secondary {
        background: linear-gradient(135deg, #0088cc, #00a8e6);
        color: white;
        padding: 16px 32px;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 136, 204, 0.3);
        text-decoration: none;
    }

    .btn-sp-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 136, 204, 0.4);
        color: white;
    }

    .hero-visual {
        position: relative;
        animation: fadeIn 1s ease 0.4s both;
    }

    .hero-image-container {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 20px;
        backdrop-filter: blur(10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        overflow: hidden;
    }

    .hero-image-container img {
        width: 100%;
        height: auto;
        border-radius: 16px;
        display: block;
    }

    .floating-card {
        position: absolute;
        background: white;
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        gap: 12px;
        animation: float 3s ease-in-out infinite;
    }

    .floating-card.card-1 {
        top: 20px;
        right: -20px;
        animation-delay: 0s;
    }

    .floating-card.card-2 {
        bottom: 40px;
        left: -30px;
        animation-delay: 1.5s;
    }

    .floating-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--sp-primary), var(--sp-secondary));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    /* Stats Bar - Bulkfollows Style */
    .sp-stats-bar {
        background: white;
        padding: 40px 0;
        border-bottom: 1px solid var(--sp-gray-light);
        position: relative;
        z-index: 10;
    }

    .stats-flex {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }

    .stat-box {
        text-align: center;
        padding: 20px;
        border-right: 1px solid var(--sp-gray-light);
    }

    .stat-box:last-child {
        border-right: none;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--sp-primary), var(--sp-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-label {
        color: var(--sp-gray);
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* Comparison Section - Bulkfollows VS Style */
    .sp-comparison {
        padding: 100px 0;
        background: var(--sp-light);
    }

    .section-header {
        text-align: center;
        max-width: 700px;
        margin: 0 auto 60px;
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
        line-height: 1.2;
    }

    .section-desc {
        color: var(--sp-gray);
        font-size: 1.1rem;
        line-height: 1.6;
    }

    .vs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        max-width: 1000px;
        margin: 0 auto;
    }

    .vs-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .vs-card.us {
        border-color: var(--sp-primary);
        position: relative;
        overflow: hidden;
    }

    .vs-card.us::before {
        content: 'RECOMMENDED';
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

    .vs-card.others {
        opacity: 0.7;
    }

    .vs-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .vs-header i {
        font-size: 2rem;
    }

    .vs-card.us .vs-header {
        color: var(--sp-primary);
    }

    .vs-card.others .vs-header {
        color: var(--sp-gray);
    }

    .vs-list {
        list-style: none;
        padding: 0;
    }

    .vs-list li {
        padding: 12px 0;
        border-bottom: 1px solid var(--sp-gray-light);
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #334155;
    }

    .vs-list li:last-child {
        border-bottom: none;
    }

    .vs-list i {
        color: var(--sp-primary);
        flex-shrink: 0;
        margin-top: 3px;
    }

    .vs-card.others .vs-list i {
        color: #cbd5e1;
    }

    /* Services Grid - Modern Cards */
    .sp-services {
        padding: 100px 0;
        background: white;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
    }

    .service-card {
        background: white;
        border: 1px solid var(--sp-gray-light);
        border-radius: 20px;
        padding: 40px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .service-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--sp-primary), var(--sp-secondary));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border-color: transparent;
    }

    .service-card:hover::before {
        transform: scaleX(1);
    }

    .service-icon-wrap {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, rgba(45, 189, 180, 0.1), rgba(249, 115, 22, 0.1));
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: var(--sp-primary);
        margin-bottom: 24px;
        transition: all 0.3s ease;
    }

    .service-card:hover .service-icon-wrap {
        background: linear-gradient(135deg, var(--sp-primary), var(--sp-secondary));
        color: white;
        transform: rotate(-5deg) scale(1.1);
    }

    .service-card h3 {
        font-size: 1.5rem;
        margin-bottom: 12px;
        color: var(--sp-dark);
    }

    .service-card p {
        color: var(--sp-gray);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .service-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--sp-primary);
        font-weight: 600;
        text-decoration: none;
        transition: gap 0.3s ease;
    }

    .service-link:hover {
        gap: 10px;
        color: var(--sp-primary-dark);
    }

    /* Process Steps - Horizontal */
    .sp-process {
        padding: 100px 0;
        background: linear-gradient(to bottom, var(--sp-light), white);
    }

    .process-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
        position: relative;
    }

    .process-grid::before {
        content: '';
        position: absolute;
        top: 50px;
        left: 20%;
        right: 20%;
        height: 2px;
        background: linear-gradient(90deg, var(--sp-primary), var(--sp-secondary));
        z-index: 0;
    }

    .process-card {
        background: white;
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        position: relative;
        z-index: 1;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--sp-gray-light);
    }

    .step-number {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--sp-primary), var(--sp-secondary));
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0 auto 20px;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(45, 189, 180, 0.3);
    }

    .process-card h3 {
        font-size: 1.3rem;
        margin-bottom: 12px;
        color: var(--sp-dark);
    }

    .process-card p {
        color: var(--sp-gray);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* Features Detailed - Alternating Layout */
    .sp-detailed {
        padding: 100px 0;
        background: var(--sp-light);
    }

    .detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        align-items: center;
        margin-bottom: 100px;
    }

    .detail-row:last-child {
        margin-bottom: 0;
    }

    .detail-row.reverse {
        direction: rtl;
    }

    .detail-row.reverse > * {
        direction: ltr;
    }

    .detail-content h3 {
        font-size: 2rem;
        color: var(--sp-dark);
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .detail-content p {
        color: var(--sp-gray);
        font-size: 1.1rem;
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .feature-list {
        list-style: none;
        padding: 0;
    }

    .feature-list li {
        padding: 10px 0;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #334155;
        font-weight: 500;
    }

    .feature-list i {
        color: var(--sp-primary);
        background: rgba(45, 189, 180, 0.1);
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .detail-image {
        background: white;
        border-radius: 24px;
        padding: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .detail-image img {
        width: 100%;
        border-radius: 16px;
        height: auto;
        display: block;
    }

    /* Reviews - Bulkfollows Style Cards */
    .sp-reviews {
        padding: 100px 0;
        background: white;
    }

    .reviews-scroll {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
    }

    .review-item {
        background: white;
        border: 1px solid var(--sp-gray-light);
        border-radius: 20px;
        padding: 30px;
        transition: all 0.3s ease;
    }

    .review-item:hover {
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        transform: translateY(-5px);
        border-color: transparent;
    }

    .review-stars {
        color: #fbbf24;
        margin-bottom: 16px;
        font-size: 18px;
    }

    .review-text {
        color: #475569;
        line-height: 1.7;
        margin-bottom: 24px;
        font-size: 1rem;
    }

    .reviewer {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .reviewer-avatar {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--sp-primary), var(--sp-secondary));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 18px;
    }

    .reviewer-info h4 {
        font-size: 1rem;
        color: var(--sp-dark);
        margin-bottom: 4px;
    }

    .reviewer-info p {
        font-size: 0.875rem;
        color: var(--sp-gray);
    }

    /* FAQ - Accordion Style */
    .sp-faq {
        padding: 100px 0;
        background: var(--sp-light);
    }

    .faq-wrapper {
        max-width: 800px;
        margin: 0 auto;
    }

    .faq-item {
        background: white;
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

    /* CTA Section */
    .sp-cta {
        padding: 100px 0;
        background: linear-gradient(135deg, var(--sp-dark) 0%, var(--sp-darker) 100%);
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .sp-cta::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(45, 189, 180, 0.2) 0%, transparent 70%);
        border-radius: 50%;
    }

    .sp-cta::after {
        content: '';
        position: absolute;
        bottom: -30%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    .sp-cta .container {
        position: relative;
        z-index: 2;
    }

    .sp-cta h2 {
        font-size: 2.5rem;
        color: white;
        margin-bottom: 20px;
    }

    .sp-cta p {
        color: #94a3b8;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto 40px;
    }

    /* Trust Badges */
    .trust-badges {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin-top: 40px;
        flex-wrap: wrap;
    }

    .trust-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
    }

    .trust-item i {
        color: var(--sp-primary);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .hero-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }


        .detail-row {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .detail-row.reverse {
            direction: ltr;
        }

        .process-grid::before {
            display: none;
        }

        .process-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .sp-hero h1 {
            font-size: 2.5rem;
        }

        /* Mobile Stats - Horizontal Layout */
        .stats-flex {
            display: flex;
            flex-direction: row;
            overflow-x: auto;
            gap: 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .stats-flex::-webkit-scrollbar {
            display: none;
        }

        .stat-box {
            flex: 0 0 auto;
            width: 25%;
            min-width: 80px;
            border-right: 1px solid var(--sp-gray-light);
            border-bottom: none;
            padding: 15px 10px;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .vs-grid {
            grid-template-columns: 1fr;
        }

        .services-grid {
            grid-template-columns: 1fr;
        }

        .section-title {
            font-size: 2rem;
        }

        .reviews-scroll {
            grid-template-columns: 1fr;
        }

        .floating-card {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .sp-hero {
            padding: 80px 0 60px;
        }

        .hero-actions {
            flex-direction: column;
        }

        .btn-sp-primary, .btn-sp-secondary {
            width: 100%;
            justify-content: center;
        }

        /* Extra small mobile stats */
        .stat-number {
            font-size: 1.25rem;
        }

        .stat-label {
            font-size: 0.7rem;
        }
        
        .stat-box {
            min-width: 70px;
            padding: 10px 5px;
        }
    }
</style>

<!-- Telegram Popup Modal -->
<div id="telegramModal" class="telegram-modal">
    <div class="telegram-modal-content">
        <button class="telegram-close" onclick="closeTelegramModal()">
            <i class="ti ti-x"></i>
        </button>
        
        <div class="telegram-icon">
            <i class="ti ti-brand-telegram"></i>
        </div>
        
        <h3>Join Our Telegram Channel!</h3>
        <p>Stay updated with the latest virtual numbers, exclusive SMM deals, instant support, and get notified when new countries are available. Don't miss out!</p>
        
        <a href="https://t.me/+0ePevXngW0U2Nzk0" target="_blank" class="btn-telegram">
            <i class="ti ti-brand-telegram"></i>
            Join Telegram Channel
        </a>
        
        <div class="telegram-note">
            <i class="ti ti-users" style="color: var(--telegram);"></i> 
            Join 5,000+ members already on board
        </div>
    </div>
</div>

<!-- Hero Section -->
<section class="sp-hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="ti ti-bolt"></i>
                    Virtual Numbers, OTP & SMM Services
                </div>
                <h1>
                    Buy. Verify. Grow <span>Social Account</span> in One Place
                </h1>
                <p class="hero-subtitle">
                    Get virtual numbers for OTP & eSIM verification, buy social accounts, and boost your growth — fast, secure, and private.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('user.register') }}" class="btn-sp-primary">
                        Get Started Now
                        <i class="ti ti-arrow-right"></i>
                    </a>
                    <a href="https://t.me/+0ePevXngW0U2Nzk0" target="_blank" class="btn-sp-secondary">
                        <i class="ti ti-brand-telegram"></i>
                        Join Our Channel
                    </a>
                </div>
                
                <div class="trust-badges" style="margin-top: 40px; justify-content: flex-start;">
                    <div class="trust-item">
                        <i class="ti ti-shield-check"></i>
                        <span>100% Secure</span>
                    </div>
                    <div class="trust-item">
                        <i class="ti ti-clock"></i>
                        <span>24/7 Support</span>
                    </div>
                    <div class="trust-item">
                        <i class="ti ti-rocket"></i>
                        <span>Instant Delivery</span>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="hero-image-container">
                    
                    <!-- Hero Section image design -->
                  
                  <img src="assets/frontend/images/socialsureplug.gif" alt="Virtual Numbers and OTP Dashboard" style="width: 100%; height: 400px; object-fit: cover;"> 
                 
                </div>
                <div class="floating-card card-1">
                    <div class="floating-icon">
                        <i class="ti ti-message-circle"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--sp-dark);">OTP Received</div>
                        <div style="font-size: 12px; color: var(--sp-gray);">Code: 584921</div>
                    </div>
                </div>
                <div class="floating-card card-2">
                    <div class="floating-icon">
                        <i class="ti ti-users"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--sp-dark);">+1,250 Followers</div>
                        <div style="font-size: 12px; color: var(--sp-gray);">Delivered in 5 mins</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar -->
<section class="sp-stats-bar">
    <div class="container">
        <div class="stats-flex">
            <div class="stat-box">
                <div class="stat-number">10K+</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">1M+</div>
                <div class="stat-label">OTPs & Orders</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">50+</div>
                <div class="stat-label">Countries</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">99.9%</div>
                <div class="stat-label">Uptime</div>
            </div>
        </div>
    </div>
</section>

<!-- Comparison Section -->
<section class="sp-comparison">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <i class="ti ti-sparkles"></i>
                Why Choose Us
            </div>
            <h2 class="section-title">{{ optional($settings)->website_name ?? config('app.name') }} vs Others</h2>
            <p class="section-desc">See why thousands choose our platform for virtual numbers and social media growth</p>
        </div>
        
        <div class="vs-grid">
            <div class="vs-card us">
                <div class="vs-header">
                    <i class="ti ti-plug-connected"></i>
                    {{ optional($settings)->website_name ?? config('app.name') }}
                </div>
                <ul class="vs-list">
                    <li><i class="ti ti-check"></i> Virtual Numbers + SMM in one dashboard</li>
                    <li><i class="ti ti-check"></i> 50+ countries available instantly</li>
                    <li><i class="ti ti-check"></i> Real-time OTP delivery (seconds)</li>
                    <li><i class="ti ti-check"></i> 24/7 live chat & ticket support</li>
                    <li><i class="ti ti-check"></i> API access for developers</li>
                    <li><i class="ti ti-check"></i> One wallet for all services</li>
                    <li><i class="ti ti-check"></i> Privacy guaranteed - no real number needed</li>
                </ul>
            </div>
            
            <div class="vs-card others">
                <div class="vs-header">
                    <i class="ti ti-plug-off"></i>
                    Other Sites
                </div>
                <ul class="vs-list">
                    <li><i class="ti ti-minus"></i> Separate platforms for each service</li>
                    <li><i class="ti ti-minus"></i> Limited country selection</li>
                    <li><i class="ti ti-minus"></i> Delayed SMS delivery</li>
                    <li><i class="ti ti-minus"></i> Email-only support (slow)</li>
                    <li><i class="ti ti-minus"></i> No API integration</li>
                    <li><i class="ti ti-minus"></i> Multiple payment systems</li>
                    <li><i class="ti ti-minus"></i> Privacy concerns</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Services Grid -->
<section class="sp-services" id="features">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <i class="ti ti-phone"></i>
                Our Services
            </div>
            <h2 class="section-title">3-in-1 Platform Solution</h2>
            <p class="section-desc">Everything you need for verification and social growth in one place</p>
        </div>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon-wrap">
                    <i class="ti ti-phone"></i>
                </div>
                <h3>Virtual Phone Numbers</h3>
                <p>Get temporary numbers from 50+ countries to receive SMS and OTP codes without using your real number. Instant activation.</p>
                <a href="{{ route('user.register') }}" class="service-link">
                    Get Number <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            
            <div class="service-card">
                <div class="service-icon-wrap">
                    <i class="ti ti-brand-instagram"></i>
                </div>
                <h3>Boost Socials (SMM)</h3>
                <p>Order followers, likes, views, and more for Instagram, TikTok, YouTube. Fast delivery and competitive rates.</p>
                <a href="{{ route('user.register') }}" class="service-link">
                    Boost Now <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            
                        
            <div class="service-card">
                <div class="service-icon-wrap">
                    <i class="ti ti-shield-lock"></i>
                </div>
                <h3>Buy Account</h3>
                <p>verified social media and digital accounts across platforms like Gmail, Facebook, Twitter, LinkedIn, Telegram, and many more. Fast delivery and and 100% customer satisfaction,</p>
                <a href="{{ route('user.register') }}" class="service-link">
                    Buy Now <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            
            <div class="service-card">
                <div class="service-icon-wrap">
                    <i class="ti ti-message-2"></i>
                </div>
                <h3>OTP & SMS Reception</h3>
                <p>Receive verification codes instantly. View all messages in your dashboard with full history and organization.</p>
                <a href="{{ route('user.register') }}" class="service-link">
                    View Demo <i class="ti ti-arrow-right"></i>
                </a>
            </div>

            <div class="service-card">
                <div class="service-icon-wrap">
                    <i class="ti ti-world"></i>
                </div>
                <h3>Global Coverage</h3>
                <p>Choose numbers from the US, UK, and many more regions. Pick the country that fits your verification needs.</p>
                <a href="{{ route('frontend.support') }}" class="service-link">
                    See Countries <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            
            <div class="service-card">
                <div class="service-icon-wrap">
                    <i class="ti ti-api"></i>
                </div>
                <h3>Developer API</h3>
                <p>Integrate virtual numbers and OTP reception into your apps. RESTful API with comprehensive documentation.</p>
                <a href="{{ route('frontend.support') }}" class="service-link">
                    API Docs <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Process Steps -->
<section class="sp-process">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <i class="ti ti-sparkles"></i>
                How It Works
            </div>
            <h2 class="section-title">Get Started in 3 Simple Steps</h2>
            <p class="section-desc">One account, one wallet — sign up, add balance, and use what you need</p>
        </div>
        
        <div class="process-grid">
            <div class="process-card">
                <div class="step-number">1</div>
                <h3>Sign Up & Add Balance</h3>
                <p>Create an account in seconds and add credit. One wallet for virtual numbers, OTP, and SMM services.</p>
            </div>
            
            <div class="process-card">
                <div class="step-number">2</div>
                <h3>Choose Your Service</h3>
                <p>Pick a virtual number for OTP/SMS, or go to Boost Socials and order followers, likes, views for any platform.</p>
            </div>
            
            <div class="process-card">
                <div class="step-number">3</div>
                <h3>Receive & Verify</h3>
                <p>Get OTPs instantly in your dashboard or watch your social metrics grow. Copy codes and complete verification.</p>
            </div>
        </div>
    </div>
</section>

<!-- Detailed Features -->
<!--
<section class="sp-detailed">
    <div class="container">
        <div class="detail-row">
            <div class="detail-content">
                <div class="section-tag" style="justify-content: flex-start;">
                    <i class="ti ti-brand-instagram"></i>
                    Social Media Growth
                </div>
                <h3>Boost Your Socials with Real Engagement</h3>
                <p>Order followers, likes, views, and more for Instagram, TikTok, and other platforms. Pick a category and service, enter your link and quantity, and we deliver.</p>
                <ul class="feature-list">
                    <li><i class="ti ti-check"></i> Followers, likes, views, and comments</li>
                    <li><i class="ti ti-check"></i> Instagram, TikTok, YouTube & more</li>
                    <li><i class="ti ti-check"></i> Single or mass order options</li>
                    <li><i class="ti ti-check"></i> Same wallet for all services</li>
                </ul>
            </div>
            <div class="detail-image">
                <img src="https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?w=800&auto=format&fit=crop&q=80" alt="Social Media Growth Dashboard" style="width: 100%; height: 300px; object-fit: cover;">
            </div>
        </div>
        
        <div class="detail-row reverse">
            <div class="detail-content">
                <div class="section-tag" style="justify-content: flex-start;">
                    <i class="ti ti-phone"></i>
                    Virtual Numbers
                </div>
                <h3>Get Virtual Numbers Instantly</h3>
                <p>Choose a temporary number from our list of available countries. Numbers are ready to receive SMS and OTP codes within seconds. Perfect for app signups and verification.</p>
                <ul class="feature-list">
                    <li><i class="ti ti-check"></i> Select country and get number instantly</li>
                    <li><i class="ti ti-check"></i> Receive SMS and OTPs in your dashboard</li>
                    <li><i class="ti ti-check"></i> Use for WhatsApp, Telegram, and more</li>
                    <li><i class="ti ti-check"></i> 50+ countries available</li>
                </ul>
            </div>
            <div class="detail-image">
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&auto=format&fit=crop&q=80" alt="Virtual Numbers Interface" style="width: 100%; height: 300px; object-fit: cover;">
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-content">
                <div class="section-tag" style="justify-content: flex-start;">
                    <i class="ti ti-shield-check"></i>
                    Privacy First
                </div>
                <h3>Keep Your Real Number Private</h3>
                <p>Use virtual numbers for signups, free trials, and one-time verifications. Your personal phone number stays private and off spam lists. Dispose of numbers when done.</p>
                <ul class="feature-list">
                    <li><i class="ti ti-check"></i> No need to share your real number</li>
                    <li><i class="ti ti-check"></i> Reduce spam and unwanted calls</li>
                    <li><i class="ti ti-check"></i> Temporary numbers for one-time use</li>
                    <li><i class="ti ti-check"></i> Secure message history</li>
                </ul>
            </div>
            <div class="detail-image">
                <img src="https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800&auto=format&fit=crop&q=80" alt="Privacy Protection" style="width: 100%; height: 300px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>   -->

<!-- Reviews -->
<section class="sp-reviews" id="reviews">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <i class="ti ti-sparkles"></i>
                Testimonials
            </div>
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-desc">Join thousands of satisfied users who trust our platform</p>
        </div>
        
        <div class="reviews-scroll">
            <div class="reviews-track">
                <!-- Original Items -->
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"{{ optional($settings)->website_name ?? config('app.name') }} is perfect for app signups and boosting my Instagram. I get OTPs in seconds and ordered followers with no hassle. The dashboard is simple and both services work great."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">SJ</div>
                        <div class="reviewer-info">
                            <h4>Sarah Johnson</h4>
                            <p>Sales Director, TechStart Inc.</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"I needed a UK number for verification and also wanted to grow my TikTok. Got both here — messages arrive instantly and my SMM order was delivered fast. This platform is a game-changer."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">MC</div>
                        <div class="reviewer-info">
                            <h4>Michael Chen</h4>
                            <p>Founder, GrowthLab</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"We use virtual numbers for testing our app's SMS flow. The API is straightforward and delivery is reliable. Support helped us get set up quickly. Highly recommend for developers."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">ER</div>
                        <div class="reviewer-info">
                            <h4>Emily Rodriguez</h4>
                            <p>Marketing Manager, ScaleUp Solutions</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"OTPs show up in seconds. I've used it for WhatsApp, Telegram, and a bunch of websites. My real number stays private and I don't get spam anymore. Exactly what I needed."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">DT</div>
                        <div class="reviewer-info">
                            <h4>David Thompson</h4>
                            <p>CEO, Digital Dynamics</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"As a freelancer, I sign up for a lot of tools. {{ optional($settings)->website_name ?? config('app.name') }} lets me verify everything without handing out my phone number. Fast, cheap, and it just works."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">JM</div>
                        <div class="reviewer-info">
                            <h4>Jessica Martinez</h4>
                            <p>Freelance Consultant</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"The selection of countries is great and the numbers actually receive SMS. I've had no issues with delivery. Support answered my question same day. Solid service for OTP verification."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">RK</div>
                        <div class="reviewer-info">
                            <h4>Robert Kim</h4>
                            <p>Operations Lead, MobileFirst Co.</p>
                        </div>
                    </div>
                </div>

                <!-- Duplicated Items for Seamless Loop -->
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"{{ optional($settings)->website_name ?? config('app.name') }} is perfect for app signups and boosting my Instagram. I get OTPs in seconds and ordered followers with no hassle. The dashboard is simple and both services work great."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">SJ</div>
                        <div class="reviewer-info">
                            <h4>Sarah Johnson</h4>
                            <p>Sales Director, TechStart Inc.</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"I needed a UK number for verification and also wanted to grow my TikTok. Got both here — messages arrive instantly and my SMM order was delivered fast. This platform is a game-changer."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">MC</div>
                        <div class="reviewer-info">
                            <h4>Michael Chen</h4>
                            <p>Founder, GrowthLab</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"We use virtual numbers for testing our app's SMS flow. The API is straightforward and delivery is reliable. Support helped us get set up quickly. Highly recommend for developers."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">ER</div>
                        <div class="reviewer-info">
                            <h4>Emily Rodriguez</h4>
                            <p>Marketing Manager, ScaleUp Solutions</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"OTPs show up in seconds. I've used it for WhatsApp, Telegram, and a bunch of websites. My real number stays private and I don't get spam anymore. Exactly what I needed."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">DT</div>
                        <div class="reviewer-info">
                            <h4>David Thompson</h4>
                            <p>CEO, Digital Dynamics</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"As a freelancer, I sign up for a lot of tools. {{ optional($settings)->website_name ?? config('app.name') }} lets me verify everything without handing out my phone number. Fast, cheap, and it just works."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">JM</div>
                        <div class="reviewer-info">
                            <h4>Jessica Martinez</h4>
                            <p>Freelance Consultant</p>
                        </div>
                    </div>
                </div>
                
                <div class="review-item">
                    <div class="review-stars">
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                        <i class="ti ti-star-filled"></i>
                    </div>
                    <p class="review-text">"The selection of countries is great and the numbers actually receive SMS. I've had no issues with delivery. Support answered my question same day. Solid service for OTP verification."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">RK</div>
                        <div class="reviewer-info">
                            <h4>Robert Kim</h4>
                            <p>Operations Lead, MobileFirst Co.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.reviews-scroll {
    overflow: hidden;
    width: 100%;
    position: relative;
    mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
}

.reviews-track {
    display: flex;
    gap: 24px;
    width: max-content;
    animation: scroll-rtl 40s linear infinite;
    will-change: transform;
}

/* Right to Left Animation */
@keyframes scroll-rtl {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

/* Individual Card Styling for Slider */
.review-item {
    flex: 0 0 auto;
    width: 400px;
    max-width: 85vw;
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

/* Pause on hover for better readability */
.reviews-scroll:hover .reviews-track {
    animation-play-state: paused;
}

/* Responsive Adjustments */
@media (max-width: 1024px) {
    .review-item {
        width: 350px;
    }
    .reviews-track {
        animation-duration: 35s;
    }
}

@media (max-width: 768px) {
    .review-item {
        width: 300px;
        padding: 20px;
    }
    .reviews-track {
        animation-duration: 30s;
        gap: 16px;
    }
    .review-text {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .review-item {
        width: 280px;
        padding: 16px;
    }
    .reviews-track {
        animation-duration: 25s;
    }
}

/* Reduced motion preference for accessibility */
@media (prefers-reduced-motion: reduce) {
    .reviews-track {
        animation: none;
        flex-wrap: wrap;
        width: 100%;
        justify-content: center;
    }
    .review-item {
        width: 100%;
        max-width: 400px;
    }
}
</style>


<!-- FAQ -->
<section class="sp-faq">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <i class="ti ti-sparkles"></i>
                FAQ
            </div>
            <h2 class="section-title">All You Need to Know</h2>
            <p class="section-desc">Common questions about virtual numbers, OTP verification, and SMM</p>
        </div>
        
        <div class="faq-wrapper">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I order followers and likes here?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes. Our Boost Socials (SMM) section lets you order followers, likes, views, and more for Instagram, TikTok, YouTube, and other platforms. Choose a service, enter your link and quantity, and pay from the same wallet you use for virtual numbers.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How quickly do I receive OTPs and SMS?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Messages are delivered to your dashboard within seconds. As soon as a service sends an SMS or OTP to your virtual number, it appears in your account so you can copy and use it immediately.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Which countries do you offer virtual numbers for?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>{{ optional($settings)->website_name ?? config('app.name') }} offers virtual numbers in 50+ countries, including the US, UK, and many others. Check the dashboard for the current list of available countries and pricing.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I use virtual numbers for WhatsApp or Telegram?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes. You can use our virtual numbers to receive verification codes from WhatsApp, Telegram, and other apps that require phone verification. Select a number and enter it when the app asks for your phone number.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Is my data secure with {{ optional($settings)->website_name ?? config('app.name') }}?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes. We use encryption and secure servers to protect your account and message data. We do not sell your information to third parties. Your privacy is our priority.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Is there a free trial or pay-as-you-go?</h3>
                    <i class="ti ti-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>We offer flexible pricing: add balance and pay only for the numbers and messages you use. Check the pricing page or your dashboard for current options and minimum deposit amounts.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="sp-cta" id="contact">
    <div class="container">
        <h2>Get Your Virtual Number & Boost Your Socials</h2>
        <p>{{ optional($settings)->website_name ?? config('app.name') }} gives you virtual numbers, instant OTP reception, and SMM (followers, likes, views) — sign up, add balance, and get started without sharing your real phone number.</p>
        
        <div class="hero-actions" style="justify-content: center;">
            <a href="{{ route('user.register') }}" class="btn-sp-primary" style="font-size: 1.1rem; padding: 18px 40px;">
                Get Started for Free
                <i class="ti ti-arrow-right"></i>
            </a>
            <a href="https://t.me/+0ePevXngW0U2Nzk0" target="_blank" class="btn-sp-secondary" style="font-size: 1.1rem; padding: 18px 40px;">
                <i class="ti ti-brand-telegram"></i>
                Join Telegram
            </a>
        </div>
        
        <div class="trust-badges">
            <div class="trust-item">
                <i class="ti ti-shield-check"></i>
                <span>SSL Secured</span>
            </div>
            <div class="trust-item">
                <i class="ti ti-clock"></i>
                <span>24/7 Available</span>
            </div>
            <div class="trust-item">
                <i class="ti ti-refresh"></i>
                <span>Money Back Guarantee</span>
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

        // Telegram Popup - Show after 25 seconds
        setTimeout(function() {
            showTelegramModal();
        }, 25000); // 25 seconds
    });

    // Telegram Modal Functions
    function showTelegramModal() {
        const modal = document.getElementById('telegramModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeTelegramModal() {
        const modal = document.getElementById('telegramModal');
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Close modal when clicking outside
    document.getElementById('telegramModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeTelegramModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTelegramModal();
        }
    });
</script>
@endsection