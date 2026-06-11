<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('service_id')->nullable();

            $table->text('chief_complaint')->nullable();      // Lý do khám / triệu chứng chính
            $table->text('diagnosis')->nullable();            // Chẩn đoán
            $table->text('treatment_plan')->nullable();       // Hướng điều trị
            $table->text('prescription')->nullable();         // Đơn thuốc nếu có
            $table->text('doctor_notes')->nullable();         // Ghi chú bác sĩ
            $table->date('follow_up_date')->nullable();       // Ngày tái khám nếu có

            $table->timestamps();

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->onDelete('cascade');

            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('doctor_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->nullOnDelete();

            $table->unique('appointment_id');
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('service_id');
            $table->index('follow_up_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_records');
    }
}