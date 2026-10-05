@php
    use App\Enums\MetodePembayaran;
    use App\Enums\ProgramDonasi;
    use App\Enums\TampilanDonatur;

    $programOptions = collect(ProgramDonasi::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value]);
    $metodeOptions = collect(MetodePembayaran::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value]);
@endphp

<x-layouts.app title="Formulir Donasi">
    <x-page-header title="Formulir Donasi" :breadcrumbs="['Formulir Donasi' => null]" />

    <section class="mx-auto w-full max-w-[1726px] px-4 py-8 lg:px-[77px] lg:pt-5 lg:pb-[60px]">
        <form action="{{ route('donasi.formulir.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="program" class="form-label mb-1">Program Donasi <span class="required">*</span></label>
                <x-form.select id="program" name="program" required :options="$programOptions" :selected="old('program', request('program', ProgramDonasi::Zakat->value))" />
                @error('program')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nominal" class="form-label mb-1">Nominal <span class="required">*</span></label>
                <div class="flex">
                    <span class="flex h-[42px] items-center rounded-l border border-field bg-[#e9ecef] px-3 font-open text-sm text-[#495057]">Rp</span>
                    <input id="nominal" name="nominal" type="text" inputmode="numeric" required value="{{ old('nominal') }}" placeholder="10.000" class="form-control -ml-px rounded-l-none" aria-describedby="nominal-hint" @error('nominal') aria-invalid="true" @enderror>
                </div>
                <p id="nominal-hint" class="mt-1 font-open text-[13px] text-placeholder">Minimal Rp10.000</p>
                @error('nominal')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,560px)] lg:gap-x-[60px]">
                <div>
                    <label for="nama_donatur" class="form-label">Nama <span class="required">*</span></label>
                    <input id="nama_donatur" name="nama_donatur" type="text" required maxlength="255" value="{{ old('nama_donatur') }}" placeholder="Nama" autocomplete="name" class="form-control" @error('nama_donatur') aria-invalid="true" @enderror>
                    @error('nama_donatur')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <fieldset>
                    <legend class="font-open text-sm font-bold text-ink">Nama yang ditampilkan <span class="text-[11.2px] font-normal text-[red]">*</span></legend>
                    <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
                        @foreach (TampilanDonatur::cases() as $tampilan)
                            <label class="flex items-center gap-2 font-open text-sm font-bold text-ink">
                                <input type="radio" name="tampil_sebagai" value="{{ $tampilan->value }}" @checked(old('tampil_sebagai', TampilanDonatur::HambaAllah->value) === $tampilan->value) class="size-[13px] accent-[#0075ff]">
                                {{ $tampilan->value }}
                            </label>
                        @endforeach
                    </div>
                    @error('tampil_sebagai')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>

            <div>
                <label for="alamat" class="form-label">Alamat</label>
                <textarea id="alamat" name="alamat" rows="2" maxlength="500" placeholder="Alamat" autocomplete="street-address" class="form-control min-h-[62px]" @error('alamat') aria-invalid="true" @enderror>{{ old('alamat') }}</textarea>
                @error('alamat')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-6 md:grid-cols-2 md:gap-x-5">
                <div>
                    <label for="no_whatsapp" class="form-label">No Whatsapp <span class="required">*</span></label>
                    <div class="flex">
                        <span class="flex h-[44px] items-center rounded-l border border-field bg-[#e9ecef] px-3 font-open text-base text-[#495057]">+62</span>
                        <input id="no_whatsapp" name="no_whatsapp" type="tel" required inputmode="numeric" value="{{ old('no_whatsapp') }}" placeholder="cth. 085100767634" autocomplete="tel-national" class="form-control -ml-px h-[44px] rounded-l-none" @error('no_whatsapp') aria-invalid="true" @enderror>
                    </div>
                    @error('no_whatsapp')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                    <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email') }}" placeholder="contoh@gmail.com" autocomplete="email" class="form-control h-[44px]" @error('email') aria-invalid="true" @enderror>
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="metode_pembayaran" class="form-label mb-1">Pilih Metode Pembayaran <span class="required">*</span></label>
                <x-form.select id="metode_pembayaran" name="metode_pembayaran" required :options="$metodeOptions" :selected="old('metode_pembayaran', MetodePembayaran::Bca->value)" data-payment-select />
                <img src="{{ asset('images/bank/bca.png') }}" alt="BCA" width="196" height="87" class="mt-2 h-[87px] w-[196px] object-contain" data-payment-logo>
                @error('metode_pembayaran')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="keterangan" class="form-label text-sm">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="3" maxlength="1000" placeholder="Keterangan" class="form-control min-h-[83px]" @error('keterangan') aria-invalid="true" @enderror>{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-center pt-4">
                <button type="submit" class="btn-primary w-full max-w-[597px]">Selanjutnya</button>
            </div>
        </form>
    </section>
</x-layouts.app>
