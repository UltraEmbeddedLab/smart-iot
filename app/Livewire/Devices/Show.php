<?php declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Models\Device;
use Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Device Details')]
final class Show extends Component
{
    public Device $device;

    public function mount(Device $device): void
    {
        abort_unless($device->user_id === Auth::id(), 403);

        $this->device = $device;
    }

    public function regenerateSecretKey(): void
    {
        $plainKey = $this->device->generateSecretKey();

        $this->dispatch('secret-key-regenerated', key: $plainKey);

        Flux::toast(heading: 'Secret key regenerated', text: 'Make sure to save it — it won\'t be shown again.', variant: 'warning');
    }

    public function deleteDevice(): void
    {
        $this->device->delete();

        Flux::toast(heading: 'Device deleted', text: 'The device has been removed.', variant: 'success');

        $this->redirect(route('devices.index'), navigate: true);
    }
}
