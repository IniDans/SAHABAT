<x-layouts.app title="Galeri">
    <x-page-header title="Galeri" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Galeri' => null]" />

    <section class="mx-auto w-full max-w-[920px] px-4 py-10 lg:pt-[50px] lg:pb-[60px]">
        <h2 class="sr-only">Gallery</h2>
        <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5">
            @foreach ($photos as $photo)
                <li>
                    @if ($photo['exists'])
                        <a href="{{ asset($photo['path']) }}" class="group block overflow-hidden" data-lightbox>
                            <img src="{{ asset($photo['path']) }}" alt="{{ $photo['caption'] }}" class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                        </a>
                    @else
                        <div class="flex aspect-[4/3] w-full items-center justify-center bg-gradient-to-br from-footer/15 to-surface text-footer/60" role="img" aria-label="{{ $photo['caption'] }}">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><circle cx="9" cy="10" r="2" /><path d="m21 16-5-5-8 8" /></svg>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        <x-pagination class="mt-8" :current="(int) request('page', 1)" :last="7" />
    </section>

    <dialog class="m-auto max-h-[90vh] max-w-[90vw] bg-transparent p-0 backdrop:bg-black/80" data-lightbox-dialog>
        <form method="dialog">
            <button class="absolute top-2 right-2 flex size-9 items-center justify-center rounded-full bg-black/60 text-xl text-white" aria-label="Tutup">&times;</button>
        </form>
        <img src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain" data-lightbox-image>
    </dialog>
</x-layouts.app>
