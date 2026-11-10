@php
    use App\Enums\MetodePembayaran;
    use App\Enums\ProgramDonasi;
    use App\Enums\StatusDonasi;
    use App\Enums\TampilanDonatur;
    use App\Models\Donasi;

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

        <x-admin.field label="Untuk kebutuhan" name="kebutuhan_panti_id" hint="Opsional. Pilih bila donasi ini khusus untuk salah satu kebutuhan panti; dananya ikut dihitung di progres kebutuhan itu.">
            <select id="kebutuhan_panti_id" name="kebutuhan_panti_id" class="admin-input" @error('kebutuhan_panti_id') aria-invalid="true" aria-describedby="kebutuhan_panti_id-error" @enderror>
                <option value="">Tidak untuk kebutuhan tertentu</option>
                @foreach ($pilihanKebutuhan as $kebutuhan)
                    <option value="{{ $kebutuhan->id }}" @selected((string) old('kebutuhan_panti_id', $donasi->kebutuhan_panti_id) === (string) $kebutuhan->id)>{{ $kebutuhan->label() }} &middot; {{ $kebutuhan->prioritas->value }}</option>
                @endforeach
            </select>
        </x-admin.field>

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

    @if ($isEdit && $donasi->bukti_transfer)
        @php
            $hasil = $donasi->bukti_hasil ?? [];
            $cocok = $donasi->buktiCocok();
        @endphp
        <section class="admin-card mt-5 max-w-2xl p-5" aria-labelledby="bukti-transfer">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="bukti-transfer" class="text-base font-semibold">Bukti transfer</h2>
                @if ($cocok === true)
                    <x-admin.badge tone="green">Nominal cocok</x-admin.badge>
                @elseif ($cocok === false)
                    <x-admin.badge tone="red">Perlu dicek</x-admin.badge>
                @else
                    <x-admin.badge>Belum terbaca</x-admin.badge>
                @endif
            </div>

            <div class="mt-4 grid gap-5 sm:grid-cols-[200px_1fr]">
                <a href="{{ route('admin.donasi.bukti', $donasi) }}" target="_blank" class="block overflow-hidden rounded-lg border border-[#e5e5e5]">
                    <img src="{{ route('admin.donasi.bukti', $donasi) }}" alt="Bukti transfer {{ $donasi->nama_donatur }}" class="max-h-72 w-full object-contain">
                </a>

                <div class="text-[13px]">
                    @if (isset($hasil['galat']))
                        <p class="text-slate">Bukti belum terbaca otomatis: {{ $hasil['galat'] }} Cek fotonya secara manual.</p>
                    @elseif ($hasil)
                        <p class="text-xs text-slate">Hasil baca AI (bantuan saja, cocokkan dengan mutasi rekening):</p>
                        @unless ($hasil['adalah_bukti_transfer'] ?? false)
                            <p class="mt-2 font-semibold text-[#c0262f]">Gambar ini sepertinya bukan bukti transfer.</p>
                        @endunless
                        <dl class="mt-2 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5">
                            <dt class="text-slate">Nominal</dt>
                            <dd class="font-semibold tabular-nums">{{ isset($hasil['nominal']) ? \App\Models\Donasi::rupiah($hasil['nominal']) : '-' }} <span class="font-normal text-slate">(tercatat {{ \App\Models\Donasi::rupiah($donasi->nominal) }})</span></dd>
                            <dt class="text-slate">Tanggal</dt>
                            <dd>{{ $hasil['tanggal'] ?? '-' }}</dd>
                            <dt class="text-slate">Bank/aplikasi</dt>
                            <dd>{{ $hasil['bank'] ?? '-' }}</dd>
                            <dt class="text-slate">Pengirim</dt>
                            <dd>{{ $hasil['nama_pengirim'] ?? '-' }}</dd>
                            <dt class="text-slate">Penerima</dt>
                            <dd>{{ $hasil['nama_penerima'] ?? '-' }}</dd>
                            @if ($hasil['catatan'] ?? null)
                                <dt class="text-slate">Catatan</dt>
                                <dd>{{ $hasil['catatan'] }}</dd>
                            @endif
                        </dl>
                    @else
                        <p class="text-slate">Bukti belum dibaca.</p>
                    @endif

                    <form action="{{ route('admin.donasi.baca-bukti', $donasi) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="admin-btn admin-btn-secondary">Baca ulang dengan AI</button>
                    </form>
                </div>
            </div>
        </section>
    @endif
</x-layouts.admin>
