<?php

namespace Tests\Feature\Attractions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attraction;
use App\Models\Department;
use App\Models\Media;
use Laravel\Sanctum\Sanctum;

class MediaManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $adminUser;
    protected User $regularUser;
    protected Department $department;
    protected Attraction $attraction;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'tourist']);
        $this->department = Department::factory()->create();
        $this->attraction = Attraction::factory()->create(['department_id' => $this->department->id]);
    }

    /** @test */
    public function admin_can_upload_images_to_attraction()
    {
        Sanctum::actingAs($this->adminUser);

        $image1 = UploadedFile::fake()->image('attraction1.jpg', 800, 600);
        $image2 = UploadedFile::fake()->image('attraction2.png', 1200, 800);

        $response = $this->postJson("/api/admin/attractions/{$this->attraction->id}/media", [
            'files' => [$image1, $image2],
            'alt_text' => 'Beautiful attraction view',
            'sort_order' => 1
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'path',
                        'file_name',
                        'alt_text'
                    ]
                ],
                'message'
            ])
            ->assertJsonCount(2, 'data');

        // Check files were stored
        $this->assertCount(2, $this->attraction->media);
        
        foreach ($this->attraction->media as $media) {
            Storage::disk('public')->assertExists($media->file_path);
            $this->assertEquals('image', $media->type);
        }
    }

    /** @test */
    public function admin_can_upload_videos_to_attraction()
    {
        Sanctum::actingAs($this->adminUser);

        $video = UploadedFile::fake()->create('attraction_video.mp4', 5000, 'video/mp4');

        $response = $this->postJson("/api/admin/attractions/{$this->attraction->id}/media", [
            'files' => [$video],
            'alt_text' => 'Attraction promotional video'
        ]);

        $response->assertStatus(201);

        $media = $this->attraction->media()->first();
        $this->assertEquals('video', $media->type);
        $this->assertEquals('video/mp4', $media->mime_type);
        Storage::disk('public')->assertExists($media->file_path);
    }

    /** @test */
    public function media_upload_validates_file_types()
    {
        Sanctum::actingAs($this->adminUser);

        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->postJson("/api/admin/attractions/{$this->attraction->id}/media", [
            'files' => [$invalidFile]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files.0']);
    }

    /** @test */
    public function media_upload_validates_file_size()
    {
        Sanctum::actingAs($this->adminUser);

        // Create oversized image (over 10MB)
        $oversizedImage = UploadedFile::fake()->create('huge_image.jpg', 15000, 'image/jpeg');

        $response = $this->postJson("/api/admin/attractions/{$this->attraction->id}/media", [
            'files' => [$oversizedImage]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files.0']);
    }

    /** @test */
    public function media_upload_validates_image_dimensions()
    {
        Sanctum::actingAs($this->adminUser);

        // Create image with dimensions too small
        $smallImage = UploadedFile::fake()->image('small.jpg', 200, 100);

        $response = $this->postJson("/api/admin/attractions/{$this->attraction->id}/media", [
            'files' => [$smallImage]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files.0']);
    }

    /** @test */
    public function admin_can_get_attraction_media_list()
    {
        Sanctum::actingAs($this->adminUser);

        // Create some media for the attraction
        Media::factory()->count(3)->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id
        ]);

        $response = $this->getJson("/api/admin/attractions/{$this->attraction->id}/media");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'path',
                        'file_name',
                        'sort_order'
                    ]
                ],
                'message'
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function admin_can_update_media_metadata()
    {
        Sanctum::actingAs($this->adminUser);

        $media = Media::factory()->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'alt_text' => 'Original alt text'
        ]);

        $updateData = [
            'alt_text' => 'Updated alt text',
            'sort_order' => 5
        ];

        $response = $this->putJson("/api/admin/attractions/{$this->attraction->id}/media/{$media->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.alt_text', 'Updated alt text')
            ->assertJsonPath('data.sort_order', 5);

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'alt_text' => 'Updated alt text',
            'sort_order' => 5
        ]);
    }

    /** @test */
    public function admin_can_delete_media()
    {
        Sanctum::actingAs($this->adminUser);

        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('test_path/test.jpg', $image->getContent());

        $media = Media::factory()->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'path' => 'test_path/test.jpg'
        ]);

        $response = $this->deleteJson("/api/admin/attractions/{$this->attraction->id}/media/{$media->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing('test_path/test.jpg');
    }

    /** @test */
    public function admin_can_set_featured_media()
    {
        Sanctum::actingAs($this->adminUser);

        $media1 = Media::factory()->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'is_featured' => true
        ]);

        $media2 = Media::factory()->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'is_featured' => false
        ]);

        $response = $this->patchJson("/api/admin/attractions/{$this->attraction->id}/media/{$media2->id}/featured");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_featured', true);

        // Check that only the new media is featured
        $media1->refresh();
        $media2->refresh();
        
        $this->assertFalse($media1->is_featured);
        $this->assertTrue($media2->is_featured);
    }

    /** @test */
    public function admin_can_update_media_sort_order()
    {
        Sanctum::actingAs($this->adminUser);

        $media1 = Media::factory()->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'sort_order' => 1
        ]);

        $media2 = Media::factory()->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'sort_order' => 2
        ]);

        $media3 = Media::factory()->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'sort_order' => 3
        ]);

        // Reorder: media3, media1, media2
        $response = $this->patchJson("/api/admin/attractions/{$this->attraction->id}/media/order", [
            'media_order' => [$media3->id, $media1->id, $media2->id]
        ]);

        $response->assertStatus(200);

        // Check new sort orders
        $media1->refresh();
        $media2->refresh();
        $media3->refresh();

        $this->assertEquals(2, $media1->sort_order);
        $this->assertEquals(3, $media2->sort_order);
        $this->assertEquals(1, $media3->sort_order);
    }

    /** @test */
    public function admin_can_get_media_statistics()
    {
        Sanctum::actingAs($this->adminUser);

        // Create different types of media
        Media::factory()->count(3)->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'type' => 'image',
            'size' => 1000000 // 1MB each
        ]);

        Media::factory()->count(2)->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id,
            'type' => 'video',
            'size' => 5000000 // 5MB each
        ]);

        $response = $this->getJson("/api/admin/attractions/{$this->attraction->id}/media/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'images',
                    'videos',
                    'featured',
                    'total_size',
                    'by_type'
                ],
                'message'
            ]);

        $stats = $response->json('data');
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['images']);
        $this->assertEquals(2, $stats['videos']);
        $this->assertEquals(13000000, $stats['total_size']); // 3MB + 10MB
    }

    /** @test */
    public function regular_user_cannot_manage_media()
    {
        Sanctum::actingAs($this->regularUser);

        $image = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson("/api/admin/attractions/{$this->attraction->id}/media", [
            'files' => [$image]
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function media_operations_validate_attraction_ownership()
    {
        Sanctum::actingAs($this->adminUser);

        $otherAttraction = Attraction::factory()->create(['department_id' => $this->department->id]);
        
        $media = Media::factory()->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $otherAttraction->id
        ]);

        // Try to access media from wrong attraction
        $response = $this->getJson("/api/admin/attractions/{$this->attraction->id}/media/{$media->id}");
        $response->assertStatus(404);

        $response = $this->putJson("/api/admin/attractions/{$this->attraction->id}/media/{$media->id}", [
            'alt_text' => 'Updated'
        ]);
        $response->assertStatus(404);

        $response = $this->deleteJson("/api/admin/attractions/{$this->attraction->id}/media/{$media->id}");
        $response->assertStatus(404);
    }

    /** @test */
    public function media_upload_prevents_exceeding_file_limit()
    {
        Sanctum::actingAs($this->adminUser);

        // Create 50 existing media files (at the limit)
        Media::factory()->count(50)->create([
            'mediable_type' => Attraction::class,
            'mediable_id' => $this->attraction->id
        ]);

        $image = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson("/api/admin/attractions/{$this->attraction->id}/media", [
            'files' => [$image]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files']);
    }
}