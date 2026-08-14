<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('source_health_check_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 64);
            $table->string('severity', 24);
            $table->string('status', 24)->default('open')->index();
            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('first_detected_at');
            $table->timestamp('last_detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->string('notification_status', 24)->default('not_configured');
            $table->text('notification_error')->nullable();
            $table->text('message');
            $table->timestamps();

            $table->unique(['source_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_alerts');
    }
};
