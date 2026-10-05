<x-layouts.app title="Formulir Donasi">
    <x-page-header title="Formulir Donasi" :breadcrumbs="['Formulir Donasi' => null]" />

    <section class="mx-auto w-full max-w-[1726px] px-4 py-8 lg:px-[77px] lg:pt-5 lg:pb-[60px]">
        <form action="{{ route('donasi.validasi') }}" method="GET" class="space-y-6">
            <div>
                <label for="program" class="form-label mb-1">Program Donasi <span class="required">*</span></label>
                <x-form.select id="program" name="program" required :options="['zakat' => 'ZAKAT', 'pendidikan' => 'Pendidikan', 'ramadhan' => 'Ramadhan', 'infaq' => 'Infaq & Sedekah']" selected="zakat" />
            </div>

            <div>
                <label for="nominal" class="form-label mb-1">Nominal <span class="required">*</span></label>
                <x-form.select id="nominal" name="nominal" required :options="['10000' => 'Rp10.000', '25000' => 'Rp25.000', '50000' => 'Rp50.000', '100000' => 'Rp100.000', '250000' => 'Rp250.000', '500000' => 'Rp500.000']" selected="10000" />
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,560px)] lg:gap-x-[60px]">
                <div>
                    <label for="nama" class="form-label">Nama <span class="required">*</span></label>
                    <input id="nama" name="nama" type="text" required placeholder="Nama" autocomplete="name" class="form-control">
                </div>

                <fieldset>
                    <legend class="font-open text-sm font-bold text-ink">Nama yang ditampilkan <span class="text-[11.2px] font-normal text-[red]">*</span></legend>
                    <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
                        @foreach (['asli' => 'Nama asli', 'hamba-allah' => 'Hamba Allah', 'anonim' => 'Anonim'] as $value => $label)
                            <label class="flex items-center gap-2 font-open text-sm font-bold text-ink">
                                <input type="radio" name="nama_tampil" value="{{ $value }}" @checked($value === 'hamba-allah') class="size-[13px] accent-[#0075ff]">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            <div>
                <label for="alamat" class="form-label">Alamat</label>
                <textarea id="alamat" name="alamat" rows="2" placeholder="Alamat" autocomplete="street-address" class="form-control min-h-[62px]"></textarea>
            </div>

            <div class="grid gap-6 md:grid-cols-2 md:gap-x-5">
                <div>
                    <label for="whatsapp" class="form-label">No Whatsapp <span class="required">*</span></label>
                    <div class="flex">
                        <span class="flex h-[44px] items-center rounded-l border border-field bg-[#e9ecef] px-3 font-open text-base text-[#495057]">+62</span>
                        <input id="whatsapp" name="whatsapp" type="tel" required inputmode="numeric" placeholder="cth. 085100767634" autocomplete="tel-national" class="form-control -ml-px h-[44px] rounded-l-none">
                    </div>
                </div>
                <div>
                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                    <input id="email" name="email" type="email" required placeholder="contoh@gmail.com" autocomplete="email" class="form-control h-[44px]">
                </div>
            </div>

            <div>
                <label for="metode" class="form-label mb-1">Pilih Metode Pembayaran <span class="required">*</span></label>
                <x-form.select id="metode" name="metode" required :options="['bca' => 'BCA', 'qris' => 'QRIS', 'mandiri' => 'Mandiri', 'bsi' => 'BSI', 'bri' => 'BRI', 'bni' => 'BNI', 'jatim' => 'Bank Jatim']" selected="bca" data-payment-select />
                <img src="{{ asset('images/bank/bca.png') }}" alt="BCA" width="196" height="87" class="mt-2 h-[87px] w-[196px] object-contain" data-payment-logo>
            </div>

            <div>
                <label for="jenis" class="form-label mb-1">Keterangan Donasi</label>
                <x-form.select id="jenis" name="jenis" :options="['zakat' => 'ZAKAT', 'infaq' => 'Infaq', 'sedekah' => 'Sedekah', 'wakaf' => 'Wakaf']" selected="zakat" />
            </div>

            <div>
                <label for="keterangan" class="form-label text-sm">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="3" placeholder="Keterangan" class="form-control min-h-[83px]"></textarea>
            </div>

            <div class="flex justify-center pt-4">
                <button type="submit" class="btn-primary w-full max-w-[597px]">Selanjutnya</button>
            </div>
        </form>
    </section>
</x-layouts.app>
