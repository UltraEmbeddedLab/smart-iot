<?php declare(strict_types=1);

namespace App\Livewire\Dashboards;

use App\Models\Dashboard;
use Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Dashboards')]
final class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteDashboard(int $dashboardId): void
    {
        Dashboard::query()
            ->where('user_id', Auth::id())
            ->where('id', $dashboardId)
            ->delete();

        Flux::toast(text: 'The dashboard has been removed.', heading: 'Dashboard deleted', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.dashboards.index');
    }

    /**
     * @return LengthAwarePaginator<int, Dashboard>
     */
    #[Computed]
    public function dashboards(): LengthAwarePaginator
    {
        return Dashboard::query()
            ->where('user_id', Auth::id())
            ->withCount('widgets')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%$this->search%"))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }
}
