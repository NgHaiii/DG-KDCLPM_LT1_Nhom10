<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clinical_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('patient_profile_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();

            $table->string('image_type', 50)->default('xray');
            $table->string('title')->nullable();

            $table->string('file_path', 500);
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->date('taken_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->onDelete('cascade');

            $table->foreign('patient_profile_id')
                ->references('id')
                ->on('patient_profiles')
                ->nullOnDelete();

            $table->foreign('doctor_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();

            $table->index('appointment_id');
            $table->index('patient_profile_id');
            $table->index('doctor_id');
            $table->index('image_type');
            $table->index('taken_date');
            $table->index(['patient_profile_id', 'taken_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('clinical_images');
    }
};