<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->string('name');
            $table->string('code')->unique();
            $table->unsignedInteger('capacity')->default(4);
            $table->boolean('is_active')->default(true);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type')->default('delivery')->after('order_number')->index();
            $table->foreignId('dining_table_id')->nullable()->after('order_type')->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable()->change();
            $table->string('customer_phone')->nullable()->change();
            $table->foreignId('delivery_area_id')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dining_table_id');
            $table->dropColumn('order_type');
        });

        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['name', 'code', 'capacity', 'is_active']);
        });
    }
};
