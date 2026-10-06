{{-- Daftar kartu tulisan dengan filter kategori. Variabel: $tulisan (paginator), $kategori, $categories, $indexRoute, $showRoute, $kosong. --}}
<form action="{{ route($indexRoute) }}" method="GET">
    @if (request('q'))
        <input type="hidden" name="q" value="{{ request('q') }}">
    @endif
    <label class="sr-only" for="kategori-tulisan">Kategori</label>
    <x-form.select id="kategori-tulisan" name="kategori" placeholder="Semua kategori" :options="array_combine(array_keys($categories), array_keys($categories))" :selected="$kategori" class="h-[42px]! rounded-[3px] border-[#dbe0e2] text-[#444]" data-autosubmit />
</form>

@if (request('q'))
    <p class="mt-4 font-open text-sm text-ink">
        Hasil pencarian "<strong>{{ request('q') }}</strong>": {{ $tulisan->total() }} tulisan.
        <a href="{{ route($indexRoute, array_filter(['kategori' => $kategori])) }}" class="text-brand-red hover:underline">Hapus pencarian</a>
    </p>
@endif

@if ($tulisan->isEmpty())
    <p class="mt-10 py-16 text-center font-open text-base text-muted">{{ $kosong }}</p>
@else
    <div class="mt-5 grid gap-x-[22px] gap-y-[34px] sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($tulisan as $item)
            <x-post-card :post="$item->kartu()" :url="route($showRoute, $item->slug)" class="lg:min-h-[388px]" />
        @endforeach
    </div>
@endif

@if ($tulisan->hasPages())
    <x-pagination class="mt-[46px]" :current="$tulisan->currentPage()" :last="$tulisan->lastPage()" />
@endif
