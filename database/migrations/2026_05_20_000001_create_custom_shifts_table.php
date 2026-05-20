<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // "Sáng", "Chiều", "Tối"
            $table->integer('start_hour');      // 8, 14, 22
            $table->integer('start_minute');    // 0
            $table->integer('end_hour');        // 17, 22, 6
            $table->integer('end_minute');      // 0
            $table->string('description')->nullable();
            $table->boolean('is_for_doctor')->default(1);      // Bác sĩ dùng?
            $table->boolean('is_for_employee')->default(1);    // Nhân viên dùng?
            $table->boolean('is_active')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();  // Admin ID
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_shifts');
    }
};