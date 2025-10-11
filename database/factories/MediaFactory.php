<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Media;
use App\Models\Attraction;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['image', 'video'];
        $type = $this->faker->randomElement($types);
        
        $extensions = [
            'image' => ['jpg', 'jpeg', 'png', 'webp'],
            'video' => ['mp4', 'mov', 'avi', 'webm']
        ];
        
        $mimeTypes = [
            'image' => ['image/jpeg', 'image/png', 'image/webp'],
            'video' => ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm']
        ];
        
        $extension = $this->faker->randomElement($extensions[$type]);
        $mimeType = $this->faker->randomElement($mimeTypes[$type]);
        
        $filename = $this->faker->uuid() . '.' . $extension;
        $originalName = $this->faker->words(3, true) . '.' . $extension;
        
        return [
            'mediable_type' => Attraction::class,
            'mediable_id' => Attraction::factory(),
            'name' => $this->faker->words(3, true),
            'file_name' => $filename,
            'original_name' => $originalName,
            'type' => $type,
            'path' => "attractions/1/{$type}s/{$filename}",
            'size' => $type === 'image' ? $this->faker->numberBetween(500000, 5000000) : $this->faker->numberBetween(5000000, 50000000),
            'mime_type' => $mimeType,
            'alt_text' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_featured' => false,
        ];
    }

    /**
     * Indicate that the media is an image.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'mime_type' => 'image/jpeg',
            'path' => 'images/placeholder.jpg', // Usar imagen placeholder real
            'size' => $this->faker->numberBetween(500000, 5000000),
        ]);
    }

    /**
     * Indicate that the media is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'mime_type' => 'video/mp4',
            'path' => 'attractions/1/videos/' . $this->faker->uuid() . '.mp4',
            'size' => $this->faker->numberBetween(5000000, 50000000),
        ]);
    }

    /**
     * Indicate that the media is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}