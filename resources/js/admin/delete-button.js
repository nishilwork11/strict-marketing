const ARMED = ['bg-magenta', 'text-paper'];
const RESTING = ['text-slate', 'hover:bg-mist', 'hover:text-ink'];

// Deleting can't be undone, so the first press only arms the button and a second
// press within a few seconds deletes.
export function deleteButton() {
    const form = document.querySelector('[data-delete-form]');
    if (!form) return;

    const button = form.querySelector('button');
    const label = button.querySelector('[data-delete-label]');
    let armed = false;
    let timer = 0;

    const arm = (on) => {
        armed = on;
        button.classList.remove(...(on ? RESTING : ARMED));
        button.classList.add(...(on ? ARMED : RESTING));
        label.textContent = on ? 'Press again to delete' : 'Delete enquiry';
        clearTimeout(timer);
        if (on) timer = setTimeout(() => arm(false), 4000);
    };

    form.addEventListener('submit', (event) => {
        if (!armed) {
            event.preventDefault();
            arm(true);
            return;
        }
        clearTimeout(timer);
        button.disabled = true;
        label.textContent = 'Deleting…';
    });
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;
        button.disabled = false;
        arm(false);
    });
}
