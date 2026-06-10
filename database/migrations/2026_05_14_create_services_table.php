<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('type')->nullable();

            // Chuyên khoa bắt buộc để tìm đúng bác sĩ
            $table->string('required_specialization')->nullable();

            // Phòng mặc định của dịch vụ
            // Khi bác sĩ xác nhận lịch online, hệ thống lấy phòng này gán vào appointment
            $table->unsignedBigInteger('room_id')->nullable();

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // Thời lượng dịch vụ
            $table->integer('slots_required')->default(1);
            $table->integer('duration_minutes')->default(30);
            $table->integer('actual_duration')->nullable();

            $table->timestamps();

            $table->foreign('room_id')
                ->references('id')
                ->on('rooms')
                ->nullOnDelete();

            $table->index('type');
            $table->index('required_specialization');
            $table->index('room_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
}