@php
    use App\Enums\JenisKelamin;
    use App\Enums\StatusAsuh;
    use App\Models\AnakPanti;
    use Illuminate\Support\Arr;

    $kolomForm = ['NIK', 'Nama', 'Jenis_Kelamin', 'Tempat_Lahir', 'Tanggal_Lahir', 'Agama', 'Keterangan', 'Status_Anak', 'Pendidikan', 'Status_Asuh', 'Kesehatan', 'Ukuran_Pakaian', 'Ukuran_Sepatu', 'ID_Wali'];
    $labelJenisKelamin = [JenisKelamin::LakiLaki->value => 'Laki-laki', JenisKelamin::Perempuan->value => 'Perempuan'];
    $opsi = fn (array $nilai): array => array_combine($nilai, $nilai);

    // Tiap field: label, jenis input, pilihan (untuk select/datalist), wajib.
    $field = [
        'NIK' => ['label' => 'NIK', 'jenis' => 'text', 'wajib' => true, 'atribut' => 'inputmode=numeric maxlength=20 autocomplete=off'],
        'Nama' => ['label' => 'Nama lengkap', 'jenis' => 'text', 'wajib' => true, 'atribut' => 'maxlength=100'],
        'Jenis_Kelamin' => ['label' => 'Jenis kelamin', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $labelJenisKelamin],
        'Agama' => ['label' => 'Agama', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $opsi($pilihan['Agama'])],
        'Tempat_Lahir' => ['label' => 'Tempat lahir', 'jenis' => 'text', 'wajib' => true, 'atribut' => 'maxlength=50'],
        'Tanggal_Lahir' => ['label' => 'Tanggal lahir', 'jenis' => 'date', 'wajib' => true, 'atribut' => 'max='.now()->toDateString()],
        'Keterangan' => ['label' => 'Status anak', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $opsi($pilihan['Keterangan'])],
        'Pendidikan' => ['label' => 'Pendidikan', 'jenis' => 'datalist', 'wajib' => true, 'pilihan' => $pilihan['Pendidikan'], 'atribut' => 'maxlength=100'],
        'Status_Asuh' => ['label' => 'Status asuh', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $opsi(array_column(StatusAsuh::cases(), 'value'))],
        'Kesehatan' => ['label' => 'Kesehatan', 'jenis' => 'datalist', 'wajib' => false, 'pilihan' => $pilihan['Kesehatan'], 'atribut' => 'maxlength=100'],
        'Ukuran_Pakaian' => ['label' => 'Ukuran pakaian', 'jenis' => 'datalist', 'wajib' => false, 'pilihan' => $pilihan['Ukuran_Pakaian'], 'atribut' => 'maxlength=20'],
        'Ukuran_Sepatu' => ['label' => 'Ukuran sepatu', 'jenis' => 'text', 'wajib' => false, 'atribut' => 'maxlength=20'],
        'Status_Anak' => ['label' => 'Kategori', 'jenis' => 'select', 'wajib' => true, 'pilihan' => $opsi($pilihan['Status_Anak'])],
        'ID_Wali' => ['label' => 'Wali', 'jenis' => 'select', 'wajib' => false, 'pilihan' => $wali->pluck('Nama_Wali', 'ID_Wali')->all(), 'kosong' => 'Tidak ada wali'],
    ];

    $dataBaru = ['NIK' => '', 'Agama' => 'Islam', 'Status_Asuh' => StatusAsuh::MasihAktif->value, 'Kesehatan' => 'Sehat'];
    $galatForm = $errors->hasAny($kolomForm);
    $nikLama = old('_nik_lama');
    $galatLain = collect(['hapus', 'format', 'cakupan', 'kolom', 'kolom.*', 'nik'])->flatMap(fn (string $kunci) => $errors->get($kunci))->flatten();

    $status = $filter['status'] ?? null;
    $kesehatan = $filter['kesehatan'] ?? null;
    $kartu = [
        ['label' => 'Total anak', 'jumlah' => $jumlahTotal, 'query' => [], 'aktif' => ! $status && ! $kesehatan],
        ['label' => 'Masih aktif', 'jumlah' => $jumlahAktif, 'query' => ['status' => StatusAsuh::MasihAktif->value], 'aktif' => $status === StatusAsuh::MasihAktif->value && ! $kesehatan],
        ['label' => 'Alumni', 'jumlah' => $jumlahAlumni, 'query' => ['status' => StatusAsuh::Alumni->value], 'aktif' => $status === StatusAsuh::Alumni->value],
        ['label' => 'Catatan kesehatan', 'jumlah' => $jumlahCatatanKesehatan, 'query' => ['status' => StatusAsuh::MasihAktif->value, 'kesehatan' => 'catatan'], 'aktif' => $kesehatan === 'catatan'],
    ];
@endphp

