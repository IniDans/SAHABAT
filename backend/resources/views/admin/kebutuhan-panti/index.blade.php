@php
    use App\Enums\PrioritasKebutuhan;

    $prioritasTone = [
        PrioritasKebutuhan::Mendesak->value => 'red',
        PrioritasKebutuhan::Sedang->value => 'amber',
        PrioritasKebutuhan::Rendah->value => 'gray',
    ];
@endphp

<x-layouts.admin title="Kebutuhan Panti">
    <x-admin.page-heading title="Kebutuhan panti" description="Daftar kebutuhan yang dibutuhkan panti, diurutkan dari prioritas tertinggi.">
        <a href="{{ route('admin.kebutuhan-panti.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Tambah kebutuhan
        </a>
    </x-admin.page-heading>

    <div class="admin-card">
        <x-admin.search-filter placeholder="Cari nama kebutuhan">
            <x-admin.filter-select name="prioritas" label="Semua prioritas" :options="collect(PrioritasKebutuhan::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
            <x-admin.filter-select name="status" label="Semua status" :options="['belum' => 'Belum terpenuhi', 'terpenuhi' => 'Terpenuhi']" />
        </x-admin.search-filter>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#fafafa] text-xs text-slate">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Kebutuhan</th>
                        <th scope="col" class="px-4 py-3 font-medium">Jumlah</th>
                        <th scope="col" class="px-4 py-3 font-medium">Prioritas</th>
                        <th scope="col" class="px-4 py-3 font-medium">Skor</th>
                        <th scope="col" class="px-4 py-3 font-medium">Status</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($kebutuhan as $item)
                        <tr @class(['text-slate' => $item->terpenuhi])>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->nama }}</p>
                                @if ($item->keterangan)
                                    <p class="line-clamp-1 text-xs text-slate">{{ $item->keterangan }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $item->jumlah ? number_format($item->jumlah, 0, ',', '.').' '.$item->satuan : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$prioritasTone[$item->prioritas->value]">{{ $item->prioritas->value }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-[#eef0ee]" aria-hidden="true">
                                        <div class="h-full rounded-full bg-footer" style="width: {{ $item->skor_prioritas }}%"></div>
                                    </div>
                                    <span class="tabular-nums">{{ $item->skor_prioritas }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$item->terpenuhi ? 'green' : 'gray'">{{ $item->terpenuhi ? 'Terpenuhi' : 'Belum terpenuhi' }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.kebutuhan-panti.edit', $item) }}" class="admin-icon-btn" aria-label="Ubah {{ $item->nama }}" title="Ubah">
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </a>
                                    <x-admin.delete-button :action="route('admin.kebutuhan-panti.destroy', $item)" :confirm="'Hapus kebutuhan '.$item->nama.'?'" :label="'Hapus '.$item->nama" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate">Belum ada kebutuhan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($kebutuhan->hasPages())
            <div class="border-t border-[#e5e5e5] p-4">{{ $kebutuhan->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
