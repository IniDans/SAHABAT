<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($request->string('role')->value(), fn ($q, $role) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate($this->perPage($request));

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $request): UserResource
    {
        return new UserResource(User::create($request->validated()));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Update the specified user.
     */
    public function update(UserRequest $request, User $user): UserResource
    {
        $data = $request->validated();

        if ($user->is($request->user())) {
            if (($data['role'] ?? Role::Admin->value) !== Role::Admin->value) {
                throw ValidationException::withMessages(['role' => 'Anda tidak bisa menurunkan role akun sendiri.']);
            }

            if (array_key_exists('is_active', $data) && ! $data['is_active']) {
                throw ValidationException::withMessages(['is_active' => 'Anda tidak bisa menonaktifkan akun sendiri.']);
            }
        }

        $user->update($data);

        // Akun yang dinonaktifkan langsung keluar dari semua perangkat.
        if (! $user->is_active) {
            $user->cabutSemuaAkses();
        }

        return new UserResource($user);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): Response
    {
        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['user' => 'Anda tidak bisa menghapus akun sendiri.']);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }
}
