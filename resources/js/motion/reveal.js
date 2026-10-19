// One observer for the whole page: marks [data-reveal] elements as shown when they
// scroll into view, and feeds scroll progress to [data-scrub] text. The matching
// CSS lives in app.css under "Scroll reveals".
export function reveal() {
    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;
                entry.target.dataset.shown = '';
                observer.unobserve(entry.target);
            }
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.12 },
    );
    document.querySelectorAll('[data-reveal]:not([data-shown])').forEach((el) => observer.observe(el));

    const scrubs = [...document.querySelectorAll('[data-scrub]')];
    let frame = 0;
    const update = () => {
        frame = 0;
        const vh = window.innerHeight;
        for (const el of scrubs) {
            const rect = el.getBoundingClientRect();
            // 0 when the text's top reaches 85% down the screen, 1 when it reaches 30%
            const p = Math.min(Math.max((vh * 0.85 - rect.top) / (vh * 0.55), 0), 1);
            el.style.setProperty('--p', p.toFixed(3));
        }
    };
    const onScroll = () => {
        if (!frame) frame = requestAnimationFrame(update);
    };
    update();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
}
