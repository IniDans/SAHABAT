@php
    $tones = [
        'green' => 'bg-[#e3f1ea] text-footer',
        'red' => 'bg-[#fde5e5] text-[#dc3c45]',
        'purple' => 'bg-[#ecebfb] text-[#6a5acd]',
    ];
@endphp

<x-layouts.admin title="Dashboard">
    <h1 class="sr-only">Dashboard</h1>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($stats as $stat)
            <article class="flex flex-col rounded-xl border border-[#e5e5e5] bg-[#fafafa]">
                <div class="flex items-start justify-between gap-4 p-5">
                    <div>
                        <p class="text-[13px] text-slate">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-[22px] font-bold">{{ $stat['value'] }}</p>
                    </div>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $tones[$stat['tone']] }}">
                        <x-admin.icon :name="$stat['icon']" />
                    </span>
                </div>
                <a href="#" class="mt-auto flex items-center gap-1 border-t border-[#e5e5e5] px-5 py-3 text-xs text-slate hover:text-[#1e2328]">
                    Lihat semua
                    <x-admin.icon name="chevron-right" width="14" height="14" />
                </a>
            </article>
        @endforeach
    </div>

    <div class="mt-6 grid gap-5 xl:grid-cols-2">
        <section class="rounded-xl border border-[#e5e5e5] bg-white" aria-labelledby="pesan-masuk">
            <div class="flex items-center justify-between border-b border-[#e5e5e5] px-5 py-4">
                <h2 id="pesan-masuk" class="text-[15px] font-semibold">Pesan masuk</h2>
                <a href="#" class="text-xs text-slate hover:text-[#1e2328]">Lihat semua</a>
            </div>
            <ul class="divide-y divide-[#f0f0f0]">
                @foreach ($messages as $message)
                    @php
                        $initials = collect(explode(' ', $message['name']))->take(2)->map(fn (string $word): string => mb_substr($word, 0, 1))->implode('');
                    @endphp
                    <li class="flex items-center gap-3 px-5 py-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-[#e3f1ea] text-xs font-semibold text-footer">{{ $initials }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[13px] font-semibold">
                                {{ $message['name'] }}
                                <span class="ml-1 font-normal text-slate">· {{ $message['role'] }}</span>
                            </p>
                            <p class="truncate text-xs text-slate">{{ $message['subject'] }}</p>
                        </div>
                        <time class="shrink-0 text-[11px] text-slate-light">{{ $message['time'] }}</time>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="rounded-xl border border-[#e5e5e5] bg-white" aria-labelledby="kebutuhan-mendesak">
            <div class="flex items-center justify-between border-b border-[#e5e5e5] px-5 py-4">
                <h2 id="kebutuhan-mendesak" class="text-[15px] font-semibold">Kebutuhan mendesak</h2>
                <a href="#" class="text-xs text-slate hover:text-[#1e2328]">Lihat semua</a>
            </div>
            <ul class="divide-y divide-[#f0f0f0]">
                @foreach ($needs as $need)
                    <li class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <p class="flex-1 text-[13px] font-semibold">{{ $need['name'] }}</p>
                            <span @class([
                                'rounded-full px-2 py-0.5 text-[11px] font-medium',
                                'bg-[#fde5e5] text-[#c0262f]' => $need['level'] === 'Mendesak',
                                'bg-[#fff3d6] text-[#9a6700]' => $need['level'] !== 'Mendesak',
                            ])>{{ $need['level'] }}</span>
                        </div>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-[#eef0ee]" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $need['score'] }}" aria-label="Skor prioritas {{ $need['name'] }}">
                                <div class="h-full rounded-full {{ $need['level'] === 'Mendesak' ? 'bg-[#dc3c45]' : 'bg-[#e0a526]' }}" style="width: {{ $need['score'] }}%"></div>
                            </div>
                            <p class="shrink-0 text-[11px] text-slate">
                                Skor prioritas {{ $need['score'] }} –
                                <span class="{{ $need['fulfilled'] ? 'text-footer' : 'text-[#c0262f]' }}">{{ $need['fulfilled'] ? 'Terpenuhi' : 'Belum terpenuhi' }}</span>
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.admin>
