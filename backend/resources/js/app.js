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
 * Tabel donatur di sidebar artikel/program: cari nama, saring program, dan bagi per halaman.
 */
function initDonorSearch() {
    document.querySelectorAll('[data-donor-table]').forEach((wrapper) => {
        const input = wrapper.querySelector('[data-donor-search]');
        const program = wrapper.querySelector('[data-donor-program]');
        const rows = [...wrapper.querySelectorAll('[data-donor-row]')];
        const info = wrapper.querySelector('[data-donor-info]');
        const kosong = wrapper.querySelector('[data-donor-kosong]');
        const tombol = Object.fromEntries([...wrapper.querySelectorAll('[data-donor-halaman]')].map((button) => [button.dataset.donorHalaman, button]));
        const perHalaman = Number(wrapper.dataset.perHalaman) || 5;
        let halaman = 1;

        const render = () => {
            const query = (input?.value ?? '').trim().toLowerCase();
            const dipilih = program?.value ?? '';
            const cocok = rows.filter((row) => (!dipilih || row.dataset.program === dipilih) && row.textContent.toLowerCase().includes(query));
            const jumlahHalaman = Math.max(1, Math.ceil(cocok.length / perHalaman));
            halaman = Math.min(halaman, jumlahHalaman);
            const awal = (halaman - 1) * perHalaman;
            const tampil = cocok.slice(awal, awal + perHalaman);

            rows.forEach((row) => {
                row.hidden = !tampil.includes(row);
            });

            kosong.hidden = cocok.length > 0;
            info.textContent = cocok.length ? `Menampilkan ${awal + 1}-${awal + tampil.length} dari ${cocok.length}` : 'Menampilkan 0 dari 0';
            tombol.pertama.disabled = tombol.sebelumnya.disabled = halaman === 1;
            tombol.terakhir.disabled = tombol.berikutnya.disabled = halaman === jumlahHalaman;

            tombol.pertama.onclick = () => { halaman = 1; render(); };
            tombol.sebelumnya.onclick = () => { halaman -= 1; render(); };
            tombol.berikutnya.onclick = () => { halaman += 1; render(); };
            tombol.terakhir.onclick = () => { halaman = jumlahHalaman; render(); };
        };

        const ulang = () => {
            halaman = 1;
            render();
        };

        input?.addEventListener('input', ulang);
        program?.addEventListener('change', ulang);
        render();
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

/**
 * Kotak unggah galeri: tampilkan jumlah foto terpilih dan terima foto yang diseret ke kotak.
 */
function initGaleriUnggah() {
    const form = document.querySelector('[data-galeri-unggah]');
    if (!form) {
        return;
    }

    const input = form.querySelector('[data-galeri-berkas]');
    const dropzone = form.querySelector('[data-galeri-dropzone]');
    const info = form.querySelector('[data-galeri-terpilih]');
    const batasByte = 5 * 1024 * 1024;

    const tampilkanInfo = () => {
        const berkas = [...input.files];
        const terlaluBesar = berkas.filter((file) => file.size > batasByte).length;
        const totalMb = berkas.reduce((total, file) => total + file.size, 0) / 1024 / 1024;

        info.textContent = berkas.length
            ? `${berkas.length} foto dipilih (${totalMb.toLocaleString('id-ID', { maximumFractionDigits: 1 })} MB)` + (terlaluBesar ? `, ${terlaluBesar} melebihi 5 MB` : '')
            : '';
        info.classList.toggle('text-[#c0262f]', terlaluBesar > 0);
    };

    input.addEventListener('change', tampilkanInfo);

    ['dragenter', 'dragover'].forEach((nama) => dropzone.addEventListener(nama, (event) => {
        event.preventDefault();
        dropzone.dataset.seret = '';
    }));
    ['dragleave', 'drop'].forEach((nama) => dropzone.addEventListener(nama, () => delete dropzone.dataset.seret));
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        if (event.dataTransfer.files.length) {
            input.files = event.dataTransfer.files;
            tampilkanInfo();
        }
    });
}

/**
 * Grid galeri admin: centang untuk hapus banyak, seret atau tombol panah untuk mengubah urutan.
 */
function initGaleriGrid() {
    const galeri = document.querySelector('[data-galeri]');
    const grid = galeri?.querySelector('[data-galeri-grid]');
    if (!grid) {
        return;
    }

    const items = () => [...grid.querySelectorAll('[data-galeri-item]')];
    const urutanAwal = items().map((item) => item.dataset.id).join(',');
    const formUrutan = galeri.querySelector('[data-galeri-form-urutan]');
    const nomorAwal = Number(grid.querySelector('[data-galeri-nomor]')?.textContent ?? 1);

    const perbaruiUrutan = () => {
        const sekarang = items();
        const berubah = sekarang.map((item) => item.dataset.id).join(',') !== urutanAwal;

        sekarang.forEach((item, index) => {
            item.querySelector('[data-galeri-nomor]').textContent = nomorAwal + index;
            item.querySelector('[data-galeri-geser="-1"]').disabled = index === 0;
            item.querySelector('[data-galeri-geser="1"]').disabled = index === sekarang.length - 1;
        });

        ['[data-galeri-berubah]', '[data-galeri-simpan]', '[data-galeri-batal]'].forEach((selector) => {
            formUrutan.querySelector(selector).classList.toggle('hidden', !berubah);
        });
        formUrutan.querySelector('[data-galeri-petunjuk]').classList.toggle('hidden', berubah);
    };

    formUrutan.addEventListener('submit', () => {
        formUrutan.querySelectorAll('input[name="urutan[]"]').forEach((input) => input.remove());
        items().forEach((item) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'urutan[]';
            input.value = item.dataset.id;
            formUrutan.append(input);
        });
    });

    grid.addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-galeri-geser]');
        if (!tombol) {
            return;
        }

        const item = tombol.closest('[data-galeri-item]');
        if (tombol.dataset.galeriGeser === '-1') {
            item.previousElementSibling?.before(item);
        } else {
            item.nextElementSibling?.after(item);
        }
        perbaruiUrutan();
        tombol.focus();
    });

    let diseret = null;
    grid.addEventListener('dragstart', (event) => {
        diseret = event.target.closest('[data-galeri-item]');
        if (!diseret || event.target.closest('input, button, a')) {
            diseret = null;
            return;
        }
        diseret.dataset.diseret = '';
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', diseret.dataset.id);
    });
    grid.addEventListener('dragover', (event) => {
        const sasaran = event.target.closest('[data-galeri-item]');
        if (!diseret || !sasaran) {
            return;
        }
        event.preventDefault();
        items().forEach((item) => delete item.dataset.sasaran);
        if (sasaran !== diseret) {
            sasaran.dataset.sasaran = '';
        }
    });
    grid.addEventListener('drop', (event) => {
        const sasaran = event.target.closest('[data-galeri-item]');
        if (!diseret || !sasaran || sasaran === diseret) {
            return;
        }
        event.preventDefault();
        const semua = items();
        if (semua.indexOf(diseret) < semua.indexOf(sasaran)) {
            sasaran.after(diseret);
        } else {
            sasaran.before(diseret);
        }
        perbaruiUrutan();
    });
    grid.addEventListener('dragend', () => {
        items().forEach((item) => {
            delete item.dataset.diseret;
            delete item.dataset.sasaran;
        });
        diseret = null;
    });

    const pilihSemua = galeri.querySelector('[data-galeri-pilih-semua]');
    const pilihan = [...grid.querySelectorAll('[data-galeri-pilih]')];
    const jumlah = galeri.querySelector('[data-galeri-jumlah-pilih]');
    const hapus = galeri.querySelector('[data-galeri-hapus]');

    const perbaruiPilihan = () => {
        const dipilih = pilihan.filter((kotak) => kotak.checked).length;
        jumlah.textContent = dipilih;
        hapus.disabled = dipilih === 0;
        pilihSemua.checked = dipilih > 0 && dipilih === pilihan.length;
        pilihSemua.indeterminate = dipilih > 0 && dipilih < pilihan.length;
    };

    if (pilihSemua) {
        pilihSemua.addEventListener('change', () => {
            pilihan.forEach((kotak) => {
                kotak.checked = pilihSemua.checked;
            });
            perbaruiPilihan();
        });
        pilihan.forEach((kotak) => kotak.addEventListener('change', perbaruiPilihan));
        perbaruiPilihan();
    }

    perbaruiUrutan();
}

