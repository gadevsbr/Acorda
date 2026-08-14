<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('name');
            $table->string('entity');
            $table->string('municipality_ibge_code', 7)->nullable()->index();
            $table->text('base_url');
            $table->text('official_url');
            $table->string('status', 32)->default('not_integrated')->index();
            $table->boolean('enabled')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamp('last_successful_at')->nullable();
            $table->timestamps();
        });

        Schema::create('collector_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('collector', 120);
            $table->string('collector_version', 40);
            $table->string('status', 24)->default('running')->index();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->json('checkpoint_before')->nullable();
            $table->json('checkpoint_after')->nullable();
            $table->unsignedInteger('records_fetched')->default(0);
            $table->unsignedInteger('records_created')->default(0);
            $table->unsignedInteger('records_unchanged')->default(0);
            $table->unsignedInteger('records_invalid')->default(0);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('error_class')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'collector', 'started_at']);
        });

        Schema::create('collector_checkpoints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('collector', 120);
            $table->string('key', 120);
            $table->json('value');
            $table->timestamps();

            $table->unique(['source_id', 'collector', 'key']);
        });

        Schema::create('raw_source_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('collector_run_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id');
            $table->text('source_url');
            $table->timestamp('fetched_at');
            $table->timestamp('source_updated_at')->nullable();
            $table->json('payload');
            $table->char('checksum', 64);
            $table->string('content_type', 120)->nullable();
            $table->string('etag')->nullable();
            $table->string('last_modified')->nullable();
            $table->unsignedSmallInteger('http_status');
            $table->string('collector', 120);
            $table->string('collector_version', 40);
            $table->string('validation_status', 24)->default('pending')->index();
            $table->json('validation_errors')->nullable();
            $table->timestamps();

            $table->unique(['source_id', 'external_id', 'checksum'], 'raw_source_record_version_unique');
            $table->index(['source_id', 'external_id', 'fetched_at'], 'raw_source_record_lookup');
        });

        Schema::create('source_health_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('checked_at');
            $table->string('status', 32)->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->unsignedInteger('records_count')->nullable();
            $table->char('schema_checksum', 64)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_health_checks');
        Schema::dropIfExists('raw_source_records');
        Schema::dropIfExists('collector_checkpoints');
        Schema::dropIfExists('collector_runs');
        Schema::dropIfExists('sources');
    }
};
