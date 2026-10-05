<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnakPantiRequest;
use App\Http\Resources\AnakPantiResource;
use App\Models\AnakPanti;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AnakPantiController extends Controller
{
    /**
     * Display a listing of the children.
     * Filters: search (Nama/NIK), Status_Asuh, Jenis_Kelamin, Keterangan, ID_Wali.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $anak = AnakPanti::query()
            ->with('wali')
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('Nama', 'like', "%{$search}%")
                    ->orWhere('NIK', 'like', "%{$search}%")
            ))
            ->when($request->string('Status_Asuh')->value(), fn ($q, $status) => $q->where('Status_Asuh', $status))
            ->when($request->string('Jenis_Kelamin')->value(), fn ($q, $jk) => $q->where('Jenis_Kelamin', $jk))
            ->when($request->string('Keterangan')->value(), fn ($q, $ket) => $q->where('Keterangan', $ket))
            ->when($request->integer('ID_Wali'), fn ($q, $id) => $q->where('ID_Wali', $id))
            ->orderBy('Nama')
            ->paginate($this->perPage($request));

        return AnakPantiResource::collection($anak);
    }

    /**
     * Store a newly created child.
     */
    public function store(AnakPantiRequest $request): AnakPantiResource
    {
        return new AnakPantiResource(AnakPanti::create($request->validated())->load('wali'));
    }

    /**
     * Display the specified child with their guardian.
     */
    public function show(AnakPanti $anakPanti): AnakPantiResource
    {
        return new AnakPantiResource($anakPanti->load('wali'));
    }

    /**
     * Update the specified child.
     */
    public function update(AnakPantiRequest $request, AnakPanti $anakPanti): AnakPantiResource
    {
        $anakPanti->update($request->validated());

        return new AnakPantiResource($anakPanti->load('wali'));
    }

    /**
     * Remove the specified child. Children who are still active cannot be
     * deleted; set Status_Asuh to "Alumni" first.
     */
    public function destroy(AnakPanti $anakPanti): Response
    {
        abort_unless($anakPanti->isDeletable(), 409, 'Anak yang masih aktif tidak dapat dihapus dari sistem.');

        $anakPanti->delete();

        return response()->noContent();
    }
}
