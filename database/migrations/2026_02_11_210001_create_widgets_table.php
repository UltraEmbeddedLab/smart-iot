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
        Schema::create('widgets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->uuid()->unique();
            $table->string('type');
            $table->string('name');
            $table->foreignId('cloud_variable_id')->nullable()->constrained()->nullOnDelete();
            $table->json('options')->nullable();
            $table->unsignedInteger('x')->default(0);
            $table->unsignedInteger('y')->default(0);
            $table->unsignedInteger('width')->default(1);
            $table->unsignedInteger('height')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
