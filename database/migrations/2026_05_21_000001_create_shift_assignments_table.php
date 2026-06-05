<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('work_date');
            $table->unsignedBigInteger('shift_id')->nullable(); // ✅ Thêm nullable()
            
            // ✅ Thêm các cột giờ
            $table->integer('start_hour')->nullable();
            $table->integer('start_minute')->default(0);
            $table->integer('end_hour')->nullable();
            $table->integer('end_minute')->default(0);
            
            $table->enum('assignment_type', ['work', 'duty'])->default('work');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('custom_shifts')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['employee_id', 'work_date', 'assignment_type']);
            $table->index(['work_date']);
            $table->index(['employee_id', 'work_date']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shift_assignments');
    }
}