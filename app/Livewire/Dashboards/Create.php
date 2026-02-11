<?php declare(strict_types=1);

namespace App\Livewire\Dashboards;

use App\Models\Dashboard;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Dashboard')]
final class Create extends Component
{
    public string $name = '';

    public function createDashboard(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $dashboard = Dashboard::query()->create([
            'user_id' => Auth::id(),
            'name' => $this->name,
        ]);

        Flux::toast(text: 'Your new dashboard has been created.', heading: 'Dashboard created', variant: 'success');

        $this->redirect(route('dashboards.show', $dashboard), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.dashboards.create');
    }
}
