<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cloud_variable_id')->constrained()->cascadeOnDelete();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->string('operator');
            $table->string('value');
            $table->string('action_type');
            $table->json('action_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_triggered_at')->nullable();
            $table->unsignedInteger('cooldown_seconds')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triggers');
    }
};
