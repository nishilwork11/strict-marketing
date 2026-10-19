// Splits "+1,200 products" into "+", "1,200" and " products".
const PATTERN = /^(\D*?)(\d[\d,]*(?:\.\d+)?)([\s\S]*)$/;

function formatter(raw) {
    const decimals = raw.split('.')[1]?.length ?? 0;
    const grouped = raw.includes(',');
    return (n) =>
        grouped
            ? n.toLocaleString('en-GB', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
            : n.toFixed(decimals);
}

// Counts the number inside each [data-count-up] figure up from zero the first time it
// is seen. The server renders the final value, so it reads correctly without JavaScript.
export function countUp(duration = 1400) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    for (const el of document.querySelectorAll('[data-count-up]')) {
        const match = el.dataset.countUp.match(PATTERN);
        if (!match) continue;

        const [, prefix, raw, suffix] = match;
        const target = Number(raw.replace(/,/g, ''));
        if (!target) continue;
        const format = formatter(raw);
        const render = (n) => (el.textContent = `${prefix}${format(n)}${suffix}`);

        render(0);
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (!entry.isIntersecting) return;
                observer.disconnect();
                const start = performance.now();
                const tick = (now) => {
                    const t = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(2, -10 * t); // ease-out expo
                    render(t === 1 ? target : target * eased);
                    if (t < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            },
            { threshold: 0.4 },
        );
        observer.observe(el);
    }
}
