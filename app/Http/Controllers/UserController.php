<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(
            User::orderBy('name')->get()
        );
    }

    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create($validated);

        $this->syncOwnerProfile($user);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();

        if ($request->user()->is($user) && ($validated['role'] ?? $user->role) !== 'manager') {
            return response()->json([
                'message' => 'No puedes quitarte el rol manager a ti mismo.',
            ], 422);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);
        $this->syncOwnerProfile($user);

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'No puedes borrar tu propio usuario.',
            ], 422);
        }

        $user->delete();

        return response()->noContent();
    }

    private function syncOwnerProfile(User $user): void
    {
        if ($user->role !== 'owner') {
            return;
        }

        Owner::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
            ],
        );
    }
}
