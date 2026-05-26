<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Contract;
use App\Models\Document;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends BaseController
{
    private function storeUploadedFile(Request $request): ?string
    {
        $path = $request->file('file')?->store('documents', 'public');

        return $path ?: null;
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

            return $owner
                ? $owner->properties()->whereKey($contract->property_id)->exists()
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

    private function userCanAccessDocument(Request $request, Document $document): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'manager') {
            return true;
        }

        if ($user->role !== 'owner') {
            return false;
        }

        $hasRelation = false;

        if ($document->property_id) {
            $hasRelation = true;

            if (! $document->property || ! $this->userCanAccessProperty($request, $document->property)) {
                return false;
            }
        }

        if ($document->contract_id) {
            $hasRelation = true;

            if (! $document->contract || ! $this->userCanAccessContract($request, $document->contract)) {
                return false;
            }
        }

        if ($document->tenant_id) {
            $hasRelation = true;

            if (! $document->tenant || ! $this->userCanAccessTenant($request, $document->tenant)) {
                return false;
            }
        }

        return $hasRelation;
    }

    private function validateDocumentRelations(Request $request, array $validated): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'manager') {
            return true;
        }

        if ($user->role !== 'owner') {
            return false;
        }

        $hasRelation = false;

        if (! empty($validated['property_id'])) {
            $hasRelation = true;
            $property = Property::findOrFail($validated['property_id']);

            if (! $this->userCanAccessProperty($request, $property)) {
                return false;
            }
        }

        if (! empty($validated['contract_id'])) {
            $hasRelation = true;
            $contract = Contract::findOrFail($validated['contract_id']);

            if (! $this->userCanAccessContract($request, $contract)) {
                return false;
            }
        }

        if (! empty($validated['tenant_id'])) {
            $hasRelation = true;
            $tenant = Tenant::findOrFail($validated['tenant_id']);

            if (! $this->userCanAccessTenant($request, $tenant)) {
                return false;
            }
        }

        return $hasRelation;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            return response()->json(DocumentResource::collection(Document::with(['property', 'tenant', 'contract'])->get()));
        }

        $owner = $user->owner;

        if (! $owner) {
            return response()->json([]);
        }

        $documents = Document::with(['property', 'tenant', 'contract'])
            ->where(function ($query) use ($owner) {
                $query->whereHas('property.owners', function ($query) use ($owner) {
                    $query->where('owners.id', $owner->id);
                })
                ->orWhereHas('contract.property.owners', function ($query) use ($owner) {
                    $query->where('owners.id', $owner->id);
                })
                ->orWhereHas('tenant.properties.owners', function ($query) use ($owner) {
                    $query->where('owners.id', $owner->id);
                });
            })
            ->get()
            ->filter(fn (Document $document) => $this->userCanAccessDocument($request, $document))
            ->values();

        return response()->json(DocumentResource::collection($documents));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentRequest $request)
    {
        $validated = $request->validated();

        if (! $this->validateDocumentRelations($request, $validated)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $file = $request->file('file');
        $validated['file_path'] = $this->storeUploadedFile($request);

        if (! $validated['file_path']) {
            return response()->json(['message' => 'No se pudo guardar el archivo.'], 500);
        }

        $validated['name'] = $validated['name'] ?? $file->getClientOriginalName();
        unset($validated['file']);

        $document = Document::create($validated);

        return response()->json(new DocumentResource($document->load(['property', 'tenant', 'contract'])), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $document = Document::with(['property', 'tenant', 'contract'])->findOrFail($id);

        if (! $this->userCanAccessDocument($request, $document)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(new DocumentResource($document));
    }

    public function download(Request $request, $id)
    {
        $document = Document::with(['property', 'tenant', 'contract'])->findOrFail($id);

        if (! $this->userCanAccessDocument($request, $document)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message' => 'Archivo no encontrado.'], 404);
        }

        return Storage::disk('public')->response(
            $document->file_path,
            $document->name,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentRequest $request, $id)
    {
        $document = Document::with(['property', 'tenant', 'contract'])->findOrFail($id);

        if (! $this->userCanAccessDocument($request, $document)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();

        $relationData = [
            'property_id' => array_key_exists('property_id', $validated) ? $validated['property_id'] : $document->property_id,
            'tenant_id' => array_key_exists('tenant_id', $validated) ? $validated['tenant_id'] : $document->tenant_id,
            'contract_id' => array_key_exists('contract_id', $validated) ? $validated['contract_id'] : $document->contract_id,
        ];

        if (! $this->validateDocumentRelations($request, $relationData)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($request->hasFile('file')) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $validated['file_path'] = $this->storeUploadedFile($request);

            if (! $validated['file_path']) {
                return response()->json(['message' => 'No se pudo guardar el archivo.'], 500);
            }

            unset($validated['file']);
        }

        $document->update($validated);

        return response()->json(new DocumentResource($document->load(['property', 'tenant', 'contract'])));
    }

    public function removeFile(Request $request, $id)
    {
        $document = Document::with(['property', 'tenant', 'contract'])->findOrFail($id);

        if (! $this->userCanAccessDocument($request, $document)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->update(['file_path' => null]);

        return response()->json(new DocumentResource($document->load(['property', 'tenant', 'contract'])));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $document = Document::with(['property', 'tenant', 'contract'])->findOrFail($id);

        if (! $this->userCanAccessDocument($request, $document)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
