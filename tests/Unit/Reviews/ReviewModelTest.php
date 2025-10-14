<?php

namespace Tests\Unit\Reviews;

use Tests\TestCase;
use App\Models\User;
use App\Models\Review;
use App\Models\Attraction;
use App\Features\Tours\Models\Booking;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Attraction $attraction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $department = Department::factory()->create();
        $this->attraction = Attraction::factory()->create(['department_id' => $department->id]);
    }

    public function test_review_belongs_to_user()
    {
        $review = Review::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($this->user->id, $review->user->id);
    }

    public function test_review_belongs_to_reviewable()
    {
        $review = Review::factory()->create([
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id
        ]);

        $this->assertInstanceOf(Attraction::class, $review->reviewable);
        $this->assertEquals($this->attraction->id, $review->reviewable->id);
    }

    public function test_review_belongs_to_booking()
    {
        // Create necessary dependencies for booking
        $tour = \App\Features\Tours\Models\Tour::factory()->create();
        $tour->attractions()->attach($this->attraction->id); // Many-to-many relationship
        
        $schedule = \App\Features\Tours\Models\TourSchedule::factory()->create(['tour_id' => $tour->id]);
        
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'tour_schedule_id' => $schedule->id
        ]);
        
        $review = Review::factory()->create(['booking_id' => $booking->id]);

        $this->assertInstanceOf(Booking::class, $review->booking);
        $this->assertEquals($booking->id, $review->booking->id);
    }

    public function test_approved_scope_returns_only_approved_reviews()
    {
        Review::factory()->create(['status' => Review::STATUS_APPROVED]);
        Review::factory()->create(['status' => Review::STATUS_PENDING]);
        Review::factory()->create(['status' => Review::STATUS_REJECTED]);

        $approvedReviews = Review::approved()->get();

        $this->assertEquals(1, $approvedReviews->count());
        $this->assertEquals(Review::STATUS_APPROVED, $approvedReviews->first()->status);
    }

    public function test_pending_scope_returns_only_pending_reviews()
    {
        Review::factory()->create(['status' => Review::STATUS_APPROVED]);
        Review::factory()->create(['status' => Review::STATUS_PENDING]);
        Review::factory()->create(['status' => Review::STATUS_REJECTED]);

        $pendingReviews = Review::pending()->get();

        $this->assertEquals(1, $pendingReviews->count());
        $this->assertEquals(Review::STATUS_PENDING, $pendingReviews->first()->status);
    }

    public function test_verified_scope_returns_only_verified_reviews()
    {
        Review::factory()->create(['is_verified' => true]);
        Review::factory()->create(['is_verified' => false]);

        $verifiedReviews = Review::verified()->get();

        $this->assertEquals(1, $verifiedReviews->count());
        $this->assertTrue($verifiedReviews->first()->is_verified);
    }

    public function test_min_rating_scope_filters_by_minimum_rating()
    {
        Review::factory()->create(['rating' => 5.0]);
        Review::factory()->create(['rating' => 3.5]);
        Review::factory()->create(['rating' => 2.0]);

        $highRatedReviews = Review::minRating(4.0)->get();

        $this->assertEquals(1, $highRatedReviews->count());
        $this->assertEquals(5.0, $highRatedReviews->first()->rating);
    }

    public function test_search_scope_searches_in_title_and_comment()
    {
        Review::factory()->create([
            'title' => 'Excelente experiencia',
            'comment' => 'Muy recomendable'
        ]);
        
        Review::factory()->create([
            'title' => 'Lugar regular',
            'comment' => 'No me gustó mucho'
        ]);

        $searchResults = Review::search('excelente')->get();

        $this->assertEquals(1, $searchResults->count());
        $this->assertStringContainsString('Excelente', $searchResults->first()->title);
    }

    public function test_status_name_attribute_returns_spanish_name()
    {
        $review = Review::factory()->create(['status' => Review::STATUS_APPROVED]);

        $this->assertEquals('Aprobada', $review->status_name);
    }

    public function test_travel_type_name_attribute_returns_spanish_name()
    {
        $review = Review::factory()->create(['travel_type' => Review::TRAVEL_FAMILY]);

        $this->assertEquals('En familia', $review->travel_type_name);
    }

    public function test_stars_attribute_returns_star_representation()
    {
        $review = Review::factory()->create(['rating' => 4.5]);

        $stars = $review->stars;
        
        // Should have 4 full stars and 1 half star
        $this->assertStringContainsString('★', $stars);
        // Count full stars (★) and half/empty stars (☆)
        $fullStarCount = substr_count($stars, '★');
        $halfEmptyStarCount = substr_count($stars, '☆');
        
        $this->assertEquals(4, $fullStarCount);
        $this->assertEquals(1, $halfEmptyStarCount);
        $this->assertEquals(5, $fullStarCount + $halfEmptyStarCount);
    }

    public function test_helpfulness_percentage_calculates_correctly()
    {
        $review = Review::factory()->create([
            'helpful_votes' => 8,
            'not_helpful_votes' => 2
        ]);

        $this->assertEquals(80.0, $review->helpfulness_percentage);
    }

    public function test_helpfulness_percentage_returns_zero_when_no_votes()
    {
        $review = Review::factory()->create([
            'helpful_votes' => 0,
            'not_helpful_votes' => 0
        ]);

        $this->assertEquals(0, $review->helpfulness_percentage);
    }

    public function test_is_recent_attribute_detects_recent_reviews()
    {
        $recentReview = Review::factory()->create(['created_at' => now()->subDays(15)]);
        $oldReview = Review::factory()->create(['created_at' => now()->subDays(45)]);

        $this->assertTrue($recentReview->is_recent);
        $this->assertFalse($oldReview->is_recent);
    }

    public function test_is_detailed_attribute_detects_long_comments()
    {
        $detailedReview = Review::factory()->create(['comment' => str_repeat('a', 250)]);
        $shortReview = Review::factory()->create(['comment' => 'Short comment']);

        $this->assertTrue($detailedReview->is_detailed);
        $this->assertFalse($shortReview->is_detailed);
    }

    public function test_summary_attribute_truncates_long_comments()
    {
        $longComment = str_repeat('a', 200);
        $review = Review::factory()->create(['comment' => $longComment]);

        $summary = $review->summary;
        
        $this->assertLessThanOrEqual(150, strlen($summary));
        $this->assertStringEndsWith('...', $summary);
    }

    public function test_summary_attribute_returns_full_short_comments()
    {
        $shortComment = 'This is a short comment';
        $review = Review::factory()->create(['comment' => $shortComment]);

        $this->assertEquals($shortComment, $review->summary);
    }

    public function test_approve_method_updates_status_and_moderation_fields()
    {
        $moderator = User::factory()->create(['role' => 'admin']);
        $review = Review::factory()->create(['status' => Review::STATUS_PENDING]);

        $review->approve($moderator->id);

        $this->assertEquals(Review::STATUS_APPROVED, $review->status);
        $this->assertEquals($moderator->id, $review->moderated_by);
        $this->assertNotNull($review->moderated_at);
    }

    public function test_reject_method_updates_status_and_adds_reason()
    {
        $moderator = User::factory()->create(['role' => 'admin']);
        $review = Review::factory()->create(['status' => Review::STATUS_PENDING]);
        $reason = 'Contenido inapropiado';

        $review->reject($reason, $moderator->id);

        $this->assertEquals(Review::STATUS_REJECTED, $review->status);
        $this->assertEquals($reason, $review->moderation_notes);
        $this->assertEquals($moderator->id, $review->moderated_by);
        $this->assertNotNull($review->moderated_at);
    }

    public function test_hide_method_updates_status()
    {
        $review = Review::factory()->create(['status' => Review::STATUS_APPROVED]);
        $reason = 'Reportado por usuarios';

        $review->hide($reason);

        $this->assertEquals(Review::STATUS_HIDDEN, $review->status);
        $this->assertEquals($reason, $review->moderation_notes);
    }

    public function test_verify_method_marks_as_verified()
    {
        $review = Review::factory()->create(['is_verified' => false]);

        $review->verify();

        $this->assertTrue($review->is_verified);
    }

    public function test_vote_helpful_increments_helpful_votes()
    {
        $review = Review::factory()->create(['helpful_votes' => 5]);

        $review->voteHelpful();

        $this->assertEquals(6, $review->helpful_votes);
    }

    public function test_vote_not_helpful_increments_not_helpful_votes()
    {
        $review = Review::factory()->create(['not_helpful_votes' => 3]);

        $review->voteNotHelpful();

        $this->assertEquals(4, $review->not_helpful_votes);
    }

    public function test_can_be_edited_by_returns_true_for_owner_of_pending_review()
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'status' => Review::STATUS_PENDING
        ]);

        $this->assertTrue($review->canBeEditedBy($this->user));
    }

    public function test_can_be_edited_by_returns_true_for_owner_within_24_hours()
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'status' => Review::STATUS_APPROVED,
            'created_at' => now()->subHours(12)
        ]);

        $this->assertTrue($review->canBeEditedBy($this->user));
    }

    public function test_can_be_edited_by_returns_false_for_owner_after_24_hours()
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'status' => Review::STATUS_APPROVED,
            'created_at' => now()->subHours(30)
        ]);

        $this->assertFalse($review->canBeEditedBy($this->user));
    }

    public function test_can_be_edited_by_returns_false_for_non_owner()
    {
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'status' => Review::STATUS_PENDING
        ]);

        $this->assertFalse($review->canBeEditedBy($this->user));
    }

    public function test_can_be_moderated_returns_true_for_pending_and_approved()
    {
        $pendingReview = Review::factory()->create(['status' => Review::STATUS_PENDING]);
        $approvedReview = Review::factory()->create(['status' => Review::STATUS_APPROVED]);

        $this->assertTrue($pendingReview->canBeModerated());
        $this->assertTrue($approvedReview->canBeModerated());
    }

    public function test_can_be_moderated_returns_false_for_rejected_and_hidden()
    {
        $rejectedReview = Review::factory()->create(['status' => Review::STATUS_REJECTED]);
        $hiddenReview = Review::factory()->create(['status' => Review::STATUS_HIDDEN]);

        $this->assertFalse($rejectedReview->canBeModerated());
        $this->assertFalse($hiddenReview->canBeModerated());
    }

    public function test_get_detailed_ratings_formatted_returns_formatted_array()
    {
        $review = Review::factory()->create([
            'detailed_ratings' => [
                'service' => 5,
                'value' => 4,
                'location' => 3
            ]
        ]);

        $formatted = $review->getDetailedRatingsFormatted();

        $this->assertIsArray($formatted);
        $this->assertEquals(3, count($formatted));
        $this->assertEquals('Servicio', $formatted[0]['aspect']);
        $this->assertEquals(5, $formatted[0]['rating']);
        $this->assertStringContainsString('★', $formatted[0]['stars']);
    }

    public function test_get_moderation_summary_returns_summary_array()
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'reviewable_type' => Attraction::class,
            'reviewable_id' => $this->attraction->id,
            'rating' => 4.5,
            'title' => 'Great place',
            'comment' => 'Really enjoyed my visit here.',
            'language' => 'en',
            'is_verified' => true
        ]);

        $summary = $review->getModerationSummary();

        $this->assertIsArray($summary);
        $this->assertEquals($review->id, $summary['id']);
        $this->assertEquals($this->user->name, $summary['user']);
        $this->assertEquals(4.5, $summary['rating']);
        $this->assertEquals('Great place', $summary['title']);
        $this->assertEquals('en', $summary['language']);
        $this->assertTrue($summary['is_verified']);
        $this->assertEquals('Attraction', $summary['reviewable_type']);
    }
}