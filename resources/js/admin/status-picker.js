// Shows the new status straight away while the change saves
export function statusPicker() {
    const form = document.querySelector('[data-status-picker]');
    if (!form) return;

    const buttons = [...form.querySelectorAll('button[name="status"]')];
    // The status pill in the details beside the message
    const pills = [...document.querySelectorAll('[data-status-pill]')];

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const next = event.submitter?.value;
        if (!next) return;

        for (const button of buttons) button.setAttribute('aria-pressed', String(button.value === next));
        for (const pill of pills) pill.hidden = pill.dataset.statusPill !== next;

        const body = new FormData(form);
        body.set('status', next);
        try {
            const response = await fetch(form.action, { method: 'POST', body, headers: { Accept: 'application/json' } });
            if (!response.ok || !(await response.json()).status) throw new Error('Not saved');
        } catch {
            // Back to what the server has: the saved status, the sign-in page or "This enquiry isn't here"
            window.location.reload();
        }
    });
}
