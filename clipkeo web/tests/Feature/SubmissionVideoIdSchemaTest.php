<?php

namespace Tests\Feature;

use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionVideoIdSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_video_id_can_be_queried(): void
    {
        $submission = Submission::query()
            ->where('video_id', '7710190825379990790')
            ->first();

        $this->assertNull($submission);
    }
}
