import { initializeApp, getApps } from 'firebase/app';
import {
    getAuth,
    GoogleAuthProvider,
    FacebookAuthProvider,
    GithubAuthProvider,
    signInWithPopup,
    signInWithRedirect,
    getRedirectResult,
} from 'firebase/auth';

const INTENT_KEY = 'studentmove_firebase_intent';

function cfg() {
    return window.__FIREBASE__ || null;
}

function enabledProviders() {
    const list = (cfg()?.providers || ['google']).map((p) => String(p).toLowerCase());
    return list.length ? list : ['google'];
}

function getFirebaseAuth() {
    const c = cfg();
    if (!c?.apiKey || !c?.authDomain || !c?.projectId || !c?.appId) {
        throw new Error('Firebase is not configured. Set FIREBASE_* keys in .env.');
    }

    const app = getApps().length
        ? getApps()[0]
        : initializeApp({
            apiKey: c.apiKey,
            authDomain: c.authDomain,
            projectId: c.projectId,
            storageBucket: c.storageBucket || undefined,
            messagingSenderId: c.messagingSenderId || undefined,
            appId: c.appId,
        });

    return getAuth(app);
}

function providerFor(name) {
    switch (name) {
        case 'google':
            return new GoogleAuthProvider();
        case 'facebook':
            return new FacebookAuthProvider();
        case 'github':
            return new GithubAuthProvider();
        default:
            throw new Error(`Unsupported provider: ${name}`);
    }
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function setStatus(message, isError = false) {
    const el = document.querySelector('[data-firebase-status]');
    if (!el) return;
    el.hidden = !message;
    el.textContent = message || '';
    el.classList.toggle('auth-alert--err', isError);
    el.classList.toggle('auth-alert--ok', !isError && !!message);
}

function friendlyAuthError(err) {
    const code = err?.code || '';
    const host = window.location.hostname;

    if (code === 'auth/unauthorized-domain') {
        return (
            `This site domain is not allowed in Firebase yet. Add “${host}” under ` +
            'Firebase Console → Authentication → Settings → Authorized domains, then try again.'
        );
    }

    if (code === 'auth/popup-blocked' || code === 'auth/popup-closed-by-user') {
        return err?.message || 'Sign-in popup was blocked. Retrying with redirect…';
    }

    if (code === 'auth/operation-not-allowed') {
        return 'This sign-in provider is disabled in Firebase. Enable it under Authentication → Sign-in method.';
    }

    return err?.message || 'Sign-in failed.';
}

function isPopupBlocked(err) {
    const code = err?.code || '';
    const msg = String(err?.message || '');
    return code === 'auth/popup-blocked' || code === 'auth/popup-closed-by-user' || /popup/i.test(msg);
}

async function syncWithLaravel(idToken, intent) {
    const res = await fetch(cfg().syncUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ id_token: idToken, intent }),
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
        const msg =
            data?.errors?.firebase?.[0] ||
            data?.message ||
            data?.errors?.id_token?.[0] ||
            'Could not sync your account with StudentMove.';
        throw new Error(msg);
    }

    return data;
}

async function finishSignIn(user, intent) {
    const idToken = await user.getIdToken();
    const data = await syncWithLaravel(idToken, intent);
    setStatus('Synced to StudentMove. Redirecting…');
    if (!data.redirect) {
        throw new Error('Server did not return a redirect URL.');
    }

    window.location.href = data.redirect;
}

async function socialSignIn(providerName, intent) {
    const buttons = document.querySelectorAll('[data-firebase-provider]');
    buttons.forEach((b) => {
        b.disabled = true;
    });
    setStatus('Connecting…');

    try {
        const auth = getFirebaseAuth();
        const provider = providerFor(providerName);

        try {
            const result = await signInWithPopup(auth, provider);
            await finishSignIn(result.user, intent);
            return;
        } catch (popupErr) {
            if (!isPopupBlocked(popupErr)) {
                throw popupErr;
            }
            // Safari / tunnel / blockers often block popups — fall back to full-page redirect.
            sessionStorage.setItem(INTENT_KEY, intent);
            setStatus('Opening Google sign-in…');
            await signInWithRedirect(auth, provider);
        }
    } catch (err) {
        console.error(err);
        setStatus(friendlyAuthError(err), true);
        buttons.forEach((b) => {
            b.disabled = false;
        });
    }
}

async function handleRedirectReturn(intent) {
    try {
        const auth = getFirebaseAuth();
        const result = await getRedirectResult(auth);
        if (!result?.user) return;

        const savedIntent = sessionStorage.getItem(INTENT_KEY) || intent;
        sessionStorage.removeItem(INTENT_KEY);
        setStatus('Finishing sign-in…');
        await finishSignIn(result.user, savedIntent);
    } catch (err) {
        console.error(err);
        setStatus(friendlyAuthError(err), true);
    }
}

function mount() {
    const root = document.querySelector('[data-firebase-auth]');
    if (!root) return;

    const intent = root.getAttribute('data-intent') || 'login';
    const allowed = enabledProviders();

    handleRedirectReturn(intent);

    root.querySelectorAll('[data-firebase-provider]').forEach((btn) => {
        const provider = btn.getAttribute('data-firebase-provider');
        if (!allowed.includes(provider)) {
            btn.hidden = true;
            return;
        }
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            socialSignIn(provider, intent);
        });
    });
}

document.addEventListener('DOMContentLoaded', mount);
