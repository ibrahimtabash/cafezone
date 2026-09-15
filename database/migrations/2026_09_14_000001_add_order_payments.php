<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('qr_code')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->json('payment_details')->nullable();
            $table->string('payment_receipt')->nullable();
            $table->string('payment_status')->default('unpaid')->index();
            $table->text('payment_note')->nullable();
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->foreignId('payment_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tracking_token', 64)->nullable()->unique();
            $table->uuid('checkout_key')->nullable()->unique();
            $table->timestamp('opened_at')->nullable()->index();
        });
        Schema::create('order_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('payment_status');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_updates');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropConstrainedForeignId('payment_confirmed_by');
            $table->dropColumn(['payment_details', 'payment_receipt', 'payment_status', 'payment_note', 'payment_confirmed_at', 'tracking_token', 'checkout_key', 'opened_at']);
        });
        Schema::dropIfExists('payment_methods');
    }
};
