<div>
    <x-header title="مدیریت نظرات" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="جستجو در نظرات..." wire:model.live.debounce.400ms="search" icon="o-magnifying-glass" clearable />
        </x-slot:middle>
    </x-header>

    <x-card>
        <x-table :headers="[
            ['key' => 'id', 'label' => '#'],
            ['key' => 'order_id', 'label' => 'سفارش'],
            ['key' => 'user.name', 'label' => 'کاربر', 'sortable' => false],
            ['key' => 'rating', 'label' => 'امتیاز'],
            ['key' => 'comment', 'label' => 'نظر'],
            ['key' => 'reviewer_type', 'label' => 'نوع نظر‌دهنده'],
            ['key' => 'created_at', 'label' => 'تاریخ'],
        ]" :rows="$reviews" :sort-by="$sortBy" with-pagination>
            @scope('cell_rating', $review)
                <div class="flex items-center gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <x-icon :name="$i <= $review->rating ? 'o-star' : 'o-star'" :class="$i <= $review->rating ? 'text-warning' : 'text-base-300'" class="w-4 h-4" />
                    @endfor
                </div>
            @endscope
            @scope('cell_comment', $review)
                <p class="truncate max-w-xs">{{ $review->comment ?? '—' }}</p>
            @endscope
            @scope('cell_reviewer_type', $review)
                {{ match($review->reviewer_type) { 'customer' => 'مشتری', 'sender' => 'فرستنده', 'receiver' => 'گیرنده', default => $review->reviewer_type } }}
            @endscope
            @scope('cell_created_at', $review)
                {{ $review->created_at?->format('Y-m-d H:i') }}
            @endscope
            @scope('actions', $review)
                @can('manage reviews')
                    <x-button icon="o-trash" wire:click="deleteReview({{ $review->id }})"
                        wire:confirm="نظر حذف بشه؟" spinner class="btn-ghost btn-sm text-error" tooltip="حذف" />
                @endcan
            @endscope
        </x-table>
    </x-card>
</div>
