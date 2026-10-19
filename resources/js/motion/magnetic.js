// Buttons marked data-magnetic lean toward the pointer while it is over them and
// spring back when it leaves. The attribute's value sets the pull from 0 to 1
// (default 0.3). One delegated listener covers the page, so buttons that appear
// later, like the form's "Send another enquiry", work without wiring.
export function magnetic() {
    const query = '(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)';
    if (!window.matchMedia(query).matches) return;

    const pulls = new Map();
    let current = null;
    let frame = 0;

    const tick = () => {
        frame = 0;
        for (const [el, p] of pulls) {
            p.x += (p.tx - p.x) * 0.18;
            p.y += (p.ty - p.y) * 0.18;
            const settled = Math.abs(p.tx - p.x) + Math.abs(p.ty - p.y) < 0.05;
            if (settled && el !== current) {
                el.style.transform = '';
                pulls.delete(el);
                continue;
            }
            el.style.transform = `translate3d(${p.x.toFixed(2)}px, ${p.y.toFixed(2)}px, 0)`;
            if (!settled && !frame) frame = requestAnimationFrame(tick);
        }
    };
    const run = () => {
        if (!frame) frame = requestAnimationFrame(tick);
    };

    const release = () => {
        const p = current && pulls.get(current);
        current = null;
        if (!p) return;
        p.tx = 0;
        p.ty = 0;
        run();
    };

    document.addEventListener(
        'pointermove',
        (e) => {
            if (e.pointerType !== 'mouse') return;
            const el = e.target.closest('[data-magnetic]');
            if (el !== current) release();
            if (!el) return;
            current = el;
            const p = pulls.get(el) ?? { x: 0, y: 0, tx: 0, ty: 0 };
            pulls.set(el, p);
            // Measure from where the button sits at rest, not where it has been pulled to
            const r = el.getBoundingClientRect();
            const pull = Number(el.dataset.magnetic) || 0.3;
            p.tx = (e.clientX - (r.left - p.x + r.width / 2)) * pull;
            p.ty = (e.clientY - (r.top - p.y + r.height / 2)) * pull;
            run();
        },
        { passive: true },
    );
    document.addEventListener('pointerout', (e) => {
        if (!e.relatedTarget) release(); // left the window
    });
    // The page moving under a still pointer would leave the target stale
    window.addEventListener('scroll', release, { passive: true });
}
