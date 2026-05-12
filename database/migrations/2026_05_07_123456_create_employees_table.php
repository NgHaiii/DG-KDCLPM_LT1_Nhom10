<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();                // Mã nhân viên/bác sĩ
            $table->string('name');
            $table->date('dob')->nullable();                   // Ngày sinh
            $table->string('gender')->nullable();              // Giới tính
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('address')->nullable();
            $table->string('workplace')->nullable();           // Nơi công tác
            $table->string('degree')->nullable();              // Bằng cấp
            $table->string('specialization')->nullable();      // Chuyên môn
            $table->string('position');
            $table->boolean('is_doctor')->default(false);
            $table->string('status')->nullable();              // Trạng thái (Hoạt động/Tạm nghỉ)
            $table->string('linkedUser')->nullable();          // Tài khoản liên kết
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}