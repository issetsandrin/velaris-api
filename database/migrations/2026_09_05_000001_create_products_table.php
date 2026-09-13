<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('collection', 20)->index();
            $table->string('family', 20)->index();
            $table->string('tagline');
            $table->text('description');
            $table->string('notes_top');
            $table->string('notes_heart');
            $table->string('notes_base');
            $table->string('wax', 9);
            $table->boolean('featured')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_sizes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('key', 1);
            $table->string('label', 30);
            $table->string('weight', 20);
            $table->unsignedSmallInteger('burn_hours');
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('products');
    }
};