/**
 * Dialog panel admin: tombol data-dialog-buka="id" membuka, data-dialog-tutup dan klik latar menutup.
 * Dialog dengan atribut data-buka langsung terbuka, mis. setelah simpan gagal validasi.
 */
function initAdminDialogs() {
    document.querySelectorAll('[data-dialog-buka]').forEach((button) => {
        button.addEventListener('click', () => document.getElementById(button.dataset.dialogBuka)?.showModal());
    });

    document.querySelectorAll('dialog:not([data-lightbox-dialog])').forEach((dialog) => {
        if (typeof dialog.showModal !== 'function') {
            return;
        }

        dialog.querySelectorAll('[data-dialog-tutup]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });

        if (dialog.hasAttribute('data-buka')) {
            dialog.showModal();
        }
    });
}

/**
 * Dialog tambah/edit data anak: isi form dari data-anak tombol yang diklik.
 */
function initAnakForm() {
    const dialog = document.querySelector('[data-anak-dialog]');
    const form = dialog?.querySelector('[data-anak-form]');

    if (!form || typeof dialog.showModal !== 'function') {
        return;
    }

    const method = form.querySelector('[data-anak-method]');
    const nikLama = form.querySelector('[data-anak-nik-lama]');
    const judul = form.querySelector('[data-anak-judul]');
    const fields = [...form.elements].filter((field) => field.name && !field.name.startsWith('_') && field.type !== 'hidden');

    document.querySelectorAll('[data-anak-buka]').forEach((button) => {
        button.addEventListener('click', () => {
            const data = JSON.parse(button.dataset.anak || '{}');
            const edit = Boolean(data.NIK);

            form.action = button.dataset.url;
            method.disabled = !edit;
            nikLama.value = edit ? data.NIK : '';
            judul.textContent = edit ? judul.dataset.judulEdit : judul.dataset.judulTambah;

            fields.forEach((field) => {
                field.value = data[field.name] ?? '';
                field.removeAttribute('aria-invalid');
            });
            form.querySelectorAll('[id$="-error"]').forEach((error) => error.remove());

            dialog.showModal();
            fields[0]?.focus();
        });
    });
}

/**
 * Dialog export data anak: hitung kolom terpilih dan aktifkan pilihan "Data yang dipilih"
 * bila ada baris yang dicentang di tabel.
 */
function initEksporAnak() {
    const form = document.querySelector('[data-ekspor-form]');

    if (!form) {
        return;
    }

    const kolom = [...form.querySelectorAll('[data-ekspor-kolom]')];
    const jumlahKolom = form.querySelector('[data-ekspor-jumlah-kolom]');
    const kirim = form.querySelector('[data-ekspor-kirim]');
    const pilihSemuaKolom = form.querySelector('[data-ekspor-pilih-semua]');
    const cakupanDipilih = form.querySelector('[data-ekspor-dipilih]');
    const cakupanSemua = form.querySelector('[name="cakupan"][value="semua"]');
    const infoDipilih = form.querySelector('[data-ekspor-info-dipilih]');
    const baris = [...document.querySelectorAll('[data-anak-pilih]')];
    const pilihSemuaBaris = document.querySelector('[data-anak-pilih-semua]');

    const perbaruiKolom = () => {
        const jumlah = kolom.filter((checkbox) => checkbox.checked).length;
        jumlahKolom.textContent = jumlah;
        kirim.disabled = jumlah === 0;
        pilihSemuaKolom.textContent = jumlah === kolom.length ? 'Kosongkan' : 'Pilih semua';
    };

    const perbaruiBaris = () => {
        const jumlah = baris.filter((checkbox) => checkbox.checked).length;
        const sebelumnyaKosong = cakupanDipilih.disabled;

        cakupanDipilih.disabled = jumlah === 0;
        infoDipilih.textContent = jumlah ? `${jumlah} anak dicentang di daftar` : infoDipilih.dataset.kosong;

        if (jumlah === 0 && cakupanDipilih.checked) {
            cakupanSemua.checked = true;
        } else if (jumlah > 0 && sebelumnyaKosong) {
            cakupanDipilih.checked = true;
        }

        if (pilihSemuaBaris) {
            pilihSemuaBaris.checked = jumlah > 0 && jumlah === baris.length;
            pilihSemuaBaris.indeterminate = jumlah > 0 && jumlah < baris.length;
        }
    };

    pilihSemuaKolom.addEventListener('click', () => {
        const semua = kolom.every((checkbox) => checkbox.checked);
        kolom.forEach((checkbox) => {
            checkbox.checked = !semua;
        });
        perbaruiKolom();
    });
    kolom.forEach((checkbox) => checkbox.addEventListener('change', perbaruiKolom));

    baris.forEach((checkbox) => checkbox.addEventListener('change', perbaruiBaris));
    pilihSemuaBaris?.addEventListener('change', () => {
        baris.forEach((checkbox) => {
            checkbox.checked = pilihSemuaBaris.checked;
        });
        perbaruiBaris();
    });

    // Unduhan tidak berpindah halaman, jadi tutup dialognya setelah form terkirim.
    form.addEventListener('submit', () => {
        window.setTimeout(() => form.closest('dialog')?.close(), 300);
    });

    perbaruiKolom();
    perbaruiBaris();
}

/**
 * Grafik tren berat badan: garis bantu dan tooltip saat bulan disorot kursor atau fokus keyboard.
 */
function initGrafikTren() {
    document.querySelectorAll('[data-grafik]').forEach((grafik) => {
        const svg = grafik.querySelector('svg');
        const tooltip = grafik.querySelector('[data-grafik-tooltip]');
        const titik = [...grafik.querySelectorAll('[data-grafik-titik]')];

        if (!svg || !tooltip) {
            return;
        }

        const lebarViewBox = svg.viewBox.baseVal.width;

        const sembunyikan = () => {
            tooltip.classList.add('hidden');
            titik.forEach((item) => item.querySelector('[data-grafik-garis]').classList.add('opacity-0'));
        };

        const tampilkan = (item) => {
            titik.forEach((lain) => lain.querySelector('[data-grafik-garis]').classList.toggle('opacity-0', lain !== item));
            tooltip.querySelector('[data-grafik-tooltip-judul]').textContent = item.dataset.judul;
            tooltip.querySelector('[data-grafik-tooltip-isi]').textContent = item.dataset.isi;
            tooltip.classList.remove('hidden');

            const lebarGrafik = svg.getBoundingClientRect().width;
            const tengah = (Number(item.dataset.x) / lebarViewBox) * lebarGrafik;
            const kiri = Math.min(Math.max(tengah - tooltip.offsetWidth / 2, 0), lebarGrafik - tooltip.offsetWidth);
            tooltip.style.transform = `translate(${kiri}px, -8px)`;
        };

        titik.forEach((item) => {
            item.addEventListener('mouseenter', () => tampilkan(item));
            item.addEventListener('focus', () => tampilkan(item));
        });
        svg.addEventListener('mouseleave', sembunyikan);
        grafik.addEventListener('focusout', (event) => {
            if (!grafik.contains(event.relatedTarget)) {
                sembunyikan();
            }
        });
        grafik.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                sembunyikan();
            }
        });
    });
}

