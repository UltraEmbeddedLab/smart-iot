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
        Schema::create('firmware', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thing_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->longText('code');
            $table->string('device_type');
            $table->json('parameters')->nullable();
            $table->timestamps();

            $table->index('thing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmware');
    }
};
