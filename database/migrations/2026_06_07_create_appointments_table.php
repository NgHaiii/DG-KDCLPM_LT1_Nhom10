<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Bệnh nhân, bác sĩ, dịch vụ, phòng khám
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('room_id')->nullable();

            // Thời gian khám
            $table->dateTime('appointment_date');
            $table->integer('slots_used')->default(1);
            $table->integer('duration_minutes')->default(30);

            $table->enum('status', [
                'pending',
                'confirmed',
                'checked_in',
                'waiting',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');

            // Mốc xử lý
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();

            // Ghi chú từ bệnh nhân / bác sĩ / lễ tân
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys
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
                ->onDelete('cascade');

            $table->foreign('room_id')
                ->references('id')
                ->on('rooms')
                ->nullOnDelete();

            // Indexes
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('service_id');
            $table->index('room_id');
            $table->index('appointment_date');
            $table->index('status');
            $table->index(['room_id', 'appointment_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}