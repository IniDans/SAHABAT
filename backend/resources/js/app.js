const SLIDE_INTERVAL = 5000;

/**
 * Hero slider beranda: autoplay, dot navigasi, dan jeda saat kursor/fokus berada di slider.
 */
function initSliders() {
    document.querySelectorAll('[data-slider]').forEach((slider) => {
        const slides = [...slider.querySelectorAll('[data-slide]')];
        const dots = [...slider.querySelectorAll('[data-slider-dot]')];

        if (slides.length < 2) {
            return;
        }

        let current = 0;
        let timer = null;

        const show = (index) => {
            current = (index + slides.length) % slides.length;

            slides.forEach((slide, i) => {
                const isActive = i === current;
                slide.classList.toggle('opacity-100', isActive);
                slide.classList.toggle('opacity-0', !isActive);
                slide.classList.toggle('pointer-events-none', !isActive);
                slide.toggleAttribute('aria-hidden', !isActive);
            });

            dots.forEach((dot, i) => {
                const isActive = i === current;
                dot.classList.toggle('bg-brand-red', isActive);
                dot.classList.toggle('bg-white/60', !isActive);

                if (isActive) {
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.removeAttribute('aria-current');
                }
            });
        };

        const stop = () => {
            clearInterval(timer);
            timer = null;
        };

        const start = () => {
            stop();

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            timer = setInterval(() => show(current + 1), SLIDE_INTERVAL);
        };

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                show(i);
                start();
            });
        });

        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        slider.addEventListener('focusin', stop);
        slider.addEventListener('focusout', start);

        start();
    });
}

/**
 * Navigasi: menu seluler, akordeon submenu, dan status aria dropdown desktop.
 */
function initNavigation() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('[data-nav-menu]');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const isOpen = menu.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
        });
    }

    document.querySelectorAll('[data-accordion-toggle]').forEach((button) => {
        const panel = button.parentElement.querySelector('[data-accordion-panel]');
        const icon = button.querySelector('[data-accordion-icon]');

        button.addEventListener('click', () => {
            const isOpen = panel.classList.toggle('hidden') === false;
            button.setAttribute('aria-expanded', String(isOpen));
            icon?.classList.toggle('rotate-180', !isOpen);
        });
    });

    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        const button = dropdown.querySelector('[data-dropdown-toggle]');
        const setExpanded = (isOpen) => button.setAttribute('aria-expanded', String(isOpen));

        dropdown.addEventListener('mouseenter', () => setExpanded(true));
        dropdown.addEventListener('mouseleave', () => setExpanded(dropdown.contains(document.activeElement)));
        dropdown.addEventListener('focusin', () => setExpanded(true));
        dropdown.addEventListener('focusout', (event) => {
            if (!dropdown.contains(event.relatedTarget)) {
                setExpanded(false);
            }
        });
        dropdown.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                button.focus();
                button.blur();
                setExpanded(false);
            }
        });
    });
}

/**
 * Select dengan atribut data-autosubmit langsung mengirim form-nya.
 */
function initAutosubmit() {
    document.querySelectorAll('[data-autosubmit]').forEach((select) => {
        select.addEventListener('change', () => select.form?.requestSubmit());
    });
}

/**
 * Tombol salin nomor rekening.
 */
function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach((button) => {
        const feedback = button.parentElement.querySelector('[data-copy-feedback]');
        let timer = null;

        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
            } catch {
                return;
            }

            if (!feedback) {
                return;
            }

            feedback.classList.remove('hidden');
            clearTimeout(timer);
            timer = setTimeout(() => feedback.classList.add('hidden'), 2000);
        });
    });
}

/**
 * Pencarian pada tabel donatur di sidebar artikel/program.
 */
function initDonorSearch() {
    document.querySelectorAll('[data-donor-table]').forEach((wrapper) => {
        const input = wrapper.querySelector('[data-donor-search]');
        const rows = [...wrapper.querySelectorAll('[data-donor-row]')];

        input?.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();

            rows.forEach((row) => {
                row.hidden = !row.textContent.toLowerCase().includes(query);
            });
        });
    });
}

/**
 * Logo bank hanya ditampilkan untuk metode pembayaran yang logonya tersedia.
 */
function initPaymentLogo() {
    const select = document.querySelector('[data-payment-select]');
    const logo = document.querySelector('[data-payment-logo]');

    if (!select || !logo) {
        return;
    }

    const update = () => {
        logo.hidden = select.value !== 'BCA';
    };

    select.addEventListener('change', update);
    update();
}

/**
 * Lightbox galeri memakai elemen <dialog>.
 */
function initLightbox() {
    const dialog = document.querySelector('[data-lightbox-dialog]');
    const image = dialog?.querySelector('[data-lightbox-image]');

    if (!dialog || !image || typeof dialog.showModal !== 'function') {
        return;
    }

    document.querySelectorAll('[data-lightbox]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const thumbnail = link.querySelector('img');
            image.src = link.href;
            image.alt = thumbnail?.alt ?? '';
            dialog.showModal();
        });
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
}

/**
 * Tombol tampilkan/sembunyikan password di halaman login admin.
 */
function initPasswordToggle() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = button.parentElement.querySelector('[data-password-input]');
        const showIcon = button.querySelector('[data-icon-show]');
        const hideIcon = button.querySelector('[data-icon-hide]');

        button.addEventListener('click', () => {
            const isVisible = input.type === 'password';
            input.type = isVisible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(isVisible));
            button.setAttribute('aria-label', isVisible ? 'Sembunyikan password' : 'Tampilkan password');
            showIcon?.classList.toggle('hidden', isVisible);
            hideIcon?.classList.toggle('hidden', !isVisible);
        });
    });
}

/**
 * Sidebar admin pada layar kecil.
 */
function initAdminSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (!sidebar || !toggle) {
        return;
    }

    const setOpen = (isOpen) => {
        sidebar.classList.toggle('-translate-x-full', !isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));

        if (backdrop) {
            backdrop.hidden = !isOpen;
        }
    };

    toggle.addEventListener('click', () => setOpen(sidebar.classList.contains('-translate-x-full')));
    backdrop?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
}

/**
 * Form dengan atribut data-confirm meminta konfirmasi sebelum dikirim, mis. tombol hapus.
 */
function initConfirmForms() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSliders();
    initNavigation();
    initAutosubmit();
    initCopyButtons();
    initDonorSearch();
    initPaymentLogo();
    initLightbox();
    initPasswordToggle();
    initAdminSidebar();
    initConfirmForms();
});
