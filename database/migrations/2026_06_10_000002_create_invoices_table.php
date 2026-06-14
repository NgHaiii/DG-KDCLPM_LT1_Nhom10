<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Mã hóa đơn
            $table->string('invoice_code')->unique();

            // Liên kết nghiệp vụ
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('patient_profile_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();

            // Người tạo / thu ngân
            // created_by giữ lại để tương thích code cũ
            // cashier_id dùng cho nghiệp vụ thu ngân mới
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cashier_id')->nullable();

            // Snapshot thông tin tại thời điểm tạo hóa đơn
            $table->string('patient_name')->nullable();
            $table->string('patient_phone', 30)->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('service_name')->nullable();
            $table->dateTime('appointment_date')->nullable();

            // Tiền dịch vụ
            // service_amount giữ lại để tương thích code cũ
            // service_price dùng cho nghiệp vụ mới
            $table->decimal('service_amount', 15, 2)->default(0);
            $table->decimal('service_price', 15, 2)->default(0);

            // Thuốc / chi phí phát sinh dạng JSON để rút gọn bảng
            // medicine_items: [{medicine_id, code, name, unit, quantity, unit_price, total}]
            // extra_items: [{name, quantity, unit_price, total, note}]
            $table->json('medicine_items')->nullable();
            $table->json('extra_items')->nullable();

            // Tổng hợp tiền
            $table->decimal('medicine_total', 15, 2)->default(0);
            $table->decimal('extra_amount', 15, 2)->default(0); // giữ lại cũ
            $table->decimal('extra_total', 15, 2)->default(0);  // dùng mới
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);

            // Phương thức thanh toán chính nếu thanh toán một lần
            // Chi tiết giao dịch vẫn nên lưu ở bảng payments
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'card',
                'momo',
                'e_wallet',
                'other'
            ])->nullable();

            // Trạng thái hóa đơn
            $table->enum('status', [
                'unpaid',
                'paid',
                'cancelled'
            ])->default('unpaid');

            // Mốc xử lý
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys
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

            $table->foreign('doctor_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('cashier_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Mỗi ca khám chỉ nên có một hóa đơn chính
            $table->unique('appointment_id');

            // Indexes
            $table->index('patient_id');
            $table->index('patient_profile_id');
            $table->index('doctor_id');
            $table->index('service_id');
            $table->index('created_by');
            $table->index('cashier_id');
            $table->index('status');
            $table->index('issued_at');
            $table->index('paid_at');
            $table->index('created_at');
            $table->index(['status', 'issued_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};