const SECONDS_PER_PASS = 45; // time for every name to pass once at rest

// A large, slowly scrolling strip of service names between the hero and the work.
// Scrolling the page pushes it faster, and scrolling back up turns it around.
// Fast scrolling also leans the names into their direction of travel.
export function ticker() {
    const strip = document.querySelector('[data-ticker]');
    const track = strip?.parentElement;
    if (!strip || !track) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    // With an odd number of names, each loop runs them twice (see ticker.blade.php)
    const names = strip.firstElementChild.children.length;
    const unique = strip.firstElementChild.querySelectorAll(':scope > li:not([aria-hidden])').length;
    const LOOP_SECONDS = SECONDS_PER_PASS * (names / unique);

    let x = 0;
    let direction = -1;
    let boost = 0;
    let speed = 1; // eases to 0 while hovered so the strip glides to a stop
    let lean = 0; // skew in degrees, follows scroll velocity
    let hovered = false;
    let lastY = window.scrollY;
    let last = performance.now();
    let frame = 0;

    const tick = (now) => {
        const dt = Math.min(now - last, 64) / 1000;
        last = now;

        const dy = window.scrollY - lastY;
        lastY = window.scrollY;
        if (dy !== 0) {
            direction = dy > 0 ? -1 : 1;
            boost = Math.min(boost + Math.abs(dy) * 0.04, 6);
        }
        boost *= Math.pow(0.94, dt * 60);
        speed += ((hovered ? 0 : 1) - speed) * Math.min(dt * 6, 1);
        // Scrolling down moves the strip left, so a positive skew tips the tops left
        const velocity = dt > 0 ? dy / dt : 0;
        lean += (Math.max(-7, Math.min(7, velocity * 0.003)) - lean) * Math.min(dt * 8, 1);

        // Two identical copies sit side by side, so wrapping at half the width is seamless.
        const half = strip.scrollWidth / 2;
        x += direction * (half / LOOP_SECONDS) * speed * (1 + boost) * dt;
        x = ((x % half) - half) % half;
        strip.style.transform = `translate3d(${x.toFixed(1)}px, 0, 0) skewX(${lean.toFixed(2)}deg)`;
        frame = requestAnimationFrame(tick);
    };

    // Only run while the strip is on screen.
    const observer = new IntersectionObserver(([entry]) => {
        cancelAnimationFrame(frame);
        frame = 0;
        if (entry.isIntersecting) {
            last = performance.now();
            lastY = window.scrollY;
            frame = requestAnimationFrame(tick);
        }
    });
    observer.observe(track);

    track.addEventListener('pointerenter', () => (hovered = true));
    track.addEventListener('pointerleave', () => (hovered = false));
}
