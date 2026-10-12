@php
    use App\Models\ProfilPanti;

    $halamanPublik = [
        'lembaga' => route('tentang.profil'),
        'visi_misi' => route('tentang.visi-misi'),
        'pengurus' => route('tentang.pengurus'),
        'kontak' => route('tentang.kontak'),
        'sosial' => route('beranda'),
    ];
    $kolomOrang = [
        ['key' => 'nama', 'label' => 'Nama', 'maxlength' => 100],
        ['key' => 'jabatan', 'label' => 'Jabatan', 'maxlength' => 100],
    ];
    // Anggota bidang diisi lewat textarea, satu nama per baris.
    $bidangForm = collect($profil['bidang'] ?? [])
        ->map(fn (array $bidang): array => ['nama' => $bidang['nama'], 'anggota' => implode(PHP_EOL, $bidang['anggota'])])
        ->all();
@endphp

<x-layouts.admin title="Profil Panti">
    <x-admin.page-heading title="Profil panti" description="Isi halaman Tentang Kami dan footer website. Perubahan langsung tampil setelah disimpan.">
        <a href="{{ $halamanPublik[$bagian] }}" class="admin-btn admin-btn-secondary" target="_blank" rel="noopener">
            <x-admin.icon name="eye" width="16" height="16" />
            Lihat di website
        </a>
    </x-admin.page-heading>

    <nav class="mb-5 flex flex-wrap gap-1 border-b border-[#e5e5e5]" aria-label="Bagian profil">
        @foreach (ProfilPanti::BAGIAN as $kunci => $judul)
            <a href="{{ route('admin.profil.edit', ['bagian' => $kunci]) }}" @class([
                '-mb-px border-b-2 px-4 py-2.5 text-[13px] transition',
                'border-footer font-semibold text-footer' => $kunci === $bagian,
                'border-transparent text-slate hover:text-[#1e2328]' => $kunci !== $bagian,
            ]) @if ($kunci === $bagian) aria-current="page" @endif>{{ $judul }}</a>
        @endforeach
    </nav>

    <form action="{{ route('admin.profil.update', $bagian) }}" method="POST" class="admin-card max-w-3xl space-y-6 p-5">
        @csrf
        @method('PUT')

        @switch($bagian)
            @case('lembaga')
                <x-admin.field label="Judul bagian legalitas" name="judul_legalitas" required>
                    <input id="judul_legalitas" name="judul_legalitas" type="text" value="{{ old('judul_legalitas', $profil['judul_legalitas']) }}" required maxlength="150" class="admin-input" @error('judul_legalitas') aria-invalid="true" aria-describedby="judul_legalitas-error" @enderror>
                </x-admin.field>

                <x-admin.repeater name="legalitas" label="Dokumen legalitas" :baris="$profil['legalitas']" tambah="Tambah dokumen" :kolom="[
                    ['key' => 'label', 'label' => 'Nama dokumen', 'placeholder' => 'Contoh: NPWP', 'maxlength' => 100],
                    ['key' => 'nilai', 'label' => 'Nomor / keterangan', 'maxlength' => 255],
                ]" />

                <x-admin.field label="Sejarah dan profil lembaga" name="sejarah" required hint="Pisahkan paragraf dengan satu baris kosong.">
                    <textarea id="sejarah" name="sejarah" rows="12" required maxlength="10000" class="admin-input" @error('sejarah') aria-invalid="true" aria-describedby="sejarah-error" @enderror>{{ old('sejarah', $profil['sejarah']) }}</textarea>
                </x-admin.field>
                @break

            @case('visi_misi')
                <x-admin.field label="Motto" name="motto" required>
                    <input id="motto" name="motto" type="text" value="{{ old('motto', $profil['motto']) }}" required maxlength="255" class="admin-input" @error('motto') aria-invalid="true" aria-describedby="motto-error" @enderror>
                </x-admin.field>

                <x-admin.field label="Visi" name="visi" required>
                    <textarea id="visi" name="visi" rows="3" required maxlength="1000" class="admin-input" @error('visi') aria-invalid="true" aria-describedby="visi-error" @enderror>{{ old('visi', $profil['visi']) }}</textarea>
                </x-admin.field>

                <x-admin.field label="Misi" name="misi" required hint="Satu misi per baris.">
                    <textarea id="misi" name="misi" rows="8" required maxlength="5000" class="admin-input" @error('misi') aria-invalid="true" aria-describedby="misi-error" @enderror>{{ old('misi', implode("\n", $profil['misi'])) }}</textarea>
                </x-admin.field>
                @break

            @case('pengurus')
                <x-admin.repeater name="inti" label="Pengurus inti" :baris="$profil['inti']" tambah="Tambah pengurus" :kolom="$kolomOrang" />

                <x-admin.repeater name="bidang" label="Bidang-bidang" hint="Tulis satu nama anggota per baris." :baris="$bidangForm" tambah="Tambah bidang" :kolom="[
                    ['key' => 'nama', 'label' => 'Nama bidang', 'maxlength' => 150, 'lebar' => 'sm:col-span-2'],
                    ['key' => 'anggota', 'label' => 'Anggota', 'tipe' => 'textarea', 'baris' => 4, 'maxlength' => 2000, 'lebar' => 'sm:col-span-2'],
                ]" />

                <x-admin.repeater name="pembina" label="Pembina" :baris="$profil['pembina']" tambah="Tambah pembina" :kolom="$kolomOrang" />
                @break

            @case('kontak')
                <x-admin.field label="Email" name="email" required>
                    <input id="email" name="email" type="email" value="{{ old('email', $profil['email']) }}" required maxlength="255" class="admin-input" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                </x-admin.field>

                <x-admin.repeater name="whatsapp" label="Nomor WhatsApp" hint="Nomor pertama dipakai untuk tombol 'Hubungi melalui WhatsApp'. Maksimal 5 nomor." :baris="$profil['whatsapp']" tambah="Tambah nomor" :kolom="[
                    ['key' => 'nomor', 'label' => 'Nomor', 'tipe' => 'tel', 'placeholder' => '+62812...', 'maxlength' => 20],
                    ['key' => 'nama', 'label' => 'Nama kontak', 'placeholder' => 'Contoh: Ustadzah Lulu', 'maxlength' => 100],
                ]" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-admin.field label="Nama lokasi" name="lokasi_nama" required>
                        <input id="lokasi_nama" name="lokasi_nama" type="text" value="{{ old('lokasi_nama', $profil['lokasi_nama']) }}" required maxlength="150" class="admin-input" @error('lokasi_nama') aria-invalid="true" aria-describedby="lokasi_nama-error" @enderror>
                    </x-admin.field>

                    <x-admin.field label="Pencarian peta" name="peta" hint="Alamat yang dicari di Google Maps. Kosongkan untuk menyembunyikan peta.">
                        <input id="peta" name="peta" type="text" value="{{ old('peta', $profil['peta']) }}" maxlength="255" class="admin-input" @error('peta') aria-invalid="true" aria-describedby="peta-error" @enderror>
                    </x-admin.field>
                </div>

                <x-admin.field label="Alamat lokasi (halaman Kontak)" name="lokasi_alamat" required>
                    <textarea id="lokasi_alamat" name="lokasi_alamat" rows="3" required maxlength="500" class="admin-input" @error('lokasi_alamat') aria-invalid="true" aria-describedby="lokasi_alamat-error" @enderror>{{ old('lokasi_alamat', $profil['lokasi_alamat']) }}</textarea>
                </x-admin.field>

                <x-admin.field label="Alamat di footer website" name="alamat_footer" required>
                    <textarea id="alamat_footer" name="alamat_footer" rows="3" required maxlength="500" class="admin-input" @error('alamat_footer') aria-invalid="true" aria-describedby="alamat_footer-error" @enderror>{{ old('alamat_footer', $profil['alamat_footer']) }}</textarea>
                </x-admin.field>
                @break

            @case('sosial')
                <p class="text-[13px] text-slate">Ikon media sosial di footer hanya tampil bila tautannya diisi.</p>
                @foreach (ProfilPanti::MEDIA_SOSIAL as $media => $nama)
                    <x-admin.field :label="$nama" :name="$media">
                        <input id="{{ $media }}" name="{{ $media }}" type="url" value="{{ old($media, $profil[$media]) }}" maxlength="255" placeholder="https://" class="admin-input" @error($media) aria-invalid="true" aria-describedby="{{ $media }}-error" @enderror>
                    </x-admin.field>
                @endforeach
                @break
        @endswitch

        <div class="border-t border-[#e5e5e5] pt-5">
            <button type="submit" class="admin-btn admin-btn-primary">Simpan {{ str(ProfilPanti::BAGIAN[$bagian])->lower() }}</button>
        </div>
    </form>
</x-layouts.admin>
