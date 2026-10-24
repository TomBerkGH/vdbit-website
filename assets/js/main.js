// De class "js" op <html> wordt al in de <head> gezet (includes/header.php), vóór het tekenen.

document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;

    // Licht/donker thema wisselen en de keuze onthouden.
    const themeToggle = document.querySelector('.theme-toggle');
    const syncThemeToggle = () => themeToggle?.setAttribute('aria-pressed', String(root.dataset.theme === 'dark'));
    syncThemeToggle();

    themeToggle?.addEventListener('click', () => {
        const theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        root.dataset.theme = theme;
        syncThemeToggle();
        try { localStorage.setItem('theme', theme); } catch (e) {}
    });

    // Volg de systeeminstelling zolang de bezoeker zelf niets gekozen heeft.
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        let saved = null;
        try { saved = localStorage.getItem('theme'); } catch (e) {}
        if (!saved) {
            root.dataset.theme = event.matches ? 'dark' : 'light';
            syncThemeToggle();
        }
    });

    // Mobiel menu.
    const nav = document.getElementById('nav');
    const navToggle = document.querySelector('.nav-toggle');

    const setMenu = (open) => {
        nav.classList.toggle('is-open', open);
        navToggle.setAttribute('aria-expanded', String(open));
        navToggle.setAttribute('aria-label', open ? 'Menu sluiten' : 'Menu openen');
    };

    navToggle?.addEventListener('click', () => {
        const open = !nav.classList.contains('is-open');
        setMenu(open);
        // Toetsenbordgebruikers komen direct bij de eerste link uit.
        if (open) nav.querySelector('a')?.focus();
    });
    nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && nav?.classList.contains('is-open')) {
            setMenu(false);
            navToggle.focus();
        }
    });

    // Rand onder de header zodra er gescrold wordt.
    const header = document.querySelector('.site-header');
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // Contactformulier: bij een fout het getypte bericht terugzetten, en de melding in beeld brengen.
    const form = document.querySelector('.contact-form');
    const status = new URLSearchParams(location.search).get('contact');
    const draftKey = 'contact-draft';

    if (form) {
        form.addEventListener('submit', () => {
            const draft = {};
            form.querySelectorAll('input[name]:not([name="website"]), textarea[name]').forEach((field) => { draft[field.name] = field.value; });
            try { sessionStorage.setItem(draftKey, JSON.stringify(draft)); } catch (e) {}
        });

        try {
            if (status === 'ok') {
                sessionStorage.removeItem(draftKey);
            } else if (status) {
                const draft = JSON.parse(sessionStorage.getItem(draftKey) || '{}');
                Object.entries(draft).forEach(([name, value]) => {
                    const field = form.elements.namedItem(name);
                    if (field && 'value' in field) field.value = value;
                });
            }
        } catch (e) {}

        form.querySelector('.form-message')?.focus();
    }

    // Elementen laten infaden zodra ze in beeld komen.
    const revealItems = document.querySelectorAll('.reveal');

    if (!('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px' });

    revealItems.forEach((item) => observer.observe(item));
});
