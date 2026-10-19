// Search and the status filter run in the browser so they're instant, and they're
// kept in the address bar, so coming back from an enquiry returns to the same view.
// Clicking anywhere on a row opens that enquiry's page.
export function inbox() {
    const root = document.querySelector('[data-inbox]');
    if (!root) return;

    const search = root.querySelector('[data-inbox-search]');
    const buttons = [...root.querySelectorAll('[data-inbox-filter]')];
    const rows = [...root.querySelectorAll('[data-inbox-row]')];
    const empty = root.querySelector('[data-inbox-empty]');
    const announce = root.querySelector('[data-inbox-announce]');

    let filter = buttons.find((b) => b.getAttribute('aria-pressed') === 'true')?.dataset.inboxFilter ?? '';
    let query = search.value;

    const show = (nextFilter, nextQuery) => {
        filter = nextFilter;
        query = nextQuery;

        const q = query.trim().toLowerCase();
        const matching = q ? rows.filter((row) => row.dataset.search.includes(q)) : rows;
        const list = filter ? matching.filter((row) => row.dataset.status === filter) : matching;

        for (const row of rows) row.hidden = !list.includes(row);
        for (const button of buttons) {
            const status = button.dataset.inboxFilter;
            button.setAttribute('aria-pressed', String(status === filter));
            button.querySelector('[data-inbox-count]').textContent = status
                ? matching.filter((row) => row.dataset.status === status).length
                : matching.length;
        }

        const label = buttons.find((b) => b.dataset.inboxFilter === filter)?.dataset.label.toLowerCase();
        empty.hidden = list.length > 0;
        empty.querySelector('[data-inbox-empty-text]').textContent = q
            ? `Nothing matches “${query.trim()}”.`
            : `No ${filter ? `${label} ` : ''}enquiries.`;
        announce.textContent = `${list.length} ${list.length === 1 ? 'enquiry' : 'enquiries'} shown`;

        const params = new URLSearchParams();
        if (filter) params.set('status', filter);
        if (query.trim()) params.set('q', query.trim());
        const qs = params.toString();
        window.history.replaceState(null, '', qs ? `?${qs}` : window.location.pathname);
    };

    search.addEventListener('input', () => show(filter, search.value));
    for (const button of buttons) {
        button.addEventListener('click', () => show(button.dataset.inboxFilter, query));
    }

    root.addEventListener('click', (event) => {
        const row = event.target.closest('[data-inbox-row]');
        if (!row) return;
        // Links in the row (name, email) do their own thing, and selecting text isn't a click
        if (event.target.closest('a') || window.getSelection()?.toString()) return;
        if (event.metaKey || event.ctrlKey) window.open(row.dataset.href, '_blank');
        else window.location.assign(row.dataset.href);
    });
}
