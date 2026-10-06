<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfilPantiRequest;
use App\Models\ProfilPanti;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Isi halaman Tentang Kami dan footer website, diubah per bagian (tab).
 */
class ProfilPantiController extends Controller
{
    public function edit(Request $request): View
    {
        $bagian = array_key_exists($request->query('bagian'), ProfilPanti::BAGIAN)
            ? $request->query('bagian')
            : array_key_first(ProfilPanti::BAGIAN);

        return view('admin.profil-panti.edit', [
            'bagian' => $bagian,
            'profil' => ProfilPanti::bagian($bagian),
        ]);
    }

    public function update(ProfilPantiRequest $request, string $bagian): RedirectResponse
    {
        ProfilPanti::simpan($bagian, $request->nilai());

        return to_route('admin.profil.edit', ['bagian' => $bagian])
            ->with('status', ProfilPanti::BAGIAN[$bagian].' berhasil disimpan dan sudah tampil di website.');
    }
}
