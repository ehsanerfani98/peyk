<?php

use App\Models\Review;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('مدیریت نظرات')] class extends Component
{
    use Toast;
    use WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function with(): array
    {
        return [
            'reviews' => Review::query()
                ->with(['order', 'user'])
                ->when($this->search, fn ($q) => $q->where('comment', 'like', "%{$this->search}%"))
                ->orderBy(...array_values($this->sortBy))
                ->paginate(15),
        ];
    }

    public function deleteReview(int $reviewId): void
    {
        abort_unless(auth()->user()->can('manage reviews'), 403);

        Review::findOrFail($reviewId)->delete();
        $this->success('نظر با موفقیت حذف شد', position: 'toast-bottom toast-end');
    }
};
