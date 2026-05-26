<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ContractController extends BaseController
{
    private function userCanAccessContract(Request $request, Contract $contract): bool
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

            return $owner && $contract->property
                ? $owner->properties()->whereKey($contract->property_id)->exists()
                : false;
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

            return $owner
                ? $tenant->properties()
                    ->whereHas('owners', function ($query) use ($owner) {
                        $query->where('owners.id', $owner->id);
                    })
                    ->exists()
                : false;
        }

        return false;
    }

    private function userCanAccessTenants(Request $request, array $tenantIds): bool
    {
        foreach ($tenantIds as $tenantId) {
            $tenant = Tenant::findOrFail($tenantId);

            if (! $this->userCanAccessTenant($request, $tenant)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            return response()->json(ContractResource::collection(Contract::with(['property', 'tenants'])->get()));
        }

        $owner = $user->owner;

        if (! $owner) {
            return response()->json([]);
        }

        $contracts = Contract::with(['property', 'tenants'])
            ->whereHas('property.owners', function ($query) use ($owner) {
                $query->where('owners.id', $owner->id);
            })
            ->get();

        return response()->json(ContractResource::collection($contracts));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContractRequest $request)
    {
        $validated = $request->validated();

        $property = Property::findOrFail($validated['property_id']);

        if (! $this->userCanAccessProperty($request, $property)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! $this->userCanAccessTenants($request, $validated['tenant_ids'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tenantIds = $validated['tenant_ids'];
        unset($validated['tenant_ids']);

        $contract = Contract::create($validated);
        $contract->tenants()->sync($tenantIds);

        return response()->json(new ContractResource($contract->load(['property', 'tenants'])), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $contract = Contract::with(['property', 'tenants'])->findOrFail($id);

        if (! $this->userCanAccessContract($request, $contract)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(new ContractResource($contract));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContractRequest $request, $id)
    {
        $contract = Contract::with('property')->findOrFail($id);

        if (! $this->userCanAccessContract($request, $contract)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();

        if (array_key_exists('property_id', $validated)) {
            $property = Property::findOrFail($validated['property_id']);

            if (! $this->userCanAccessProperty($request, $property)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        if (array_key_exists('tenant_ids', $validated)) {
            if (! $this->userCanAccessTenants($request, $validated['tenant_ids'])) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $tenantIds = $validated['tenant_ids'];
            unset($validated['tenant_ids']);
            $contract->tenants()->sync($tenantIds);
        }

        $contract->update($validated);

        return response()->json(new ContractResource($contract->load(['property', 'tenants'])));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $contract = Contract::with('property')->findOrFail($id);

        if (! $this->userCanAccessContract($request, $contract)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $contract->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
