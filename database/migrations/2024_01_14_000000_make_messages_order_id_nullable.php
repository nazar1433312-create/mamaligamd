<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE messages ALTER COLUMN order_id DROP NOT NULL');

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['sender_id', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['messages_sender_id_recipient_id_index']);
        });

        DB::statement('ALTER TABLE messages ALTER COLUMN order_id SET NOT NULL');
    }
};
