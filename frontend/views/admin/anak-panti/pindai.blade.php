@php
    use App\Enums\JenisKelamin;
    use App\Enums\StatusAsuh;

    $labelJenisKelamin = [JenisKelamin::LakiLaki->value => 'Laki-laki', JenisKelamin::Perempuan->value => 'Perempuan'];
    $opsi = fn (array $nilai): array => array_combine($nilai, $nilai);
    $namaDokumen = $hasil['jenis_dokumen'] === 'kk' ? 'Kartu Keluarga' : 'Akta kelahiran';

    // Sama dengan form tambah anak, tanpa ukuran dan wali (wali dipilih sekali untuk semua anak).
    $field = [
        'NIK' => ['label' => 'NIK', 'jenis' => 'text', 'wajib' => true, 'atribut' => 'inputmode=numeric maxlength=20 autocomplete=off'],
        'Nama' => ['label' => 'Nama lengkap', 'jenis' => 'text', 'wajib' => true, 'atribut' => 'maxlength=100'],
        'Jenis_Kelamin' => ['label' => 'Jenis kelamin', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $labelJenisKelamin],
        'Agama' => ['label' => 'Agama', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $opsi($pilihan['Agama'])],
        'Tempat_Lahir' => ['label' => 'Tempat lahir', 'jenis' => 'text', 'wajib' => true, 'atribut' => 'maxlength=50'],
        'Tanggal_Lahir' => ['label' => 'Tanggal lahir', 'jenis' => 'date', 'wajib' => true, 'atribut' => 'max='.now()->toDateString()],
        'Keterangan' => ['label' => 'Status anak', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $opsi($pilihan['Keterangan'])],
        'Status_Anak' => ['label' => 'Kategori', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $opsi($pilihan['Status_Anak'])],
        'Pendidikan' => ['label' => 'Pendidikan', 'jenis' => 'datalist', 'wajib' => true, 'pilihan' => $pilihan['Pendidikan'], 'atribut' => 'maxlength=100'],
        'Status_Asuh' => ['label' => 'Status asuh', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $opsi(array_column(StatusAsuh::cases(), 'value'))],
        'Kesehatan' => ['label' => 'Kesehatan', 'jenis' => 'datalist', 'wajib' => false, 'pilihan' => $pilihan['Kesehatan'], 'atribut' => 'maxlength=100'],
    ];

    $adaInputLama = session()->hasOldInput();
    $pilihanWali = (string) old('wali', $hasil['wali']['pilihan']);
@endphp

