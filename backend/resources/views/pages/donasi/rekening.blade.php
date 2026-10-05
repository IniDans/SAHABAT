@php
    $accounts = [
        'Mandiri' => '144.001.257.698.6',
        'BSI' => '7678.877.888',
        'BRI' => '0344.0100.1747.564',
        'Bank Jatim' => '0047.714.303',
        'BNI' => '0884.294.210',
        'BCA' => '4485.444.999',
    ];
@endphp

<x-layouts.app title="Rekening Donasi">
    <x-page-header title="Rekening Donasi" :breadcrumbs="['Rekening Donasi' => null]" />

    <section class="container-site flex flex-col items-center gap-10 py-10 lg:flex-row lg:items-center lg:justify-center lg:gap-[38px] lg:pt-5 lg:pb-[60px]">
        <div class="flex flex-col gap-8 sm:flex-row sm:gap-[38px]">
            <img src="{{ asset('images/qris/qris-panti.png') }}" alt="QRIS donasi Panti Asuhan YASIBU" width="284" height="400" class="h-auto w-[284px]">
            <img src="{{ asset('images/qris/qris-masjid.png') }}" alt="QRIS donasi Masjid YASIBU" width="284" height="400" class="h-auto w-[284px]">
        </div>

        <div class="font-open text-base text-[#212529]">
            <h2 class="text-xl font-bold">Rekening Donasi Panti Asuhan Yasibu:</h2>
            <ul class="mt-3 space-y-1.5">
                @foreach ($accounts as $bank => $number)
                    <li class="flex items-center gap-2">
                        <span><strong>{{ $bank }}:</strong> {{ $number }}</span>
                        <button type="button" class="inline-flex size-7 items-center justify-center rounded hover:bg-surface focus-visible:ring-2 focus-visible:ring-brand-blue/40 focus-visible:outline-none" data-copy="{{ str_replace('.', '', $number) }}" aria-label="Salin nomor rekening {{ $bank }}">
                            <img src="{{ asset('images/icons/copy.svg') }}" alt="" width="10.5" height="14">
                        </button>
                        <span class="hidden text-xs text-brand-green" role="status" data-copy-feedback>Tersalin!</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-3">a.n. Yayasan Insan Indonesia Bersatu</p>
        </div>
    </section>
</x-layouts.app>
