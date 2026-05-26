<?php

namespace App\Http\Controllers;

use App\Http\Requests\OwnerRequest;
use App\Http\Resources\OwnerResource;
use App\Models\Owner;
use Illuminate\Http\Request;

class OwnerController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return OwnerResource::collection(Owner::with('properties')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OwnerRequest $request)
    {
        $validated = $request->validated();

        $owner = Owner::create($validated);

        return response()->json(new OwnerResource($owner->load('properties')), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return new OwnerResource(Owner::with('properties')->findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OwnerRequest $request, $id)
    {
        $owner = Owner::findOrFail($id);

        $validated = $request->validated();

        $owner->update($validated);

        return response()->json(new OwnerResource($owner->load('properties')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $owner = Owner::findOrFail($id);
        $owner->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function assignProperty(Request $request, $id)
    {
        $owner = Owner::findOrFail($id);

        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $owner->properties()->syncWithoutDetaching([$request->property_id]);

        return response()->json(['message' => 'Property assigned']);
    }
}
