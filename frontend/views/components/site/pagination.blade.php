@props([
    'current' => 1,
    'last' => 7,
])

@php
    $pageUrl = fn (int $page): string => request()->fullUrlWithQuery(['page' => $page]);
@endphp

<nav {{ $attributes->class('flex justify-center') }} aria-label="Navigasi halaman">
    <ul class="flex flex-wrap items-center">
        <li>
            @if ($current > 1)
                <a href="{{ $pageUrl($current - 1) }}" class="flex h-10 w-[50px] items-center justify-center border border-[#edeff1] hover:bg-surface" aria-label="Halaman sebelumnya">
                    <img src="{{ asset('images/icons/page-prev.svg') }}" alt="" width="16.01" height="15.99">
                </a>
            @else
                <span class="flex h-10 w-[50px] items-center justify-center border border-[#edeff1] opacity-50" aria-hidden="true">
                    <img src="{{ asset('images/icons/page-prev.svg') }}" alt="" width="16.01" height="15.99">
                </span>
            @endif
        </li>

        @for ($page = 1; $page <= $last; $page++)
            <li class="-ml-px">
                @if ($page === $current)
                    <span class="flex h-10 w-[43px] items-center justify-center border border-[#edeff1] bg-[#edeff1] font-open text-base text-slate" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $pageUrl($page) }}" class="flex h-10 w-[43px] items-center justify-center border border-[#edeff1] font-open text-base text-[#aaa] hover:bg-surface hover:text-brand-red">{{ $page }}</a>
                @endif
            </li>
        @endfor

        <li class="-ml-px">
            @if ($current < $last)
                <a href="{{ $pageUrl($current + 1) }}" class="flex h-10 w-[50px] items-center justify-center border border-[#edeff1] hover:bg-surface" aria-label="Halaman berikutnya">
                    <img src="{{ asset('images/icons/page-next.svg') }}" alt="" width="16.01" height="15.99">
                </a>
            @else
                <span class="flex h-10 w-[50px] items-center justify-center border border-[#edeff1] opacity-50" aria-hidden="true">
                    <img src="{{ asset('images/icons/page-next.svg') }}" alt="" width="16.01" height="15.99">
                </span>
            @endif
        </li>
    </ul>
</nav>
