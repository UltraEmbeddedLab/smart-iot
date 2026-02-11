<?php declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Enums\DeviceType;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Add Device')]
final class Create extends Component
{
    public int $step = 1;

    public string $type = '';

    public string $name = '';

    public ?string $deviceId = null;

    public ?string $secretKey = null;

    public function selectType(string $type): void
    {
        $this->type = $type;
        $this->step = 2;
    }

    public function goBack(): void
    {
        if ($this->step === 2) {
            $this->step = 1;
        }
    }

    public function createDevice(): void
    {
        $this->validate([
            'type' => ['required', 'string'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $result = Device::createWithCredentials([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'type' => DeviceType::from($this->type),
        ]);

        $this->deviceId = $result['device']->device_id;
        $this->secretKey = $result['secret_key'];
        $this->step = 3;
    }
}
