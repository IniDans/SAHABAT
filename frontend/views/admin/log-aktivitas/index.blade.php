@php
    use App\Models\LogAktivitas;

    $aksiTone = [
        LogAktivitas::AKSI_TAMBAH => 'green',
        LogAktivitas::AKSI_UBAH => 'amber',
        LogAktivitas::AKSI_HAPUS => 'red',
        LogAktivitas::AKSI_MASUK => 'gray',
        LogAktivitas::AKSI_UNDUH => 'gray',
    ];
@endphp

<x-layouts.admin title="Log Aktivitas">
    <x-admin.page-heading title="Log aktivitas" description="Siapa menambah, mengubah, atau menghapus data, dan kapan. Hanya nama kolom yang dicatat, bukan isinya." />

    <div class="admin-card overflow-hidden">
        <x-admin.search-filter placeholder="Cari nama data">
            <x-admin.filter-select name="user" label="Semua pengguna" :options="$pengguna" />
            <x-admin.filter-select name="jenis" label="Semua jenis data" :options="LogAktivitas::JENIS_DATA" />
            <x-admin.filter-select name="aksi" label="Semua aksi" :options="array_combine($aksi = array_keys($aksiTone), $aksi)" />
            <label class="flex items-center gap-1.5 text-[13px] text-slate">
                Dari
                <input type="date" name="dari" value="{{ request('dari') }}" max="{{ today()->toDateString() }}" class="admin-input w-auto">
            </label>
            <label class="flex items-center gap-1.5 text-[13px] text-slate">
                Sampai
                <input type="date" name="sampai" value="{{ request('sampai') }}" max="{{ today()->toDateString() }}" class="admin-input w-auto">
            </label>
        </x-admin.search-filter>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Waktu</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Pengguna</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Aksi</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Data</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Kolom diubah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($log as $item)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $item->created_at->locale('id')->translatedFormat('j M Y') }}
                                <span class="block text-xs text-slate tabular-nums">{{ $item->created_at->format('H:i') }} &middot; {{ $item->ip }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $item->user?->name ?? $item->nama_pengguna }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$aksiTone[$item->aksi] ?? 'gray'">{{ $item->aksi }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                @if ($item->jenisData())
                                    <span class="text-xs text-slate">{{ $item->jenisData() }}</span>
                                @endif
                                <p class="font-semibold">{{ $item->subjek_label ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate">{{ $item->kolom ? implode(', ', $item->kolom) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate">Belum ada aktivitas pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $log->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'aktivitas']) }}
    </div>
</x-layouts.admin>
