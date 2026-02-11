<?php declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Models\Device;
use Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Device')]
final class Edit extends Component
{
    public Device $device;

    public string $name = '';

    public function mount(Device $device): void
    {
        abort_unless($device->user_id === Auth::id(), 403);

        $this->device = $device;
        $this->name = $device->name;
    }

    public function updateDevice(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $this->device->update(['name' => $this->name]);

        Flux::toast(text: 'The device name has been saved.', heading: 'Device updated', variant: 'success');

        $this->redirect(route('devices.show', $this->device), navigate: true);
    }
}
