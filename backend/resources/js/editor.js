import { Editor, Node, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';

/**
 * Paragraf "Baca juga: <judul>" yang menautkan ke artikel lain.
 * Disimpan sebagai <p data-baca-juga><a href="...">Baca Juga: ...</a></p>.
 */
const BacaJuga = Node.create({
    name: 'bacaJuga',
    // Harus lebih tinggi dari Paragraph (1000) supaya <p data-baca-juga> tidak dibaca sebagai paragraf biasa.
    priority: 1100,
    group: 'block',
    atom: true,

    addAttributes() {
        return {
            href: { default: null },
            judul: { default: '' },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'p[data-baca-juga]',
                getAttrs: (el) => {
                    const link = el.querySelector('a');

                    return {
                        href: link?.getAttribute('href'),
                        judul: (link?.textContent ?? '').replace(/^Baca juga:\s*/i, ''),
                    };
                },
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['p', { 'data-baca-juga': '' }, ['a', mergeAttributes({ href: HTMLAttributes.href }), `Baca Juga: ${HTMLAttributes.judul}`]];
    },
});

function slugify(text) {
    return text
        .normalize('NFKD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function hitungKata(text) {
    const words = text.trim().split(/\s+/).filter(Boolean);

    return words.length;
}

function initEditor(form) {
    const wrapper = form.querySelector('[data-editor]');
    const source = wrapper.querySelector('[data-editor-source]');
    const toolbar = wrapper.querySelector('[data-editor-toolbar]');
    const blok = toolbar.querySelector('[data-editor-blok]');
    const fileInput = toolbar.querySelector('[data-editor-file]');
    const kata = wrapper.querySelector('[data-editor-kata]');
    const info = wrapper.querySelector('[data-editor-info]');
    const bacaJugaDialog = document.querySelector('[data-baca-juga]');

    const mount = document.createElement('div');
    source.after(mount);
    source.hidden = true;
    source.required = false;
    toolbar.hidden = false;

    const editor = new Editor({
        element: mount,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
                code: false,
                codeBlock: false,
                link: { openOnClick: false, autolink: true, defaultProtocol: 'https' },
            }),
            Image,
            BacaJuga,
        ],
        content: source.value,
        editorProps: {
            attributes: {
                class: 'isi-editor min-h-[420px] px-6 py-5 focus:outline-none',
                'aria-label': 'Isi artikel',
                'aria-multiline': 'true',
                role: 'textbox',
            },
        },
    });

    let berubah = false;

    const sync = () => {
        source.value = editor.isEmpty ? '' : editor.getHTML();
        kata.textContent = `${hitungKata(editor.getText())} kata`;
    };

    const refreshToolbar = () => {
        toolbar.querySelectorAll('[data-cmd]').forEach((button) => {
            const name = { bulletList: 'bulletList', orderedList: 'orderedList', blockquote: 'blockquote', link: 'link' }[button.dataset.cmd] ?? button.dataset.cmd;
            const aktif = ['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'blockquote', 'link'].includes(name) && editor.isActive(name);
            button.setAttribute('aria-pressed', aktif ? 'true' : 'false');
        });
        blok.value = editor.isActive('heading', { level: 2 }) ? 'h2' : editor.isActive('heading', { level: 3 }) ? 'h3' : 'paragraph';
    };

    editor.on('update', () => {
        sync();
        if (!berubah) {
            berubah = true;
            info.textContent = 'Ada perubahan yang belum disimpan';
        }
    });
    editor.on('selectionUpdate', refreshToolbar);
    editor.on('transaction', refreshToolbar);
    sync();
    refreshToolbar();

    blok.addEventListener('change', () => {
        const chain = editor.chain().focus();
        if (blok.value === 'paragraph') {
            chain.setParagraph().run();
        } else {
            chain.setHeading({ level: blok.value === 'h2' ? 2 : 3 }).run();
        }
    });

    const uploadImage = async (file) => {
        const body = new FormData();
        body.append('gambar', file);
        info.textContent = 'Mengunggah gambar...';

        const response = await fetch(form.dataset.uploadUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            },
            body,
        });

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            info.textContent = data.errors?.gambar?.[0] ?? 'Gambar gagal diunggah.';

            return;
        }

        const { url } = await response.json();
        editor.chain().focus().setImage({ src: url, alt: '' }).run();
        info.textContent = 'Gambar ditambahkan';
    };

    fileInput.addEventListener('change', () => {
        const [file] = fileInput.files;
        if (file) {
            uploadImage(file);
        }
        fileInput.value = '';
    });

    bacaJugaDialog?.addEventListener('close', () => {
        const pilih = bacaJugaDialog.querySelector('[data-baca-juga-pilih]');
        if (bacaJugaDialog.returnValue !== 'sisipkan' || !pilih) {
            editor.commands.focus();

            return;
        }

        const option = pilih.selectedOptions[0];
        editor.chain().focus().insertContent({ type: 'bacaJuga', attrs: { href: option.value, judul: option.textContent.trim() } }).run();
    });

    const commands = {
        bold: () => editor.chain().focus().toggleBold().run(),
        italic: () => editor.chain().focus().toggleItalic().run(),
        underline: () => editor.chain().focus().toggleUnderline().run(),
        bulletList: () => editor.chain().focus().toggleBulletList().run(),
        orderedList: () => editor.chain().focus().toggleOrderedList().run(),
        blockquote: () => editor.chain().focus().toggleBlockquote().run(),
        link: () => {
            const lama = editor.getAttributes('link').href ?? '';
            const url = window.prompt('Alamat tautan (kosongkan untuk menghapus tautan):', lama);
            if (url === null) {
                return;
            }
            if (url.trim() === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
            } else {
                editor.chain().focus().extendMarkRange('link').setLink({ href: url.trim() }).run();
            }
        },
        image: () => fileInput.click(),
        bacaJuga: () => bacaJugaDialog?.showModal(),
    };

    toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-cmd]');
        if (button) {
            commands[button.dataset.cmd]?.();
        }
    });

    form.addEventListener('submit', () => {
        sync();
        berubah = false;
    });

    window.addEventListener('beforeunload', (event) => {
        if (berubah) {
            event.preventDefault();
        }
    });

    return editor;
}

