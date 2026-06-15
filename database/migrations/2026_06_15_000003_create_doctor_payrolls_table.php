<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_payrolls', function (Blueprint $table) {
            $table->id();

            $table->string('payroll_code')->unique();

            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('salary_config_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->unsignedSmallInteger('salary_month');
            $table->unsignedSmallInteger('salary_year');

            $table->decimal('base_hourly_rate', 15, 2)->default(0);
            $table->decimal('doctor_coefficient', 5, 2)->default(1.30);

            $table->decimal('total_work_hours', 8, 2)->default(0);
            $table->decimal('total_patient_complexity_coefficient', 8, 2)->default(0);
            $table->decimal('total_converted_hours', 8, 2)->default(0);

            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('bonus_amount', 15, 2)->default(0);
            $table->decimal('deduction_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);

            $table->json('items')->nullable();

            $table->enum('status', [
                'draft',
                'approved',
                'paid',
                'cancelled'
            ])->default('draft');

            $table->timestamp('generated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('salary_config_id')->references('id')->on('salary_configs')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['doctor_id', 'salary_month', 'salary_year']);

            $table->index('doctor_id');
            $table->index('salary_config_id');
            $table->index(['salary_year', 'salary_month']);
            $table->index('status');
            $table->index('generated_at');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_payrolls');
    }
};