<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('platform_fee_paid_at')->nullable()->after('paid_at');
        });

        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_type_check');
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_type_check CHECK (type IN ('charge','payout','refund','platform_fee'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_type_check');
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_type_check CHECK (type IN ('charge','payout','refund'))");

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('platform_fee_paid_at');
        });
    }
};
