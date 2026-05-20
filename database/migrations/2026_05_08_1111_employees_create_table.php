<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('address')->nullable();
            $table->string('workplace')->nullable();
            $table->string('degree')->nullable();
            $table->string('specialization')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_doctor')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};