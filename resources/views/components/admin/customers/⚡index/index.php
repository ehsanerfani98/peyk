<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('مدیریت مشتریان')] class extends Component
{
    use Toast;
    use WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function with(): array
    {
        return [
            'customers' => User::role('customer')
                ->withCount('orders')
                ->withSum('orders', 'price')
                ->when($this->search, fn ($q) => $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                }))
                ->orderBy(...array_values($this->sortBy))
                ->paginate(15),
        ];
    }
};
