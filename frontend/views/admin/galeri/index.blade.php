@php
    use App\Http\Requests\Admin\GaleriRequest;

    $galatUnggah = collect($errors->get('foto'))->merge(collect($errors->get('foto.*'))->flatten());
    $galatLain = collect(['keterangan', 'urutan', 'urutan.*'])->flatMap(fn (string $kunci) => $errors->get($kunci))->flatten();
@endphp

<x-layouts.admin title="Kelola Galeri">
    <x-admin.page-heading title="Kelola galeri" description="Foto tampil di halaman Galeri website sesuai susunan di bawah ini, dari kiri atas." />

    <form action="{{ route('admin.galeri.store') }}" method="POST" enctype="multipart/form-data" class="admin-card mb-6 grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_320px]" data-galeri-unggah>
        @csrf

        <div>
            <label for="foto" class="flex min-h-36 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-[#c9d6cf] bg-[#f7faf8] px-4 py-6 text-center transition hover:border-footer data-[seret]:border-footer data-[seret]:bg-[#e3f1ea]" data-galeri-dropzone>
                <span class="flex size-11 items-center justify-center rounded-full bg-[#e3f1ea] text-footer">
                    <x-admin.icon name="image" width="20" height="20" />
                </span>
                <span class="text-sm font-semibold text-[#1e2328]">Pilih foto atau seret ke sini</span>
                <span class="text-xs text-slate">JPG, PNG, atau WEBP. Maksimal 5 MB per foto, {{ GaleriRequest::JUMLAH_MAKS }} foto sekali unggah.</span>
                <span class="text-xs font-medium text-footer" aria-live="polite" data-galeri-terpilih></span>
            </label>
            <input id="foto" type="file" name="foto[]" accept="image/jpeg,image/png,image/webp" multiple required class="sr-only" @if ($galatUnggah->isNotEmpty()) aria-invalid="true" aria-describedby="foto-error" @endif data-galeri-berkas>

            @if ($galatUnggah->isNotEmpty())
                <ul id="foto-error" class="mt-2 space-y-0.5 text-xs text-[#c0262f]">
                    @foreach ($galatUnggah->unique() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex flex-col gap-4">
            <x-admin.field label="Keterangan (opsional)" name="keterangan_foto" hint="Dipakai untuk semua foto yang diunggah kali ini.">
                <input id="keterangan_foto" type="text" name="keterangan" maxlength="150" value="{{ old('keterangan') }}" class="admin-input" placeholder="mis. Lebaran Yatim 1447 H">
            </x-admin.field>

            <button type="submit" class="admin-btn admin-btn-primary mt-auto">
                <x-admin.icon name="plus" width="16" height="16" />
                Unggah foto
            </button>
        </div>
    </form>

    @if ($galatLain->isNotEmpty())
        <div class="mb-5 rounded-lg border border-[#f3c2c5] bg-[#fdecee] px-4 py-3 text-[13px] text-[#a3212a]" role="alert">
            {{ $galatLain->first() }}
        </div>
    @endif

    {{-- Form tanpa isi: dipakai tombol dan centang di grid lewat atribut form="". --}}
    @can('admin')
        <form id="hapus-banyak" action="{{ route('admin.galeri.hapus-banyak') }}" method="POST" data-confirm="Hapus foto yang dipilih? Foto yang dihapus tidak bisa dikembalikan.">
            @csrf
            @method('DELETE')
        </form>
    @endcan

    <div class="admin-card" data-galeri>
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#f0f0f0] px-4 py-3">
            <div class="flex flex-wrap items-center gap-3 text-[13px]">
                @can('admin')
                    @if ($foto->isNotEmpty())
                        <label class="flex items-center gap-2 font-medium">
                            <input type="checkbox" class="size-4 accent-footer" data-galeri-pilih-semua>
                            Pilih semua
                        </label>
                        <span class="text-slate" aria-live="polite"><span data-galeri-jumlah-pilih>0</span> dipilih</span>
                        <button type="submit" form="hapus-banyak" class="admin-btn h-8 border border-[#f3c2c5] bg-white px-3 text-[#c0262f] hover:bg-[#fdecee] disabled:pointer-events-none disabled:opacity-50" disabled data-galeri-hapus>
                            <x-admin.icon name="trash" width="14" height="14" />
                            Hapus terpilih
                        </button>
                    @endif
                @endcan
                @cannot('admin')
                    <span class="text-slate">{{ $foto->total() }} foto</span>
                @endcannot
            </div>

            <form action="{{ route('admin.galeri.urutan') }}" method="POST" class="flex items-center gap-2" data-galeri-form-urutan>
                @csrf
                @method('PATCH')
                <p class="text-xs text-slate" data-galeri-petunjuk>Seret foto atau pakai tombol panah untuk mengubah urutan.</p>
                <p class="hidden text-xs font-medium text-[#a15c00]" data-galeri-berubah>Urutan berubah, belum disimpan.</p>
                <a href="{{ request()->fullUrl() }}" class="admin-btn admin-btn-secondary hidden h-8 px-3" data-galeri-batal>Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary hidden h-8 px-3" data-galeri-simpan>Simpan urutan</button>
            </form>
        </div>

        @if ($foto->isEmpty())
            <p class="px-4 py-16 text-center text-[13px] text-slate">Belum ada foto. Unggah foto kegiatan pertama di atas.</p>
        @else
            <ul class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-galeri-grid>
                @foreach ($foto as $item)
                    @php($nomor = $foto->firstItem() + $loop->index)
                    <li class="group overflow-hidden rounded-xl border border-[#e5e5e5] bg-white transition data-[diseret]:opacity-40 data-[sasaran]:ring-2 data-[sasaran]:ring-footer" draggable="true" data-galeri-item data-id="{{ $item->id }}">
                        <div class="relative cursor-grab bg-[#f3f4f2] active:cursor-grabbing">
                            <img src="{{ $item->url() }}" alt="{{ $item->teksAlt() }}" class="aspect-[4/3] w-full object-cover" loading="lazy" draggable="false">
                            @can('admin')
                                <label class="absolute top-2 left-2 flex size-8 items-center justify-center rounded-lg bg-white/90 shadow-sm">
                                    <input type="checkbox" form="hapus-banyak" name="foto[]" value="{{ $item->id }}" class="size-4 accent-footer" aria-label="Pilih foto {{ $nomor }}" data-galeri-pilih>
                                </label>
                            @endcan
                            <span class="absolute top-2 right-2 rounded-md bg-black/55 px-2 py-0.5 text-xs font-semibold text-white tabular-nums" data-galeri-nomor>{{ $nomor }}</span>
                        </div>

                        <div class="space-y-3 p-3">
                            <form action="{{ route('admin.galeri.update', $item) }}" method="POST" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <label for="keterangan-{{ $item->id }}" class="sr-only">Keterangan foto {{ $nomor }}</label>
                                <input id="keterangan-{{ $item->id }}" type="text" name="keterangan" value="{{ $item->keterangan }}" maxlength="150" placeholder="Tambah keterangan" class="admin-input h-8 text-xs">
                                <button type="submit" class="admin-icon-btn shrink-0" aria-label="Simpan keterangan foto {{ $nomor }}" title="Simpan keterangan">
                                    <x-admin.icon name="check" width="16" height="16" />
                                </button>
                            </form>

                            <div class="flex items-center justify-between">
                                <div class="flex gap-1">
                                    <button type="button" class="admin-icon-btn disabled:opacity-40" aria-label="Geser foto {{ $nomor }} ke depan" title="Geser ke depan" data-galeri-geser="-1">
                                        <x-admin.icon name="arrow-left" width="16" height="16" />
                                    </button>
                                    <button type="button" class="admin-icon-btn disabled:opacity-40" aria-label="Geser foto {{ $nomor }} ke belakang" title="Geser ke belakang" data-galeri-geser="1">
                                        <x-admin.icon name="arrow-left" width="16" height="16" class="rotate-180" />
                                    </button>
                                </div>
                                <div class="flex gap-1">
                                    <a href="{{ $item->url() }}" class="admin-icon-btn" aria-label="Lihat foto {{ $nomor }}" title="Lihat foto" target="_blank" rel="noopener">
                                        <x-admin.icon name="eye" width="16" height="16" />
                                    </a>
                                    <x-admin.delete-button :action="route('admin.galeri.destroy', $item)" confirm="Hapus foto ini?" :label="'Hapus foto '.$nomor" />
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        {{ $foto->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'foto']) }}
    </div>
</x-layouts.admin>
