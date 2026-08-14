<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->restrictOnDelete();
            $table->foreignId('source_record_id')->constrained('raw_source_records')->restrictOnDelete();
            $table->string('external_id');
            $table->string('public_slug')->unique();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->string('tax_identifier')->nullable();
            $table->string('tax_identifier_type', 4)->nullable();
            $table->timestamps();
            $table->unique(['source_id', 'external_id']);
        });
        Schema::create('procurements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->restrictOnDelete();
            $table->foreignId('source_record_id')->constrained('raw_source_records')->restrictOnDelete();
            $table->string('external_id');
            $table->string('public_slug')->unique();
            $table->string('number')->nullable();
            $table->string('process_number')->nullable();
            $table->text('object')->nullable();
            $table->string('modality')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('status')->nullable();
            $table->date('publication_date')->nullable();
            $table->date('event_date')->nullable();
            $table->bigInteger('estimated_cents')->nullable();
            $table->bigInteger('approved_cents')->nullable();
            $table->string('pncp_url')->nullable();
            $table->json('situations')->nullable();
            $table->timestamps();
            $table->unique(['source_id', 'external_id']);
        });
        Schema::create('contracts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->restrictOnDelete();
            $table->foreignId('source_record_id')->constrained('raw_source_records')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('procurement_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id');
            $table->string('public_slug')->unique();
            $table->string('number')->nullable();
            $table->string('process_number')->nullable();
            $table->text('object')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_external_id')->nullable()->index();
            $table->string('supplier_tax_identifier')->nullable();
            $table->bigInteger('value_cents')->nullable();
            $table->string('status')->nullable();
            $table->date('signature_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('pncp_url')->nullable();
            $table->timestamps();
            $table->unique(['source_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('procurements');
        Schema::dropIfExists('suppliers');
    }
};
