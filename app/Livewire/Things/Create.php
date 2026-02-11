<?php declare(strict_types=1);

namespace App\Livewire\Things;

use App\Models\Device;
use App\Models\Thing;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Thing')]
final class Create extends Component
{
    public string $name = '';

    public string $timezone = 'UTC';

    public ?int $device_id = null;

    public function createThing(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'timezone' => ['required', 'string', 'timezone:all'],
            'device_id' => ['nullable', 'integer', 'exists:devices,id'],
        ]);

        if ($this->device_id) {
            $deviceOwned = Device::query()
                ->where('id', $this->device_id)
                ->where('user_id', Auth::id())
                ->whereDoesntHave('thing')
                ->exists();

            if (! $deviceOwned) {
                $this->addError('device_id', 'The selected device is not available.');

                return;
            }
        }

        Thing::query()->create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'timezone' => $this->timezone,
            'device_id' => $this->device_id,
        ]);

        Flux::toast(text: 'Your new thing has been created.', heading: 'Thing created', variant: 'success');

        $this->redirect(route('things.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.things.create', [
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    /**
     * @return Collection<int, Device>
     */
    #[Computed]
    public function availableDevices(): Collection
    {
        return Device::query()
            ->where('user_id', Auth::id())
            ->whereDoesntHave('thing')
            ->orderBy('name')
            ->get();
    }
}
