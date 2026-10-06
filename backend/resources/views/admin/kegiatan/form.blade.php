@php
    use App\Enums\JenisKegiatan;

    $isEdit = $kegiatan->exists;
@endphp

<x-layouts.admin :title="$isEdit ? 'Ubah Kegiatan' : 'Tambah Kegiatan'">
    <a href="{{ route('admin.kegiatan.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-[13px] text-slate hover:text-[#1e2328]">
        <x-admin.icon name="arrow-left" width="14" height="14" />
        Kembali ke daftar kegiatan
    </a>

    <x-admin.page-heading :title="$isEdit ? 'Ubah kegiatan' : 'Tambah kegiatan'" />

    <form action="{{ $isEdit ? route('admin.kegiatan.update', $kegiatan) : route('admin.kegiatan.store') }}" method="POST" enctype="multipart/form-data" class="admin-card max-w-2xl space-y-5 p-5">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <x-admin.field label="Nama kegiatan" name="nama_kegiatan" required>
            <input id="nama_kegiatan" name="nama_kegiatan" type="text" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required maxlength="150" class="admin-input" @error('nama_kegiatan') aria-invalid="true" aria-describedby="nama_kegiatan-error" @enderror>
        </x-admin.field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field label="Jenis kegiatan" name="jenis_kegiatan" required>
                <select id="jenis_kegiatan" name="jenis_kegiatan" required class="admin-input" @error('jenis_kegiatan') aria-invalid="true" aria-describedby="jenis_kegiatan-error" @enderror>
                    @foreach (JenisKegiatan::cases() as $jenis)
                        <option value="{{ $jenis->value }}" @selected(old('jenis_kegiatan', $kegiatan->jenis_kegiatan?->value) === $jenis->value)>{{ $jenis->value }}</option>
                    @endforeach
                </select>
            </x-admin.field>

            <x-admin.field label="Tanggal kegiatan" name="tanggal_kegiatan" required>
                <input id="tanggal_kegiatan" name="tanggal_kegiatan" type="date" value="{{ old('tanggal_kegiatan', $kegiatan->tanggal_kegiatan?->toDateString()) }}" required class="admin-input" @error('tanggal_kegiatan') aria-invalid="true" aria-describedby="tanggal_kegiatan-error" @enderror>
            </x-admin.field>
        </div>

        <x-admin.field label="Lokasi" name="lokasi">
            <input id="lokasi" name="lokasi" type="text" value="{{ old('lokasi', $kegiatan->lokasi) }}" maxlength="100" placeholder="Contoh: Aula panti" class="admin-input" @error('lokasi') aria-invalid="true" aria-describedby="lokasi-error" @enderror>
        </x-admin.field>

        <x-admin.field label="Deskripsi" name="deskripsi">
            <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="5000" class="admin-input" @error('deskripsi') aria-invalid="true" aria-describedby="deskripsi-error" @enderror>{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
        </x-admin.field>

        <x-admin.field :label="$kegiatan->fotoPath() ? 'Ganti foto' : 'Foto'" name="foto" hint="JPG, PNG, atau WEBP, maksimal 2 MB.">
            @if ($kegiatan->fotoPath())
                <img src="{{ route('admin.kegiatan.foto', $kegiatan) }}" alt="Foto {{ $kegiatan->nama_kegiatan }} saat ini" width="160" height="120" class="mb-2 h-[120px] w-40 rounded-lg object-cover">
            @endif
            <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input" @error('foto') aria-invalid="true" aria-describedby="foto-error" @enderror>
        </x-admin.field>

        <div class="flex gap-2 border-t border-[#e5e5e5] pt-5">
            <button type="submit" class="admin-btn admin-btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Simpan kegiatan' }}</button>
            <a href="{{ route('admin.kegiatan.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
        </div>
    </form>
</x-layouts.admin>
