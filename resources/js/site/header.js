// The header card's two looks: see-through over the dark hero, a white card past it
const LIGHT = ['mx-3', 'rounded-b-2xl', 'border', 'border-t-0', 'border-line', 'bg-paper', 'text-ink', 'sm:mx-5'];
const DARK = ['border', 'border-transparent', 'text-paper'];

// Over the dark hero the header is transparent; past it, it becomes a white card.
// Below the hero it slides away while scrolling down and returns on scroll up.
export function header() {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;

    const card = header.querySelector('[data-header-card]');
    const progress = header.querySelector('[data-header-progress]');
    const arrow = header.querySelector('[data-header-arrow]');
    const button = header.querySelector('[data-menu-button]');
    const label = button.querySelector('[data-menu-label]');
    const top = button.querySelector('[data-menu-line="top"]');
    const bottom = button.querySelector('[data-menu-line="bottom"]');
    const menu = header.querySelector('#mobile-menu');
    const links = [...header.querySelectorAll('[data-nav-link]')];

    let solid = false;
    let tucked = false;
    let open = false;

    const render = () => {
        const light = solid || open;
        header.classList.toggle('-translate-y-[110%]', tucked && !open);
        card.classList.remove(...DARK, ...LIGHT);
        card.classList.add(...(light ? LIGHT : DARK));
        progress.classList.toggle('opacity-100', solid);
        progress.classList.toggle('opacity-0', !solid);
        // The "Let's talk" arrow is white over the hero and black on the white card
        arrow.classList.toggle('bg-paper', !light);
        arrow.classList.toggle('text-ink', !light);
        arrow.classList.toggle('bg-ink', light);
        arrow.classList.toggle('text-paper', light);

        button.setAttribute('aria-expanded', String(open));
        label.textContent = open ? 'Close' : 'Menu';
        top.classList.toggle('translate-y-[3px]', open);
        top.classList.toggle('rotate-45', open);
        bottom.classList.toggle('-translate-y-[3px]', open);
        bottom.classList.toggle('-rotate-45', open);
        menu.hidden = !open;
    };

    let lastY = window.scrollY;
    const onScroll = () => {
        const y = window.scrollY;
        const nextSolid = y > window.innerHeight - 120;
        let nextTucked = tucked;
        if (Math.abs(y - lastY) > 6) {
            nextTucked = y > lastY && y > window.innerHeight;
            lastY = y;
        }
        if (nextSolid === solid && nextTucked === tucked) return;
        solid = nextSolid;
        tucked = nextTucked;
        render();
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // Marks the menu link for the section crossing the middle of the screen.
    let active = null;
    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                const href = `#${entry.target.id}`;
                if (entry.isIntersecting) active = href;
                else if (active === href) active = null;
            }
            for (const link of links) {
                if (link.getAttribute('href') === active) link.setAttribute('aria-current', 'location');
                else link.removeAttribute('aria-current');
            }
        },
        { rootMargin: '-45% 0px -54% 0px' },
    );
    links
        .map((link) => document.querySelector(link.getAttribute('href')))
        .filter(Boolean)
        .forEach((section) => observer.observe(section));

    const setOpen = (next) => {
        open = next;
        render();
    };
    button.addEventListener('click', () => setOpen(!open));
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    window.addEventListener('keydown', (e) => {
        if (open && e.key === 'Escape') setOpen(false);
    });
}