<x-layouts.admin title="Data Anak Panti">
    <x-admin.page-heading title="Data anak panti" description="Data anak asuh beserta wali, pendidikan, dan kesehatannya.">
        <button type="button" class="admin-btn admin-btn-secondary" data-dialog-buka="dialog-ekspor">
            <x-admin.icon name="download" width="16" height="16" />
            Export data
        </button>
        <button type="button" class="admin-btn admin-btn-primary" data-anak-buka data-url="{{ route('admin.anak-panti.store') }}" data-anak='@json($dataBaru)'>
            <x-admin.icon name="plus" width="16" height="16" />
            Tambah anak
        </button>
    </x-admin.page-heading>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kartu as $item)
            <div @class([
                'flex flex-col rounded-xl border bg-white',
                'border-footer ring-1 ring-footer' => $item['aktif'],
                'border-[#e5e5e5]' => ! $item['aktif'],
            ])>
                <div class="flex-1 px-4 pt-3.5 pb-3">
                    <p class="text-xs text-slate">{{ $item['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums">{{ $item['jumlah'] }}</p>
                </div>
                <a href="{{ route('admin.anak-panti.index', $item['query']) }}" class="flex items-center gap-1 border-t border-[#e5e5e5] px-4 py-2 text-xs text-slate hover:text-footer" @if ($item['aktif']) aria-current="page" @endif>
                    Lihat semua <span class="sr-only">{{ $item['label'] }}</span>
                    <x-admin.icon name="chevron-right" width="12" height="12" />
                </a>
            </div>
        @endforeach
    </div>

    <form method="GET" class="mt-5 flex flex-wrap items-center gap-2" role="search">
        <label class="sr-only" for="cari-anak">Cari nama atau NIK</label>
        <div class="relative w-full sm:w-[300px]">
            <x-admin.icon name="search" width="16" height="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate" />
            <input id="cari-anak" type="search" name="search" value="{{ $filter['search'] ?? '' }}" placeholder="Cari nama atau NIK" class="admin-input pl-9">
        </div>
        <x-admin.filter-select name="status" label="Semua status asuh" :options="$opsi(array_column(StatusAsuh::cases(), 'value'))" />
        <x-admin.filter-select name="keterangan" label="Semua status anak" :options="$opsi($pilihan['Keterangan'])" />
        <x-admin.filter-select name="jenis_kelamin" label="Semua jenis kelamin" :options="$labelJenisKelamin" />
        @if ($kesehatan)
            <input type="hidden" name="kesehatan" value="{{ $kesehatan }}">
        @endif
        @if ($filter)
            <a href="{{ route('admin.anak-panti.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
        @endif
    </form>

    @if ($galatLain->isNotEmpty())
        <div class="mt-4 rounded-lg border border-[#f3c2c5] bg-[#fdecee] px-4 py-3 text-[13px] text-[#a3212a]" role="alert">
            {{ $galatLain->first() }}
        </div>
    @endif

    <div class="admin-card mt-4 overflow-hidden" data-anak-daftar>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="w-10 py-3 pr-2 pl-5">
                            <input type="checkbox" class="size-4 align-middle accent-footer" aria-label="Pilih semua anak di halaman ini" data-anak-pilih-semua @disabled($anak->isEmpty())>
                        </th>
                        <th scope="col" class="px-4 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Jenis kelamin</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Umur</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status anak</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Pendidikan</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Kesehatan</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status asuh</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($anak as $item)
                        @php
                            $tanggalLahir = $item->Tanggal_Lahir?->locale('id');
                        @endphp
                        <tr class="has-[:checked]:bg-[#f5faf7]">
                            <td class="py-3 pr-2 pl-5">
                                <input type="checkbox" form="form-ekspor" name="nik[]" value="{{ $item->NIK }}" class="size-4 align-middle accent-footer" aria-label="Pilih {{ $item->Nama }}" data-anak-pilih>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->Nama }}</p>
                                <p class="text-xs text-slate tabular-nums">{{ $item->NIK }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $labelJenisKelamin[$item->Jenis_Kelamin->value] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($tanggalLahir)
                                    <p>{{ $item->umur() }} tahun</p>
                                    <p class="text-xs text-slate">{{ $tanggalLahir->translatedFormat('j M Y') }}</p>
                                @else
                                    <span class="text-slate">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $item->Keterangan }}</td>
                            <td class="px-4 py-3">
                                <p>{{ $item->Pendidikan }}</p>
                                <p class="text-xs text-slate">{{ $item->Status_Anak }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($item->punyaCatatanKesehatan())
                                    <x-admin.badge tone="amber">{{ $item->Kesehatan }}</x-admin.badge>
                                @else
                                    <span class="text-slate">{{ $item->Kesehatan ?: '-' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$item->Status_Asuh === StatusAsuh::MasihAktif ? 'green' : 'gray'" class="gap-1.5">
                                    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                    {{ $item->Status_Asuh->value }}
                                </x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <button type="button" class="admin-icon-btn hover:text-footer" aria-label="Edit data {{ $item->Nama }}" title="Edit" data-anak-buka data-url="{{ route('admin.anak-panti.update', $item) }}" data-anak='@json(Arr::only($item->attributesToArray(), $kolomForm))'>
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </button>
                                    @if ($item->isDeletable())
                                        <x-admin.delete-button :action="route('admin.anak-panti.destroy', $item)" :confirm="'Hapus data '.$item->Nama.'? Data yang dihapus tidak bisa dikembalikan.'" :label="'Hapus data '.$item->Nama" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-slate">Tidak ada data anak pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $anak->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'anak']) }}
    </div>

    {{-- Dialog tambah/edit. Terbuka lagi otomatis bila simpan gagal validasi. --}}
    <dialog id="dialog-anak" aria-labelledby="dialog-anak-judul" class="m-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-[800px] overflow-hidden rounded-2xl bg-white p-0 text-[#1e2328] shadow-xl backdrop:bg-[#1e2328]/45 open:flex" @if ($galatForm) data-buka @endif data-anak-dialog>
        <form method="POST" action="{{ $galatForm && ! $nikLama ? route('admin.anak-panti.store') : ($nikLama ? route('admin.anak-panti.update', $nikLama) : '') }}" class="flex max-h-full w-full flex-col" novalidate data-anak-form>
            @csrf
            <input type="hidden" name="_method" value="PUT" @disabled(! $nikLama) data-anak-method>
            <input type="hidden" name="_nik_lama" value="{{ $nikLama }}" data-anak-nik-lama>

            <div class="flex items-start justify-between gap-4 border-b border-[#eceeec] px-6 py-5 sm:px-8">
                <div>
                    <h2 id="dialog-anak-judul" class="text-lg font-semibold" data-anak-judul data-judul-tambah="Tambah data anak" data-judul-edit="Edit data anak">{{ $nikLama || ! $galatForm ? 'Edit data anak' : 'Tambah data anak' }}</h2>
                    <p class="mt-0.5 text-[13px] text-slate">Ubah data lalu simpan perubahan.</p>
                </div>
                <button type="button" class="admin-icon-btn" aria-label="Tutup" data-dialog-tutup>
                    <x-admin.icon name="x" width="16" height="16" />
                </button>
            </div>

            <div class="grid gap-x-8 gap-y-4 overflow-y-auto px-6 py-5 sm:grid-cols-2 sm:px-8">
                @foreach ($field as $nama => $konfigurasi)
                    @php
                        $nilai = (string) old($nama, '');
                        $galat = $errors->has($nama);
                    @endphp
                    <x-admin.field :label="$konfigurasi['label']" :name="$nama" :required="$konfigurasi['wajib']">
                        @if ($konfigurasi['jenis'] === 'select')
                            <select id="{{ $nama }}" name="{{ $nama }}" class="admin-input pr-8" @required($konfigurasi['wajib']) @if ($galat) aria-invalid="true" aria-describedby="{{ $nama }}-error" @endif>
                                <option value="">{{ $konfigurasi['kosong'] ?? 'Pilih '.strtolower($konfigurasi['label']) }}</option>
                                @foreach ($konfigurasi['pilihan'] as $nilaiOpsi => $teks)
                                    <option value="{{ $nilaiOpsi }}" @selected($nilai === (string) $nilaiOpsi)>{{ $teks }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $nama }}" type="{{ $konfigurasi['jenis'] === 'date' ? 'date' : 'text' }}" name="{{ $nama }}" value="{{ $nilai }}" class="admin-input" @required($konfigurasi['wajib']) @if ($konfigurasi['jenis'] === 'datalist') list="pilihan-{{ $nama }}" @endif @if ($galat) aria-invalid="true" aria-describedby="{{ $nama }}-error" @endif {!! $konfigurasi['atribut'] ?? '' !!}>
                            @if ($konfigurasi['jenis'] === 'datalist')
                                <datalist id="pilihan-{{ $nama }}">
                                    @foreach ($konfigurasi['pilihan'] as $teks)
                                        <option value="{{ $teks }}"></option>
                                    @endforeach
                                </datalist>
                            @endif
                        @endif
                    </x-admin.field>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 border-t border-[#eceeec] px-6 py-4 sm:px-8">
                <button type="button" class="admin-btn admin-btn-secondary min-w-24" data-dialog-tutup>Batal</button>
                <button type="submit" class="admin-btn admin-btn-primary min-w-28">Simpan</button>
            </div>
        </form>
    </dialog>

    {{-- Dialog export. Centang baris di tabel ikut terkirim lewat atribut form="form-ekspor". --}}
    <dialog id="dialog-ekspor" aria-labelledby="dialog-ekspor-judul" class="m-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-[640px] overflow-hidden rounded-2xl bg-white p-0 text-[#1e2328] shadow-xl backdrop:bg-[#1e2328]/45 open:flex">
        <form id="form-ekspor" action="{{ route('admin.anak-panti.ekspor') }}" method="GET" class="flex max-h-full w-full flex-col" data-ekspor-form>
            @foreach ($filter as $kunci => $nilai)
                <input type="hidden" name="filter[{{ $kunci }}]" value="{{ $nilai }}">
            @endforeach

            <div class="flex items-start justify-between gap-4 border-b border-[#eceeec] px-6 py-5 sm:px-8">
                <div>
                    <h2 id="dialog-ekspor-judul" class="text-lg font-semibold">Export data anak</h2>
                    <p class="mt-0.5 text-[13px] text-slate">Pilih format, data, dan kolom yang ingin diunduh.</p>
                </div>
                <button type="button" class="admin-icon-btn" aria-label="Tutup" data-dialog-tutup>
                    <x-admin.icon name="x" width="16" height="16" />
                </button>
            </div>

            <div class="space-y-6 overflow-y-auto px-6 py-5 sm:px-8">
                <fieldset>
                    <legend class="admin-label">Format file</legend>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach (['xlsx' => 'Excel', 'csv' => 'CSV', 'pdf' => 'PDF'] as $format => $judul)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#d9dcd9] px-4 py-3 transition hover:bg-[#f7faf8] has-[:checked]:border-footer has-[:checked]:bg-[#e3f1ea] has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-footer/20">
                                <input type="radio" name="format" value="{{ $format }}" class="sr-only" @checked($loop->first)>
                                <x-admin.icon name="file-text" width="20" height="20" class="shrink-0 text-slate" />
                                <span>
                                    <span class="block text-sm font-semibold">{{ $judul }}</span>
                                    <span class="block text-xs text-slate">Berekstensi .{{ $format }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="space-y-3 text-[13px]">
                    <legend class="admin-label">Data yang di-export</legend>
                    <label class="flex items-center gap-3">
                        <input type="radio" name="cakupan" value="semua" class="size-4 accent-footer" checked>
                        <span class="font-semibold">Semua data</span>
                        <span class="text-xs text-slate">{{ $jumlahTotal }} anak</span>
                    </label>
                    <label class="flex items-center gap-3 has-[:disabled]:opacity-50">
                        <input type="radio" name="cakupan" value="filter" class="size-4 accent-footer" @disabled(! $filter)>
                        <span class="font-semibold">Sesuai filter saat ini</span>
                        <span class="text-xs text-slate">{{ $filter ? $jumlahSesuaiFilter.' anak dari hasil pencarian dan filter di daftar' : 'Belum ada pencarian atau filter di daftar' }}</span>
                    </label>
                    <label class="flex items-center gap-3 has-[:disabled]:opacity-50">
                        <input type="radio" name="cakupan" value="dipilih" class="size-4 accent-footer" disabled data-ekspor-dipilih>
                        <span class="font-semibold">Data yang dipilih</span>
                        <span class="text-xs text-slate" data-ekspor-info-dipilih data-kosong="Pilih baris di daftar terlebih dulu">Pilih baris di daftar terlebih dulu</span>
                    </label>
                </fieldset>

                <fieldset>
                    <div class="mb-3 flex items-center justify-between">
                        <legend class="admin-label mb-0">Kolom yang disertakan</legend>
                        <button type="button" class="text-[13px] font-semibold text-footer hover:underline" data-ekspor-pilih-semua>Pilih semua</button>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-[13px] sm:grid-cols-3">
                        @foreach (AnakPanti::KOLOM_EKSPOR as $kunci => $judul)
                            <label class="flex items-center gap-2.5">
                                <input type="checkbox" name="kolom[]" value="{{ $kunci }}" class="size-4 accent-footer" checked data-ekspor-kolom>
                                {{ $judul }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-[#eceeec] px-6 py-4 sm:px-8">
                <p class="text-xs text-slate" aria-live="polite"><span data-ekspor-jumlah-kolom>{{ count(AnakPanti::KOLOM_EKSPOR) }}</span> kolom dipilih</p>
                <div class="flex gap-3">
                    <button type="button" class="admin-btn admin-btn-secondary min-w-24" data-dialog-tutup>Batal</button>
                    <button type="submit" class="admin-btn admin-btn-primary disabled:pointer-events-none disabled:opacity-50" data-ekspor-kirim>
                        <x-admin.icon name="download" width="16" height="16" />
                        Export data
                    </button>
                </div>
            </div>
        </form>
    </dialog>
</x-layouts.admin>
