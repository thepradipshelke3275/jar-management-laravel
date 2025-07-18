<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->date('date')->useCurrent();
            $table->unsignedInteger('total_amount');
            $table->unsignedInteger('paid_amount');
            $table->unsignedInteger('unpaid_amount');
            $table->unsignedInteger('discount')->default(0);
            $table->enum('status', array_column(OrderStatus::cases(), "value"));
            $table->boolean('has_returned')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
