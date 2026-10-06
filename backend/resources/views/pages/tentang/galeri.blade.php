<x-layouts.app title="Galeri">
    <x-page-header title="Galeri" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Galeri' => null]" />

    <section class="mx-auto w-full max-w-[1044px] px-4 py-10 lg:pt-[50px] lg:pb-[60px]">
        <h2 class="sr-only">Gallery</h2>
        @if ($foto->isEmpty())
            <p class="py-16 text-center font-open text-base text-muted">Belum ada foto kegiatan.</p>
        @else
            <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6">
                @foreach ($foto as $item)
                    <li>
                        <a href="{{ $item->url() }}" class="group block overflow-hidden" data-lightbox>
                            <img src="{{ $item->url() }}" alt="{{ $item->teksAlt() }}" class="aspect-[16/9] w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($foto->hasPages())
            <x-pagination class="mt-8" :current="$foto->currentPage()" :last="$foto->lastPage()" />
        @endif
    </section>

    <dialog class="m-auto max-h-[90vh] max-w-[90vw] bg-transparent p-0 backdrop:bg-black/80" data-lightbox-dialog>
        <form method="dialog">
            <button class="absolute top-2 right-2 flex size-9 items-center justify-center rounded-full bg-black/60 text-xl text-white" aria-label="Tutup">&times;</button>
        </form>
        <img src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain" data-lightbox-image>
    </dialog>
</x-layouts.app>
