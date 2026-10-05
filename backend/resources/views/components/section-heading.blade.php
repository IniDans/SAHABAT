@props(['eyebrow', 'title'])

{{-- Full-width row: the eyebrow + divider run from the page edge up to the content column. --}}
<div {{ $attributes->class('items-center xl:grid xl:grid-cols-[1fr_minmax(0,1140px)_1fr]') }}>
    <div class="mx-auto mb-2 flex max-w-[1172px] items-center gap-3 px-4 xl:mx-0 xl:mb-0 xl:max-w-none xl:pr-8 xl:pl-[14px]">
        <span class="font-poppins text-sm font-bold tracking-[2px] whitespace-nowrap text-[#aaa] uppercase">{{ $eyebrow }}</span>
        <span class="h-px w-16 bg-brand-red-soft xl:w-auto xl:flex-1"></span>
    </div>
    <h2 class="mx-auto max-w-[1172px] px-4 font-poppins text-2xl font-bold text-brand-green uppercase md:text-[36px] md:leading-[54px] xl:mx-0 xl:max-w-none xl:px-0">{{ $title }}</h2>
</div>
