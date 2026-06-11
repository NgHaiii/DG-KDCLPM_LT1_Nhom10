<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDentalChartsTable extends Migration
{
    public function up()
    {
        Schema::create('dental_charts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('patient_profile_id');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();

            $table->string('tooth_number', 5);
            $table->enum('status', [
                'healthy',
                'caries',
                'filled',
                'crown',
                'root_canal',
                'missing'
            ])->default('healthy');

            $table->text('note')->nullable();

            $table->timestamps();

            $table->foreign('patient_profile_id')
                ->references('id')
                ->on('patient_profiles')
                ->onDelete('cascade');

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->nullOnDelete();

            $table->foreign('doctor_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();

            $table->unique(['patient_profile_id', 'tooth_number']);
            $table->index('patient_profile_id');
            $table->index('appointment_id');
            $table->index('doctor_id');
            $table->index('tooth_number');
            $table->index('status');
        });

        Schema::create('dental_chart_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('patient_profile_id');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('dental_chart_id')->nullable();

            $table->string('action_type', 50)->default('update_tooth');
            $table->string('tooth_number', 5)->nullable();

            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();

            $table->text('old_note')->nullable();
            $table->text('new_note')->nullable();

            $table->timestamps();

            $table->foreign('patient_profile_id')
                ->references('id')
                ->on('patient_profiles')
                ->onDelete('cascade');

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->nullOnDelete();

            $table->foreign('doctor_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();

            $table->foreign('dental_chart_id')
                ->references('id')
                ->on('dental_charts')
                ->nullOnDelete();

            $table->index('patient_profile_id');
            $table->index('appointment_id');
            $table->index('doctor_id');
            $table->index('dental_chart_id');
            $table->index('tooth_number');
            $table->index('action_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dental_chart_histories');
        Schema::dropIfExists('dental_charts');
    }
}