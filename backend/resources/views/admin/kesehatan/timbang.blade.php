@php
    use App\Models\BeratBadan;

    $namaBulan = $bulan->locale('id')->translatedFormat('F Y');
    $namaBulanLalu = $bulan->subMonth()->locale('id')->translatedFormat('M Y');
    $angka = fn (?float $berat): string => $berat === null ? '' : number_format($berat, 1, ',', '');
@endphp

<x-layouts.admin title="Input Berat Badan">
    <a href="{{ route('admin.kesehatan.index') }}" class="mb-3 inline-flex items-center gap-1 text-[13px] text-slate hover:text-[#1e2328]">
        <x-admin.icon name="arrow-left" width="16" height="16" />
        Kesehatan anak
    </a>

    <x-admin.page-heading title="Input berat badan" description="Isi hasil timbang tiap anak aktif dalam kilogram. Kosongkan kolom untuk menghapus berat bulan ini.">
        <form method="GET" class="flex items-center gap-2">
            <label for="bulan-timbang" class="text-[13px] text-slate">Bulan</label>
            <input id="bulan-timbang" type="month" name="bulan" value="{{ $bulan->format('Y-m') }}" max="{{ now()->format('Y-m') }}" class="admin-input w-auto" data-autosubmit>
            <noscript><button type="submit" class="admin-btn admin-btn-secondary">Tampilkan</button></noscript>
        </form>
    </x-admin.page-heading>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-[#f3c2c5] bg-[#fdecee] px-4 py-3 text-[13px] text-[#a3212a]" role="alert">
            Ada {{ count($errors->get('berat.*')) ?: 1 }} isian yang perlu diperbaiki. {{ $errors->first('bulan') ?: $errors->first('berat') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.kesehatan.simpan') }}" class="admin-card overflow-hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="bulan" value="{{ $bulan->format('Y-m') }}">

        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#f0f0f0] px-5 py-4">
            <h2 class="text-[15px] font-semibold">Penimbangan {{ $namaBulan }}</h2>
            <p class="text-xs text-slate">{{ $anak->filter(fn ($item): bool => $beratBulanIni->has($item->NIK))->count() }} dari {{ $anak->count() }} anak sudah ditimbang</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Usia</th>
                        <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">BB {{ $namaBulanLalu }}</th>
                        <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">BB {{ $bulan->locale('id')->translatedFormat('M Y') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($anak as $item)
                        @php
                            $kunci = "berat.{$item->NIK}";
                            $galat = $errors->first($kunci);
                            $idInput = "berat-{$item->NIK}";
                        @endphp
                        <tr>
                            <td class="px-5 py-3">
                                <label for="{{ $idInput }}" class="font-semibold">{{ $item->Nama }}</label>
                                <p class="text-xs text-slate tabular-nums">{{ $item->NIK }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $item->Tanggal_Lahir ? $item->umur().' th' : '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate">{{ $beratBulanLalu->has($item->NIK) ? BeratBadan::kg($beratBulanLalu[$item->NIK]) : '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="relative w-32">
                                    <input id="{{ $idInput }}" type="text" name="berat[{{ $item->NIK }}]" value="{{ old($kunci, $angka($beratBulanIni[$item->NIK] ?? null)) }}" inputmode="decimal" autocomplete="off" placeholder="0,0" class="admin-input pr-9 tabular-nums" @if ($galat) aria-invalid="true" aria-describedby="{{ $idInput }}-error" @endif>
                                    <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-xs text-slate">kg</span>
                                </div>
                                @if ($galat)
                                    <p id="{{ $idInput }}-error" class="mt-1 text-xs text-[#c0262f]">{{ $galat }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate">
                                Belum ada anak aktif. <a href="{{ route('admin.anak-panti.index') }}" class="font-medium text-footer hover:underline">Kelola data anak</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($anak->isNotEmpty())
            <div class="sticky bottom-0 flex flex-wrap items-center justify-end gap-3 border-t border-[#e5e5e5] bg-white px-5 py-3">
                <a href="{{ route('admin.kesehatan.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">Simpan berat badan</button>
            </div>
        @endif
    </form>
</x-layouts.admin>
