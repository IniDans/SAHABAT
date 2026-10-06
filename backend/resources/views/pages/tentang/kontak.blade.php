@php
    $whatsappContacts = [
        ['nomor' => '+62881036667747', 'nama' => 'Ustadzah Lulu'],
        ['nomor' => '+6285791336429', 'nama' => 'Ustadzah Ratu'],
    ];
@endphp

<x-layouts.app title="Kontak Kami">
    <x-page-header title="Kontak Kami" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Kontak Kami' => null]" />

    <section class="mx-auto grid w-full max-w-[1440px] gap-10 px-4 py-10 font-open text-ink lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-[70px] lg:px-[20px] lg:pt-4 lg:pb-[60px] xl:px-[20px]">
        <div>
            <h2 class="font-raleway text-2xl font-bold text-ink-dark">Lokasi</h2>
            <div class="mt-3 aspect-[4/3] w-full overflow-hidden bg-surface lg:max-w-[460px]">
                <iframe
                    title="Peta lokasi Panti Asuhan Yasibu 2 (Suhat)"
                    src="https://maps.google.com/maps?q=Jl.%20Kembang%20Kertas%20No.09%2C%20Jatimulyo%2C%20Lowokwaru%2C%20Kota%20Malang&z=16&output=embed"
                    class="size-full border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                ></iframe>
            </div>
            <div class="mt-4 flex gap-2">
                <svg class="mt-0.5 shrink-0 text-slate" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z" /></svg>
                <div>
                    <p class="font-raleway text-base font-semibold text-ink-dark">Panti Asuhan Yasibu 2 (Suhat)</p>
                    <address class="mt-1 text-sm text-slate-light not-italic">
                        Jl. Kembang Kertas No.09, RT.09/RW.004, Jatimulyo,<br>
                        Kec. Lowokwaru, Kota Malang, Jawa Timur 65141
                    </address>
                </div>
            </div>
        </div>

        <div>
            <h2 class="font-raleway text-2xl font-bold text-ink-dark">Kontak</h2>

            <dl class="mt-4 space-y-5">
                <div class="flex gap-3">
                    <dt class="flex size-11 shrink-0 items-center justify-center rounded-full bg-surface text-slate" aria-label="Email">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" /></svg>
                    </dt>
                    <dd>
                        <p class="font-raleway text-2xl text-ink-dark">Email</p>
                        <a href="mailto:sahabatyasibu@gmail.com" class="text-sm text-slate-light hover:text-brand-red">sahabatyasibu@gmail.com</a>
                    </dd>
                </div>
                <div class="flex gap-3">
                    <dt class="flex size-11 shrink-0 items-center justify-center rounded-full bg-[#25d366] text-white" aria-label="WhatsApp">
                        <x-whatsapp-icon class="size-6" />
                    </dt>
                    <dd>
                        <p class="font-raleway text-2xl text-ink-dark">WhatsApp</p>
                        <ul class="text-sm">
                            @foreach ($whatsappContacts as $contact)
                                <li>
                                    <a href="https://wa.me/{{ ltrim($contact['nomor'], '+') }}" class="text-[#25a244] hover:underline" target="_blank" rel="noopener">{{ $contact['nomor'] }}</a>
                                    <span class="text-slate-light">({{ $contact['nama'] }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </dd>
                </div>
            </dl>

            @session('status')
                <p class="mt-8 rounded border border-[#badbcc] bg-[#d1e7dd] px-4 py-3 text-sm text-[#0f5132]" role="status">{{ $value }}</p>
            @endsession

            <form action="{{ route('tentang.kontak.store') }}" method="POST" class="mt-8 space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="kontak-nama" class="form-label text-sm">Nama <span class="required">*</span></label>
                        <input id="kontak-nama" name="nama" type="text" value="{{ old('nama') }}" required maxlength="255" placeholder="Nama" autocomplete="name" class="form-control" @error('nama') aria-invalid="true" aria-describedby="kontak-nama-error" @enderror>
                        @error('nama')
                            <p id="kontak-nama-error" class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="kontak-email" class="form-label text-sm">Email <span class="required">*</span></label>
                        <input id="kontak-email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="contoh@gmail.com" autocomplete="email" class="form-control" @error('email') aria-invalid="true" aria-describedby="kontak-email-error" @enderror>
                        @error('email')
                            <p id="kontak-email-error" class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="kontak-subjek" class="form-label text-sm">Subjek</label>
                    <input id="kontak-subjek" name="subjek" type="text" value="{{ old('subjek') }}" maxlength="255" placeholder="Subjek" class="form-control" @error('subjek') aria-invalid="true" aria-describedby="kontak-subjek-error" @enderror>
                    @error('subjek')
                        <p id="kontak-subjek-error" class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="kontak-isi" class="form-label text-sm">Keterangan <span class="required">*</span></label>
                    <textarea id="kontak-isi" name="isi" rows="4" required maxlength="5000" placeholder="Pesan" class="form-control" @error('isi') aria-invalid="true" aria-describedby="kontak-isi-error" @enderror>{{ old('isi') }}</textarea>
                    @error('isi')
                        <p id="kontak-isi-error" class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="btn-primary h-9 px-8 text-sm">Kirim</button>
                </div>
            </form>

            <a href="https://wa.me/{{ ltrim($whatsappContacts[0]['nomor'], '+') }}" target="_blank" rel="noopener" class="mt-6 inline-flex items-center gap-3 font-raleway text-xl text-ink-dark hover:text-[#25a244]">
                <span class="flex size-9 items-center justify-center rounded-full bg-[#25d366] text-white">
                    <x-whatsapp-icon class="size-5" />
                </span>
                Hubungi melalui link WhatsApp
            </a>
        </div>
    </section>
</x-layouts.app>
