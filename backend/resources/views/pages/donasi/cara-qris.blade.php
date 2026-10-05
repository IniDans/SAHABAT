<x-layouts.app title="Cara Donasi QRIS">
    <x-page-header title="Cara QRIS" :breadcrumbs="['Cara QRIS' => null]" />

    <article class="mx-auto w-full max-w-[1726px] px-4 py-8 font-open text-base leading-6 text-ink lg:px-[77px] lg:pb-[60px] lg:text-xl">
        <h2 class="font-raleway text-[28px] leading-tight font-bold md:text-[36px]">Apa itu QRIS?</h2>
        <div class="mt-3 space-y-4">
            <p>QRIS merupakan singkatan dari Quick Response Code Indonesian Standard atau standar kode QR untuk setiap pembayaran cepat yang digunakan di Indonesia. Sistem ini diluncurkan oleh Bank Indonesia bersama Asosiasi Sistem Pembayaran Indonesia (ASPI) pada tanggal 17 Agustus 2019 lalu.</p>
            <p>Dengan adanya QRIS, Panti Asuhan Kami dapat menerima Donasi dari seluruh jenis e-wallet yang sudah terdaftar dalam QRIS. Anda sebagai Donatur pun juga pasti akan lebih senang, karena tidak perlu repot-repot mengisi detail pembayaran donasi.</p>
            <p>Sebagai kode standar, QRIS dapat digunakan oleh setiap pelaku usaha maupun pelanggan di seluruh daerah yang ada di Indonesia. Selama tempat usaha tersebut memasang tanda QRIS, berarti pembayaran dengan e-wallet dapat dilakukan terlepas di mana tempat usaha tersebut berada di dalam wilayah Indonesia.</p>
        </div>

        <h2 class="mt-10 font-raleway text-[26px] leading-tight font-bold md:text-[32px]">Apa Saja e-Wallet QRIS yang Tersedia?</h2>
        <div class="mt-3 space-y-4">
            <p>Satu hal keunggulan kode QRIS yang menarik adalah adanya aturan dari Bank Indonesia yang mewajibkan seluruh penyedia layanan pembayaran nontunai untuk menggunakan QRIS sebagai metode pembayaran digital dengan kode QR.</p>
            <p>Artinya, <strong>setiap e-wallet yang saat ini dipakai di Indonesia</strong> dapat menggunakan kode QR dari QRIS untuk melakukan pembayaran; terlepas apakah layanan e-wallet tersebut disediakan oleh bank seperti BCA, Mandiri, atau fitur Jenius Pay, maupun oleh penyedia layanan pembayaran nontunai pihak ketiga seperti OVO, Gopay, LinkAja, dan sebagainya.</p>
        </div>

        <h2 class="mt-10 font-raleway text-[26px] leading-tight font-bold md:text-[32px]">Cara Donasi QRIS</h2>
        <ol class="mt-4 space-y-8 font-bold lg:text-base">
            <li>
                <p>1. Isi <a href="{{ route('donasi.formulir') }}" class="text-brand-red hover:underline">Formulir Donasi</a> dengan metode pembayaran QRIS</p>
                <img src="{{ asset('images/qris/step1-formulir.png') }}" alt="Contoh pengisian Formulir Donasi dengan metode pembayaran QRIS" width="855" height="553" class="mt-3 h-auto w-full max-w-[855px]" loading="lazy">
            </li>
            <li>
                <p>2. Pada halaman validasi donasi, cek apakah pembayaran telah sesuai dan Kode QR masih berlaku.</p>
                <img src="{{ asset('images/qris/step2-qris.png') }}" alt="Halaman validasi donasi dengan Kode QR" width="546" height="524" class="mt-3 h-auto w-full max-w-[546px] rounded border border-[#dee2e6]" loading="lazy">
            </li>
            <li>
                <p>3. Scan Kode QR tertera pada halaman pembayaran QRIS E-Wallet Anda.</p>
                <ul class="mt-2 list-disc pl-6 font-normal">
                    <li>
                        Bagaimana jika hanya menggunakan 1 perangkat Handphone / Tablet,
                        <ul class="list-[circle] pl-6">
                            <li><strong>Unduh</strong> Kode QR dengan cara klik Kode QR dan simpan pada perangkat Anda.</li>
                            <li>Pada halaman pembayaran QRIS E-Wallet terdapat pilihan untuk mengupload Gambar Kode QR.</li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ol>
    </article>
</x-layouts.app>
