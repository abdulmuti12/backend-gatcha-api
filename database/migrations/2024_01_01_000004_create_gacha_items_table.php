<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gacha_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gacha_event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('rarity', ['common', 'rare', 'legendary'])->default('common');
            // Drop rate disimpan dalam basis poin (1 unit = 0.01%) agar presisi,
            // contoh: 100 = 1.00%, 8000 = 80.00%. Divalidasi total = 10000 per event.
            $table->unsignedInteger('drop_rate_bp');
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gacha_items');
    }
};
