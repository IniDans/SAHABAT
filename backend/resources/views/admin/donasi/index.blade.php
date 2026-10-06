@php
    use App\Enums\ProgramDonasi;
    use App\Enums\StatusDonasi;
    use App\Http\Controllers\Admin\DonasiController;
    use App\Models\Donasi;

    $statusTone = [
        StatusDonasi::Menunggu->value => 'amber',
        StatusDonasi::Diterima->value => 'green',
        StatusDonasi::Ditolak->value => 'red',
    ];

    $nilaiBulan = $bulan?->format('Y-m') ?? 'semua';
    $labelTotal = match (true) {
        $bulan === null => 'Total donasi',
        $bulan->isSameMonth(now()) => 'Donasi bulan ini',
        default => 'Donasi '.$bulan->locale('id')->translatedFormat('M Y'),
    };
    $program = request('program');
    $kartu = [
        ['label' => $labelTotal, 'total' => $totalDiterima, 'program' => null],
        ...collect($totalPerProgram)->map(fn (int $total, string $nama): array => ['label' => $nama, 'total' => $total, 'program' => $nama])->values(),
    ];
@endphp

<x-layouts.admin title="Kelola Donasi">
    <x-admin.page-heading title="Kelola donasi" description="Total di kartu hanya menghitung donasi yang sudah diterima.">
        <a href="{{ route('admin.donasi.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Catat donasi
        </a>
    </x-admin.page-heading>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ($kartu as $item)
            @php
                $aktif = $item['program'] === $program || ($item['program'] === null && ! $program);
            @endphp
            <div @class([
                'flex flex-col rounded-xl border bg-white',
                'border-footer ring-1 ring-footer' => $aktif,
                'border-[#e5e5e5]' => ! $aktif,
            ])>
                <div class="flex-1 px-4 pt-3.5 pb-3">
                    <p class="text-xs text-slate">{{ $item['label'] }}</p>
                    <p class="mt-1 text-lg font-bold tabular-nums">{{ Donasi::rupiah($item['total']) }}</p>
                </div>
                <a href="{{ route('admin.donasi.index', array_filter(['program' => $item['program'], 'bulan' => $nilaiBulan])) }}" class="flex items-center gap-1 border-t border-[#e5e5e5] px-4 py-2 text-xs text-slate hover:text-footer" @if ($aktif) aria-current="page" @endif>
                    Lihat semua <span class="sr-only">{{ $item['label'] }}</span>
                    <x-admin.icon name="chevron-right" width="12" height="12" />
                </a>
            </div>
        @endforeach
    </div>

    <form method="GET" class="mt-5 flex flex-wrap items-center gap-2" role="search">
        <label class="sr-only" for="cari-donasi">Cari nama donatur atau email</label>
        <input id="cari-donasi" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama donatur atau email" class="admin-input w-full sm:w-[280px]">
        <x-admin.filter-select name="program" label="Semua program" :options="collect(ProgramDonasi::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
        <x-admin.filter-select name="status" label="Semua status" :options="collect(StatusDonasi::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
        <select name="bulan" aria-label="Bulan" class="admin-input w-auto min-w-[130px] pr-8" data-autosubmit>
            @foreach ($pilihanBulan as $nilai => $label)
                <option value="{{ $nilai }}" @selected($nilaiBulan === $nilai)>{{ $label }}</option>
            @endforeach
            <option value="semua" @selected($nilaiBulan === 'semua')>Semua bulan</option>
        </select>
        <select name="urut" aria-label="Urutkan" class="admin-input w-auto min-w-[130px] pr-8" data-autosubmit>
            @foreach (DonasiController::URUTAN as $nilai => [$label])
                <option value="{{ $nilai }}" @selected(request('urut', 'terbaru') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
        <a href="{{ route('admin.donasi.ekspor', request()->query()) }}" class="admin-btn admin-btn-secondary">
            <x-admin.icon name="download" width="16" height="16" />
            Ekspor data
        </a>
        @if (request()->hasAny(['search', 'program', 'status', 'bulan', 'urut']))
            <a href="{{ route('admin.donasi.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
        @endif
    </form>

    @if ($jumlahMenunggu && request('status') !== StatusDonasi::Menunggu->value)
        <p class="mt-4 flex flex-wrap items-center gap-2 rounded-lg border border-[#f3dca0] bg-[#fff8e6] px-4 py-2.5 text-[13px] text-[#7a5300]" role="status">
            {{ $jumlahMenunggu }} donasi menunggu dicek. Cocokkan dengan mutasi rekening atau QRIS.
            <a href="{{ route('admin.donasi.index', ['status' => StatusDonasi::Menunggu->value, 'bulan' => 'semua']) }}" class="font-semibold underline underline-offset-2">Lihat donasi menunggu</a>
        </p>
    @endif

    <div class="admin-card mt-4 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Tanggal</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Donatur</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Ditampilkan sebagai</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Program dan keterangan</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Nominal</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Metode</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($donasi as $item)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-slate">
                                {{ $item->tanggal_donasi->locale('id')->translatedFormat($item->tanggal_donasi->isCurrentYear() ? 'j M' : 'j M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->nama_donatur }}</p>
                                @if ($kontak = $item->whatsappTampil() ?? $item->email)
                                    <p class="text-xs whitespace-nowrap text-slate">{{ $kontak }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->tampil_sebagai->value }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->program->value }}</p>
                                @if ($item->keterangan)
                                    <p class="line-clamp-1 text-xs text-slate">{{ $item->keterangan }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold whitespace-nowrap tabular-nums">{{ Donasi::rupiah($item->nominal) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->metode_pembayaran->value }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$statusTone[$item->status->value]">{{ $item->status->value }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
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
                            <td colspan="8" class="px-4 py-12 text-center text-slate">Belum ada donasi pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $donasi->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'donasi']) }}
    </div>
</x-layouts.admin>
