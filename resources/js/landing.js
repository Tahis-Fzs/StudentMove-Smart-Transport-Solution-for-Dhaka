import { animate, createTimeline, stagger, onScroll } from 'animejs';

function initHeroIntro() {
    const tl = createTimeline({ defaults: { ease: 'outExpo' } });

    tl.add('.l-hero__img', {
        scale: [1.12, 1],
        duration: 2200,
    }, 0)
        .add('.l-hero__brand', {
            opacity: [0, 1],
            y: [48, 0],
            duration: 1100,
        }, 280)
        .add('.l-hero__headline', {
            opacity: [0, 1],
            y: [36, 0],
            duration: 1000,
        }, 480)
        .add('.l-hero__lede', {
            opacity: [0, 1],
            y: [28, 0],
            duration: 900,
        }, 640)
        .add('.l-hero__cta', {
            opacity: [0, 1],
            y: [20, 0],
            duration: 800,
        }, 780);
}

function initHeroParallax() {
    const media = document.querySelector('.l-hero__media');
    const img = document.querySelector('.l-hero__img');
    if (!media || !img) return;

    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    let raf = 0;

    const tick = () => {
        currentX += (targetX - currentX) * 0.08;
        currentY += (targetY - currentY) * 0.08;
        img.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) scale(1.06)`;
        raf = requestAnimationFrame(tick);
    };

    media.addEventListener('pointermove', (e) => {
        const rect = media.getBoundingClientRect();
        const nx = (e.clientX - rect.left) / rect.width - 0.5;
        const ny = (e.clientY - rect.top) / rect.height - 0.5;
        targetX = nx * -28;
        targetY = ny * -18;
    });

    media.addEventListener('pointerleave', () => {
        targetX = 0;
        targetY = 0;
    });

    raf = requestAnimationFrame(tick);

    window.addEventListener('scroll', () => {
        const y = Math.min(window.scrollY, 600);
        media.style.transform = `translate3d(0, ${y * 0.22}px, 0)`;
    }, { passive: true });
}

function initScene3D() {
    const scene = document.querySelector('.l-scene');
    const world = document.querySelector('.l-scene__world');
    const bus = document.querySelector('.l-scene__bus');
    const ring = document.querySelector('.l-scene__ring');
    if (!scene || !world) return;

    let rx = 58;
    let rz = -28;
    let tx = 58;
    let tz = -28;

    scene.addEventListener('pointermove', (e) => {
        const rect = scene.getBoundingClientRect();
        const nx = (e.clientX - rect.left) / rect.width - 0.5;
        const ny = (e.clientY - rect.top) / rect.height - 0.5;
        tx = 58 + ny * -10;
        tz = -28 + nx * 14;
    });

    scene.addEventListener('pointerleave', () => {
        tx = 58;
        tz = -28;
    });

    const loop = () => {
        rx += (tx - rx) * 0.06;
        rz += (tz - rz) * 0.06;
        world.style.transform = `rotateX(${rx}deg) rotateZ(${rz}deg)`;
        requestAnimationFrame(loop);
    };
    requestAnimationFrame(loop);

    if (bus) {
        animate(bus, {
            keyframes: [
                { left: '14%' },
                { left: '68%' },
                { left: '14%' },
            ],
            duration: 7200,
            ease: 'inOutSine',
            loop: true,
        });
    }

    if (ring) {
        animate(ring, {
            scale: [1, 1.35],
            opacity: [0.7, 0],
            duration: 2200,
            ease: 'outQuad',
            loop: true,
        });
    }

    animate('.l-scene__node', {
        scale: [0.85, 1.1, 0.85],
        duration: 2400,
        ease: 'inOutQuad',
        delay: stagger(220),
        loop: true,
    });
}

function initScrollReveals() {
    document.querySelectorAll('[data-reveal]').forEach((el) => {
        animate(el, {
            opacity: [0, 1],
            y: [36, 0],
            duration: 1000,
            ease: 'outExpo',
            autoplay: onScroll({
                target: el,
                enter: 'bottom-=12%',
                once: true,
            }),
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (!document.body.classList.contains('landing-body')) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) {
        document.querySelectorAll(
            '.l-hero__brand, .l-hero__headline, .l-hero__lede, .l-hero__cta, [data-reveal]'
        ).forEach((el) => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
        return;
    }

    initHeroIntro();
    initHeroParallax();
    initScene3D();
    initScrollReveals();
});
