@props(['title', 'description' => null])

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-xl font-semibold">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-[13px] text-slate">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
