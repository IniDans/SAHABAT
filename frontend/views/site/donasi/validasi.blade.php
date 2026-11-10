@php
    use App\Enums\StatusDonasi;
    use App\Models\Donasi;

    $statusTone = [
        StatusDonasi::Menunggu->value => 'bg-[#fff3d6] text-[#9a6700]',
        StatusDonasi::Diterima->value => 'bg-[#d1e7dd] text-[#0f5132]',
        StatusDonasi::Ditolak->value => 'bg-[#fde5e5] text-[#c0262f]',
    ];
@endphp

<x-layouts.app title="Validasi Donasi">
    <x-site.page-header title="Validasi Donasi" :breadcrumbs="['Validasi Donasi' => null]" />

    <section class="container-site py-10 lg:pb-[60px]">
        <div class="mx-auto max-w-2xl">
            <form action="{{ route('donasi.validasi') }}" method="GET">
                <label for="kunci" class="form-label text-sm">Kunci Validasi Donasi YASIBU <span class="required">*</span></label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input id="kunci" name="kunci" type="text" value="{{ $kunci }}" required maxlength="20" placeholder="Contoh: YSB-7KQ2MX" autocomplete="off" class="form-control uppercase">
                    <button type="submit" class="btn-primary h-10 shrink-0 text-sm sm:w-36">Cek donasi</button>
                </div>
            </form>

            @session('status')
                <p class="mt-6 rounded border border-[#badbcc] bg-[#d1e7dd] px-4 py-3 font-open text-sm text-[#0f5132]" role="status">{{ $value }}</p>
            @endsession

            @if ($kunci !== '' && ! $donasi)
                <p class="mt-6 rounded border border-[#f5c2c7] bg-[#f8d7da] px-4 py-3 font-open text-sm text-[#842029]" role="alert">Kunci <strong>{{ $kunci }}</strong> tidak ditemukan. Periksa kembali kunci yang Anda terima setelah mengisi formulir donasi.</p>
            @endif

            @if ($donasi)
                <div class="mt-6 rounded border border-[#e6e6e6] bg-white p-5 font-open text-sm text-ink shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-raleway text-lg font-bold text-ink-dark">Donasi {{ $donasi->kunci }}</h2>
                        <span class="rounded-full px-3 py-0.5 text-xs font-semibold {{ $statusTone[$donasi->status->value] }}">{{ $donasi->status->value }}</span>
                    </div>

                    <dl class="mt-4 grid gap-x-6 gap-y-2 sm:grid-cols-[auto_1fr]">
                        <dt class="text-slate-light">Atas nama</dt>
                        <dd>{{ $donasi->namaTampil() }}</dd>
                        <dt class="text-slate-light">Tujuan</dt>
                        <dd>{{ $donasi->tujuan() }}</dd>
                        <dt class="text-slate-light">Nominal</dt>
                        <dd class="font-semibold">{{ Donasi::rupiah($donasi->nominal) }}</dd>
                        <dt class="text-slate-light">Metode</dt>
                        <dd>{{ $donasi->metode_pembayaran->value }}</dd>
                        <dt class="text-slate-light">Tanggal</dt>
                        <dd>{{ $donasi->tanggal_donasi->format('d/m/Y') }}</dd>
                    </dl>

                    @if ($donasi->status === StatusDonasi::Diterima)
                        <div class="mt-5 border-t border-[#e6e6e6] pt-4">
                            <p class="font-bold text-ink-dark">Donasi sudah diterima. Terima kasih!</p>
                            <a href="{{ route('donasi.kuitansi', ['kunci' => $donasi->kunci]) }}" class="btn-primary mt-3 inline-flex h-10 items-center text-sm">Unduh kuitansi (PDF)</a>
                        </div>
                    @endif

                    @if ($donasi->bukti_transfer)
                        <div class="mt-5 border-t border-[#e6e6e6] pt-4">
                            <p class="font-bold text-ink-dark">Bukti transfer sudah diunggah</p>
                            @php($cocok = $donasi->buktiCocok())
                            @if ($cocok === true)
                                <p class="mt-1 text-[#0f5132]">Nominal di bukti transfer sesuai dengan donasi Anda.</p>
                            @elseif ($cocok === false)
                                <p class="mt-1 text-[#9a6700]">
                                    @if ($donasi->bukti_hasil['nominal'] ?? null)
                                        Nominal yang terbaca di bukti ({{ Donasi::rupiah($donasi->bukti_hasil['nominal']) }}) berbeda dengan donasi Anda.
                                    @else
                                        Nominal di bukti transfer tidak terbaca.
                                    @endif
                                    Bila foto kurang jelas, Anda bisa mengunggah ulang. Pengurus tetap akan mengecek secara manual.
                                </p>
                            @else
                                <p class="mt-1 text-slate-light">Bukti akan dicek secara manual oleh pengurus.</p>
                            @endif
                        </div>
                    @endif

                    @if ($donasi->status === StatusDonasi::Menunggu)
                        <form action="{{ route('donasi.validasi.bukti') }}" method="POST" enctype="multipart/form-data" class="mt-5 border-t border-[#e6e6e6] pt-4">
                            @csrf
                            <input type="hidden" name="kunci" value="{{ $donasi->kunci }}">
                            <label for="bukti" class="form-label text-sm">{{ $donasi->bukti_transfer ? 'Ganti foto bukti transfer' : 'Unggah foto bukti transfer' }} <span class="required">*</span></label>
                            <input id="bukti" name="bukti" type="file" required accept="image/jpeg,image/png,image/webp" class="form-control h-auto py-2 file:mr-3 file:rounded file:border-0 file:bg-surface file:px-3 file:py-1" @error('bukti') aria-invalid="true" @enderror>
                            <p class="mt-1 text-xs text-slate-light">Foto struk atau tangkapan layar transfer, format JPG/PNG/WEBP, maksimal 4 MB. Foto dibaca otomatis dengan bantuan AI untuk mempercepat pengecekan.</p>
                            @error('bukti')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="btn-primary mt-4 h-10 w-full text-sm sm:w-auto">Kirim bukti transfer</button>
                        </form>
                    @endif
                </div>
            @endif

            <p class="mt-10 font-open text-sm text-ink">
                Belum mempunyai kunci?
                <a href="{{ route('donasi.formulir') }}" class="text-brand-red hover:underline">Isi formulir donasi untuk mendapatkan kunci.</a>
            </p>
        </div>
    </section>
</x-layouts.app>