/**
 * Daftar baris berulang (x-admin.repeater): tombol tambah menyalin <template>, tombol hapus membuang barisnya.
 */
function initRepeater() {
    document.querySelectorAll('[data-repeater]').forEach((repeater) => {
        const daftar = repeater.querySelector('[data-repeater-daftar]');
        const template = repeater.querySelector('template[data-repeater-template]');
        let indeks = Number(repeater.dataset.repeaterIndeks);

        repeater.querySelector('[data-repeater-tambah]')?.addEventListener('click', () => {
            const baris = template.content.firstElementChild.cloneNode(true);

            baris.querySelectorAll('[id], [for], [name]').forEach((elemen) => {
                ['id', 'for', 'name'].forEach((atribut) => {
                    if (elemen.hasAttribute(atribut)) {
                        elemen.setAttribute(atribut, elemen.getAttribute(atribut).replace('__INDEX__', String(indeks)));
                    }
                });
            });
            indeks += 1;

            daftar.append(baris);
            baris.querySelector('input, textarea')?.focus();
        });

        daftar.addEventListener('click', (event) => {
            event.target.closest('[data-repeater-hapus]')?.closest('[data-repeater-baris]')?.remove();
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
    initGaleriUnggah();
    initGaleriGrid();
    initAdminDialogs();
    initAnakForm();
    initEksporAnak();
    initGrafikTren();
    initRepeater();
});
