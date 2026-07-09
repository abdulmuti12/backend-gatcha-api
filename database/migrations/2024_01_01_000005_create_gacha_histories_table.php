<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gacha_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gacha_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gacha_item_id')->constrained()->cascadeOnDelete();
            // Disimpan snapshot supaya histori tetap valid walau drop rate/cost berubah di kemudian hari
            $table->unsignedInteger('coins_spent');
            $table->unsignedInteger('drop_rate_bp_snapshot');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gacha_histories');
    }
};
