<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sources')
            ->where('key', 'alcobaca.prefeitura.dados-abertos')
            ->update([
                'key' => 'alcobaca.prefeitura.payroll',
                'name' => 'Prefeitura de Alcobaça — Folha de Pagamento',
                'updated_at' => now(),
            ]);

        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('source_record_id')->constrained('raw_source_records')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('external_id');
            $table->string('municipality_ibge_code', 7)->index();
            $table->string('public_slug')->unique();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->string('type', 48)->nullable()->index();
            $table->string('responsible_name')->nullable();
            $table->string('parent_source_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->longText('competencies')->nullable();
            $table->string('opening_hours')->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->boolean('is_current')->default(true)->index();
            $table->timestamps();

            $table->unique(['source_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');

        DB::table('sources')
            ->where('key', 'alcobaca.prefeitura.payroll')
            ->update([
                'key' => 'alcobaca.prefeitura.dados-abertos',
                'name' => 'Prefeitura de Alcobaça — Dados Abertos',
                'updated_at' => now(),
            ]);
    }
};
