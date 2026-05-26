<?php

namespace App\Http\Controllers;

use App\Http\Requests\TenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends BaseController
{
    private function getValidatedPropertyIds(array $validated): array
    {
        if (array_key_exists('property_ids', $validated)) {
            return array_values(array_filter($validated['property_ids']));
        }

        return ! empty($validated['property_id']) ? [$validated['property_id']] : [];
    }

    private function userCanAccessProperties(Request $request, array $propertyIds): bool
    {
        foreach ($propertyIds as $propertyId) {
            $property = Property::findOrFail($propertyId);

            if (! $this->userCanAccessProperty($request, $property)) {
                return false;
            }
        }

        return true;
    }

    private function userCanAccessTenant(Request $request, Tenant $tenant): bool
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

            if (! $owner) {
                return false;
            }

            if ((int) $tenant->owner_id === (int) $owner->id) {
                return true;
            }

            return $tenant->properties()
                    ->whereHas('owners', function ($query) use ($owner) {
                        $query->where('owners.id', $owner->id);
                    })
                    ->exists();
        }

        return false;
    }

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
            return response()->json(TenantResource::collection(Tenant::with('properties')->get()));
        }

        $owner = $user->owner;

        if (! $owner) {
            return response()->json([]);
        }

        $tenants = Tenant::with('properties')
            ->where(function ($query) use ($owner) {
                $query->where('owner_id', $owner->id)
                    ->orWhereHas('properties.owners', function ($query) use ($owner) {
                        $query->where('owners.id', $owner->id);
                    });
            })
            ->get();

        return response()->json(TenantResource::collection($tenants));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantRequest $request)
    {
        $validated = $request->validated();

        $owner = null;
        $propertyIds = $this->getValidatedPropertyIds($validated);

        if ($request->user()->role === 'owner') {
            $owner = $request->user()->owner;

            if (! $owner) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $validated['owner_id'] = $owner->id;
        }

        if (! $this->userCanAccessProperties($request, $propertyIds)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        unset($validated['property_id']);
        unset($validated['property_ids']);

        $tenant = Tenant::create($validated);

        if ($propertyIds) {
            $tenant->properties()->sync($propertyIds);
        }

        return response()->json(new TenantResource($tenant->load('properties')), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $tenant = Tenant::with('properties')->findOrFail($id);

        if (! $this->userCanAccessTenant($request, $tenant)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(new TenantResource($tenant));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(TenantRequest $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        if (! $this->userCanAccessTenant($request, $tenant)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();
        $propertyIds = $this->getValidatedPropertyIds($validated);

        if (! $this->userCanAccessProperties($request, $propertyIds)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        unset($validated['property_id']);
        unset($validated['property_ids']);

        $tenant->update($validated);

        if ($request->has('property_ids') || $request->has('property_id')) {
            $tenant->properties()->sync($propertyIds);
        }

        return response()->json(new TenantResource($tenant->load('properties')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        if (! $this->userCanAccessTenant($request, $tenant)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tenant->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function assignProperty(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $property = Property::findOrFail($request->property_id);

        if (! $this->userCanAccessProperty($request, $property)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tenant->properties()->syncWithoutDetaching([$request->property_id]);

        return response()->json(['message' => 'Property assigned']);
    }

    public function detachProperty(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $property = Property::findOrFail($request->property_id);

        if (! $this->userCanAccessProperty($request, $property)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! $this->userCanAccessTenant($request, $tenant)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tenant->properties()->detach($request->property_id);

        return response()->json(['message' => 'Property detached']);
    }
}
