<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WaliAnakRequest;
use App\Http\Resources\WaliAnakResource;
use App\Models\WaliAnak;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WaliAnakController extends Controller
{
    /**
     * Display a listing of guardians with the number of children they look after.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $wali = WaliAnak::query()
            ->withCount('anak')
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('Nama_Wali', 'like', "%{$search}%")
                    ->orWhere('Alamat_Wali', 'like', "%{$search}%")
            ))
            ->orderBy('Nama_Wali')
            ->paginate($this->perPage($request));

        return WaliAnakResource::collection($wali);
    }

    /**
     * Store a newly created guardian.
     */
    public function store(WaliAnakRequest $request): WaliAnakResource
    {
        return new WaliAnakResource(WaliAnak::create($request->validated()));
    }

    /**
     * Display the specified guardian with their children.
     */
    public function show(WaliAnak $waliAnak): WaliAnakResource
    {
        return new WaliAnakResource($waliAnak->load(['anak' => fn ($q) => $q->orderBy('Nama')]));
    }

    /**
     * Update the specified guardian.
     */
    public function update(WaliAnakRequest $request, WaliAnak $waliAnak): WaliAnakResource
    {
        $waliAnak->update($request->validated());

        return new WaliAnakResource($waliAnak);
    }

    /**
     * Remove the specified guardian. Their children are kept, with ID_Wali set to null.
     */
    public function destroy(WaliAnak $waliAnak): Response
    {
        $waliAnak->delete();

        return response()->noContent();
    }
}
