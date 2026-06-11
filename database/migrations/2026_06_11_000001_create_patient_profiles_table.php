<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();

            // Nếu bệnh nhân có tài khoản trong bảng users thì liên kết.
            // Bệnh nhân offline có thể chưa có tài khoản nên để nullable.
            $table->unsignedBigInteger('user_id')->nullable();

            // Thông tin cá nhân bệnh nhân
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('address')->nullable();

            // Thông tin bổ sung nếu cần dùng sau này
            $table->string('identity_number', 50)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();

            // Nguồn tạo hồ sơ: online/offline/imported
            $table->string('source', 20)->default('offline');

            // Hồ sơ tạm cho bệnh nhân khám nhanh, chưa cần đầy đủ thông tin
            $table->boolean('is_temporary')->default(false);

            // Lần khám gần nhất
            $table->dateTime('last_visit_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('user_id');
            $table->index('phone');
            $table->index('full_name');
            $table->index('source');
            $table->index('last_visit_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_profiles');
    }
}