// A soft light that eases toward the pointer while it moves over the hero.
export function heroGlow() {
    const glow = document.querySelector('[data-hero-glow]');
    const hero = glow?.parentElement;
    if (!glow || !hero) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce), (pointer: coarse)').matches) return;

    let x = 0.5,
        y = 0.45,
        tx = x,
        ty = y;
    let frame = 0;
    const tick = () => {
        x += (tx - x) * 0.07;
        y += (ty - y) * 0.07;
        glow.style.setProperty('--gx', `${(x * 100).toFixed(2)}%`);
        glow.style.setProperty('--gy', `${(y * 100).toFixed(2)}%`);
        frame = Math.abs(tx - x) + Math.abs(ty - y) > 0.001 ? requestAnimationFrame(tick) : 0;
    };

    hero.addEventListener('pointermove', (e) => {
        const r = hero.getBoundingClientRect();
        tx = (e.clientX - r.left) / r.width;
        ty = (e.clientY - r.top) / r.height;
        glow.style.opacity = '1';
        if (!frame) frame = requestAnimationFrame(tick);
    });
    hero.addEventListener('pointerleave', () => (glow.style.opacity = '0'));
}
