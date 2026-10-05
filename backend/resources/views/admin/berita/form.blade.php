@php
    use App\Enums\KategoriBerita;
    use App\Enums\StatusBerita;

    $isEdit = $berita->exists;
@endphp

<x-layouts.admin :title="$isEdit ? 'Ubah Berita' : 'Tambah Berita'">
    <a href="{{ route('admin.berita.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-[13px] text-slate hover:text-[#1e2328]">
        <x-admin.icon name="arrow-left" width="14" height="14" />
        Kembali ke daftar berita
    </a>

    <x-admin.page-heading :title="$isEdit ? 'Ubah berita' : 'Tambah berita'" />

    <form action="{{ $isEdit ? route('admin.berita.update', $berita) : route('admin.berita.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="admin-card space-y-5 p-5">
            <x-admin.field label="Judul" name="judul" required>
                <input id="judul" name="judul" type="text" value="{{ old('judul', $berita->judul) }}" required maxlength="255" class="admin-input" @error('judul') aria-invalid="true" aria-describedby="judul-error" @enderror>
            </x-admin.field>

            <x-admin.field label="Ringkasan" name="ringkasan" hint="Ditampilkan di daftar berita. Maksimal 500 karakter.">
                <textarea id="ringkasan" name="ringkasan" rows="2" maxlength="500" class="admin-input" @error('ringkasan') aria-invalid="true" aria-describedby="ringkasan-error" @enderror>{{ old('ringkasan', $berita->ringkasan) }}</textarea>
            </x-admin.field>

            <x-admin.field label="Isi berita" name="isi" required>
                <textarea id="isi" name="isi" rows="14" required class="admin-input leading-relaxed" @error('isi') aria-invalid="true" aria-describedby="isi-error" @enderror>{{ old('isi', $berita->isi) }}</textarea>
            </x-admin.field>
        </div>

        <div class="space-y-5">
            <div class="admin-card space-y-5 p-5">
                <x-admin.field label="Status" name="status" required>
                    <select id="status" name="status" required class="admin-input">
                        @foreach (StatusBerita::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $berita->status?->value) === $status->value)>{{ $status->value }}</option>
                        @endforeach
                    </select>
                </x-admin.field>

                <x-admin.field label="Tanggal terbit" name="tanggal_terbit" required>
                    <input id="tanggal_terbit" name="tanggal_terbit" type="date" value="{{ old('tanggal_terbit', $berita->tanggal_terbit?->toDateString()) }}" required class="admin-input" @error('tanggal_terbit') aria-invalid="true" aria-describedby="tanggal_terbit-error" @enderror>
                </x-admin.field>

                <x-admin.field label="Kategori" name="kategori" required>
                    <select id="kategori" name="kategori" required class="admin-input" @error('kategori') aria-invalid="true" aria-describedby="kategori-error" @enderror>
                        <option value="">Pilih kategori</option>
                        @foreach (KategoriBerita::cases() as $kategori)
                            <option value="{{ $kategori->value }}" @selected(old('kategori', $berita->kategori?->value) === $kategori->value)>{{ $kategori->value }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>

            <div class="admin-card space-y-3 p-5">
                <x-admin.field label="Gambar" name="gambar" hint="JPG, PNG, atau WEBP, maksimal 2 MB.">
                    @if ($berita->gambarUrl())
                        <img src="{{ $berita->gambarUrl() }}" alt="Gambar berita saat ini" class="mb-3 aspect-video w-full rounded-lg object-cover">
                    @endif
                    <input id="gambar" name="gambar" type="file" accept="image/*" class="block w-full text-[13px] text-slate file:mr-3 file:rounded-lg file:border-0 file:bg-[#e3f1ea] file:px-3 file:py-2 file:text-[13px] file:font-medium file:text-footer hover:file:bg-[#d3eadf]" @error('gambar') aria-invalid="true" aria-describedby="gambar-error" @enderror>
                </x-admin.field>

                @if ($berita->gambar)
                    <label class="flex items-center gap-2 text-[13px]">
                        <input type="hidden" name="hapus_gambar" value="0">
                        <input type="checkbox" name="hapus_gambar" value="1" class="size-4 accent-[#dc3c45]">
                        Hapus gambar saat ini
                    </label>
                @endif
            </div>

            <div class="flex gap-2">
                <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ $isEdit ? 'Simpan perubahan' : 'Simpan berita' }}</button>
                <a href="{{ route('admin.berita.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</x-layouts.admin>
