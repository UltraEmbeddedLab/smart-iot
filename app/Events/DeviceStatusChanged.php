<?php declare(strict_types=1);

namespace App\Events;

use App\Enums\DeviceStatus;
use App\Models\Device;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class DeviceStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Device $device,
        public DeviceStatus $oldStatus,
        public DeviceStatus $newStatus,
    ) {}
}
