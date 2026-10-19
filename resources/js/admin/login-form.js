import { setRollLabel } from '../motion/roll-label';

// "Signing in…" while the password is checked
export function loginForm() {
    const form = document.querySelector('[data-login-form]');
    if (!form) return;

    const button = form.querySelector('button[type="submit"]');
    const setPending = (pending) => {
        button.disabled = pending;
        setRollLabel(button, pending ? 'Signing in…' : 'Sign in');
    };

    form.addEventListener('submit', () => setPending(true));
    // Coming back to this page with the browser's back button shows it as it was left
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) setPending(false);
    });
}
