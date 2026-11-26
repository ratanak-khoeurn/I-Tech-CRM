<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('purpose', 30)->default('event'); // event, opportunity, survey, announcement, reunion, other
            // When the campaign promotes one specific event, registrations feed the board.
            $table->foreignId('alumni_event_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->json('audience_filters')->nullable();
            $table->enum('status', ['active', 'completed', 'archived'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });

        Schema::create('outreach_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outreach_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('stage', 30)->default('to_contact');
            $table->string('channel', 50)->nullable(); // last channel used on this campaign
            $table->text('note')->nullable();
            $table->dateTime('contacted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['outreach_campaign_id', 'student_id']);
            $table->index(['outreach_campaign_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_leads');
        Schema::dropIfExists('outreach_campaigns');
    }
};