function initSlug(form) {
    const judul = form.querySelector('[data-judul]');
    const slug = form.querySelector('[data-slug]');
    const update = () => {
        slug.textContent = slugify(judul.value) || '...';
    };

    judul.addEventListener('input', update);
    if (!slug.textContent.trim()) {
        update();
    }
}

function initKategori(form) {
    const root = form.querySelector('[data-kategori]');
    const select = root.querySelector('[data-kategori-select]');
    const chips = root.querySelector('[data-kategori-chips]');
    const baru = root.querySelector('[data-kategori-baru]');
    const baruInput = root.querySelector('[data-kategori-baru-input]');
    const bukaBaru = root.querySelector('[data-kategori-baru-buka]');

    chips.hidden = false;

    const tandai = () => {
        chips.querySelectorAll('[data-kategori-chip]').forEach((chip) => {
            chip.setAttribute('aria-pressed', chip.dataset.kategoriChip === select.value ? 'true' : 'false');
        });
    };

    const pilih = (nama) => {
        select.value = nama;
        tandai();
    };

    chips.addEventListener('click', (event) => {
        const chip = event.target.closest('[data-kategori-chip]');
        if (chip) {
            pilih(chip.dataset.kategoriChip);
        }
    });
    select.addEventListener('change', tandai);

    bukaBaru.addEventListener('click', () => {
        baru.hidden = false;
        baruInput.focus();
    });

    const tambah = () => {
        const nama = baruInput.value.trim().replace(/\s+/g, ' ');
        if (!nama) {
            return;
        }

        const ada = [...select.options].find((option) => option.value.toLowerCase() === nama.toLowerCase());
        if (ada) {
            pilih(ada.value);
        } else {
            const label = nama.charAt(0).toUpperCase() + nama.slice(1);
            select.add(new Option(label, label));

            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'kategori-chip';
            chip.dataset.kategoriChip = label;
            chip.textContent = label;
            bukaBaru.before(chip);
            pilih(label);
        }

        baruInput.value = '';
        baru.hidden = true;
    };

    root.querySelector('[data-kategori-baru-tambah]').addEventListener('click', tambah);
    baruInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            tambah();
        }
    });
}

function initGambar(form) {
    const root = form.querySelector('[data-gambar]');
    const input = root.querySelector('[data-gambar-input]');
    const pratinjau = root.querySelector('[data-gambar-pratinjau]');
    const kosong = root.querySelector('[data-gambar-kosong]');
    const hapusInput = root.querySelector('[data-gambar-hapus-input]');

    const tampilkan = (src) => {
        pratinjau.hidden = !src;
        kosong.hidden = Boolean(src);
        if (src) {
            pratinjau.src = src;
        } else {
            pratinjau.removeAttribute('src');
        }
    };

    input.addEventListener('change', () => {
        const [file] = input.files;
        if (file) {
            hapusInput.value = '0';
            tampilkan(URL.createObjectURL(file));
        }
    });

    root.querySelector('[data-gambar-hapus]').addEventListener('click', () => {
        input.value = '';
        hapusInput.value = '1';
        tampilkan(null);
    });
}

function initHitungHuruf(form) {
    form.querySelectorAll('[data-hitung-huruf]').forEach((textarea) => {
        const jumlah = textarea.parentElement.querySelector('[data-hitung-huruf-jumlah]');
        textarea.addEventListener('input', () => {
            jumlah.textContent = textarea.value.length;
        });
    });
}

function initPratinjau(form, editor) {
    const dialog = document.querySelector('[data-pratinjau]');
    const gambar = dialog.querySelector('[data-pratinjau-gambar]');

    form.querySelector('[data-pratinjau-buka]').addEventListener('click', () => {
        const sumberGambar = form.querySelector('[data-gambar-pratinjau]');
        gambar.hidden = sumberGambar.hidden;
        if (!sumberGambar.hidden) {
            gambar.src = sumberGambar.src;
        }

        dialog.querySelector('[data-pratinjau-h1]').textContent = form.querySelector('[data-judul]').value || 'Tanpa judul';

        // Program tidak punya isian tanggal; tanggal terbitnya hari ini saat diterbitkan.
        const tanggal = form.querySelector('[name="tanggal_terbit"]')?.value ?? new Date().toISOString().slice(0, 10);
        const tanggalTampil = tanggal ? new Date(`${tanggal}T00:00:00`).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';
        dialog.querySelector('[data-pratinjau-meta]').textContent = [tanggalTampil, form.querySelector('[name="kategori"]').value].filter(Boolean).join(' · ');

        dialog.querySelector('[data-pratinjau-isi]').innerHTML = editor.getHTML();
        dialog.showModal();
    });
}

const form = document.querySelector('[data-artikel-form]');

if (form) {
    const editor = initEditor(form);
    initSlug(form);
    initKategori(form);
    initGambar(form);
    initHitungHuruf(form);
    initPratinjau(form, editor);
}
