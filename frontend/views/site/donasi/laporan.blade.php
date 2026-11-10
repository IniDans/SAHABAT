@php
    use App\Models\Donasi;

    $bulanTertinggi = max(1, $perBulan->max());
    $namaBulan = fn (int $bulan): string => now()->setDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
@endphp

<x-layouts.app title="Laporan Donasi {{ $tahun }}">
    <x-site.page-header title="Laporan Donasi" :breadcrumbs="['Laporan Donasi' => null]" />

    <section class="container-site py-8 font-open text-ink lg:pb-[60px]">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-raleway text-xl font-bold text-ink-dark">Donasi diterima tahun {{ $tahun }}</h2>
                <p class="mt-1 text-sm text-slate-light">Hanya donasi yang sudah dicek dan diterima pengurus. Terima kasih kepada semua donatur.</p>
            </div>
            @if ($daftarTahun->count() > 1)
                <form action="{{ route('donasi.laporan') }}" method="GET" class="flex items-center gap-2">
                    <label for="tahun" class="text-sm">Tahun</label>
                    <x-form.select id="tahun" name="tahun" :options="$daftarTahun->mapWithKeys(fn ($item) => [$item => $item])" :selected="$tahun" class="h-[38px] w-28 text-sm" onchange="this.form.submit()" />
                    <noscript><button type="submit" class="btn-primary h-[38px] text-sm">Tampilkan</button></noscript>
                </form>
            @endif
        </div>

        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded border border-[#e6e6e6] bg-white p-5 shadow-sm">
                <dt class="text-sm text-slate-light">Total donasi diterima</dt>
                <dd class="mt-1 font-raleway text-2xl font-bold text-ink-dark">{{ Donasi::rupiah($total) }}</dd>
            </div>
            <div class="rounded border border-[#e6e6e6] bg-white p-5 shadow-sm">
                <dt class="text-sm text-slate-light">Jumlah donasi</dt>
                <dd class="mt-1 font-raleway text-2xl font-bold text-ink-dark">{{ number_format($jumlahDonasi, 0, ',', '.') }}</dd>
            </div>
        </dl>

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
            <section class="rounded border border-[#e6e6e6] bg-white p-5 shadow-sm" aria-labelledby="per-bulan">
                <h3 id="per-bulan" class="font-raleway text-lg font-bold text-ink-dark">Per bulan</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($perBulan as $bulan => $jumlah)
                        <li class="grid grid-cols-[5.5rem_minmax(0,1fr)] items-center gap-3 sm:grid-cols-[6rem_minmax(0,1fr)_8rem]">
                            <span>{{ $namaBulan($bulan) }}</span>
                            <span class="h-3 overflow-hidden rounded-full bg-[#f1f3f5]" aria-hidden="true">
                                <span class="block h-full rounded-full bg-brand-green" style="width: {{ round($jumlah / $bulanTertinggi * 100) }}%"></span>
                            </span>
                            <span class="col-start-2 text-slate-light tabular-nums sm:col-start-auto sm:text-right">{{ Donasi::rupiah($jumlah) }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="rounded border border-[#e6e6e6] bg-white p-5 shadow-sm" aria-labelledby="per-program">
                <h3 id="per-program" class="font-raleway text-lg font-bold text-ink-dark">Per program</h3>
                <table class="mt-4 w-full text-sm">
                    <caption class="sr-only">Total donasi per program tahun {{ $tahun }}</caption>
                    <tbody>
                        @foreach ($perProgram as $program => $jumlah)
                            <tr class="border-t border-[#e6e6e6] first:border-t-0">
                                <th scope="row" class="py-2 text-left font-normal">{{ $program }}</th>
                                <td class="py-2 text-right font-semibold tabular-nums">{{ Donasi::rupiah($jumlah) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>

        @if ($kebutuhan->isNotEmpty())
            <section class="mt-8 rounded border border-[#e6e6e6] bg-white p-5 shadow-sm" aria-labelledby="progres-kebutuhan">
                <h3 id="progres-kebutuhan" class="font-raleway text-lg font-bold text-ink-dark">Progres kebutuhan panti</h3>
                <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($kebutuhan as $item)
                        <li>
                            <div class="flex items-baseline justify-between gap-2 text-sm">
                                <span class="font-semibold text-ink-dark">{{ $item->label() }}</span>
                                <span class="shrink-0 text-slate-light">{{ $item->terpenuhi ? 'Terpenuhi' : $item->persenTerkumpul().'%' }}</span>
                            </div>
                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-[#f1f3f5]" role="progressbar" aria-label="Dana terkumpul untuk {{ $item->nama }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $item->terpenuhi ? 100 : $item->persenTerkumpul() }}">
                                <div class="h-full rounded-full bg-brand-green" style="width: {{ $item->terpenuhi ? 100 : $item->persenTerkumpul() }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-light">{{ Donasi::rupiah($item->danaTerkumpul()) }} dari {{ Donasi::rupiah($item->target_dana) }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="mt-8 rounded border border-[#e6e6e6] bg-white p-5 shadow-sm" aria-labelledby="donatur-terbaru">
            <h3 id="donatur-terbaru" class="font-raleway text-lg font-bold text-ink-dark">Donatur terbaru</h3>
            @if ($donatur->isEmpty())
                <p class="mt-3 text-sm text-slate-light">Belum ada donasi yang diterima pada tahun ini.</p>
            @else
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[480px] text-sm">
                        <thead>
                            <tr class="text-left text-slate-light">
                                <th scope="col" class="py-2 font-normal">Tanggal</th>
                                <th scope="col" class="py-2 font-normal">Nama</th>
                                <th scope="col" class="py-2 font-normal">Tujuan</th>
                                <th scope="col" class="py-2 text-right font-normal">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($donatur as $item)
                                <tr class="border-t border-[#e6e6e6]">
                                    <td class="py-2 whitespace-nowrap">{{ $item->tanggal_donasi->format('d/m/Y') }}</td>
                                    <td class="py-2">{{ $item->namaTampil() }}</td>
                                    <td class="py-2">{{ $item->tujuan() }}</td>
                                    <td class="py-2 text-right font-semibold whitespace-nowrap tabular-nums">{{ Donasi::rupiah($item->nominal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <p class="mt-8 text-sm">
            Ingin ikut membantu?
            <a href="{{ route('donasi.formulir') }}" class="text-brand-red hover:underline">Isi formulir donasi.</a>
        </p>
    </section>
</x-layouts.app>
