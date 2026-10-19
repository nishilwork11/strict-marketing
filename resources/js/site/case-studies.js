const PIN_TOP = 88; // just below the header, which slides back in while scrolling up
const GAP = 16; // space kept under a card that's taller than the screen

// The case studies as a stack of cards: each one holds near the top of the screen
// while the next slides up over it, and the one underneath shrinks back and fades.
export function caseStudies() {
    const list = document.querySelector('[data-case-stack]');
    if (!list) return;

    const items = [...list.children];
    const motion = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let frame = 0;

    // Below the header, or held by its bottom edge when the card is taller than
    // the screen, so all of it has been seen before the next one covers it
    const place = () => {
        for (const item of items) {
            item.style.top = `${Math.min(PIN_TOP, window.innerHeight - item.offsetHeight - GAP)}px`;
        }
    };
    // --covered runs from 0, when the next card starts up the screen, to 1, when it
    // has arrived on top. The CSS turns it into the shrink and fade (app.css).
    const update = () => {
        frame = 0;
        items.forEach((item, i) => {
            const next = items[i + 1];
            if (!next) return;
            const travel = window.innerHeight - parseFloat(next.style.top);
            const covered = Math.min(Math.max((window.innerHeight - next.getBoundingClientRect().top) / travel, 0), 1);
            item.style.setProperty('--covered', covered.toFixed(3));
        });
    };
    const schedule = () => {
        if (motion && !frame) frame = requestAnimationFrame(update);
    };
    const resize = new ResizeObserver(() => {
        place();
        schedule();
    });

    place();
    schedule();
    items.forEach((item) => resize.observe(item));
    window.addEventListener('scroll', schedule, { passive: true });
}
