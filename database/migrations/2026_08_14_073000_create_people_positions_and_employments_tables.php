<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('source_record_id')->constrained('raw_source_records')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('external_id');
            $table->string('public_slug')->unique();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->string('municipality_ibge_code', 7)->index();
            $table->timestamps();

            $table->unique(['source_id', 'external_id']);
        });

        Schema::create('positions', function (Blueprint $table): void {
            $table->id();
            $table->string('municipality_ibge_code', 7)->index();
            $table->string('name');
            $table->string('normalized_name');
            $table->timestamps();

            $table->unique(['municipality_ibge_code', 'normalized_name']);
        });

        Schema::create('employments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('source_record_id')->constrained('raw_source_records')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('person_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('registration');
            $table->date('admission_date')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('normalized_cost_center')->nullable()->index();
            $table->string('employment_regime');
            $table->string('monthly_workload')->nullable();
            $table->boolean('is_current')->default(true)->index();
            $table->timestamp('last_seen_at');
            $table->timestamp('ended_observed_at')->nullable();
            $table->timestamps();

            $table->unique(['source_id', 'registration']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employments');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('people');
    }
};
