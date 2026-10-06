@php
    use App\Enums\StatusPesan;

    $statusTone = [
        StatusPesan::BelumDibaca->value => 'red',
        StatusPesan::Dibaca->value => 'gray',
        StatusPesan::Dibalas->value => 'green',
        StatusPesan::Diarsipkan->value => 'gray',
    ];

    $status = request('status');
    $nilaiBulan = $bulan?->format('Y-m');
    $jumlahBelumDibaca = $jumlahPerStatus->get(StatusPesan::BelumDibaca->value, 0);
    $kartu = [
        ['label' => 'Total pesan', 'jumlah' => $jumlahTotal, 'status' => null],
        ['label' => 'Belum dibaca', 'jumlah' => $jumlahBelumDibaca, 'status' => StatusPesan::BelumDibaca->value],
        ['label' => 'Sudah dibalas', 'jumlah' => $jumlahPerStatus->get(StatusPesan::Dibalas->value, 0), 'status' => StatusPesan::Dibalas->value],
        ['label' => 'Diarsipkan', 'jumlah' => $jumlahPerStatus->get(StatusPesan::Diarsipkan->value, 0), 'status' => StatusPesan::Diarsipkan->value],
    ];
@endphp

<x-layouts.admin title="Pesan Masuk">
    <x-admin.page-heading title="Pesan masuk" description="Pesan dari formulir Kontak di website." />

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kartu as $item)
            @php
                $aktif = $item['status'] === $status || ($item['status'] === null && ! $status);
            @endphp
            <div @class([
                'flex flex-col rounded-xl border bg-white',
                'border-footer ring-1 ring-footer' => $aktif,
                'border-[#e5e5e5]' => ! $aktif,
            ])>
                <div class="flex-1 px-4 pt-3.5 pb-3">
                    <p class="text-xs text-slate">{{ $item['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums">{{ $item['jumlah'] }}</p>
                </div>
                <a href="{{ route('admin.pesan.index', array_filter(['status' => $item['status'], 'bulan' => $nilaiBulan])) }}" class="flex items-center gap-1 border-t border-[#e5e5e5] px-4 py-2 text-xs text-slate hover:text-footer" @if ($aktif) aria-current="page" @endif>
                    Lihat semua <span class="sr-only">{{ $item['label'] }}</span>
                    <x-admin.icon name="chevron-right" width="12" height="12" />
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-2">
        <form method="GET" class="flex flex-wrap items-center gap-2" role="search">
            <label class="sr-only" for="cari-pesan">Cari nama, email, atau subjek</label>
            <div class="relative w-full sm:w-[300px]">
                <x-admin.icon name="search" width="16" height="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate" />
                <input id="cari-pesan" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau subjek" class="admin-input pl-9">
            </div>
            <x-admin.filter-select name="status" label="Semua status" :options="collect(StatusPesan::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
            <select name="bulan" aria-label="Bulan" class="admin-input w-auto min-w-[130px] pr-8" data-autosubmit>
                <option value="">Semua bulan</option>
                @foreach ($pilihanBulan as $nilai => $label)
                    <option value="{{ $nilai }}" @selected($nilaiBulan === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="urut" aria-label="Urutkan" class="admin-input w-auto min-w-[120px] pr-8" data-autosubmit>
                <option value="terbaru">Terbaru</option>
                <option value="terlama" @selected(request('urut') === 'terlama')>Terlama</option>
            </select>
            @if (request()->hasAny(['search', 'status', 'bulan', 'urut']))
                <a href="{{ route('admin.pesan.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
            @endif
        </form>

        <form action="{{ route('admin.pesan.tandai-dibaca') }}" method="POST">
            @csrf
            <button type="submit" class="admin-btn admin-btn-secondary disabled:cursor-not-allowed disabled:opacity-50" @disabled(! $adaBelumDibaca)>
                <x-admin.icon name="check-check" width="16" height="16" />
                Tandai semua dibaca
            </button>
        </form>
    </div>

    <div class="admin-card mt-4 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="py-3 pr-4 pl-6 font-semibold">Tanggal</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Pengirim</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Subjek</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Pesan</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($pesan as $item)
                        @php
                            $belumDibaca = $item->status === StatusPesan::BelumDibaca;
                            $waktu = $item->created_at->locale('id');
                        @endphp
                        <tr @class(['bg-[#fffafa]' => $belumDibaca])>
                            <td class="relative py-3 pr-4 pl-6 whitespace-nowrap">
                                @if ($belumDibaca)
                                    <span class="absolute top-1/2 left-2.5 size-1.5 -translate-y-1/2 rounded-full bg-[#dc3c45]" aria-hidden="true"></span>
                                @endif
                                <p>{{ $waktu->translatedFormat($waktu->isCurrentYear() ? 'j M' : 'j M Y') }}</p>
                                <p class="text-xs text-slate">{{ $waktu->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->nama }}</p>
                                <p class="text-xs text-slate">{{ $item->email }}</p>
                            </td>
                            <td @class(['px-4 py-3', 'font-semibold' => $belumDibaca])>{{ $item->subjek ?: '(Tanpa subjek)' }}</td>
                            <td class="px-4 py-3 text-slate">
                                <p class="line-clamp-1 max-w-[280px]">{{ $item->isi }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$statusTone[$item->status->value]" class="gap-1.5">
                                    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                    {{ $item->status->value }}
                                </x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.pesan.show', $item) }}" class="admin-icon-btn" aria-label="Baca pesan dari {{ $item->nama }}" title="Baca">
                                        <x-admin.icon name="eye" width="16" height="16" />
                                    </a>
                                    <form action="{{ route('admin.pesan.balas', $item) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="admin-icon-btn hover:text-footer" aria-label="Balas {{ $item->nama }} lewat email" title="Balas lewat email">
                                            <x-admin.icon name="reply" width="16" height="16" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate">Belum ada pesan pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $pesan->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'pesan']) }}
    </div>
</x-layouts.admin>
