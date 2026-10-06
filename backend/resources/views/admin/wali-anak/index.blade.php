<x-layouts.admin title="Data Wali Anak">
    <x-admin.page-heading title="Data wali anak" description="Wali bisa dipilih saat mengisi data anak panti.">
        <a href="{{ route('admin.wali-anak.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Tambah wali
        </a>
    </x-admin.page-heading>

    <form method="GET" class="flex flex-wrap items-center gap-2" role="search">
        <label class="sr-only" for="cari-wali">Cari nama atau alamat wali</label>
        <input id="cari-wali" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau alamat wali" class="admin-input w-full sm:w-[280px]">
        <button type="submit" class="admin-btn admin-btn-secondary">Cari</button>
        @if (request()->filled('search'))
            <a href="{{ route('admin.wali-anak.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Nama wali</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Alamat</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Anak diwalikan</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($wali as $item)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $item->Nama_Wali }}</td>
                            <td class="px-4 py-3">{{ $item->Alamat_Wali }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ $item->anak_count }} anak</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.wali-anak.edit', $item) }}" class="admin-icon-btn" aria-label="Ubah {{ $item->Nama_Wali }}" title="Ubah">
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </a>
                                    <x-admin.delete-button
                                        :action="route('admin.wali-anak.destroy', $item)"
                                        :confirm="'Hapus wali '.$item->Nama_Wali.'?'.($item->anak_count ? ' '.$item->anak_count.' anak akan tidak punya wali.' : '')"
                                        :label="'Hapus '.$item->Nama_Wali" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-slate">Belum ada data wali.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $wali->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'wali']) }}
    </div>
</x-layouts.admin>
