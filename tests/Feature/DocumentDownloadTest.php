<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_download_document_through_api(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/demo.pdf', 'Contenido demo');

        $property = Property::factory()->create();
        $document = Document::factory()->create([
            'name' => 'demo.pdf',
            'file_path' => 'documents/demo.pdf',
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs(User::factory()->create(['role' => 'manager']));

        $this->getJson("/api/documents/{$document->id}/download")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_owner_cannot_download_document_from_other_owner(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/private.pdf', 'Contenido privado');

        $user = User::factory()->create(['role' => 'owner']);
        Owner::factory()->create(['user_id' => $user->id]);
        $document = Document::factory()->create([
            'name' => 'private.pdf',
            'file_path' => 'documents/private.pdf',
            'property_id' => Property::factory()->create()->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/documents/{$document->id}/download")
            ->assertForbidden();
    }
}
