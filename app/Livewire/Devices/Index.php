<?php declare(strict_types=1);

namespace App\Livewire\Devices;

use App\Models\Device;
use Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Devices')]
final class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterType = '';

    #[Url]
    public string $filterStatus = '';

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

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function deleteDevice(int $deviceId): void
    {
        Device::query()
            ->where('user_id', Auth::id())
            ->where('id', $deviceId)
            ->delete();

        Flux::toast(text: 'The device has been removed.', heading: 'Device deleted', variant: 'success');
    }

    /**
     * @return LengthAwarePaginator<int, Device>
     */
    #[Computed]
    public function devices(): LengthAwarePaginator
    {
        return Device::query()
            ->where('user_id', Auth::id())
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn ($query) => $query->where('type', $this->filterType))
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }
}
