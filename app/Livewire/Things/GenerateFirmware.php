<?php declare(strict_types=1);

namespace App\Livewire\Things;

use App\Ai\Agents\FirmwareGenerator;
use App\Models\Thing;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Generate Firmware')]
final class GenerateFirmware extends Component
{
    public Thing $thing;

    public string $wifiSsid = '';

    public string $wifiPassword = '';

    public string $generatedCode = '';

    public bool $isGenerating = false;

    public string $errorMessage = '';

    public function mount(Thing $thing): void
    {
        abort_unless($thing->user_id === Auth::id(), 403);
        abort_unless($thing->device !== null, 404, 'This thing has no device attached.');
        abort_unless($thing->cloudVariables->isNotEmpty(), 404, 'This thing has no cloud variables defined.');

        $this->thing = $thing->load(['device', 'cloudVariables']);
    }

    public function generateCode(): void
    {
        $this->validate([
            'wifiSsid' => ['required', 'string', 'max:255'],
            'wifiPassword' => ['required', 'string', 'max:255'],
        ]);

        $this->errorMessage = '';
        $this->isGenerating = true;

        try {
            $agent = new FirmwareGenerator(
                thing: $this->thing->load(['device', 'cloudVariables']),
                wifiSsid: $this->wifiSsid,
                wifiPassword: $this->wifiPassword,
            );

            $response = $agent->prompt('Generate the complete firmware code for this device.');

            $this->generatedCode = (string) $response;
        } catch (Throwable $e) {
            $this->generatedCode = '';
            $this->errorMessage = __('Failed to generate firmware. Please check your AI provider configuration and try again.');

            report($e);
        } finally {
            $this->isGenerating = false;
        }
    }

    public function render(): View
    {
        return view('livewire.things.generate-firmware');
    }
}
