@php
    use App\Enums\JenisKelamin;

    $isEdit = $pengasuh->exists;

    $teks = [
        ['Nama', 'Nama lengkap', 'text', 100, true],
        ['Jabatan', 'Jabatan', 'text', 50, true],
        ['Tempat_Lahir', 'Tempat lahir', 'text', 100, true],
        ['Agama', 'Agama', 'text', 10, true],
        ['Pendidikan', 'Pendidikan terakhir', 'text', 100, true],
        ['Kesehatan', 'Kondisi kesehatan', 'text', 100, false],
        ['Nomor_Telepon', 'Nomor telepon', 'tel', 20, true],
        ['Email', 'Email', 'email', 50, true],
    ];
@endphp

<x-layouts.admin :title="$isEdit ? 'Ubah Pengasuh' : 'Tambah Pengasuh'">
    <a href="{{ route('admin.pengasuh.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-[13px] text-slate hover:text-[#1e2328]">
        <x-admin.icon name="arrow-left" width="14" height="14" />
        Kembali ke data pengasuh
    </a>

    <x-admin.page-heading :title="$isEdit ? 'Ubah data pengasuh' : 'Tambah pengasuh'" />

    <form action="{{ $isEdit ? route('admin.pengasuh.update', $pengasuh) : route('admin.pengasuh.store') }}" method="POST" class="admin-card max-w-2xl space-y-5 p-5">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <x-admin.field label="NIK" name="NIK" required hint="Nomor Induk Kependudukan, hanya angka.">
            <input id="NIK" name="NIK" type="text" inputmode="numeric" pattern="[0-9]*" value="{{ old('NIK', $pengasuh->NIK) }}" required maxlength="20" class="admin-input tabular-nums" @error('NIK') aria-invalid="true" aria-describedby="NIK-error" @enderror>
        </x-admin.field>

        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($teks as [$nama, $label, $tipe, $maks, $wajib])
                <x-admin.field :label="$label" :name="$nama" :required="$wajib">
                    <input id="{{ $nama }}" name="{{ $nama }}" type="{{ $tipe }}" value="{{ old($nama, $pengasuh->{$nama}) }}" @required($wajib) maxlength="{{ $maks }}" class="admin-input" @error($nama) aria-invalid="true" aria-describedby="{{ $nama }}-error" @enderror>
                </x-admin.field>
            @endforeach

            <x-admin.field label="Jenis kelamin" name="Jenis_Kelamin" required>
                <select id="Jenis_Kelamin" name="Jenis_Kelamin" required class="admin-input" @error('Jenis_Kelamin') aria-invalid="true" aria-describedby="Jenis_Kelamin-error" @enderror>
                    @foreach (JenisKelamin::cases() as $jenisKelamin)
                        <option value="{{ $jenisKelamin->value }}" @selected(old('Jenis_Kelamin', $pengasuh->Jenis_Kelamin?->value) === $jenisKelamin->value)>{{ $jenisKelamin->value }}</option>
                    @endforeach
                </select>
            </x-admin.field>

            <x-admin.field label="Tanggal lahir" name="Tanggal_Lahir">
                <input id="Tanggal_Lahir" name="Tanggal_Lahir" type="date" max="{{ today()->toDateString() }}" value="{{ old('Tanggal_Lahir', $pengasuh->Tanggal_Lahir?->toDateString()) }}" class="admin-input" @error('Tanggal_Lahir') aria-invalid="true" aria-describedby="Tanggal_Lahir-error" @enderror>
            </x-admin.field>
        </div>

        <x-admin.field label="Alamat" name="Alamat" required>
            <textarea id="Alamat" name="Alamat" rows="2" required maxlength="100" class="admin-input" @error('Alamat') aria-invalid="true" aria-describedby="Alamat-error" @enderror>{{ old('Alamat', $pengasuh->Alamat) }}</textarea>
        </x-admin.field>

        <div class="flex gap-2 border-t border-[#e5e5e5] pt-5">
            <button type="submit" class="admin-btn admin-btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Simpan pengasuh' }}</button>
            <a href="{{ route('admin.pengasuh.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
        </div>
    </form>
</x-layouts.admin>
