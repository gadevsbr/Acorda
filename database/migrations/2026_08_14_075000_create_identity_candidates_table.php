<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('left_person_id')->constrained('people')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('right_person_id')->constrained('people')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('reason', 64);
            $table->json('evidence');
            $table->string('status', 24)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['left_person_id', 'right_person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_candidates');
    }
};
