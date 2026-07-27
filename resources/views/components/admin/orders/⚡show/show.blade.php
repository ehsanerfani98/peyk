<div>
    <x-header title="جزئیات سفارش #{{ $order->id }}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="بازگشت به لیست" icon="o-arrow-right" link="{{ route('admin.orders') }}"
                class="btn-ghost btn-sm" />
            @can('manual order actions')
                <x-button label="اقدامات دستی" icon="o-adjustments-horizontal"
                    link="{{ route('admin.orders.manual-actions', $order) }}" class="btn-primary btn-sm" />
            @endcan
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- اطلاعات فرستنده --}}
        <x-card title="اطلاعات فرستنده" icon="o-user" shadow>
            <div class="space-y-2">
                <p><span class="font-bold">نام:</span> {{ $order->sender_name }}</p>
                <p><span class="font-bold">موبایل:</span> {{ $order->sender_mobile }}</p>
                <p><span class="font-bold">آدرس:</span> {{ $order->sender_address }}</p>
                <p><span class="font-bold">موقعیت:</span> {{ $order->sender_lat }}, {{ $order->sender_lng }}</p>
            </div>
        </x-card>

        {{-- اطلاعات گیرنده --}}
        <x-card title="اطلاعات گیرنده" icon="o-user" shadow>
            <div class="space-y-2">
                <p><span class="font-bold">نام:</span> {{ $order->receiver_name }}</p>
                <p><span class="font-bold">موبایل:</span> {{ $order->receiver_mobile }}</p>
                <p><span class="font-bold">آدرس:</span> {{ $order->receiver_address }}</p>
                <p><span class="font-bold">موقعیت:</span> {{ $order->receiver_lat }}, {{ $order->receiver_lng }}</p>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        {{-- مشخصات بسته --}}
        <x-card title="مشخصات بسته" icon="o-gift" shadow>
            <div class="space-y-2">
                <p><span class="font-bold">توضیحات:</span> {{ $order->package_description ?? '—' }}</p>
                <p><span class="font-bold">وزن:</span> {{ $order->package_weight_kg ? $order->package_weight_kg . ' کیلوگرم' : '—' }}</p>
                <p><span class="font-bold">اندازه:</span> {{ $order->package_size ?? '—' }}</p>
            </div>
        </x-card>

        {{-- اطلاعات مالی --}}
        <x-card title="اطلاعات مالی" icon="o-banknotes" shadow>
            <div class="space-y-2">
                <p><span class="font-bold">مبلغ:</span> {{ number_format($order->price) }} تومان</p>
                <p><span class="font-bold">روش پرداخت:</span> {{ $order->payment_method === 'online' ? 'آنلاین' : 'نقدی' }}</p>
                <p><span class="font-bold">وضعیت پرداخت:</span>
                    <x-badge :value="$order->payment_status === 'paid' ? 'پرداخت شده' : 'در انتظار'"
                        :class="$order->payment_status === 'paid' ? 'badge-success' : 'badge-warning'" />
                </p>
                @if ($order->payment_driver)
                    <p><span class="font-bold">درگاه:</span> {{ $order->payment_driver }}</p>
                @endif
                @if ($order->payment_ref_id)
                    <p><span class="font-bold">کد پیگیری:</span> {{ $order->payment_ref_id }}</p>
                @endif
            </div>
        </x-card>
    </div>

    {{-- اطلاعات پیک --}}
    @if ($order->courier)
        <x-card title="اطلاعات پیک" icon="o-bolt" shadow class="mt-4">
            <div class="space-y-2">
                <p><span class="font-bold">نام:</span> {{ $order->courier->name }}</p>
                <p><span class="font-bold">موبایل:</span> {{ $order->courier->mobile }}</p>
                @if ($order->courier->courierProfile)
                    <p><span class="font-bold">وسیله نقلیه:</span> {{ $order->courier->courierProfile->vehicle_type }}</p>
                @endif
            </div>
        </x-card>
    @endif

    {{-- نقشه --}}
    <x-card title="نقشه مسیر" icon="o-map" shadow class="mt-4">
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        @endpush

        <div wire:ignore id="order-map" style="height: 400px; width: 100%;" class="rounded-box"></div>

        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener('livewire:init', function () {
                    const map = L.map('order-map').setView([{{ $order->sender_lat }}, {{ $order->sender_lng }}], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    // مبدا (سبز)
                    L.marker([{{ $order->sender_lat }}, {{ $order->sender_lng }}], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                        })
                    })
                        .addTo(map)
                        .bindPopup('مبدا: {{ $order->sender_address }}');

                    // مقصد (قرمز)
                    L.marker([{{ $order->receiver_lat }}, {{ $order->receiver_lng }}], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                        })
                    })
                        .addTo(map)
                        .bindPopup('مقصد: {{ $order->receiver_address }}');

                    // اسنپ‌شات‌ها (آبی)
                    const snapshots = @json($snapshots);
                    const latlngs = [];
                    snapshots.forEach(s => {
                        const marker = L.marker([s.latitude, s.longitude], {
                            icon: L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                            })
                        })
                            .addTo(map)
                            .bindPopup(`نوع: ${s.snapshot_type}<br>زمان: ${s.created_at}`);
                        latlngs.push([s.latitude, s.longitude]);
                    });

                    // خط مسیر
                    if (latlngs.length > 0) {
                        L.polyline(latlngs, { color: 'blue' }).addTo(map);
                    }
                });
            </script>
        @endpush
    </x-card>

    {{-- تاریخچه وضعیت‌ها --}}
    <x-card title="تاریخچه وضعیت‌ها" icon="o-clock" shadow class="mt-4">
        <x-table :headers="[
            ['key' => 'old_status', 'label' => 'وضعیت قبلی'],
            ['key' => 'new_status', 'label' => 'وضعیت جدید'],
            ['key' => 'changed_by', 'label' => 'تغییردهنده'],
            ['key' => 'created_at', 'label' => 'تاریخ'],
        ]" :rows="$order->statusHistories" with-pagination>
            @scope('cell_old_status', $history)
                {{ $this->statusLabel($history->old_status) }}
            @endscope
            @scope('cell_new_status', $history)
                {{ $this->statusLabel($history->new_status) }}
            @endscope
            @scope('cell_changed_by', $history)
                {{ $history->changedByUser?->name ?? 'سیستم' }}
            @endscope
            @scope('cell_created_at', $history)
                {{ $history->created_at?->format('Y-m-d H:i') }}
            @endscope
        </x-table>
    </x-card>

    {{-- تاریخچه تاییدها --}}
    @if ($order->verifications->isNotEmpty())
        <x-card title="تاریخچه تاییدها" icon="o-shield-check" shadow class="mt-4">
            <x-table :headers="[
                ['key' => 'type', 'label' => 'نوع'],
                ['key' => 'mobile', 'label' => 'موبایل'],
                ['key' => 'verified_at', 'label' => 'زمان تایید'],
            ]" :rows="$order->verifications">
                @scope('cell_type', $verification)
                    {{ $verification->type === 'sender' ? 'فرستنده' : 'گیرنده' }}
                @endscope
                @scope('cell_verified_at', $verification)
                    {{ $verification->verified_at?->format('Y-m-d H:i') ?? '—' }}
                @endscope
            </x-table>
        </x-card>
    @endif

    {{-- نظرات --}}
    @if ($order->reviews->isNotEmpty())
        <x-card title="نظرات ثبت‌شده" icon="o-star" shadow class="mt-4">
            @foreach ($order->reviews as $review)
                <div class="border-b border-base-200 pb-3 mb-3 last:border-0">
                    <div class="flex justify-between">
                        <span class="font-bold">{{ $review->user?->name ?? '—' }}</span>
                        <span>{{ $review->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="flex items-center gap-1 mt-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-icon :name="$i <= $review->rating ? 'o-star' : 'o-star'" :class="$i <= $review->rating ? 'text-warning' : 'text-base-300'" />
                        @endfor
                    </div>
                    <p class="mt-1">{{ $review->comment ?? '—' }}</p>
                </div>
            @endforeach
        </x-card>
    @endif
</div>
