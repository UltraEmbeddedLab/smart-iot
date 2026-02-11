<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloud_variables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thing_id')->constrained()->cascadeOnDelete();
            $table->uuid()->unique();
            $table->string('name');
            $table->string('variable_name');
            $table->string('type');
            $table->string('permission');
            $table->string('update_policy');
            $table->decimal('update_parameter', 10)->nullable();
            $table->decimal('min_value', 10)->nullable();
            $table->decimal('max_value', 10)->nullable();
            $table->json('last_value')->nullable();
            $table->timestamp('value_updated_at')->nullable();
            $table->boolean('persist')->default(false);
            $table->timestamps();

            $table->unique(['thing_id', 'variable_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_variables');
    }
};
