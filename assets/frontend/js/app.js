// =========== toggle theme mode
const root = document.documentElement;
const toggle = document.getElementById('toggle');

const setTheme = (dark) => {
    root.setAttribute('data-theme', dark ? 'dark' : 'light');
    if (toggle) toggle.checked = dark;
    try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (_) { }
};

const saved = localStorage.getItem('theme');
const dark = saved ? saved === 'dark' : toggle?.checked;
setTheme(dark);

toggle?.addEventListener('change', () => setTheme(toggle.checked));

document.addEventListener('DOMContentLoaded', function() {
    const aboutItems = document.querySelectorAll('.about-item');
    const aboutImage = document.querySelector('.about-image');
    
    aboutItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            aboutItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Get the image URL from data-image attribute
            const imageUrl = this.getAttribute('data-image');
            
            // Fade out current image
            if (aboutImage) {
                aboutImage.classList.add('fade-out');
                
                // Change image source after fade out
                setTimeout(() => {
                    aboutImage.src = imageUrl;
                    aboutImage.classList.remove('fade-out');
                    aboutImage.classList.add('fade-in');
                }, 300);
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const icon = item.querySelector('.faq-icon');
        
        question.addEventListener('click', function() {
            const isActive = item.classList.contains('active');
            
            // Close all FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    const otherAnswer = otherItem.querySelector('.faq-answer');
                    const otherIcon = otherItem.querySelector('.faq-icon');
                    otherAnswer.classList.remove('active');
                    otherIcon.classList.remove('ti-chevron-up');
                    otherIcon.classList.add('ti-chevron-down');
                }
            });
            
            // Toggle current item
            if (isActive) {
                item.classList.remove('active');
                answer.classList.remove('active');
                icon.classList.remove('ti-chevron-up');
                icon.classList.add('ti-chevron-down');
            } else {
                item.classList.add('active');
                answer.classList.add('active');
                icon.classList.remove('ti-chevron-down');
                icon.classList.add('ti-chevron-up');
            }
        });
    });

    // Minimal JavaScript - just adds 'animate' class when element is in view
    // All animation logic is handled by CSS
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    // Observe all sections and animated elements
    document.querySelectorAll('section, .section-header, .stats-card, .features-card, .integration-card, .steps-card, .faq-item, .automation-card, .review-card, .cta, .footer, .hero-tags, .hero-title, .hero-description, .hero-buttons').forEach(el => {
        if (el) observer.observe(el);
    });

    // Mobile Menu Toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            const icon = this.querySelector('i');
            if (icon) {
                if (mobileMenu.classList.contains('active')) {
                    icon.classList.remove('ti-menu-2');
                    icon.classList.add('ti-x');
                } else {
                    icon.classList.remove('ti-x');
                    icon.classList.add('ti-menu-2');
                }
            }
        });
    }

});

