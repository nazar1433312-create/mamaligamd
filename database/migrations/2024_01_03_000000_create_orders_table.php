<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();

            $table->string('title');
            $table->text('description');
            $table->string('address')->nullable();
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();

            // open: accepting offers; in_progress: executor selected & (optionally) paid;
            // completed: work accepted by customer; cancelled: closed with no deal; disputed: under admin review
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled', 'disputed'])
                ->default('open');

            $table->foreignId('accepted_offer_id')->nullable();
            $table->decimal('commission_percent', 5, 2)->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
