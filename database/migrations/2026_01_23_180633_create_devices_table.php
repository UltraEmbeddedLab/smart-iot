<?php declare(strict_types=1);

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
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
        Schema::create('devices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('device_id')->unique();
            $table->string('name');
            $table->string('type')->default(DeviceType::Generic->value);
            $table->string('secret_key');
            $table->string('status')->default(DeviceStatus::Provisioning->value);
            $table->timestamp('last_activity_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
