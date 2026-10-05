<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengasuhRequest;
use App\Http\Resources\PengasuhResource;
use App\Models\Pengasuh;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PengasuhController extends Controller
{
    /**
     * Display a listing of caregivers. Filters: search (Nama/NIK), Jabatan.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $pengasuh = Pengasuh::query()
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('Nama', 'like', "%{$search}%")
                    ->orWhere('NIK', 'like', "%{$search}%")
            ))
            ->when($request->string('Jabatan')->value(), fn ($q, $jabatan) => $q->where('Jabatan', $jabatan))
            ->orderBy('Nama')
            ->paginate($this->perPage($request));

        return PengasuhResource::collection($pengasuh);
    }

    /**
     * Store a newly created caregiver.
     */
    public function store(PengasuhRequest $request): PengasuhResource
    {
        return new PengasuhResource(Pengasuh::create($request->validated()));
    }

    /**
     * Display the specified caregiver.
     */
    public function show(Pengasuh $pengasuh): PengasuhResource
    {
        return new PengasuhResource($pengasuh);
    }

    /**
     * Update the specified caregiver.
     */
    public function update(PengasuhRequest $request, Pengasuh $pengasuh): PengasuhResource
    {
        $pengasuh->update($request->validated());

        return new PengasuhResource($pengasuh);
    }

    /**
     * Remove the specified caregiver.
     */
    public function destroy(Pengasuh $pengasuh): Response
    {
        $pengasuh->delete();

        return response()->noContent();
    }
}
