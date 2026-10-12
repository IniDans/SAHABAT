@php
    use App\Enums\JenisKegiatan;
@endphp

<x-layouts.admin title="Kegiatan Panti">
    <x-admin.page-heading title="Kegiatan panti" description="Catatan kegiatan harian dan acara panti.">
        <a href="{{ route('admin.kegiatan.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Tambah kegiatan
        </a>
    </x-admin.page-heading>

    <form method="GET" class="flex flex-wrap items-center gap-2" role="search">
        <label class="sr-only" for="cari-kegiatan">Cari nama kegiatan atau lokasi</label>
        <input id="cari-kegiatan" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama kegiatan atau lokasi" class="admin-input w-full sm:w-[280px]">
        <x-admin.filter-select name="jenis" label="Semua jenis" :options="collect(JenisKegiatan::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
        @if (request()->hasAny(['search', 'jenis']))
            <a href="{{ route('admin.kegiatan.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Kegiatan</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Jenis</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Tanggal</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Lokasi</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($kegiatan as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($item->fotoPath())
                                        <img src="{{ route('admin.kegiatan.foto', $item) }}" alt="" width="48" height="48" loading="lazy" class="size-12 shrink-0 rounded-lg object-cover">
                                    @else
                                        <span class="grid size-12 shrink-0 place-items-center rounded-lg bg-[#f0f0f0] text-slate" aria-hidden="true">
                                            <x-admin.icon name="calendar" width="18" height="18" />
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-semibold">{{ $item->nama_kegiatan }}</p>
                                        @if ($item->deskripsi)
                                            <p class="line-clamp-1 text-xs text-slate">{{ $item->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><x-admin.badge tone="gray">{{ $item->jenis_kegiatan->value }}</x-admin.badge></td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->tanggal_kegiatan?->translatedFormat('j M Y') }}</td>
                            <td class="px-4 py-3">{{ $item->lokasi ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.kegiatan.edit', $item) }}" class="admin-icon-btn" aria-label="Ubah {{ $item->nama_kegiatan }}" title="Ubah">
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </a>
                                    <x-admin.delete-button :action="route('admin.kegiatan.destroy', $item)" :confirm="'Hapus kegiatan '.$item->nama_kegiatan.'?'" :label="'Hapus '.$item->nama_kegiatan" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate">Belum ada kegiatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $kegiatan->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'kegiatan']) }}
    </div>
</x-layouts.admin>
