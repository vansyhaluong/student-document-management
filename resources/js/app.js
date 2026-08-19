const sidebar = document.querySelector('#app-sidebar');
const sidebarToggle = document.querySelector('#sidebar-toggle');
const sidebarOverlay = document.querySelector('#sidebar-overlay');
const sidebarClose = document.querySelector('#sidebar-close');

const setSidebarOpen = (open) => {
    if (!sidebar || !sidebarToggle || !sidebarOverlay) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full', !open);
    sidebarOverlay.classList.toggle('hidden', !open);
    sidebarOverlay.setAttribute('aria-hidden', open ? 'false' : 'true');
    sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.classList.toggle('overflow-hidden', open);

    if (open) {
        sidebarClose?.focus();
    }
};

sidebarToggle?.addEventListener('click', () => {
    setSidebarOpen(sidebarToggle.getAttribute('aria-expanded') !== 'true');
});

sidebarOverlay?.addEventListener('click', () => setSidebarOpen(false));
sidebarClose?.addEventListener('click', () => {
    setSidebarOpen(false);
    sidebarToggle?.focus();
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebarOpen(false);
        sidebarToggle?.focus();
    }
});

window.addEventListener('resize', () => {
    if (window.matchMedia('(min-width: 1024px)').matches) {
        setSidebarOpen(false);
    }
});

document.querySelectorAll('[data-status-form]').forEach((form) => {
    const select = form.querySelector('[data-status-select]');
    const invalidReason = form.querySelector('[data-invalid-reason]');
    const textarea = invalidReason?.querySelector('textarea');
    const syncInvalidReason = () => {
        const isInvalid = select?.value === 'invalid';
        invalidReason?.classList.toggle('hidden', !isInvalid);
        if (textarea) textarea.required = isInvalid;
    };
    select?.addEventListener('change', () => {
        syncInvalidReason();

        if (
            form.hasAttribute('data-status-autosubmit')
            && select?.value
            && select.value !== 'invalid'
        ) {
            form.submit();
        }
    });
    syncInvalidReason();
});

const publicNav = document.querySelector('.public-nav');
const publicGuideSection = document.querySelector('#huong-dan');

if (publicNav) {
    const publicNavLinks = publicNav.querySelectorAll('[data-public-nav]');

    const setPublicNavActive = (target) => {
        publicNavLinks.forEach((link) => {
            const isActive = link.getAttribute('data-public-nav') === target;
            link.classList.toggle('public-nav-link-active', isActive);

            if (isActive) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const syncPublicNavFromLocation = () => {
        setPublicNavActive(window.location.hash === '#huong-dan' ? 'guide' : 'home');
    };

    syncPublicNavFromLocation();
    window.addEventListener('hashchange', syncPublicNavFromLocation);

    if (publicGuideSection && 'IntersectionObserver' in window) {
        const guideObserver = new IntersectionObserver((entries) => {
            const entry = entries[0];

            if (!entry) {
                return;
            }

            if (entry.isIntersecting) {
                setPublicNavActive('guide');
                return;
            }

            if (window.scrollY + 80 < publicGuideSection.offsetTop) {
                setPublicNavActive('home');
            }
        }, {
            rootMargin: '-25% 0px -55% 0px',
            threshold: 0.1,
        });

        guideObserver.observe(publicGuideSection);
    }
}

document.querySelector('[data-copy-document-code]')?.addEventListener('click', async () => {
    const code = document.querySelector('[data-public-document-code]')?.textContent?.trim();
    const status = document.querySelector('[data-copy-status]');

    if (!code) {
        return;
    }

    if (!navigator.clipboard) {
        if (status) {
            status.textContent = 'Vui lòng sao chép mã hồ sơ thủ công.';
            status.classList.remove('hidden');
        }
        return;
    }

    try {
        await navigator.clipboard.writeText(code);
        if (status) {
            status.textContent = 'Đã sao chép mã hồ sơ.';
            status.classList.remove('hidden');
        }
    } catch {
        if (status) {
            status.textContent = 'Không thể sao chép tự động. Vui lòng sao chép mã thủ công.';
            status.classList.remove('hidden');
        }
    }
});
