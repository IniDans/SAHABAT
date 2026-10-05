<x-layouts.app title="Anak Asuh">
    <x-page-header title="Anak Asuh" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Anak Asuh' => null]" />

    <section class="mx-auto w-full max-w-[860px] px-4 py-10 font-open text-sm text-[#212529] lg:pt-[50px] lg:pb-[60px]" data-table-filter>
        <div class="flex items-center gap-2">
            <label for="cari-anak">Search:</label>
            <input id="cari-anak" type="search" class="h-8 w-full max-w-[200px] rounded border border-field px-2 focus:border-[#86b7fe] focus:ring-2 focus:ring-[#0d6efd]/25 focus:outline-none" data-table-search>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[520px] border-collapse text-left">
                <thead>
                    <tr class="border-b-2 border-[#dee2e6]">
                        <th scope="col" class="w-14 px-3 py-2 font-bold">No</th>
                        <th scope="col" class="px-3 py-2 font-bold">Nama</th>
                        <th scope="col" class="px-3 py-2 font-bold">Jenis Kelamin</th>
                        <th scope="col" class="px-3 py-2 font-bold">Pendidikan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($children as $child)
                        <tr class="border-b border-[#dee2e6] odd:bg-black/[0.05]" data-table-row>
                            <td class="px-3 py-2">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2">{{ $child['nama'] }}</td>
                            <td class="px-3 py-2">{{ $child['jenis_kelamin'] }}</td>
                            <td class="px-3 py-2">{{ $child['pendidikan'] }}</td>
                        </tr>
                    @endforeach
                    <tr hidden data-table-empty>
                        <td colspan="4" class="px-3 py-6 text-center text-placeholder">Data tidak ditemukan</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
            <p data-table-info data-total="{{ $children->count() }}">Menampilkan 1 sampai {{ $children->count() }} dari {{ $children->count() }} data</p>
            <x-pagination :current="1" :last="1" />
        </div>
    </section>
</x-layouts.app>
