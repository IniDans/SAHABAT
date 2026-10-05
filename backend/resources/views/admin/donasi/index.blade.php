@php
    use App\Enums\ProgramDonasi;
    use App\Enums\StatusDonasi;
    use App\Models\Donasi;

    $statusTone = [
        StatusDonasi::Menunggu->value => 'amber',
        StatusDonasi::Diterima->value => 'green',
        StatusDonasi::Ditolak->value => 'red',
    ];
@endphp

<x-layouts.admin title="Kelola Donasi">
    <x-admin.page-heading title="Kelola donasi" description="Catat donasi yang masuk dan tandai setelah dananya dicek.">
        <a href="{{ route('admin.donasi.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Catat donasi
        </a>
    </x-admin.page-heading>

    <div class="mb-5 grid gap-4 sm:grid-cols-2">
        <div class="admin-card p-5">
            <p class="text-[13px] text-slate">Total donasi diterima</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ Donasi::rupiah($totalDiterima) }}</p>
            <p class="mt-1 text-xs text-slate">Sesuai filter yang dipilih</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-[13px] text-slate">Menunggu dicek</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $jumlahMenunggu }} donasi</p>
            <p class="mt-1 text-xs text-slate">Cocokkan dengan mutasi rekening atau QRIS</p>
        </div>
    </div>

    <div class="admin-card">
        <x-admin.search-filter placeholder="Cari nama donatur">
            <x-admin.filter-select name="program" label="Semua program" :options="collect(ProgramDonasi::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
            <x-admin.filter-select name="status" label="Semua status" :options="collect(StatusDonasi::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
            <input type="date" name="dari" value="{{ request('dari') }}" aria-label="Dari tanggal" class="admin-input w-auto">
            <span class="text-[13px] text-slate">s.d.</span>
            <input type="date" name="sampai" value="{{ request('sampai') }}" aria-label="Sampai tanggal" class="admin-input w-auto">
        </x-admin.search-filter>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#fafafa] text-xs text-slate">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Donatur</th>
                        <th scope="col" class="px-4 py-3 font-medium">Program</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Nominal</th>
                        <th scope="col" class="px-4 py-3 font-medium">Metode</th>
                        <th scope="col" class="px-4 py-3 font-medium">Tanggal</th>
                        <th scope="col" class="px-4 py-3 font-medium">Status</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($donasi as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->nama_donatur }}</p>
                                @if ($item->no_whatsapp || $item->email)
                                    <p class="text-xs text-slate">{{ collect([$item->no_whatsapp, $item->email])->filter()->implode(' · ') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->program->value }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap tabular-nums">{{ Donasi::rupiah($item->nominal) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->metode_pembayaran->value }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->tanggal_donasi->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$statusTone[$item->status->value]">{{ $item->status->value }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    @if ($item->status === StatusDonasi::Menunggu)
                                        <form action="{{ route('admin.donasi.status', $item) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ StatusDonasi::Diterima->value }}">
                                            <button type="submit" class="admin-icon-btn hover:text-footer" aria-label="Terima donasi {{ $item->nama_donatur }}" title="Terima">
                                                <x-admin.icon name="check" width="16" height="16" />
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.donasi.status', $item) }}" method="POST" data-confirm="Tolak donasi {{ $item->nama_donatur }}?">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ StatusDonasi::Ditolak->value }}">
                                            <button type="submit" class="admin-icon-btn hover:text-[#c0262f]" aria-label="Tolak donasi {{ $item->nama_donatur }}" title="Tolak">
                                                <x-admin.icon name="x" width="16" height="16" />
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.donasi.edit', $item) }}" class="admin-icon-btn" aria-label="Ubah donasi {{ $item->nama_donatur }}" title="Ubah">
                                        <x-admin.icon name="pencil" width="16" height="16" />
                                    </a>
                                    <x-admin.delete-button :action="route('admin.donasi.destroy', $item)" :confirm="'Hapus donasi dari '.$item->nama_donatur.'?'" :label="'Hapus donasi '.$item->nama_donatur" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate">Belum ada donasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($donasi->hasPages())
            <div class="border-t border-[#e5e5e5] p-4">{{ $donasi->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
