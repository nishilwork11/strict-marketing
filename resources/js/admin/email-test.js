import { setRollLabel } from '../motion/roll-label';

// "Sending…" while the test email is on its way, which can take a few seconds when
// the mail server is slow to answer: the cyan, magenta and yellow dots take the
// arrow's place, as they do in the contact form.
export function emailTest() {
    const form = document.querySelector('[data-email-test]');
    if (!form) return;

    const button = form.querySelector('button[type="submit"]');
    const arrow = button.querySelector('[data-email-arrow]');
    const dots = button.querySelector('[data-email-dots]');
    const setPending = (pending) => {
        button.disabled = pending;
        setRollLabel(button, pending ? 'Sending…' : 'Send test email');
        arrow.hidden = pending;
        dots.hidden = !pending;
    };

    form.addEventListener('submit', () => setPending(true));
    // Coming back to this page with the browser's back button shows it as it was left
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) setPending(false);
    });
}
