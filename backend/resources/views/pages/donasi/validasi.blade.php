<x-layouts.app title="Validasi Donasi">
    <x-page-header title="Validasi Donasi" :breadcrumbs="['Validasi Donasi' => null]" />

    <section class="mx-auto w-full max-w-[1726px] px-4 py-12 lg:px-[77px] lg:pt-[110px] lg:pb-4">
        <form action="{{ route('donasi.validasi') }}" method="GET" class="mx-auto max-w-[1270px]">
            <label for="kunci" class="form-label text-sm">Kunci Validasi Donasi YASIBU <span class="required">*</span></label>
            <input id="kunci" name="kunci" type="text" required placeholder="Key..." autocomplete="off" class="form-control">

            <div class="mt-5 flex justify-center">
                <button type="submit" class="btn-primary h-10 w-full max-w-[402px] text-sm">Validasi</button>
            </div>
        </form>

        <p class="mt-16 font-open text-sm text-ink lg:mt-[96px]">
            Belum mempunyai kunci?
            <a href="{{ route('donasi.formulir') }}" class="text-brand-red hover:underline">Klik disini untuk mengisi form dan mendapatkan kunci!</a>
        </p>
    </section>
</x-layouts.app>
