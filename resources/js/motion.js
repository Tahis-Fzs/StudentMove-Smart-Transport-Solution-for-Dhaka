import { animate, createTimeline, stagger, onScroll } from 'animejs';

const reduceMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function pageEnter() {
    if (reduceMotion()) return;
    const root =
        document.querySelector('main') ||
        document.querySelector('.admin-container') ||
        document.querySelector('.driver-shell') ||
        document.body;
    if (!root) return;

    animate(root, {
        opacity: [0.4, 1],
        y: [14, 0],
        duration: 680,
        ease: 'outExpo',
    });
}

function revealBlocks() {
    if (reduceMotion()) {
        document.querySelectorAll('[data-reveal], .sm-reveal').forEach((el) => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
        return;
    }

    const targets = document.querySelectorAll(
        '[data-reveal], .sm-reveal, .sm-panel[data-reveal], .dashboard-container > section, .signin-container, .profile-container, .notification-section, .subscription-container, .next-bus-container, .route-suggestion-container, .admin-section, .promo-carousel-section, .main-greeting-section, .sm-page-head'
    );

    targets.forEach((el, i) => {
        if (el.dataset.motionBound === '1') return;
        el.dataset.motionBound = '1';

        animate(el, {
            opacity: [0, 1],
            y: [26, 0],
            duration: 880,
            delay: Math.min(i * 36, 220),
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
    if (reduceMotion()) return;
    const logo = document.querySelector('.nav-logo, .l-nav__brand, .admin-top-nav-brand');
    if (!logo) return;
    animate(logo, {
        opacity: [0, 1],
        x: [-10, 0],
        duration: 750,
        ease: 'outExpo',
    });
}

function hoverLift() {
    if (reduceMotion()) return;
    document
        .querySelectorAll('.nav-button, .signin-btn, .promo-btn, .l-btn, .action-btn, .sm-btn, .plan-cta')
        .forEach((btn) => {
            btn.addEventListener('pointerenter', () => {
                animate(btn, { y: -2, duration: 200, ease: 'outQuad' });
            });
            btn.addEventListener('pointerleave', () => {
                animate(btn, { y: 0, duration: 200, ease: 'outQuad' });
            });
        });
}

/** Ambient CSS corridor widgets (.sm-corridor) — JS only for pointer tilt */
function corridorTilt() {
    if (reduceMotion()) return;
    document.querySelectorAll('.sm-corridor').forEach((scene) => {
        const world = scene.querySelector('.sm-corridor__world');
        if (!world) return;

        let rx = 58;
        let rz = -26;
        let tx = 58;
        let tz = -26;

        scene.addEventListener('pointermove', (e) => {
            const rect = scene.getBoundingClientRect();
            const nx = (e.clientX - rect.left) / rect.width - 0.5;
            const ny = (e.clientY - rect.top) / rect.height - 0.5;
            tx = 58 + ny * -8;
            tz = -26 + nx * 12;
        });
        scene.addEventListener('pointerleave', () => {
            tx = 58;
            tz = -26;
        });

        const loop = () => {
            rx += (tx - rx) * 0.07;
            rz += (tz - rz) * 0.07;
            world.style.transform = `rotateX(${rx}deg) rotateZ(${rz}deg)`;
            requestAnimationFrame(loop);
        };
        requestAnimationFrame(loop);
    });
}

export function initPremiumMotion() {
    navPulse();
    pageEnter();
    revealBlocks();
    hoverLift();
    corridorTilt();
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.body.classList.contains('landing-body')) return;
    initPremiumMotion();
});
