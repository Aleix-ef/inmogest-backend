<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends BaseController
{
    private function userCanAccessProperty(Request $request, Property $property): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'manager') {
            return true;
        }

        if ($user->role === 'owner') {
            $owner = $user->owner;

            return $owner
                ? $owner->properties()->whereKey($property->id)->exists()
                : false;
        }

        return false;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            return response()->json(PropertyResource::collection(Property::all()));
        }

        $owner = $user->owner;

        return response()->json($owner ? PropertyResource::collection($owner->properties) : []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropertyRequest $request)
    {
        $validated = $request->validated();

        if ($request->user()->role === 'owner' && ! $request->user()->owner) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $property = Property::create($validated);

        if ($request->user()->role === 'owner') {
            $request->user()->owner->properties()->syncWithoutDetaching([$property->id]);
        }

        return response()->json(new PropertyResource($property), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $property = Property::with('tenants', 'owners')->findOrFail($id);

        if (! $this->userCanAccessProperty($request, $property)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(new PropertyResource($property));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PropertyRequest $request, string $id)
    {
        $property = Property::findOrFail($id);

        if (! $this->userCanAccessProperty($request, $property)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();

        $property->update($validated);

        return response()->json(new PropertyResource($property));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $property = Property::findOrFail($id);

        if (! $this->userCanAccessProperty($request, $property)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $property->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function assignTenant(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        if (! $this->userCanAccessProperty($request, $property)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id'
        ]);

        $property->tenants()->syncWithoutDetaching([$request->tenant_id]);

        return response()->json(['message' => 'Tenant assigned']);
    }
}
