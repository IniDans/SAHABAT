@php
    use App\Enums\KategoriKebutuhan;
    use App\Enums\PrioritasKebutuhan;

    $prioritasTone = [
        PrioritasKebutuhan::Mendesak->value => 'red',
        PrioritasKebutuhan::Sedang->value => 'amber',
        PrioritasKebutuhan::Rendah->value => 'gray',
    ];

    $status = request('status');
    $prioritas = request('prioritas');
    $kartu = [
        ['label' => 'Semua kebutuhan', 'jumlah' => $ringkasan['semua'], 'query' => [], 'aktif' => ! $status && ! $prioritas],
        ['label' => 'Mendesak (belum terpenuhi)', 'jumlah' => $ringkasan['mendesak'], 'query' => ['prioritas' => PrioritasKebutuhan::Mendesak->value, 'status' => 'belum'], 'aktif' => $status === 'belum' && $prioritas === PrioritasKebutuhan::Mendesak->value],
        ['label' => 'Belum terpenuhi', 'jumlah' => $ringkasan['belum'], 'query' => ['status' => 'belum'], 'aktif' => $status === 'belum' && ! $prioritas],
        ['label' => 'Terpenuhi', 'jumlah' => $ringkasan['terpenuhi'], 'query' => ['status' => 'terpenuhi'], 'aktif' => $status === 'terpenuhi' && ! $prioritas],
    ];
@endphp

<x-layouts.admin title="Kelola Kebutuhan Panti">
    <x-admin.page-heading title="Kelola kebutuhan panti" description="Kebutuhan yang belum terpenuhi tampil di halaman Donasi website, diurutkan dari yang paling mendesak.">
        <a href="{{ route('admin.kebutuhan-panti.create') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Tambah kebutuhan
        </a>
    </x-admin.page-heading>

    <nav class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Saring kebutuhan">
        @foreach ($kartu as $item)
            <a href="{{ route('admin.kebutuhan-panti.index', $item['query']) }}" @class([
                'rounded-xl border bg-[#fafafa] px-4 py-3.5 transition hover:border-footer/60',
                'border-footer ring-1 ring-footer' => $item['aktif'],
                'border-[#e5e5e5]' => ! $item['aktif'],
            ]) @if ($item['aktif']) aria-current="page" @endif>
                <span class="block text-xs text-slate">{{ $item['label'] }}</span>
                <span class="mt-1 block text-[22px] font-bold tabular-nums">{{ $item['jumlah'] }}</span>
            </a>
        @endforeach
    </nav>

    <form method="GET" class="mt-5 flex flex-wrap items-center gap-2" role="search">
        @if ($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <label class="sr-only" for="cari-kebutuhan">Cari nama kebutuhan</label>
        <input id="cari-kebutuhan" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama kebutuhan" class="admin-input w-full sm:w-[280px]">
        <x-admin.filter-select name="prioritas" label="Semua urgensi" :options="collect(PrioritasKebutuhan::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
        <x-admin.filter-select name="kategori" label="Semua kategori" :options="collect(KategoriKebutuhan::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])" />
        @if (request()->hasAny(['search', 'kategori', 'status', 'prioritas']))
            <a href="{{ route('admin.kebutuhan-panti.index') }}" class="px-2 text-[13px] text-slate hover:text-[#1e2328]">Reset</a>
        @endif
    </form>

    <p class="mt-5 mb-2 text-[13px] font-semibold text-slate">Diurutkan dari yang paling mendesak</p>

    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Nama kebutuhan</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Kategori</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Jumlah</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Urgensi</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($kebutuhan as $item)
                        <tr @class(['text-slate' => $item->terpenuhi])>
                            <td class="px-4 py-3">
                                <p @class(['font-semibold' => ! $item->terpenuhi])>{{ $item->nama }}</p>
                                @if ($item->keterangan)
                                    <p class="line-clamp-1 text-xs text-slate">{{ $item->keterangan }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $item->kategori->value }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $item->jumlah ? number_format($item->jumlah, 0, ',', '.').' '.$item->satuan : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.badge :tone="$prioritasTone[$item->prioritas->value]">{{ $item->prioritas->value }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center gap-1.5 text-xs font-semibold whitespace-nowrap',
                                    'text-footer' => $item->terpenuhi,
                                    'text-[#c0262f]' => ! $item->terpenuhi,
                                ])>
                                    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                    {{ $item->terpenuhi ? 'Terpenuhi' : 'Belum terpenuhi' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <form action="{{ route('admin.kebutuhan-panti.terpenuhi', $item) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if ($item->terpenuhi)
                                            <button type="submit" class="admin-icon-btn" aria-label="Kembalikan {{ $item->nama }} ke belum terpenuhi" title="Belum terpenuhi">
                                                <x-admin.icon name="rotate-ccw" width="16" height="16" />
                                            </button>
                                        @else
                                            <button type="submit" class="admin-icon-btn hover:text-footer" aria-label="Tandai {{ $item->nama }} terpenuhi" title="Tandai terpenuhi">
                                                <x-admin.icon name="check" width="16" height="16" />
                                            </button>
                                        @endif
                                    </form>
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

        {{ $kebutuhan->onEachSide(1)->links('admin.partials.pagination', ['satuan' => 'kebutuhan']) }}
    </div>
</x-layouts.admin>
