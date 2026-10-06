<x-layouts.app title="Anak Asuh">
    <x-page-header title="Anak Asuh" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Anak Asuh' => null]" />

    <section class="mx-auto w-full max-w-[640px] px-4 py-10 font-poppins text-[13px] text-[#212529] lg:pt-[50px] lg:pb-[60px]">
        <form action="{{ route('tentang.anak-asuh') }}" method="GET" class="flex items-center gap-2" role="search">
            <label for="cari-anak">Search:</label>
            <input id="cari-anak" type="search" name="q" value="{{ request('q') }}" class="h-8 w-full max-w-[234px] rounded border border-field px-2 focus:border-[#86b7fe] focus:ring-2 focus:ring-[#0d6efd]/25 focus:outline-none">
            @if ($isDescending)
                <input type="hidden" name="urut" value="desc">
            @endif
        </form>

        <div class="mt-6 overflow-x-auto border border-[#e5e5e5]">
            <table class="w-full min-w-[520px] border-separate border-spacing-0.5 text-left">
                <thead>
                    <tr>
                        <th scope="col" class="w-12 border-b border-[#e5e5e5] px-3 py-4 text-center font-normal">No</th>
                        <th scope="col" class="border-b border-[#e5e5e5] px-3 py-4 font-normal" aria-sort="{{ $isDescending ? 'descending' : 'ascending' }}">
                            <a href="{{ request()->fullUrlWithQuery(['urut' => $isDescending ? null : 'desc', 'page' => null]) }}" class="flex items-center justify-between gap-2 hover:text-brand-red">
                                Nama
                                <svg width="10" height="14" viewBox="0 0 10 14" fill="none" stroke="currentColor" stroke-width="1.2" class="text-slate-light" aria-hidden="true"><path d="m2 5 3-3 3 3" @class(['opacity-30' => $isDescending]) /><path d="m2 9 3 3 3-3" @class(['opacity-30' => ! $isDescending]) /></svg>
                                <span class="sr-only">(urutkan {{ $isDescending ? 'A-Z' : 'Z-A' }})</span>
                            </a>
                        </th>
                        <th scope="col" class="border-b border-[#e5e5e5] px-3 py-4 font-normal">Jenis Kelamin</th>
                        <th scope="col" class="border-b border-[#e5e5e5] px-3 py-4 font-normal">Pendidikan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($children as $child)
                        <tr class="odd:[&>td]:bg-[#f1f1f1]">
                            <td class="px-3 py-3.5 text-center">{{ $children->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-3.5 uppercase">{{ $child->Nama }}</td>
                            <td class="px-3 py-3.5">{{ $child->Jenis_Kelamin->value }}</td>
                            <td class="px-3 py-3.5">{{ $child->Pendidikan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-placeholder">Data tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="mt-4">Menampilkan {{ $children->firstItem() ?? 0 }} sampai {{ $children->lastItem() ?? 0 }} dari {{ $children->total() }} data</p>
        @if ($children->hasPages())
            <x-pagination class="mt-2" :current="$children->currentPage()" :last="$children->lastPage()" />
        @endif
    </section>
</x-layouts.app>
