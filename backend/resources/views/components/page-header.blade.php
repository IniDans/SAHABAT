@props([
    'title' => null,
    'variant' => 'light',
    'breadcrumbs' => [],
])

{{--
    variant "light": Raleway Light title (Donasi pages).
    variant "brand": green bold uppercase title (Tentang Kami & Artikel pages).
--}}
<section class="bg-surface">
    <div class="flex min-h-[74px] flex-col justify-center gap-1 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-11">
        @if ($title)
            <h1 @class([
                'font-raleway text-2xl font-light text-ink md:text-[32px] md:leading-[37px]' => $variant === 'light',
                'font-poppins text-xl font-bold text-brand-green uppercase md:text-2xl' => $variant === 'brand',
            ])>{{ $title }}</h1>
        @else
            <span></span>
        @endif

        <nav aria-label="Breadcrumb" class="xl:mr-[20vw]">
            <ol class="flex flex-wrap items-center font-open text-sm">
                <li><a href="{{ route('beranda') }}" class="text-slate hover:text-brand-red">Home</a></li>
                @foreach ($breadcrumbs as $label => $url)
                    <li class="flex items-center">
                        <span class="px-2.5 text-slate-light" aria-hidden="true">/</span>
                        @if ($loop->last)
                            <span class="text-brand-red" aria-current="page">{{ $label }}</span>
                        @else
                            <a href="{{ $url }}" class="text-slate hover:text-brand-red">{{ $label }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</section>
