@php
    use App\Enums\JenisKelamin;
@endphp

<x-layouts.admin title="Data Pengasuh">
    <x-admin.page-heading title="Data pengasuh" description="Pengasuh dan pengurus harian panti.">
        <a href="{{ route('admin.pengasuh.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Tambah pengasuh
        </a>
    </x-admin.page-heading>

    <form method="GET" class="flex flex-wrap items-center gap-2" role="search">
        <label class="sr-only" for="cari-pengasuh">Cari nama, NIK, atau jabatan</label>
        <input id="cari-pengasuh" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIK, atau jabatan" class="admin-input w-full sm:w-[280px]">
        <x-admin.filter-select name="jenis_kelamin" label="Semua jenis kelamin" :options="collect(JenisKelamin::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
        @if (request()->hasAny(['search', 'jenis_kelamin']))
            <a href="{{ route('admin.pengasuh.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Jabatan</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Jenis kelamin</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Kontak</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($pengasuh as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->Nama }}</p>
                                <p class="text-xs text-slate tabular-nums">NIK {{ $item->NIK }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $item->Jabatan }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->Jenis_Kelamin?->value }}</td>
                            <td class="px-4 py-3">
                                <p class="tabular-nums">{{ $item->Nomor_Telepon }}</p>
                                <p class="text-xs text-slate">{{ $item->Email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.pengasuh.edit', $item) }}" class="admin-icon-btn" aria-label="Ubah {{ $item->Nama }}" title="Ubah">
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </a>
                                    <x-admin.delete-button :action="route('admin.pengasuh.destroy', $item)" :confirm="'Hapus data pengasuh '.$item->Nama.'?'" :label="'Hapus '.$item->Nama" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate">Belum ada data pengasuh.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $pengasuh->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'pengasuh']) }}
    </div>
</x-layouts.admin>
