<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('unit', 50)->default('viên');

            $table->decimal('sale_price', 15, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_quantity')->default(0);

            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('generic_name');
            $table->index('is_active');
            $table->index('stock_quantity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicines');
    }
};