<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnakPanti;
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
     * @return list<array{label: string, value: string, icon: string, tone: string}>
     */
    private function stats(): array
    {
        return [
            ['label' => 'Anak panti', 'value' => (string) AnakPanti::aktif()->count(), 'icon' => 'users', 'tone' => 'green'],
            ['label' => 'Kebutuhan mendesak', 'value' => '5', 'icon' => 'package', 'tone' => 'red'],
            ['label' => 'Donasi bulan ini', 'value' => 'Rp 12,5 jt', 'icon' => 'heart', 'tone' => 'purple'],
        ];
    }

    /**
     * @return list<array{name: string, role: string, subject: string, time: string}>
     */
    private function messages(): array
    {
        return [
            ['name' => 'Ibu Ratna', 'role' => 'Donatur', 'subject' => 'Ingin berdonasi sembako', 'time' => '5 Okt, 09:12'],
            ['name' => 'Bapak Andi', 'role' => 'Pengunjung', 'subject' => 'Apakah menerima kunjungan?', 'time' => '5 Okt, 08:40'],
            ['name' => 'Komunitas Peduli', 'role' => 'Komunitas', 'subject' => 'Rencana bakti sosial', 'time' => '4 Okt, 19:05'],
            ['name' => 'Dewi Lestari', 'role' => 'Donatur', 'subject' => 'Terima kasih informasinya', 'time' => '4 Okt, 16:20'],
            ['name' => 'Yayasan Harapan', 'role' => 'Lembaga', 'subject' => 'Permintaan proposal', 'time' => '3 Okt, 11:30'],
        ];
    }

    /**
     * @return list<array{name: string, level: string, score: int, fulfilled: bool}>
     */
    private function needs(): array
    {
        return [
            ['name' => 'Beras 50 kg', 'level' => 'Mendesak', 'score' => 95, 'fulfilled' => false],
            ['name' => 'Susu dan vitamin anak', 'level' => 'Mendesak', 'score' => 88, 'fulfilled' => false],
            ['name' => 'Seragam sekolah', 'level' => 'Sedang', 'score' => 62, 'fulfilled' => false],
            ['name' => 'Buku tulis', 'level' => 'Sedang', 'score' => 50, 'fulfilled' => true],
        ];
    }
}
