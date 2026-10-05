<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPesan;
use App\Http\Controllers\Controller;
use App\Models\AnakPanti;
use App\Models\Donasi;
use App\Models\KebutuhanPanti;
use App\Models\Pesan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard admin dengan data ringkasan.
     */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->stats(),
            'messages' => $this->messages(),
            'needs' => $this->needs(),
        ]);
    }

    /**
     * @return list<array{label: string, value: string, icon: string, tone: string, url: string|null}>
     */
    private function stats(): array
    {
        $donasiBulanIni = (int) Donasi::diterima()
            ->whereBetween('tanggal_donasi', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('nominal');

        return [
            ['label' => 'Anak panti', 'value' => (string) AnakPanti::aktif()->count(), 'icon' => 'users', 'tone' => 'green', 'url' => null],
            ['label' => 'Kebutuhan mendesak', 'value' => (string) KebutuhanPanti::mendesak()->count(), 'icon' => 'package', 'tone' => 'red', 'url' => route('admin.kebutuhan-panti.index', ['prioritas' => 'Mendesak', 'status' => 'belum'])],
            ['label' => 'Donasi bulan ini', 'value' => $this->rupiahSingkat($donasiBulanIni), 'icon' => 'heart', 'tone' => 'purple', 'url' => route('admin.donasi.index', ['status' => 'Diterima', 'dari' => now()->startOfMonth()->toDateString()])],
        ];
    }

    /**
     * Lima pesan terbaru dari formulir Kontak.
     *
     * @return list<array{name: string, email: string, subject: string, time: string, unread: bool}>
     */
    private function messages(): array
    {
        return Pesan::query()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Pesan $pesan): array => [
                'name' => $pesan->nama,
                'email' => $pesan->email,
                'subject' => $pesan->subjek ?: str($pesan->isi)->limit(60)->value(),
                'time' => $pesan->created_at->locale('id')->translatedFormat('j M, H:i'),
                'unread' => $pesan->status === StatusPesan::BelumDibaca,
            ])
            ->all();
    }

    /**
     * Empat kebutuhan teratas, dari yang paling mendesak.
     *
     * @return list<array{name: string, level: string, score: int, fulfilled: bool}>
     */
    private function needs(): array
    {
        return KebutuhanPanti::query()
            ->palingMendesak()
            ->limit(4)
            ->get()
            ->map(fn (KebutuhanPanti $kebutuhan): array => [
                'name' => $kebutuhan->nama,
                'level' => $kebutuhan->prioritas->value,
                'score' => $kebutuhan->skor_prioritas,
                'fulfilled' => $kebutuhan->terpenuhi,
            ])
            ->all();
    }

    /**
     * Nominal ringkas untuk kartu statistik, mis. "Rp 12,5 jt".
     */
    private function rupiahSingkat(int $nominal): string
    {
        foreach ([1_000_000_000 => 'M', 1_000_000 => 'jt'] as $satuan => $label) {
            if ($nominal >= $satuan) {
                $angka = rtrim(rtrim(number_format($nominal / $satuan, 1, ',', '.'), '0'), ',');

                return "Rp {$angka} {$label}";
            }
        }

        return Donasi::rupiah($nominal);
    }
}
