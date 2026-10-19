import { setRollLabel } from '../motion/roll-label';

const FIELDS = ['name', 'email', 'phone', 'message'];

// Sends the enquiry form with fetch and shows the answer in place: field errors
// under each field, or the "Enquiry sent" panel. While it's sending, cyan, magenta
// and yellow dots bounce in the send button.
export function contactForm() {
    const root = document.querySelector('[data-contact]');
    if (!root) return;

    const form = root.querySelector('[data-contact-form]');
    const sent = root.querySelector('[data-contact-sent]');
    const submit = form.querySelector('[data-contact-submit]');
    const arrow = submit.querySelector('[data-contact-arrow]');
    const dots = submit.querySelector('[data-contact-dots]');
    const status = form.querySelector('[data-contact-status]');

    const setPending = (pending) => {
        submit.disabled = pending;
        setRollLabel(submit, pending ? 'Sending…' : 'Send enquiry');
        arrow.hidden = pending;
        dots.hidden = !pending;
    };

    const showErrors = (errors = {}) => {
        for (const name of FIELDS) {
            const input = form.elements.namedItem(name);
            const error = form.querySelector(`[data-error-for="${name}"]`);
            const message = errors[name];
            input.setAttribute('aria-invalid', String(Boolean(message)));
            if (message) input.setAttribute('aria-describedby', error.id);
            else input.removeAttribute('aria-describedby');
            error.textContent = message ?? '';
            error.hidden = !message;
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submit.disabled) return;

        setPending(true);
        let result = null;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
            });
            result = await response.json();
        } catch {
            // Offline, or the server didn't answer with JSON
        }
        setPending(false);

        if (result?.status === 'success') {
            showErrors();
            status.textContent = '';
            sent.querySelector('[data-contact-sent-message]').textContent = result.message;
            form.hidden = true;
            sent.hidden = false;
            return;
        }

        // Keeps what the visitor typed, so they can fix it and send again
        showErrors(result?.status === 'error' ? result.errors : {});
        status.textContent = result?.status === 'error' ? result.message : form.dataset.failedMessage;
    });

    // "Send another enquiry" brings back an empty form
    root.querySelector('[data-contact-again]').addEventListener('click', (event) => {
        event.preventDefault();
        for (const field of form.querySelectorAll('input, textarea')) {
            if (field.type === 'hidden') continue;
            if (field.type === 'checkbox' || field.type === 'radio') field.checked = false;
            else field.value = '';
        }
        showErrors();
        status.textContent = '';
        sent.hidden = true;
        form.hidden = false;
    });
}
