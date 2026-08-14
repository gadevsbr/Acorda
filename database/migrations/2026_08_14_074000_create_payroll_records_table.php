<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('source_record_id')->unique()->constrained('raw_source_records')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('employment_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('supersedes_id')->nullable()->constrained('payroll_records')->cascadeOnUpdate()->nullOnDelete();
            $table->string('registration');
            $table->string('employee_name');
            $table->unsignedSmallInteger('reference_year');
            $table->unsignedTinyInteger('reference_month');
            $table->string('calculation_type');
            $table->bigInteger('gross_cents');
            $table->bigInteger('deductions_cents');
            $table->bigInteger('net_cents');
            $table->string('position_name')->nullable();
            $table->string('weekly_workload')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('workplace')->nullable();
            $table->date('admission_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->boolean('is_latest')->default(true)->index();
            $table->timestamps();

            $table->index(['source_id', 'reference_year', 'reference_month']);
            $table->index(['registration', 'reference_year', 'reference_month'], 'payroll_registration_period_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
