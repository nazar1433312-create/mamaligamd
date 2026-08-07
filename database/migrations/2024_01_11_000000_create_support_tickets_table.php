<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_contact')->nullable(); // phone, telegram @username or email
            $table->string('subject');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->unsignedBigInteger('telegram_notify_chat_id')->nullable();
            $table->unsignedBigInteger('telegram_notify_message_id')->nullable();
            $table->timestamps();

            $table->index(['telegram_notify_chat_id', 'telegram_notify_message_id']);
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->enum('sender_type', ['user', 'admin']);
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
    }
};
