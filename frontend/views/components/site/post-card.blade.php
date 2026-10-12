@props([
    'post',
    'url',
    'withExcerpt' => false,
])

<article {{ $attributes->class('flex flex-col overflow-hidden bg-white shadow-card') }}>
    <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
        <img src="{{ asset($post['image']) }}" alt="" class="h-[215px] w-full object-cover transition duration-300 hover:scale-105" loading="lazy">
    </a>

    <div class="flex flex-1 flex-col px-5 pt-5 pb-5">
        <h3 class="font-raleway text-xl leading-[26px] font-bold text-slate">
            <a href="{{ $url }}" class="hover:text-brand-red">{{ $post['title'] }}</a>
        </h3>

        @if ($withExcerpt)
            <p class="mt-5 line-clamp-3 font-open text-[15px] leading-6 text-ink">{{ $post['excerpt'] }}</p>
        @endif

        <div class="mt-auto flex items-center justify-between gap-3 pt-5">
            <span class="flex items-center gap-1.5 font-open text-[13px] text-muted">
                <img src="{{ asset('images/icons/calendar.svg') }}" alt="" width="14" height="14">
                <time datetime="{{ $post['date_iso'] }}">{{ $post['date'] }}</time>
            </span>
            <a href="{{ $url }}" class="inline-flex h-[34px] shrink-0 items-center rounded bg-brand-red px-5 font-open text-sm text-white transition hover:bg-[#b81d26]">Read More</a>
        </div>
    </div>
</article>
