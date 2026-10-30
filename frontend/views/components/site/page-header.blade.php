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
    <div class="flex min-h-14 flex-col justify-center gap-1 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-11">
        @if ($title)
            <h1 @class([
                'font-raleway text-xl font-light text-ink md:text-2xl' => $variant === 'light',
                'font-poppins text-lg font-bold text-brand-green uppercase md:text-xl' => $variant === 'brand',
            ])>{{ $title }}</h1>
        @else
            <span></span>
        @endif

        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center font-open text-[13px]">
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
