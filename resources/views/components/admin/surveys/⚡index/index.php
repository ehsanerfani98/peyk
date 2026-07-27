<?php

use App\Models\Review;
use App\Models\SurveyToken;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('مدیریت نظرسنجی‌ها')] class extends Component
{
    use Toast;
    use WithPagination;

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $typeFilter = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function with(): array
    {
        $query = SurveyToken::query()->with('order');

        if ($this->statusFilter === 'used') {
            $query->whereNotNull('used_at');
        } elseif ($this->statusFilter === 'expired') {
            $query->whereNull('used_at')->where('expires_at', '<', now());
        } elseif ($this->statusFilter === 'pending') {
            $query->whereNull('used_at')->where('expires_at', '>=', now());
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        return [
            'tokens' => $query->orderBy(...array_values($this->sortBy))->paginate(15),
            'totalCount' => SurveyToken::count(),
            'usedCount' => SurveyToken::whereNotNull('used_at')->count(),
            'avgRating' => Review::avg('rating'),
        ];
    }
};
