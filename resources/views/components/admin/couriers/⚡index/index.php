<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('مدیریت پیک‌ها')] class extends Component
{
    use Toast;
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $vehicleFilter = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function with(): array
    {
        return [
            'couriers' => User::role('courier')
                ->with('courierProfile')
                ->when($this->search, fn ($q) => $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%");
                }))
                ->when($this->statusFilter, fn ($q) => $q->whereHas('courierProfile', fn ($qp) => $qp->where('status', $this->statusFilter)))
                ->when($this->vehicleFilter, fn ($q) => $q->whereHas('courierProfile', fn ($qp) => $qp->where('vehicle_type', $this->vehicleFilter)))
                ->orderBy(...array_values($this->sortBy))
                ->paginate(15),
        ];
    }

    public function deleteCourier(int $userId): void
    {
        abort_unless(auth()->user()->can('manage couriers'), 403);

        User::findOrFail($userId)->delete();
        $this->success('پیک با موفقیت حذف شد', position: 'toast-bottom toast-end');
    }
};
