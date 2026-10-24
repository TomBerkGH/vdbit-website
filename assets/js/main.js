document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;

    // Licht/donker thema wisselen en de keuze onthouden.
    document.querySelector('.theme-toggle')?.addEventListener('click', () => {
        const theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        root.dataset.theme = theme;
        try { localStorage.setItem('theme', theme); } catch (e) {}
    });

    // Volg de systeeminstelling zolang de bezoeker zelf niets gekozen heeft.
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        let saved = null;
        try { saved = localStorage.getItem('theme'); } catch (e) {}
        if (!saved) {
            root.dataset.theme = event.matches ? 'dark' : 'light';
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

    navToggle?.addEventListener('click', () => setMenu(!nav.classList.contains('is-open')));
    nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setMenu(false);
    });

    // Rand onder de header zodra er gescrold wordt.
    const header = document.querySelector('.site-header');
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

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
