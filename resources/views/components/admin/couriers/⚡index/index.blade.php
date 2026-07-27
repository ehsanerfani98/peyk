<div>
    <x-header title="مدیریت پیک‌ها" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="جستجو..." wire:model.live.debounce.400ms="search" icon="o-magnifying-glass" clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="پیک جدید" icon="o-plus" class="btn-primary btn-soft" link="{{ route('admin.couriers.create') }}" />
            <x-button label="نقشه لحظه‌ای" icon="o-map" class="btn-ghost btn-sm" link="{{ route('admin.couriers.live-map') }}" />
        </x-slot:actions>
    </x-header>

    <x-card class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-select label="وضعیت" wire:model="statusFilter" placeholder="همه"
                :options="[['id' => 'online', 'name' => 'آنلاین'], ['id' => 'offline', 'name' => 'آفلاین']]" />
            <x-select label="وسیله نقلیه" wire:model="vehicleFilter" placeholder="همه"
                :options="[['id' => 'motorcycle', 'name' => 'موتور'], ['id' => 'car', 'name' => 'ماشین'], ['id' => 'bicycle', 'name' => 'دوچرخه']]" />
        </div>
    </x-card>

    <x-card>
        <x-table :headers="[
            ['key' => 'name', 'label' => 'نام'],
            ['key' => 'mobile', 'label' => 'موبایل'],
            ['key' => 'courierProfile.national_code', 'label' => 'کد ملی', 'sortable' => false],
            ['key' => 'courierProfile.vehicle_type', 'label' => 'وسیله نقلیه', 'sortable' => false],
            ['key' => 'courierProfile.vehicle_number', 'label' => 'شماره وسیله', 'sortable' => false],
            ['key' => 'courierProfile.status', 'label' => 'وضعیت', 'sortable' => false],
            ['key' => 'courierProfile.rating', 'label' => 'امتیاز', 'sortable' => false],
        ]" :rows="$couriers" :sort-by="$sortBy" with-pagination>
            @scope('cell_courierProfile.status', $courier)
                <x-badge :value="$courier->courierProfile?->status === 'online' ? 'آنلاین' : 'آفلاین'"
                    :class="$courier->courierProfile?->status === 'online' ? 'badge-success' : 'badge-ghost'" />
            @endscope

            @scope('cell_courierProfile.rating', $courier)
                {{ $courier->courierProfile?->rating ?? '—' }}
            @endscope

            @scope('actions', $courier)
                <div class="flex gap-1">
                    <x-button icon="o-eye" link="{{ route('admin.couriers.show', $courier) }}" class="btn-ghost btn-sm"
                        tooltip="پروفایل" />
                    @can('manage couriers')
                        <x-button icon="o-trash" wire:click="deleteCourier({{ $courier->id }})"
                            wire:confirm="پیک حذف بشه؟" spinner class="btn-ghost btn-sm text-error" tooltip="حذف" />
                    @endcan
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
