<?php

use App\Enums\ItemType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', array_column(ItemType::cases(), "value"))
                ->default(ItemType::BATLA->value);
            $table->string('image')->nullable();
            $table->integer('price');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('items');
    }
};
