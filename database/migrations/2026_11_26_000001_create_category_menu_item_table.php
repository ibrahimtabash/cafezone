<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يسمح بعرض منتج في أقسام إضافية (مثل القسم الشتوي) مع قسمه الأصلي
        Schema::create('category_menu_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->unique(['category_id', 'menu_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_menu_item');
    }
};
