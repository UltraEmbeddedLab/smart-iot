<?php declare(strict_types=1);

namespace App\Livewire\Things;

use App\Models\Thing;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Thing Details')]
final class Show extends Component
{
    public Thing $thing;

    public string $tagKey = '';

    public string $tagValue = '';

    public function mount(Thing $thing): void
    {
        abort_unless($thing->user_id === Auth::id(), 403);

        $this->thing = $thing;
    }

    public function addTag(): void
    {
        $this->validate([
            'tagKey' => ['required', 'string', 'max:255'],
            'tagValue' => ['required', 'string', 'max:255'],
        ]);

        $exists = $this->thing->tags()->where('key', $this->tagKey)->exists();

        if ($exists) {
            $this->addError('tagKey', 'This tag key already exists.');

            return;
        }

        $this->thing->tags()->create([
            'key' => $this->tagKey,
            'value' => $this->tagValue,
        ]);

        $this->reset('tagKey', 'tagValue');
        $this->thing->load('tags');

        Flux::toast(text: 'Tag has been added.', heading: 'Tag added', variant: 'success');
    }

    public function deleteTag(int $tagId): void
    {
        $this->thing->tags()->where('id', $tagId)->delete();
        $this->thing->load('tags');

        Flux::toast(text: 'Tag has been removed.', heading: 'Tag deleted', variant: 'success');
    }

    public function detachDevice(): void
    {
        $this->thing->update(['device_id' => null]);
        $this->thing->load('device');

        Flux::modal('detach-device')->close();

        Flux::toast(text: 'The device has been detached.', heading: 'Device detached', variant: 'success');
    }

    public function deleteThing(): void
    {
        $this->thing->delete();

        Flux::toast(text: 'The thing has been removed.', heading: 'Thing deleted', variant: 'success');

        $this->redirect(route('things.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.things.show');
    }
}
