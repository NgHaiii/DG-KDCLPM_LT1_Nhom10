<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_configs', function (Blueprint $table) {
            $table->id();

            $table->string('name')->default('Cấu hình lương mặc định');

            $table->decimal('base_hourly_rate', 15, 2)->default(0);

            $table->decimal('degree_bachelor_coefficient', 5, 2)->default(1.30);
            $table->decimal('degree_master_coefficient', 5, 2)->default(1.50);
            $table->decimal('degree_doctor_coefficient', 5, 2)->default(1.70);
            $table->decimal('degree_associate_professor_coefficient', 5, 2)->default(2.00);
            $table->decimal('degree_professor_coefficient', 5, 2)->default(2.50);
            $table->decimal('degree_default_coefficient', 5, 2)->default(1.30);

            $table->decimal('weekday_office_coefficient', 5, 2)->default(1.00);
            $table->decimal('weekday_overtime_coefficient', 5, 2)->default(1.20);
            $table->decimal('weekend_coefficient', 5, 2)->default(1.50);

            $table->time('office_start_time')->default('08:00:00');
            $table->time('office_end_time')->default('17:00:00');

            $table->boolean('exclude_lunch_break')->default(true);
            $table->time('lunch_start_time')->default('11:30:00');
            $table->time('lunch_end_time')->default('12:30:00');

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_configs');
    }
};