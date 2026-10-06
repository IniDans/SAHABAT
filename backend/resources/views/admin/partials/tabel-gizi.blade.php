@php
    use App\Models\BeratBadan;

    $namaBulan = fn ($tanggal): string => $tanggal->locale('id')->translatedFormat('M');
@endphp

{{-- Tabel hasil skrining gizi, dipakai di dashboard dan halaman Kesehatan anak. --}}
<div class="overflow-x-auto">
    <table class="w-full text-left text-[13px]">
        <thead class="bg-[#fafafa] text-xs text-slate">
            <tr>
                <th scope="col" class="px-5 py-3 font-semibold">Nama</th>
                <th scope="col" class="px-4 py-3 font-semibold">Usia</th>
                <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">BB {{ $namaBulan($bulan->subMonth()) }}</th>
                <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">BB {{ $namaBulan($bulan) }}</th>
                <th scope="col" class="px-4 py-3 font-semibold">Perubahan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                <th scope="col" class="px-4 py-3 font-semibold">Saran</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#f0f0f0]">
            @forelse ($daftar as $hasil)
                <tr>
                    <td class="px-5 py-3 font-semibold">{{ $hasil['anak']->Nama }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $hasil['umur'] === null ? '-' : $hasil['umur'].' th' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $hasil['beratLalu'] === null ? '-' : BeratBadan::kg($hasil['beratLalu']) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $hasil['berat'] === null ? '-' : BeratBadan::kg($hasil['berat']) }}</td>
                    <td @class([
                        'px-4 py-3 font-semibold whitespace-nowrap',
                        'text-[#c0262f]' => $hasil['perubahan'] !== null && $hasil['perubahan'] < 0,
                        'text-[#9a6700]' => $hasil['perubahan'] !== null && $hasil['perubahan'] == 0,
                        'text-footer' => $hasil['perubahan'] !== null && $hasil['perubahan'] > 0,
                    ])>
                        @if ($hasil['perubahan'] === null)
                            <span class="font-normal text-slate">-</span>
                        @else
                            {{ ($hasil['perubahan'] > 0 ? '+' : ($hasil['perubahan'] < 0 ? '-' : '')).BeratBadan::kg(abs($hasil['perubahan'])) }}
                        @endif
                    </td>
                    <td class="px-4 py-3"><x-admin.badge :tone="$hasil['status']->tone()">{{ $hasil['status']->label() }}</x-admin.badge></td>
                    <td class="px-4 py-3 text-slate">{{ $hasil['saran'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate">{{ $kosong }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="border-t border-[#f0f0f0] px-5 py-4 text-[11px] text-slate">Perubahan dihitung dari selisih berat badan bulan ini dan bulan lalu, lalu dibandingkan dengan standar berat menurut usia dan jenis kelamin.</p>
