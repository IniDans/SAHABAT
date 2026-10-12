@php
    use App\Enums\StatusGizi;

    $status = request('status');
    $kueri = request()->only(['bulan', 'search']);
    $kartu = [
        ['label' => 'Semua anak aktif', 'jumlah' => $jumlahAnak, 'status' => null],
        ['label' => 'Perlu perhatian', 'jumlah' => $jumlahPerhatian, 'status' => 'perhatian'],
        ...collect([StatusGizi::Kurang, StatusGizi::Dipantau, StatusGizi::Baik, StatusGizi::BelumDitimbang])
            ->map(fn (StatusGizi $item): array => ['label' => $item->label(), 'jumlah' => $jumlahPerStatus[$item->value], 'status' => $item->value]),
    ];
    $pilihanStatus = ['perhatian' => 'Perlu perhatian'] + collect(StatusGizi::cases())->mapWithKeys(fn (StatusGizi $item): array => [$item->value => $item->label()])->all();
    $namaBulan = $bulan->locale('id')->translatedFormat('F Y');
@endphp

<x-layouts.admin title="Kesehatan Anak">
    <x-admin.page-heading title="Kesehatan anak" description="Skrining gizi dari berat badan bulanan, dibandingkan dengan standar berat menurut usia dan jenis kelamin.">
        <a href="{{ route('admin.kesehatan.timbang', ['bulan' => $bulan->format('Y-m')]) }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Input berat badan
        </a>
    </x-admin.page-heading>

    <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-6">
        @foreach ($kartu as $item)
            @php
                $aktif = (string) $status === (string) $item['status'];
            @endphp
            <a href="{{ route('admin.kesehatan.index', array_filter([...$kueri, 'status' => $item['status']])) }}" @class([
                'rounded-xl border bg-white px-4 pt-3.5 pb-3 hover:border-footer',
                'border-footer ring-1 ring-footer' => $aktif,
                'border-[#e5e5e5]' => ! $aktif,
            ]) @if ($aktif) aria-current="page" @endif>
                <p class="text-xs text-slate">{{ $item['label'] }}</p>
                <p class="mt-1 text-2xl font-bold tabular-nums">{{ $item['jumlah'] }}</p>
            </a>
        @endforeach
    </div>

    <form method="GET" class="mt-5 flex flex-wrap items-center gap-2" role="search">
        <label class="sr-only" for="cari-anak">Cari nama anak</label>
        <div class="relative w-full sm:w-[260px]">
            <x-admin.icon name="search" width="16" height="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate" />
            <input id="cari-anak" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama anak" class="admin-input pl-9">
        </div>
        <label class="sr-only" for="bulan-skrining">Bulan penimbangan</label>
        <input id="bulan-skrining" type="month" name="bulan" value="{{ $bulan->format('Y-m') }}" max="{{ now()->format('Y-m') }}" class="admin-input w-auto" data-autosubmit>
        <x-admin.filter-select name="status" label="Semua status" :options="$pilihanStatus" />
        <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
        @if (request()->hasAny(['search', 'status', 'bulan']))
            <a href="{{ route('admin.kesehatan.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-4 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#f0f0f0] px-5 py-4">
            <h2 class="text-[15px] font-semibold">Hasil skrining {{ $namaBulan }}</h2>
            <p class="text-xs text-slate">{{ $daftar->count() }} dari {{ $jumlahAnak }} anak</p>
        </div>
        @include('admin.partials.tabel-gizi', ['daftar' => $daftar, 'bulan' => $bulan, 'kosong' => $jumlahAnak ? 'Tidak ada anak yang cocok dengan filter.' : 'Belum ada anak aktif.'])
    </div>

    <p class="mt-3 text-[11px] text-slate">Hasil skrining membantu deteksi awal, bukan pengganti pemeriksaan tenaga kesehatan.</p>
</x-layouts.admin>
