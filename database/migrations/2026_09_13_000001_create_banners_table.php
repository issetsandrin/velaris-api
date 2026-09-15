<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table): void {
            $table->id();
            $table->string('image_path', 200);
            $table->string('title', 120)->nullable();
            $table->string('text', 300)->nullable();
            $table->string('button_label', 40)->nullable();
            $table->string('button_link', 200)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
