<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');          // Bệnh nhân (từ users table)
            $table->unsignedBigInteger('doctor_id');           // Bác sĩ (từ employees)
            $table->unsignedBigInteger('service_id');          // Dịch vụ khám
            $table->dateTime('appointment_date');              // Ngày giờ khám
            $table->integer('slots_used');                     // Số slot sử dụng
            $table->integer('duration_minutes');               // Thời lượng (phút)
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();                 // Ghi chú từ bệnh nhân
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            
            // Indexes
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('service_id');
            $table->index('appointment_date');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};