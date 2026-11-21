<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('store_name')->nullable()->after('name');
            $table->string('store_phone', 30)->nullable()->after('store_name');
            $table->text('store_address')->nullable()->after('store_phone');
            $table->string('currency', 3)->default('PKR');
            $table->unsignedTinyInteger('reminder_after_days')->default(7);
            $table->boolean('sms_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'store_name',
                'store_phone',
                'store_address',
                'currency',
                'reminder_after_days',
                'sms_enabled',
            ]);
        });
    }
};
