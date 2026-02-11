<?php declare(strict_types=1);

namespace App\Livewire\Things;

use App\Models\Thing;
use Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Things')]
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

    public function deleteThing(int $thingId): void
    {
        Thing::query()
            ->where('user_id', Auth::id())
            ->where('id', $thingId)
            ->delete();

        Flux::toast(text: 'The thing has been removed.', heading: 'Thing deleted', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.things.index');
    }

    /**
     * @return LengthAwarePaginator<int, Thing>
     */
    #[Computed]
    public function things(): LengthAwarePaginator
    {
        return Thing::query()
            ->where('user_id', Auth::id())
            ->with(['device', 'tags'])
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%$this->search%"))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }
}
