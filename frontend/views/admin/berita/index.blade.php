@php
    use App\Enums\StatusBerita;
    use App\Models\Berita;
@endphp

<x-layouts.admin title="Kelola Artikel Kegiatan">
    <x-admin.page-heading title="Kelola artikel kegiatan" description="Tulis, ubah, dan terbitkan artikel kegiatan panti.">
        <a href="{{ route('admin.berita.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Tulis artikel
        </a>
    </x-admin.page-heading>

    <div class="admin-card">
        <x-admin.search-filter placeholder="Cari judul artikel">
            <x-admin.filter-select name="kategori" label="Semua kategori" :options="collect(Berita::daftarKategori())->mapWithKeys(fn ($kategori) => [$kategori => $kategori])" />
            <x-admin.filter-select name="status" label="Semua status" :options="collect(StatusBerita::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
        </x-admin.search-filter>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Artikel</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Kategori</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Tanggal terbit</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($berita as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($item->gambarUrl())
                                        <img src="{{ $item->gambarUrl() }}" alt="" class="size-12 shrink-0 rounded-lg object-cover">
                                    @else
                                        <span class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-[#f3f4f2] text-slate">
                                            <x-admin.icon name="image" width="18" height="18" />
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="line-clamp-2 font-semibold">{{ $item->judul }}</p>
                                        @if ($item->ringkasan)
                                            <p class="line-clamp-1 text-xs text-slate">{{ $item->ringkasan }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->kategori }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->tanggal_terbit->locale('id')->translatedFormat('j M Y') }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$item->status === StatusBerita::Terbit ? 'green' : 'gray'">{{ $item->status->value }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.berita.edit', $item) }}" class="admin-icon-btn" aria-label="Ubah {{ $item->judul }}" title="Ubah">
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </a>
                                    <x-admin.delete-button :action="route('admin.berita.destroy', $item)" :confirm="'Hapus artikel '.$item->judul.'?'" :label="'Hapus '.$item->judul" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate">Belum ada artikel.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $berita->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'artikel']) }}
    </div>
</x-layouts.admin>
