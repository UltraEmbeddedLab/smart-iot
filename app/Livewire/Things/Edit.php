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

#[Title('Edit Thing')]
final class Edit extends Component
{
    public Thing $thing;

    public string $name = '';

    public string $timezone = '';

    public ?int $device_id = null;

    public function mount(Thing $thing): void
    {
        abort_unless($thing->user_id === Auth::id(), 403);

        $this->thing = $thing;
        $this->name = $thing->name;
        $this->timezone = $thing->timezone;
        $this->device_id = $thing->device_id;
    }

    public function updateThing(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'timezone' => ['required', 'string', 'timezone:all'],
            'device_id' => ['nullable', 'integer', 'exists:devices,id'],
        ]);

        if ($this->device_id && $this->device_id !== $this->thing->device_id) {
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

        $this->thing->update([
            'name' => $this->name,
            'timezone' => $this->timezone,
            'device_id' => $this->device_id,
        ]);

        Flux::toast(text: 'The thing has been updated.', heading: 'Thing updated', variant: 'success');

        $this->redirect(route('things.show', $this->thing), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.things.edit', [
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
            ->where(function ($query): void {
                $query->whereDoesntHave('thing')
                    ->orWhere('id', $this->thing->device_id);
            })
            ->orderBy('name')
            ->get();
    }
}
