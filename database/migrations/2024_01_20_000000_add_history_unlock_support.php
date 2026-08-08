<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('history_paid_at')->nullable()->after('is_verified');
        });

        // Payments can now represent a user-level purchase (no order involved),
        // so order_id has to become optional and we need to know which user paid.
        DB::statement('ALTER TABLE payments ALTER COLUMN order_id DROP NOT NULL');

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('order_id')->constrained('users')->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_type_check');
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_type_check CHECK (type IN ('charge','payout','refund','platform_fee','history_unlock'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_type_check');
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_type_check CHECK (type IN ('charge','payout','refund','platform_fee'))");

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        DB::statement('ALTER TABLE payments ALTER COLUMN order_id SET NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('history_paid_at');
        });
    }
};
