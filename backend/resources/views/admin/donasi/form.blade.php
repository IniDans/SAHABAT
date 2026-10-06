@php
    use App\Enums\MetodePembayaran;
    use App\Enums\ProgramDonasi;
    use App\Enums\StatusDonasi;
    use App\Enums\TampilanDonatur;

    $isEdit = $donasi->exists;
@endphp

<x-layouts.admin :title="$isEdit ? 'Ubah Donasi' : 'Catat Donasi'">
    <a href="{{ route('admin.donasi.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-[13px] text-slate hover:text-[#1e2328]">
        <x-admin.icon name="arrow-left" width="14" height="14" />
        Kembali ke daftar donasi
    </a>

    <x-admin.page-heading :title="$isEdit ? 'Ubah donasi' : 'Catat donasi'" />

    <form action="{{ $isEdit ? route('admin.donasi.update', $donasi) : route('admin.donasi.store') }}" method="POST" class="admin-card max-w-2xl space-y-5 p-5">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <x-admin.field label="Nama donatur" name="nama_donatur" required>
            <input id="nama_donatur" name="nama_donatur" type="text" value="{{ old('nama_donatur', $donasi->nama_donatur) }}" required maxlength="255" placeholder="Nama lengkap donatur" class="admin-input" @error('nama_donatur') aria-invalid="true" aria-describedby="nama_donatur-error" @enderror>
        </x-admin.field>

        <x-admin.field label="Ditampilkan sebagai" name="tampil_sebagai" required hint="Nama yang boleh muncul di daftar donatur website.">
            <select id="tampil_sebagai" name="tampil_sebagai" required class="admin-input sm:w-1/2" @error('tampil_sebagai') aria-invalid="true" aria-describedby="tampil_sebagai-error" @enderror>
                @foreach (TampilanDonatur::cases() as $tampilan)
                    <option value="{{ $tampilan->value }}" @selected(old('tampil_sebagai', $donasi->tampil_sebagai?->value) === $tampilan->value)>{{ $tampilan->value }}</option>
                @endforeach
            </select>
        </x-admin.field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field label="No WhatsApp" name="no_whatsapp">
                <input id="no_whatsapp" name="no_whatsapp" type="tel" maxlength="20" value="{{ old('no_whatsapp', $donasi->no_whatsapp) }}" class="admin-input" @error('no_whatsapp') aria-invalid="true" aria-describedby="no_whatsapp-error" @enderror>
            </x-admin.field>

            <x-admin.field label="Email" name="email">
                <input id="email" name="email" type="email" value="{{ old('email', $donasi->email) }}" class="admin-input" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            </x-admin.field>
        </div>

        <x-admin.field label="Alamat" name="alamat">
            <textarea id="alamat" name="alamat" rows="2" maxlength="500" class="admin-input" @error('alamat') aria-invalid="true" aria-describedby="alamat-error" @enderror>{{ old('alamat', $donasi->alamat) }}</textarea>
        </x-admin.field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field label="Program" name="program" required>
                <select id="program" name="program" required class="admin-input" @error('program') aria-invalid="true" aria-describedby="program-error" @enderror>
                    <option value="">Pilih program</option>
                    @foreach (ProgramDonasi::cases() as $program)
                        <option value="{{ $program->value }}" @selected(old('program', $donasi->program?->value) === $program->value)>{{ $program->value }}</option>
                    @endforeach
                </select>
            </x-admin.field>

            <x-admin.field label="Nominal (Rp)" name="nominal" required hint="Minimal Rp1.000, tanpa titik.">
                <input id="nominal" name="nominal" type="number" min="1000" step="1" inputmode="numeric" value="{{ old('nominal', $donasi->nominal) }}" required class="admin-input" @error('nominal') aria-invalid="true" aria-describedby="nominal-error" @enderror>
            </x-admin.field>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field label="Metode pembayaran" name="metode_pembayaran" required>
                <select id="metode_pembayaran" name="metode_pembayaran" required class="admin-input" @error('metode_pembayaran') aria-invalid="true" aria-describedby="metode_pembayaran-error" @enderror>
                    <option value="">Pilih metode</option>
                    @foreach (MetodePembayaran::cases() as $metode)
                        <option value="{{ $metode->value }}" @selected(old('metode_pembayaran', $donasi->metode_pembayaran?->value) === $metode->value)>{{ $metode->value }}</option>
                    @endforeach
                </select>
            </x-admin.field>

            <x-admin.field label="Tanggal donasi" name="tanggal_donasi" required>
                <input id="tanggal_donasi" name="tanggal_donasi" type="date" max="{{ today()->toDateString() }}" value="{{ old('tanggal_donasi', $donasi->tanggal_donasi?->toDateString()) }}" required class="admin-input" @error('tanggal_donasi') aria-invalid="true" aria-describedby="tanggal_donasi-error" @enderror>
            </x-admin.field>
        </div>

        <x-admin.field label="Status" name="status" required>
            <select id="status" name="status" required class="admin-input sm:w-1/2" @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
                @foreach (StatusDonasi::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $donasi->status?->value) === $status->value)>{{ $status->value }}</option>
                @endforeach
            </select>
        </x-admin.field>

        <x-admin.field label="Keterangan" name="keterangan">
            <textarea id="keterangan" name="keterangan" rows="3" placeholder="Contoh: nomor referensi transfer" class="admin-input" @error('keterangan') aria-invalid="true" aria-describedby="keterangan-error" @enderror>{{ old('keterangan', $donasi->keterangan) }}</textarea>
        </x-admin.field>

        <div class="flex gap-2 border-t border-[#e5e5e5] pt-5">
            <button type="submit" class="admin-btn admin-btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Simpan donasi' }}</button>
            <a href="{{ route('admin.donasi.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
        </div>
    </form>
</x-layouts.admin>
