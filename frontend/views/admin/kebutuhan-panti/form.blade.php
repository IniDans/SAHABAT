@php
    use App\Enums\KategoriKebutuhan;
    use App\Enums\PrioritasKebutuhan;

    $isEdit = $kebutuhan->exists;
@endphp

<x-layouts.admin :title="$isEdit ? 'Ubah Kebutuhan' : 'Tambah Kebutuhan'">
    <a href="{{ route('admin.kebutuhan-panti.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-[13px] text-slate hover:text-[#1e2328]">
        <x-admin.icon name="arrow-left" width="14" height="14" />
        Kembali ke daftar kebutuhan
    </a>

    <x-admin.page-heading :title="$isEdit ? 'Ubah kebutuhan' : 'Tambah kebutuhan'" />

    <form action="{{ $isEdit ? route('admin.kebutuhan-panti.update', $kebutuhan) : route('admin.kebutuhan-panti.store') }}" method="POST" class="admin-card max-w-2xl space-y-5 p-5">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <x-admin.field label="Nama kebutuhan" name="nama" required>
            <input id="nama" name="nama" type="text" value="{{ old('nama', $kebutuhan->nama) }}" required maxlength="255" placeholder="Contoh: Beras" class="admin-input" @error('nama') aria-invalid="true" aria-describedby="nama-error" @enderror>
        </x-admin.field>

        <x-admin.field label="Kategori" name="kategori" required>
            <select id="kategori" name="kategori" required class="admin-input" @error('kategori') aria-invalid="true" aria-describedby="kategori-error" @enderror>
                @foreach (KategoriKebutuhan::cases() as $kategori)
                    <option value="{{ $kategori->value }}" @selected(old('kategori', $kebutuhan->kategori?->value) === $kategori->value)>{{ $kategori->value }}</option>
                @endforeach
            </select>
        </x-admin.field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field label="Jumlah" name="jumlah">
                <input id="jumlah" name="jumlah" type="number" min="1" value="{{ old('jumlah', $kebutuhan->jumlah) }}" class="admin-input" @error('jumlah') aria-invalid="true" aria-describedby="jumlah-error" @enderror>
            </x-admin.field>

            <x-admin.field label="Satuan" name="satuan">
                <input id="satuan" name="satuan" type="text" maxlength="30" value="{{ old('satuan', $kebutuhan->satuan) }}" placeholder="Contoh: kg, paket, buah" class="admin-input" @error('satuan') aria-invalid="true" aria-describedby="satuan-error" @enderror>
            </x-admin.field>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field label="Prioritas" name="prioritas" required>
                <select id="prioritas" name="prioritas" required class="admin-input" @error('prioritas') aria-invalid="true" aria-describedby="prioritas-error" @enderror>
                    @foreach (PrioritasKebutuhan::cases() as $prioritas)
                        <option value="{{ $prioritas->value }}" @selected(old('prioritas', $kebutuhan->prioritas?->value) === $prioritas->value)>{{ $prioritas->value }}</option>
                    @endforeach
                </select>
            </x-admin.field>

            <x-admin.field label="Skor prioritas" name="skor_prioritas" required hint="0 sampai 100. Makin tinggi makin di atas.">
                <input id="skor_prioritas" name="skor_prioritas" type="number" min="0" max="100" value="{{ old('skor_prioritas', $kebutuhan->skor_prioritas) }}" required class="admin-input" @error('skor_prioritas') aria-invalid="true" aria-describedby="skor_prioritas-error" @enderror>
            </x-admin.field>
        </div>

        <x-admin.field label="Keterangan" name="keterangan">
            <textarea id="keterangan" name="keterangan" rows="3" class="admin-input" @error('keterangan') aria-invalid="true" aria-describedby="keterangan-error" @enderror>{{ old('keterangan', $kebutuhan->keterangan) }}</textarea>
        </x-admin.field>

        <label class="flex items-center gap-2 text-[13px]">
            <input type="checkbox" name="terpenuhi" value="1" class="size-4 accent-footer" @checked(old('terpenuhi', $kebutuhan->terpenuhi))>
            Kebutuhan ini sudah terpenuhi
        </label>

        <div class="flex gap-2 border-t border-[#e5e5e5] pt-5">
            <button type="submit" class="admin-btn admin-btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Simpan kebutuhan' }}</button>
            <a href="{{ route('admin.kebutuhan-panti.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
        </div>
    </form>
</x-layouts.admin>
