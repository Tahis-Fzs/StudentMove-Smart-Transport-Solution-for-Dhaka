<div id="pwa-install" class="pwa-install" aria-live="polite">
    <div class="pwa-install__inner">
        <img src="{{ asset('icons/icon.svg') }}" alt="" class="pwa-install__icon" width="40" height="40">
        <div class="pwa-install__copy">
            <p class="pwa-install__title">Install StudentMove</p>
            <p class="pwa-install__sub">Add to your home screen for quick access to live buses and bookings.</p>
        </div>
        <div class="pwa-install__actions">
            <button type="button" class="pwa-install__btn pwa-install__btn--ghost" id="pwa-dismiss-btn">Not now</button>
            <button type="button" class="pwa-install__btn pwa-install__btn--primary" id="pwa-install-btn">Install</button>
        </div>
    </div>
</div>
<script src="{{ asset('js/pwa.js') }}" defer></script>
