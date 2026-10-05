@php
    use App\Enums\StatusBerita;

    $isEdit = $berita->exists;
    $judulHalaman = $isEdit ? 'Edit artikel' : 'Tulis artikel';
    $status = old('status', $berita->status?->value ?? StatusBerita::Draft->value);
    $kategoriTerpilih = old('kategori', $berita->kategori);
    $pilihanKategori = collect($daftarKategori)->push($kategoriTerpilih)->filter()->unique()->values();
    $ringkasan = old('ringkasan', $berita->ringkasan);
@endphp

<x-layouts.admin :title="$judulHalaman">
    <form action="{{ $isEdit ? route('admin.berita.update', $berita) : route('admin.berita.store') }}" method="POST" enctype="multipart/form-data" data-artikel-form data-upload-url="{{ route('admin.berita.gambar') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1 text-[13px] text-slate" aria-label="Breadcrumb">
                    <a href="{{ route('admin.berita.index') }}" class="inline-flex items-center gap-1 hover:text-[#1e2328]">
                        <x-admin.icon name="arrow-left" width="12" height="12" />
                        Kelola artikel kegiatan
                    </a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">{{ $judulHalaman }}</span>
                </nav>
                <h1 class="mt-1 text-2xl font-semibold">{{ $judulHalaman }}</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="admin-btn admin-btn-secondary font-semibold" data-pratinjau-buka>Pratinjau</button>
                <button type="submit" name="aksi" value="draf" class="admin-btn admin-btn-secondary font-semibold">Simpan draf</button>
                <button type="submit" name="aksi" value="terbitkan" class="admin-btn admin-btn-primary px-6 font-semibold">Terbitkan</button>
            </div>
        </div>

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="admin-card space-y-5 p-6">
                <x-admin.field label="Judul artikel" name="judul">
                    <input id="judul" name="judul" type="text" value="{{ old('judul', $berita->judul) }}" required maxlength="255" placeholder="Tulis judul artikel" class="admin-input h-12 text-base font-semibold" data-judul @error('judul') aria-invalid="true" aria-describedby="judul-error" @enderror>
                </x-admin.field>

                <div>
                    <p class="admin-label">Alamat tautan</p>
                    <p class="flex h-10 items-center overflow-hidden rounded-lg border border-[#e5e5e5] bg-[#f3f4f2] px-3 text-[13px] whitespace-nowrap">
                        <span class="text-slate">/artikel/</span><span class="truncate" data-slug>{{ $berita->slug }}</span>
                    </p>
                    <p class="mt-1 text-xs text-slate">Dibuat otomatis dari judul.</p>
                </div>

                <div>
                    <label for="isi" class="admin-label">Isi artikel</label>
                    <div class="overflow-hidden rounded-lg border border-[#d9dcd9] focus-within:border-footer focus-within:ring-4 focus-within:ring-footer/15" data-editor>
                        <div class="flex flex-wrap items-center gap-0.5 border-b border-[#e5e5e5] bg-[#f3f4f2] px-2 py-1.5" role="toolbar" aria-label="Format teks" aria-controls="isi" hidden data-editor-toolbar>
                            <select class="mr-3 h-8 w-28 rounded-lg border border-[#d9dcd9] bg-white px-3 text-[13px]" aria-label="Gaya teks" data-editor-blok>
                                <option value="paragraph">Paragraf</option>
                                <option value="h2">Judul</option>
                                <option value="h3">Subjudul</option>
                            </select>
                            <button type="button" class="editor-btn font-bold" data-cmd="bold" aria-label="Tebal" title="Tebal (Ctrl+B)">B</button>
                            <button type="button" class="editor-btn italic" data-cmd="italic" aria-label="Miring" title="Miring (Ctrl+I)">I</button>
                            <button type="button" class="editor-btn underline" data-cmd="underline" aria-label="Garis bawah" title="Garis bawah (Ctrl+U)">U</button>
                            <span class="mx-1 h-5 w-px bg-[#d9dcd9]" aria-hidden="true"></span>
                            <button type="button" class="editor-btn" data-cmd="bulletList">Daftar</button>
                            <button type="button" class="editor-btn" data-cmd="orderedList">Nomor</button>
                            <button type="button" class="editor-btn" data-cmd="blockquote">Kutipan</button>
                            <span class="mx-1 h-5 w-px bg-[#d9dcd9]" aria-hidden="true"></span>
                            <button type="button" class="editor-btn" data-cmd="link">Tautan</button>
                            <button type="button" class="editor-btn" data-cmd="image">Gambar</button>
                            <button type="button" class="editor-btn" data-cmd="bacaJuga">Baca juga</button>
                            <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" data-editor-file>
                        </div>
                        <textarea id="isi" name="isi" rows="18" required class="block w-full border-0 p-4 text-sm leading-relaxed focus:outline-none" data-editor-source @error('isi') aria-invalid="true" aria-describedby="isi-error" @enderror>{{ old('isi', $berita->isi) }}</textarea>
                        <div class="flex justify-between border-t border-[#e5e5e5] px-4 py-2 text-xs text-slate">
                            <span data-editor-kata></span>
                            <span data-editor-info aria-live="polite">{{ $isEdit ? 'Terakhir disimpan '.$berita->updated_at->locale('id')->translatedFormat('j M Y, H.i') : 'Belum disimpan' }}</span>
                        </div>
                    </div>
                    @error('isi')
                        <p id="isi-error" class="mt-1 text-xs text-[#c0262f]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-4">
                <section class="admin-card space-y-4 p-5" aria-labelledby="penerbitan-judul">
                    <h2 id="penerbitan-judul" class="text-[15px] font-semibold">Penerbitan</h2>
                    <fieldset>
                        <legend class="admin-label">Status</legend>
                        <div class="grid grid-cols-2 gap-1 rounded-lg bg-[#eef0ee] p-1 text-[13px]">
                            @foreach ([StatusBerita::Draft->value => 'Draf', StatusBerita::Terbit->value => 'Terbit'] as $nilai => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $nilai }}" class="peer sr-only" @checked($status === $nilai)>
                                    <span class="block rounded-md py-1.5 text-center text-slate transition peer-checked:bg-white peer-checked:font-semibold peer-checked:text-footer peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-footer/40">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <x-admin.field label="Tanggal terbit" name="tanggal_terbit">
                        <input id="tanggal_terbit" name="tanggal_terbit" type="date" value="{{ old('tanggal_terbit', $berita->tanggal_terbit?->toDateString()) }}" required class="admin-input" @error('tanggal_terbit') aria-invalid="true" aria-describedby="tanggal_terbit-error" @enderror>
                    </x-admin.field>
                </section>

                <section class="admin-card space-y-3 p-5" aria-labelledby="kategori-judul" data-kategori>
                    <h2 id="kategori-judul" class="text-[15px] font-semibold">Kategori</h2>
                    <div>
                        <label for="kategori" class="sr-only">Kategori</label>
                        <select id="kategori" name="kategori" required class="admin-input" data-kategori-select @error('kategori') aria-invalid="true" aria-describedby="kategori-error" @enderror>
                            @foreach ($pilihanKategori as $kategori)
                                <option value="{{ $kategori }}" @selected($kategoriTerpilih === $kategori)>{{ $kategori }}</option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <p id="kategori-error" class="mt-1 text-xs text-[#c0262f]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex flex-wrap gap-2" data-kategori-chips hidden>
                        @foreach ($pilihanKategori as $kategori)
                            <button type="button" class="kategori-chip" data-kategori-chip="{{ $kategori }}" aria-pressed="{{ $kategoriTerpilih === $kategori ? 'true' : 'false' }}">{{ $kategori }}</button>
                        @endforeach
                        <button type="button" class="kategori-chip" data-kategori-baru-buka>+ Baru</button>
                    </div>
                    <div class="flex gap-2" hidden data-kategori-baru>
                        <label for="kategori-baru" class="sr-only">Nama kategori baru</label>
                        <input id="kategori-baru" type="text" maxlength="20" placeholder="Nama kategori baru" class="admin-input h-9" data-kategori-baru-input>
                        <button type="button" class="admin-btn admin-btn-primary h-9" data-kategori-baru-tambah>Tambah</button>
                    </div>
                </section>

                <section class="admin-card space-y-3 p-5" aria-labelledby="gambar-judul" data-gambar>
                    <h2 id="gambar-judul" class="text-[15px] font-semibold">Gambar utama</h2>
                    <div class="relative flex aspect-video items-center justify-center overflow-hidden rounded-lg bg-[#8fbc9f] text-white">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" data-gambar-kosong @if ($berita->gambarUrl()) hidden @endif><circle cx="16" cy="8" r="2"/><path d="m3 19 6-7 4 4 3-3 5 6H3Z" fill="currentColor"/></svg>
                        <img src="{{ $berita->gambarUrl() }}" alt="Pratinjau gambar utama" class="absolute inset-0 size-full object-cover" data-gambar-pratinjau @unless ($berita->gambarUrl()) hidden @endunless>
                    </div>
                    <input type="hidden" name="hapus_gambar" value="0" data-gambar-hapus-input>
                    <div class="grid grid-cols-2 gap-2">
                        <label for="gambar" class="admin-btn admin-btn-secondary cursor-pointer font-semibold focus-within:ring-4 focus-within:ring-footer/20">
                            Ganti gambar
                            <input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-gambar-input @error('gambar') aria-invalid="true" aria-describedby="gambar-error" @enderror>
                        </label>
                        <button type="button" class="admin-btn border border-[#f3b8bb] bg-white font-semibold text-[#c0262f] hover:bg-[#fdf0f0]" data-gambar-hapus>Hapus</button>
                    </div>
                    <p class="text-xs text-slate">Rasio 16:9, maksimal 2 MB. Tampil di bagian atas artikel.</p>
                    @error('gambar')
                        <p id="gambar-error" class="text-xs text-[#c0262f]">{{ $message }}</p>
                    @enderror
                </section>

                <section class="admin-card space-y-2 p-5" aria-labelledby="ringkasan-judul">
                    <h2 id="ringkasan-judul" class="text-[15px] font-semibold"><label for="ringkasan">Ringkasan di kartu</label></h2>
                    <textarea id="ringkasan" name="ringkasan" rows="3" maxlength="160" class="admin-input" aria-describedby="ringkasan-info" data-hitung-huruf @error('ringkasan') aria-invalid="true" @enderror>{{ $ringkasan }}</textarea>
                    <p id="ringkasan-info" class="flex justify-between text-xs text-slate">
                        <span>Tampil di kartu daftar artikel</span>
                        <span><span data-hitung-huruf-jumlah>{{ mb_strlen((string) $ringkasan) }}</span> dari 160 huruf</span>
                    </p>
                    @error('ringkasan')
                        <p class="text-xs text-[#c0262f]">{{ $message }}</p>
                    @enderror
                </section>
            </div>
        </div>
    </form>

    <dialog class="w-full max-w-3xl rounded-xl p-0 backdrop:bg-black/50" aria-labelledby="pratinjau-judul" data-pratinjau>
        <div class="flex items-center justify-between border-b border-[#e5e5e5] px-5 py-3">
            <p id="pratinjau-judul" class="text-sm font-semibold">Pratinjau artikel</p>
            <form method="dialog">
                <button class="admin-icon-btn" aria-label="Tutup pratinjau"><x-admin.icon name="x" width="16" height="16" /></button>
            </form>
        </div>
        <article class="max-h-[75vh] overflow-y-auto p-6">
            <img alt="" class="mb-5 aspect-video w-full object-cover" hidden data-pratinjau-gambar>
            <h1 class="font-raleway text-[28px] leading-tight font-bold text-ink" data-pratinjau-h1></h1>
            <p class="mt-2 font-open text-sm text-muted" data-pratinjau-meta></p>
            <div class="prose-yasibu mt-5" data-pratinjau-isi></div>
        </article>
    </dialog>

    <dialog class="w-full max-w-md rounded-xl p-0 backdrop:bg-black/50" aria-labelledby="baca-juga-judul" data-baca-juga>
        <form method="dialog" class="space-y-4 p-5">
            <h2 id="baca-juga-judul" class="text-[15px] font-semibold">Sisipkan "Baca juga"</h2>
            @if ($artikelLain)
                <div>
                    <label for="baca-juga-pilih" class="admin-label">Artikel terkait</label>
                    <select id="baca-juga-pilih" class="admin-input" data-baca-juga-pilih>
                        @foreach ($artikelLain as $lain)
                            <option value="{{ $lain['url'] }}">{{ $lain['judul'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button value="batal" class="admin-btn admin-btn-secondary">Batal</button>
                    <button value="sisipkan" class="admin-btn admin-btn-primary">Sisipkan</button>
                </div>
            @else
                <p class="text-[13px] text-slate">Belum ada artikel lain yang terbit.</p>
                <div class="flex justify-end">
                    <button value="batal" class="admin-btn admin-btn-secondary">Tutup</button>
                </div>
            @endif
        </form>
    </dialog>

    @vite('resources/js/editor.js')
</x-layouts.admin>
