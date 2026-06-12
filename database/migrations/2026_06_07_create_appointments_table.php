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

            // Bệnh nhân / hồ sơ bệnh nhân
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('patient_profile_id')->nullable();

            // Bác sĩ, dịch vụ, phòng khám
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('room_id')->nullable();

            // Nguồn lịch khám: bệnh nhân đặt online hoặc lễ tân tiếp nhận trực tiếp
            $table->enum('source', ['online', 'offline'])->default('online');

            // Lưu bản chụp thông tin bệnh nhân tại thời điểm đặt lịch / tiếp nhận
            // Ví dụ: họ tên, SĐT, email, ngày sinh, giới tính, địa chỉ
            $table->json('patient_snapshot')->nullable();

            // Thời gian khám
            $table->dateTime('appointment_date');
            $table->integer('slots_used')->default(1);
            $table->integer('duration_minutes')->default(30);

            // Số thứ tự trong ngày
            $table->integer('queue_number')->nullable();

            // Trạng thái lịch/lượt khám
            $table->enum('status', [
    'pending',
    'confirmed',
    'checked_in',
    'waiting',
    'in_progress',
    'completed',
    'cancelled',
    'missed',
])->default('pending');
            // Mốc xử lý
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Thời gian dự kiến và thực tế
            $table->dateTime('estimated_start_at')->nullable();
            $table->dateTime('estimated_end_at')->nullable();
            $table->integer('actual_duration_minutes')->nullable();

            // Cảnh báo trễ giờ / nguy cơ lấn lịch
            $table->boolean('delay_warning')->default(false);
            $table->text('delay_reason')->nullable();

            // Ghi chú từ bệnh nhân / bác sĩ / lễ tân
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('patient_profile_id')
                ->references('id')
                ->on('patient_profiles')
                ->nullOnDelete();

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
            $table->index('patient_profile_id');
            $table->index('doctor_id');
            $table->index('service_id');
            $table->index('room_id');
            $table->index('source');
            $table->index('appointment_date');
            $table->index('queue_number');
            $table->index('status');
            $table->index(['room_id', 'appointment_date']);
            $table->index(['doctor_id', 'appointment_date']);
            $table->index(['patient_profile_id', 'appointment_date']);
            $table->index(['status', 'appointment_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}