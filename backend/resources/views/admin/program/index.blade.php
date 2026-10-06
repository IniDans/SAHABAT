@php
    use App\Enums\StatusBerita;
    use App\Models\Program;
@endphp

<x-layouts.admin title="Kelola Program">
    <x-admin.page-heading title="Kelola program" description="Tulis program panti dan pilih program yang tampil di Beranda.">
        <a href="{{ route('admin.program.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Tulis program
        </a>
    </x-admin.page-heading>

    <div class="admin-card">
        <x-admin.search-filter placeholder="Cari judul program">
            <x-admin.filter-select name="kategori" label="Semua kategori" :options="collect(Program::daftarKategori())->mapWithKeys(fn ($kategori) => [$kategori => $kategori])" />
            <x-admin.filter-select name="status" label="Semua status" :options="collect(StatusBerita::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
        </x-admin.search-filter>

        <p class="border-t border-[#f0f0f0] px-4 py-2.5 text-xs text-slate">
            {{ $jumlahDiBeranda }} program terbit sedang tampil di Beranda.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Program</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Kategori</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Tanggal terbit</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Tampil di Beranda</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($program as $item)
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
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->tanggal_terbit?->locale('id')->translatedFormat('j M Y') ?? '–' }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$item->status === StatusBerita::Terbit ? 'green' : 'gray'">{{ $item->status->value }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <form action="{{ route('admin.program.beranda', $item) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" role="switch" aria-checked="{{ $item->tampil_di_beranda ? 'true' : 'false' }}" aria-label="Tampilkan {{ $item->judul }} di Beranda" @class([
                                        'relative h-6 w-11 rounded-full transition focus-visible:ring-4 focus-visible:ring-footer/25 focus-visible:outline-none',
                                        'bg-footer' => $item->tampil_di_beranda,
                                        'bg-[#d9dcd9]' => ! $item->tampil_di_beranda,
                                    ])>
                                        <span @class([
                                            'absolute top-0.5 left-0.5 size-5 rounded-full bg-white shadow transition',
                                            'translate-x-5' => $item->tampil_di_beranda,
                                        ])></span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    @if ($item->status === StatusBerita::Terbit)
                                        <a href="{{ route('program.show', $item->slug) }}" class="admin-icon-btn" aria-label="Lihat {{ $item->judul }} di website" title="Lihat di website" target="_blank" rel="noopener">
                                            <x-admin.icon name="eye" width="16" height="16" />
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.program.edit', $item) }}" class="admin-icon-btn" aria-label="Ubah {{ $item->judul }}" title="Ubah">
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </a>
                                    <x-admin.delete-button :action="route('admin.program.destroy', $item)" :confirm="'Hapus program '.$item->judul.'?'" :label="'Hapus '.$item->judul" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate">Belum ada program.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $program->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'program']) }}
    </div>
</x-layouts.admin>
