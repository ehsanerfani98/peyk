<div>
    <x-header title="پروفایل پیک: {{ $courier->name }}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="بازگشت به لیست" icon="o-arrow-right" link="{{ route('admin.couriers') }}"
                class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- اطلاعات پیک --}}
        <x-card title="اطلاعات پیک" icon="o-user" shadow>
            <div class="space-y-2">
                <p><span class="font-bold">نام:</span> {{ $courier->name }}</p>
                <p><span class="font-bold">موبایل:</span> {{ $courier->mobile }}</p>
                <p><span class="font-bold">ایمیل:</span> {{ $courier->email }}</p>
                <p><span class="font-bold">کد ملی:</span> {{ $courier->courierProfile?->national_code ?? '—' }}</p>
                <p><span class="font-bold">وسیله نقلیه:</span> {{ $courier->courierProfile?->vehicle_type ?? '—' }}</p>
                <p><span class="font-bold">شماره وسیله:</span> {{ $courier->courierProfile?->vehicle_number ?? '—' }}</p>
                <p><span class="font-bold">امتیاز:</span> {{ $courier->courierProfile?->rating ?? '—' }}</p>
                <p><span class="font-bold">وضعیت:</span>
                    <x-badge :value="$courier->courierProfile?->status === 'online' ? 'آنلاین' : 'آفلاین'"
                        :class="$courier->courierProfile?->status === 'online' ? 'badge-success' : 'badge-ghost'" />
                </p>
            </div>

            <x-slot:actions>
                <x-button :label="$courier->courierProfile?->status === 'online' ? 'غیرفعال کردن' : 'فعال کردن'"
                    :icon="$courier->courierProfile?->status === 'online' ? 'o-pause' : 'o-play'"
                    wire:click="toggleStatus" class="btn-warning btn-sm" spinner="toggleStatus" />
                <x-button label="حذف پیک" icon="o-trash" wire:click="deleteCourier"
                    wire:confirm="از حذف این پیک اطمینان دارید؟" class="btn-error btn-sm" spinner="deleteCourier" />
            </x-slot:actions>
        </x-card>

        {{-- موقعیت لحظه‌ای --}}
        <x-card title="موقعیت لحظه‌ای" icon="o-map" shadow>
            @if ($currentLocation)
                @push('styles')
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                @endpush

                <div wire:ignore id="courier-location-map" style="height: 300px; width: 100%;" class="rounded-box"></div>

                @push('scripts')
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                    <script>
                        document.addEventListener('livewire:init', function () {
                            const map = L.map('courier-location-map').setView([{{ $currentLocation['lat'] }}, {{ $currentLocation['lng'] }}], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(map);

                            L.marker([{{ $currentLocation['lat'] }}, {{ $currentLocation['lng'] }}])
                                .addTo(map)
                                .bindPopup('{{ $courier->name }}');
                        });
                    </script>
                @endpush
            @else
                <p class="text-base-500">موقعیتی ثبت نشده است.</p>
            @endif
        </x-card>
    </div>

    {{-- مسیرهای طی‌شده --}}
    @if (!empty($routeSnapshots))
        <x-card title="مسیرهای طی‌شده" icon="o-map" shadow class="mt-4">
            @push('styles')
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            @endpush

            <div wire:ignore id="courier-route-map" style="height: 300px; width: 100%;" class="rounded-box"></div>

            @push('scripts')
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                <script>
                    document.addEventListener('livewire:init', function () {
                        const snapshots = @json($routeSnapshots);
                        const map = L.map('courier-route-map').setView([snapshots[0].lat, snapshots[0].lng], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        const latlngs = snapshots.map(s => [s.lat, s.lng]);
                        L.polyline(latlngs, { color: 'blue' }).addTo(map);
                    });
                </script>
            @endpush
        </x-card>
    @endif

    {{-- تاریخچه سفارش‌ها --}}
    <x-card title="تاریخچه سفارش‌ها" icon="o-truck" shadow class="mt-4">
        <x-table :headers="[
            ['key' => 'id', 'label' => '#'],
            ['key' => 'sender_name', 'label' => 'فرستنده'],
            ['key' => 'receiver_name', 'label' => 'گیرنده'],
            ['key' => 'status', 'label' => 'وضعیت'],
            ['key' => 'price', 'label' => 'مبلغ'],
            ['key' => 'created_at', 'label' => 'تاریخ'],
        ]" :rows="$orders">
            @scope('cell_status', $order)
                <x-badge :value="$order->status" class="badge-sm" />
            @endscope
            @scope('cell_price', $order)
                {{ number_format($order->price) }} تومان
            @endscope
            @scope('cell_created_at', $order)
                {{ $order->created_at->format('Y-m-d H:i') }}
            @endscope
            @scope('actions', $order)
                <x-button icon="o-eye" link="{{ route('admin.orders.show', $order) }}" class="btn-ghost btn-sm" />
            @endscope
        </x-table>
    </x-card>
</div>
