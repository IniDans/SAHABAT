@php
    use App\Enums\StatusDonasi;
    use App\Models\Donasi;
@endphp

<x-mail::message>
# Assalamu'alaikum {{ $donasi->nama_donatur }},

@switch($donasi->status)
    @case(StatusDonasi::Menunggu)
Terima kasih sudah berdonasi untuk Panti Asuhan YASIBU. Donasi Anda sudah tercatat dan menunggu transfer.
        @break
    @case(StatusDonasi::Diterima)
Alhamdulillah, donasi Anda sudah kami terima. Kuitansi donasi terlampir di email ini. Semoga menjadi amal jariyah dan dibalas dengan kebaikan yang berlipat.
        @break
    @case(StatusDonasi::Ditolak)
Mohon maaf, donasi berikut belum dapat kami konfirmasi karena dananya belum kami temukan di rekening panti. Bila Anda sudah mentransfer, silakan balas email ini atau hubungi kami lewat WhatsApp.
        @break
@endswitch

<x-mail::table>
| | |
|:--|:--|
| Kunci donasi | **{{ $donasi->kunci }}** |
| Tujuan | {{ $donasi->tujuan() }} |
| Nominal | {{ Donasi::rupiah($donasi->nominal) }} |
| Metode | {{ $donasi->metode_pembayaran->value }} |
| Tanggal | {{ $donasi->tanggal_donasi->format('d/m/Y') }} |
</x-mail::table>

@if ($donasi->status === StatusDonasi::Menunggu)
Setelah mentransfer, unggah foto bukti transfer dengan kunci di atas agar donasi Anda lebih cepat kami konfirmasi.

<x-mail::button :url="$tautanValidasi">
Unggah bukti transfer
</x-mail::button>
@else
<x-mail::button :url="$tautanValidasi" color="success">
Lihat status donasi
</x-mail::button>
@endif

Salam,<br>
Panti Asuhan YASIBU
</x-mail::message>
