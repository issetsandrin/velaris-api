<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false)->index();
        });

        Schema::table('product_sizes', function (Blueprint $table): void {
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('promo_price', 10, 2)->nullable();
            $table->dateTime('promo_starts_at')->nullable();
            $table->dateTime('promo_ends_at')->nullable();
        });

        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('description')->nullable();
            $table->string('type', 20); // percent | fixed | free_shipping
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_subtotal', 10, 2)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->nullable()->after('address_id')->constrained()->nullOnDelete();
            $table->string('coupon_code', 40)->nullable()->after('coupon_id');
            $table->decimal('coupon_discount', 10, 2)->default(0)->after('discount');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('list_price', 10, 2)->nullable()->after('unit_price');
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->string('key', 60)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('list_price'));
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['coupon_code', 'coupon_discount']);
        });
        Schema::dropIfExists('coupons');
        Schema::table('product_sizes', fn (Blueprint $table) => $table->dropColumn(['stock', 'promo_price', 'promo_starts_at', 'promo_ends_at']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_admin'));
    }
};
