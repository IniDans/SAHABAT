<x-layouts.admin title="Cadangan Data">
    <x-admin.page-heading title="Cadangan data" description="Salinan seluruh database dibuat otomatis setiap hari; 14 cadangan terakhir disimpan. File berisi data pribadi anak, simpan hasil unduhan di tempat yang aman.">
        <form action="{{ route('admin.cadangan.store') }}" method="POST">
            @csrf
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-admin.icon name="database" width="16" height="16" />
                Buat cadangan sekarang
            </button>
        </form>
    </x-admin.page-heading>

    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#e3f1ea] text-xs text-footer">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">Waktu</th>
                        <th scope="col" class="px-4 py-3 font-semibold">File</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Ukuran</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f0f0]">
                    @forelse ($cadangan as $item)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">{{ \Illuminate\Support\Carbon::createFromTimestamp($item['waktu'], config('app.timezone'))->locale('id')->translatedFormat('j M Y, H:i') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $item['nama'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap tabular-nums">{{ \Illuminate\Support\Number::fileSize($item['ukuran'], 1) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.cadangan.unduh', $item['nama']) }}" class="admin-icon-btn" aria-label="Unduh {{ $item['nama'] }}" title="Unduh">
                                        <x-admin.icon name="download" width="16" height="16" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-slate">Belum ada cadangan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="mt-4 max-w-2xl text-xs text-slate">Cara memulihkan: buat database kosong, jalankan <code>php artisan migrate</code>, ekstrak file .sql.gz, lalu impor file .sql (mis. lewat phpMyAdmin). Foto dan dokumen yang diunggah tidak termasuk dalam cadangan ini.</p>
</x-layouts.admin>
