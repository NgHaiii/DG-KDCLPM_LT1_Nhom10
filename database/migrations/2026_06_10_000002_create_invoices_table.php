<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_code')->unique();

            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->decimal('service_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('extra_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'card',
                'momo',
                'other'
            ])->nullable();

            $table->enum('status', [
                'unpaid',
                'paid',
                'cancelled'
            ])->default('unpaid');

            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->onDelete('cascade');

            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique('appointment_id');
            $table->index('patient_id');
            $table->index('service_id');
            $table->index('status');
            $table->index('paid_at');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}