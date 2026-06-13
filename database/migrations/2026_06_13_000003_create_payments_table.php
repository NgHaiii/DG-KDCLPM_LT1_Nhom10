<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('payment_code')->unique();

            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('patient_profile_id')->nullable();
            $table->unsignedBigInteger('cashier_id')->nullable();

            $table->decimal('amount', 15, 2)->default(0);

            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'card',
                'e_wallet',
                'other'
            ])->default('cash');

            $table->enum('status', [
                'success',
                'cancelled'
            ])->default('success');

            $table->string('payer_name')->nullable();
            $table->string('payer_phone', 30)->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('note')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->cascadeOnDelete();

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->nullOnDelete();

            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('patient_profile_id')
                ->references('id')
                ->on('patient_profiles')
                ->nullOnDelete();

            $table->foreign('cashier_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('invoice_id');
            $table->index('appointment_id');
            $table->index('patient_id');
            $table->index('patient_profile_id');
            $table->index('cashier_id');
            $table->index('payment_method');
            $table->index('status');
            $table->index('paid_at');
            $table->index(['status', 'paid_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};