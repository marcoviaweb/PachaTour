<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Attraction;
use App\Models\Tour;
use App\Models\Booking;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', '!=', User::ROLE_ADMIN)->get();
        $attractions = Attraction::all();
        $tours = Tour::all();
        $completedBookings = Booking::where('status', 'completed')->get();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles para crear reseñas');
            return;
        }

        // Reseñas para atractivos
        foreach ($attractions->random(min(20, $attractions->count())) as $attraction) {
            $reviewsCount = fake()->numberBetween(1, 5);
            
            for ($i = 0; $i < $reviewsCount; $i++) {
                $user = $users->random();
                $rating = fake()->randomFloat(2, 3.0, 5.0);
                
                Review::create([
                    'user_id' => $user->id,
                    'reviewable_type' => Attraction::class,
                    'reviewable_id' => $attraction->id,
                    'booking_id' => null,
                    'rating' => $rating,
                    'title' => $this->generateReviewTitle($rating),
                    'comment' => $this->generateReviewComment($attraction->name, $rating),
                    'detailed_ratings' => [
                        'location' => fake()->randomFloat(1, 3.0, 5.0),
                        'value' => fake()->randomFloat(1, 3.0, 5.0),
                        'facilities' => fake()->randomFloat(1, 3.0, 5.0),
                        'service' => fake()->randomFloat(1, 3.0, 5.0),
                    ],
                    'pros' => $this->generatePros($rating),
                    'cons' => $rating < 4.0 ? $this->generateCons() : null,
                    'would_recommend' => $rating >= 4.0,
                    'visit_date' => fake()->dateTimeBetween('-6 months', '-1 week')->format('Y-m-d'),
                    'travel_type' => fake()->randomElement(array_keys(Review::TRAVEL_TYPES)),
                    'status' => fake()->randomElement(['approved', 'approved', 'approved', 'pending']),
                    'helpful_votes' => fake()->numberBetween(0, 25),
                    'not_helpful_votes' => fake()->numberBetween(0, 5),
                    'is_verified' => fake()->boolean(60),
                    'language' => $user->preferred_language ?? 'es',
                ]);
            }
        }

        // Reseñas para tours
        foreach ($tours->random(min(15, $tours->count())) as $tour) {
            $reviewsCount = fake()->numberBetween(1, 3);
            
            for ($i = 0; $i < $reviewsCount; $i++) {
                $user = $users->random();
                $rating = fake()->randomFloat(2, 3.5, 5.0);
                
                Review::create([
                    'user_id' => $user->id,
                    'reviewable_type' => Tour::class,
                    'reviewable_id' => $tour->id,
                    'booking_id' => null,
                    'rating' => $rating,
                    'title' => $this->generateTourReviewTitle($rating),
                    'comment' => $this->generateTourReviewComment($tour->name, $rating),
                    'detailed_ratings' => [
                        'guide' => fake()->randomFloat(1, 3.5, 5.0),
                        'organization' => fake()->randomFloat(1, 3.5, 5.0),
                        'value' => fake()->randomFloat(1, 3.0, 5.0),
                        'activities' => fake()->randomFloat(1, 3.5, 5.0),
                        'accommodation' => $tour->duration_days > 1 ? fake()->randomFloat(1, 3.0, 5.0) : null,
                        'food' => fake()->randomFloat(1, 3.0, 5.0),
                    ],
                    'pros' => $this->generateTourPros($rating),
                    'cons' => $rating < 4.0 ? $this->generateTourCons() : null,
                    'would_recommend' => $rating >= 4.0,
                    'visit_date' => fake()->dateTimeBetween('-4 months', '-1 week')->format('Y-m-d'),
                    'travel_type' => fake()->randomElement(array_keys(Review::TRAVEL_TYPES)),
                    'status' => fake()->randomElement(['approved', 'approved', 'approved', 'pending']),
                    'helpful_votes' => fake()->numberBetween(0, 30),
                    'not_helpful_votes' => fake()->numberBetween(0, 8),
                    'is_verified' => fake()->boolean(70),
                    'language' => $user->preferred_language ?? 'es',
                ]);
            }
        }

        // Reseñas basadas en reservas completadas
        foreach ($completedBookings->random(min(10, $completedBookings->count())) as $booking) {
            $rating = fake()->randomFloat(2, 3.8, 5.0);
            
            Review::create([
                'user_id' => $booking->user_id,
                'reviewable_type' => Tour::class,
                'reviewable_id' => $booking->tourSchedule->tour_id,
                'booking_id' => $booking->id,
                'rating' => $rating,
                'title' => $this->generateTourReviewTitle($rating),
                'comment' => $this->generateVerifiedReviewComment($booking, $rating),
                'detailed_ratings' => [
                    'guide' => fake()->randomFloat(1, 4.0, 5.0),
                    'organization' => fake()->randomFloat(1, 4.0, 5.0),
                    'value' => fake()->randomFloat(1, 3.5, 5.0),
                    'activities' => fake()->randomFloat(1, 4.0, 5.0),
                ],
                'would_recommend' => $rating >= 4.0,
                'visit_date' => $booking->tourSchedule->date->format('Y-m-d'),
                'travel_type' => fake()->randomElement(array_keys(Review::TRAVEL_TYPES)),
                'status' => 'approved',
                'helpful_votes' => fake()->numberBetween(5, 40),
                'not_helpful_votes' => fake()->numberBetween(0, 3),
                'is_verified' => true, // Reseñas de reservas verificadas
                'language' => $booking->user->preferred_language ?? 'es',
            ]);
        }

        $this->command->info('✅ Creadas ' . Review::count() . ' reseñas de prueba');
    }

    private function generateReviewTitle(float $rating): string
    {
        if ($rating >= 4.5) {
            return fake()->randomElement([
                'Experiencia increíble', 'Altamente recomendado', 'Superó mis expectativas',
                'Lugar mágico', 'Imperdible', 'Experiencia única'
            ]);
        } elseif ($rating >= 4.0) {
            return fake()->randomElement([
                'Muy buena experiencia', 'Vale la pena visitarlo', 'Recomendado',
                'Buena opción', 'Interesante visita'
            ]);
        } elseif ($rating >= 3.0) {
            return fake()->randomElement([
                'Experiencia regular', 'Está bien', 'Puede mejorar',
                'No está mal', 'Promedio'
            ]);
        } else {
            return fake()->randomElement([
                'Decepcionante', 'No lo recomiendo', 'Puede mejorar mucho',
                'No cumplió expectativas'
            ]);
        }
    }

    private function generateTourReviewTitle(float $rating): string
    {
        if ($rating >= 4.5) {
            return fake()->randomElement([
                'Tour excepcional', 'Organización perfecta', 'Guía excelente',
                'Experiencia inolvidable', 'Todo perfecto', 'Superó expectativas'
            ]);
        } elseif ($rating >= 4.0) {
            return fake()->randomElement([
                'Buen tour', 'Bien organizado', 'Recomendable',
                'Buena experiencia', 'Vale la pena'
            ]);
        } else {
            return fake()->randomElement([
                'Tour regular', 'Puede mejorar', 'Organización deficiente',
                'No cumplió expectativas'
            ]);
        }
    }

    private function generateReviewComment(string $attractionName, float $rating): string
    {
        $positiveComments = [
            "Visitar {$attractionName} fue una experiencia increíble. Los paisajes son espectaculares y la organización fue excelente.",
            "Recomiendo totalmente {$attractionName}. Es un lugar único que no se puede perder cuando se visita Bolivia.",
            "La belleza de {$attractionName} es indescriptible. Las fotos no le hacen justicia a la realidad.",
            "Excelente atractivo turístico. {$attractionName} ofrece vistas impresionantes y una experiencia cultural rica."
        ];

        $neutralComments = [
            "La visita a {$attractionName} estuvo bien. Es un lugar interesante aunque esperaba un poco más.",
            "{$attractionName} es un lugar bonito para visitar, aunque la organización podría mejorar.",
            "Experiencia promedio en {$attractionName}. Tiene potencial pero necesita mejor mantenimiento."
        ];

        $negativeComments = [
            "Mi experiencia en {$attractionName} no fue la esperada. El lugar necesita mejor mantenimiento.",
            "Aunque {$attractionName} tiene belleza natural, la organización y servicios pueden mejorar significativamente."
        ];

        if ($rating >= 4.0) {
            return fake()->randomElement($positiveComments);
        } elseif ($rating >= 3.0) {
            return fake()->randomElement($neutralComments);
        } else {
            return fake()->randomElement($negativeComments);
        }
    }

    private function generateTourReviewComment(string $tourName, float $rating): string
    {
        $positiveComments = [
            "El tour {$tourName} fue excepcional. El guía muy conocedor y la organización impecable.",
            "Recomiendo 100% {$tourName}. Cada momento fue especial y aprendimos mucho sobre Bolivia.",
            "Experiencia inolvidable con {$tourName}. La atención al detalle y profesionalismo destacables.",
            "El tour {$tourName} superó todas mis expectativas. Guía excelente y lugares increíbles."
        ];

        $neutralComments = [
            "El tour {$tourName} estuvo bien en general, aunque algunos aspectos podrían mejorar.",
            "Experiencia promedio con {$tourName}. Lugares bonitos pero organización regular.",
            "{$tourName} es un tour interesante, aunque esperaba un poco más por el precio."
        ];

        if ($rating >= 4.0) {
            return fake()->randomElement($positiveComments);
        } else {
            return fake()->randomElement($neutralComments);
        }
    }

    private function generateVerifiedReviewComment(Booking $booking, float $rating): string
    {
        $tourName = $booking->tourSchedule->tour->name;
        $date = $booking->tourSchedule->date->format('d/m/Y');
        
        return "Realicé el tour {$tourName} el {$date}. " . $this->generateTourReviewComment($tourName, $rating);
    }

    private function generatePros(float $rating): array
    {
        $allPros = [
            'Paisajes espectaculares', 'Fácil acceso', 'Bien señalizado',
            'Personal amable', 'Limpio y ordenado', 'Precio justo',
            'Experiencia única', 'Ideal para fotos', 'Ambiente tranquilo',
            'Rica historia', 'Cultura auténtica', 'Vistas panorámicas'
        ];

        $count = $rating >= 4.5 ? fake()->numberBetween(3, 5) : fake()->numberBetween(2, 4);
        return fake()->randomElements($allPros, $count);
    }

    private function generateCons(): array
    {
        $allCons = [
            'Falta de mantenimiento', 'Precio elevado', 'Difícil acceso',
            'Poca información', 'Servicios limitados', 'Muy concurrido',
            'Falta de señalización', 'Personal poco capacitado'
        ];

        return fake()->randomElements($allCons, fake()->numberBetween(1, 3));
    }

    private function generateTourPros(float $rating): array
    {
        $allPros = [
            'Guía muy conocedor', 'Excelente organización', 'Grupo pequeño',
            'Comida deliciosa', 'Transporte cómodo', 'Horarios puntuales',
            'Lugares increíbles', 'Buena relación calidad-precio', 'Atención personalizada',
            'Experiencia auténtica', 'Actividades variadas', 'Hospedaje confortable'
        ];

        $count = $rating >= 4.5 ? fake()->numberBetween(4, 6) : fake()->numberBetween(2, 4);
        return fake()->randomElements($allPros, $count);
    }

    private function generateTourCons(): array
    {
        $allCons = [
            'Precio elevado', 'Horarios muy ajustados', 'Grupo muy grande',
            'Comida regular', 'Transporte incómodo', 'Falta de tiempo libre',
            'Hospedaje básico', 'Actividades repetitivas', 'Guía poco preparado'
        ];

        return fake()->randomElements($allCons, fake()->numberBetween(1, 2));
    }
}