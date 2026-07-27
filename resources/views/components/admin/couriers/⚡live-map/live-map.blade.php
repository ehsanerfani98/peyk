<div>
    <x-header title="نقشه لحظه‌ای پیک‌ها" separator progress-indicator>
        <x-slot:actions>
            <x-button label="بازگشت به لیست" icon="o-arrow-right" link="{{ route('admin.couriers') }}"
                class="btn-ghost btn-sm" />
        </x-slot:actions>
    </x-header>

    <x-card class="mb-4">
        <div class="flex gap-4">
            <x-select label="فیلتر" wire:model.live="filter" :options="[
                ['id' => 'all', 'name' => 'همه پیک‌ها'],
                ['id' => 'online', 'name' => 'فقط آنلاین'],
                ['id' => 'busy', 'name' => 'فقط مشغول'],
            ]" />
        </div>
    </x-card>

    <x-card>
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        @endpush

        <div wire:ignore id="live-map" style="height: 500px; width: 100%;" class="rounded-box"></div>

        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener('livewire:init', function () {
                    const map = L.map('live-map').setView([36.2794, 50.0049], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    const couriersData = @json($couriers);
                    const markers = {};

                    couriersData.forEach(c => {
                        if (c.lat && c.lng) {
                            const marker = L.marker([c.lat, c.lng])
                                .addTo(map)
                                .bindPopup(`
                                    <b>${c.name}</b><br>
                                    وضعیت: ${c.status}<br>
                                    وسیله: ${c.vehicle}<br>
                                    ${c.has_order ? 'مشغول سفارش #' + c.order_id : 'آزاد'}
                                `);
                            markers[c.id] = marker;
                        }
                    });

                    // گوش دادن به به‌روزرسانی موقعیت هر پیک
                    couriersData.forEach(c => {
                        const channel = window.Echo.join(`courier-tracking.${c.id}`);
                        channel.listen('.courier.location-updated', (payload) => {
                            if (markers[c.id]) {
                                markers[c.id].setLatLng([payload.lat, payload.lng]);
                            } else {
                                const marker = L.marker([payload.lat, payload.lng]).addTo(map);
                                markers[c.id] = marker;
                            }
                        });
                    });
                });
            </script>
        @endpush
    </x-card>
</div>
