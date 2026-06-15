<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_case_complexities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('patient_profile_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->unsignedSmallInteger('salary_month');
            $table->unsignedSmallInteger('salary_year');
            $table->date('case_date');

            // Hệ số ca bệnh phức tạp: 0.10 - 0.50
            $table->decimal('complexity_coefficient', 4, 2)->default(0.10);

            $table->string('case_title')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('doctor_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->nullOnDelete();

            $table->foreign('patient_profile_id')
                ->references('id')
                ->on('patient_profiles')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Đặt tên index ngắn để tránh lỗi MySQL identifier too long
            $table->index('doctor_id', 'dcc_doctor_idx');
            $table->index('appointment_id', 'dcc_appointment_idx');
            $table->index('patient_profile_id', 'dcc_profile_idx');
            $table->index(['salary_year', 'salary_month'], 'dcc_year_month_idx');
            $table->index(['doctor_id', 'salary_year', 'salary_month'], 'dcc_doctor_month_idx');
            $table->index('case_date', 'dcc_case_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_case_complexities');
    }
};