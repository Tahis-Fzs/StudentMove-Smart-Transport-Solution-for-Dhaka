import { animate, createTimeline, stagger, onScroll } from 'animejs';

function pageEnter() {
    const root = document.querySelector('main') || document.querySelector('.admin-container') || document.body;
    if (!root) return;

    animate(root, {
        opacity: [0.35, 1],
        y: [16, 0],
        duration: 700,
        ease: 'outExpo',
    });
}

function revealBlocks() {
    const targets = document.querySelectorAll(
        '[data-reveal], .sm-reveal, .dashboard-container > section, .signin-container, .profile-container, .notification-section, .subscription-container, .next-bus-container, .route-suggestion-container, .admin-section, .promo-carousel-section, .main-greeting-section'
    );

    targets.forEach((el, i) => {
        if (el.dataset.motionBound === '1') return;
        el.dataset.motionBound = '1';

        animate(el, {
            opacity: [0, 1],
            y: [28, 0],
            duration: 900,
            delay: Math.min(i * 40, 240),
            ease: 'outExpo',
            autoplay: onScroll({
                target: el,
                enter: 'bottom-=8%',
                once: true,
            }),
        });
    });
}

function navPulse() {
    const logo = document.querySelector('.nav-logo, .l-nav__brand, .admin-top-nav-brand');
    if (!logo) return;
    animate(logo, {
        opacity: [0, 1],
        x: [-12, 0],
        duration: 800,
        ease: 'outExpo',
    });
}

function hoverLift() {
    document.querySelectorAll('.nav-button, .signin-btn, .promo-btn, .l-btn, .action-btn').forEach((btn) => {
        btn.addEventListener('pointerenter', () => {
            animate(btn, { y: -2, duration: 220, ease: 'outQuad' });
        });
        btn.addEventListener('pointerleave', () => {
            animate(btn, { y: 0, duration: 220, ease: 'outQuad' });
        });
    });
}

export function initPremiumMotion() {
    navPulse();
    pageEnter();
    revealBlocks();
    hoverLift();
}

document.addEventListener('DOMContentLoaded', () => {
    // Landing has its own richer timeline
    if (document.body.classList.contains('landing-body')) return;
    initPremiumMotion();
});
