@php
    $isEdit = $wali->exists;
@endphp

<x-layouts.admin :title="$isEdit ? 'Ubah Wali' : 'Tambah Wali'">
    <a href="{{ route('admin.wali-anak.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-[13px] text-slate hover:text-[#1e2328]">
        <x-admin.icon name="arrow-left" width="14" height="14" />
        Kembali ke data wali
    </a>

    <x-admin.page-heading :title="$isEdit ? 'Ubah data wali' : 'Tambah wali'" />

    <form action="{{ $isEdit ? route('admin.wali-anak.update', $wali) : route('admin.wali-anak.store') }}" method="POST" class="admin-card max-w-2xl space-y-5 p-5">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <x-admin.field label="Nama wali" name="Nama_Wali" required>
            <input id="Nama_Wali" name="Nama_Wali" type="text" value="{{ old('Nama_Wali', $wali->Nama_Wali) }}" required maxlength="100" class="admin-input" @error('Nama_Wali') aria-invalid="true" aria-describedby="Nama_Wali-error" @enderror>
        </x-admin.field>

        <x-admin.field label="Alamat wali" name="Alamat_Wali" required>
            <textarea id="Alamat_Wali" name="Alamat_Wali" rows="2" required maxlength="100" class="admin-input" @error('Alamat_Wali') aria-invalid="true" aria-describedby="Alamat_Wali-error" @enderror>{{ old('Alamat_Wali', $wali->Alamat_Wali) }}</textarea>
        </x-admin.field>

        <div class="flex gap-2 border-t border-[#e5e5e5] pt-5">
            <button type="submit" class="admin-btn admin-btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Simpan wali' }}</button>
            <a href="{{ route('admin.wali-anak.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
        </div>
    </form>
</x-layouts.admin>
