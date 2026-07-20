(function () {
    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            /* SW optional — app still works without it */
        });
    });

    let deferredPrompt = null;
    const banner = document.getElementById('pwa-install');
    const installBtn = document.getElementById('pwa-install-btn');
    const dismissBtn = document.getElementById('pwa-dismiss-btn');

    if (!banner || window.matchMedia('(display-mode: standalone)').matches) return;
    if (localStorage.getItem('pwa-install-dismissed') === '1') return;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        banner.classList.add('is-visible');
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        banner.classList.remove('is-visible');
    });

    dismissBtn?.addEventListener('click', () => {
        localStorage.setItem('pwa-install-dismissed', '1');
        banner.classList.remove('is-visible');
    });
})();
