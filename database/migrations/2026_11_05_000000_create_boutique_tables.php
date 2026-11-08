<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('size', 40)->nullable();
            $table->string('color', 80)->nullable();
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->decimal('max_discount', 10, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('image_path')->nullable();
            $table->date('received_at');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['category_id', 'is_active', 'stock']);
            $table->index(['name', 'size']);
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->decimal('cost_total', 12, 2);
            $table->decimal('profit_total', 12, 2);
            $table->string('payment_method', 80)->nullable();
            $table->string('status', 20)->default('completed')->index();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('product_code');
            $table->string('product_name');
            $table->string('category_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_purchase_price', 10, 2);
            $table->decimal('unit_sale_price', 10, 2);
            $table->decimal('unit_discount', 10, 2)->default(0);
            $table->decimal('max_discount_snapshot', 10, 2);
            $table->decimal('line_total', 12, 2);
            $table->decimal('line_cost', 12, 2);
            $table->decimal('line_profit', 12, 2);
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('type', 30)->index();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->text('reason');
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });

        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('Mi tienda');
            $table->string('logo_path')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('currency_code', 3)->default('GTQ');
            $table->string('currency_symbol', 5)->default('Q');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('sale_details');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