<x-layouts.admin title="Periksa Hasil Scan">
    <x-admin.page-heading title="Periksa hasil scan" description="Cocokkan dengan dokumen asli dan perbaiki bila ada yang salah baca. Data baru tersimpan setelah Anda menekan Simpan." />

    <section class="admin-card p-5" aria-labelledby="info-dokumen">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="info-dokumen" class="text-base font-semibold">{{ $namaDokumen }}</h2>
            <x-admin.badge>Dibaca AI</x-admin.badge>
        </div>
        <dl class="mt-3 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5 text-[13px]">
            <dt class="text-slate">Nomor</dt>
            <dd class="tabular-nums">{{ $hasil['nomor_dokumen'] ?? '-' }}</dd>
            @if ($hasil['jenis_dokumen'] === 'kk')
                <dt class="text-slate">Kepala keluarga</dt>
                <dd>{{ $hasil['kepala_keluarga'] ?? '-' }}</dd>
            @endif
            <dt class="text-slate">Alamat</dt>
            <dd>{{ $hasil['alamat'] ?? '-' }}</dd>
        </dl>
        @if ($hasil['catatan'])
            <p class="mt-3 rounded-lg bg-[#fff6e5] px-4 py-3 text-[13px] text-[#8a5300]">Catatan AI: {{ $hasil['catatan'] }}</p>
        @endif
    </section>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-[#f3c2c5] bg-[#fdecee] px-4 py-3 text-[13px] text-[#a3212a]" role="alert">
            {{ $errors->has('anak') ? $errors->first('anak') : 'Ada isian yang perlu diperbaiki. Periksa kolom yang ditandai merah.' }}
        </div>
    @endif

    <form id="form-batal-pindai" action="{{ route('admin.anak-panti.pindai.batal') }}" method="POST">
        @csrf
        @method('DELETE')
    </form>

    <form action="{{ route('admin.anak-panti.pindai.simpan') }}" method="POST" novalidate>
        @csrf

        <section class="admin-card mt-4 p-5" aria-labelledby="wali-dokumen">
            <h2 id="wali-dokumen" class="text-base font-semibold">Wali</h2>
            <p class="mt-0.5 text-[13px] text-slate">Berlaku untuk semua anak yang disimpan dari dokumen ini.</p>

            <div class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                <x-admin.field label="Wali anak" name="wali">
                    <select id="wali" name="wali" class="admin-input pr-8" data-pindai-wali @error('wali') aria-invalid="true" aria-describedby="wali-error" @enderror>
                        <option value="">Tanpa wali</option>
                        <option value="baru" @selected($pilihanWali === 'baru')>Buat wali baru</option>
                        @foreach ($wali as $item)
                            <option value="{{ $item->ID_Wali }}" @selected($pilihanWali === (string) $item->ID_Wali)>{{ $item->Nama_Wali }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>

            <fieldset class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2" @if ($pilihanWali !== 'baru') hidden @endif data-pindai-wali-baru>
                <legend class="sr-only">Data wali baru</legend>
                <x-admin.field label="Nama wali" name="Nama_Wali" required>
                    <input id="Nama_Wali" type="text" name="Nama_Wali" value="{{ old('Nama_Wali', $hasil['wali']['Nama_Wali']) }}" class="admin-input" maxlength="100" required @error('Nama_Wali') aria-invalid="true" aria-describedby="Nama_Wali-error" @enderror>
                </x-admin.field>
                <x-admin.field label="Alamat wali" name="Alamat_Wali" required hint="Maksimal 100 karakter.">
                    <input id="Alamat_Wali" type="text" name="Alamat_Wali" value="{{ old('Alamat_Wali', $hasil['wali']['Alamat_Wali']) }}" class="admin-input" maxlength="100" required @error('Alamat_Wali') aria-invalid="true" aria-describedby="Alamat_Wali-error" @enderror>
                </x-admin.field>
            </fieldset>
        </section>

        <h2 class="mt-6 text-base font-semibold">{{ $hasil['jenis_dokumen'] === 'kk' ? 'Anggota keluarga' : 'Data anak' }}</h2>
        <p class="mt-0.5 text-[13px] text-slate">Centang anak yang akan didata. Anak yang sudah terdaftar dan anggota dewasa tidak dicentang otomatis.</p>

        <div class="mt-3 space-y-4">
            @foreach ($hasil['anggota'] as $i => $orang)
                @php
                    $dipilih = $adaInputLama ? filled(old("anak.{$i}.pilih")) : $orang['pilih'];
                    $keterangan = array_filter([$orang['hubungan'], $orang['umur'] !== null ? $orang['umur'].' tahun' : null]);
                @endphp
                <section class="admin-card overflow-hidden" aria-labelledby="pindai-anak-{{ $i }}-judul">
                    <label class="flex cursor-pointer items-center gap-3 border-b border-[#eceeec] px-5 py-3.5 has-[:checked]:bg-[#f5faf7]">
                        <input type="checkbox" name="anak[{{ $i }}][pilih]" value="1" class="size-4 accent-footer" @checked($dipilih) data-pindai-pilih="pindai-anak-{{ $i }}">
                        <span class="min-w-0 flex-1">
                            <span id="pindai-anak-{{ $i }}-judul" class="block font-semibold">{{ $orang['data']['Nama'] ?: 'Tanpa nama' }}</span>
                            @if ($keterangan)
                                <span class="block text-xs text-slate">{{ implode(' · ', $keterangan) }}</span>
                            @endif
                        </span>
                        @if ($orang['terdaftar'])
                            <x-admin.badge tone="amber">Sudah terdaftar</x-admin.badge>
                        @endif
                    </label>

                    <fieldset id="pindai-anak-{{ $i }}" class="grid gap-x-8 gap-y-4 px-5 py-4 disabled:opacity-50 sm:grid-cols-2">
                        <legend class="sr-only">Data {{ $orang['data']['Nama'] }}</legend>
                        @foreach ($field as $kolom => $konfigurasi)
                            @php
                                $kunci = "anak.{$i}.{$kolom}";
                                $petunjuk = $kolom === 'Pendidikan' && $orang['pendidikan_dokumen'] ? "Di dokumen: {$orang['pendidikan_dokumen']}" : null;
                            @endphp
                            <x-admin.field :label="$konfigurasi['label']" :name="$kunci" :required="$konfigurasi['wajib']" :hint="$petunjuk">
                                <x-admin.anak-input :id="$kunci" :name="'anak['.$i.']['.$kolom.']'" :konfigurasi="$konfigurasi" :nilai="(string) old($kunci, $orang['data'][$kolom])" :galat="$errors->has($kunci)" />
                            </x-admin.field>
                        @endforeach
                    </fieldset>
                </section>
            @endforeach
        </div>

        <div class="sticky bottom-0 mt-5 flex justify-end gap-3 border-t border-[#eceeec] bg-white/95 py-4 backdrop-blur">
            <button type="submit" form="form-batal-pindai" class="admin-btn admin-btn-secondary min-w-24">Batal</button>
            <button type="submit" class="admin-btn admin-btn-primary min-w-28">Simpan</button>
        </div>
    </form>
</x-layouts.admin>
