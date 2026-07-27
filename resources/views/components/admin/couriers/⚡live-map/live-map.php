<?php

use App\Models\CourierCurrentLocation;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('نقشه لحظه‌ای پیک‌ها')] class extends Component
{
    use Toast;

    public string $filter = 'online'; // all, online, busy

    public function with(): array
    {
        $query = CourierCurrentLocation::query()
            ->with('courier.user');

        if ($this->filter === 'online') {
            $query->whereHas('courier', fn ($q) => $q->where('status', 'online'));
        } elseif ($this->filter === 'busy') {
            $query->whereNotNull('order_id');
        }

        return [
            'couriers' => $query->get()->map(function ($loc) {
                $point = DB::select(
                    'SELECT ST_X(location) as lng, ST_Y(location) as lat FROM courier_current_locations WHERE courier_id = ?',
                    [$loc->courier_id]
                )[0] ?? null;

                return [
                    'id' => $loc->courier_id,
                    'name' => $loc->courier?->user?->name ?? 'نامشخص',
                    'lat' => $point?->lat,
                    'lng' => $point?->lng,
                    'status' => $loc->courier?->status,
                    'vehicle' => $loc->courier?->vehicle_type,
                    'has_order' => $loc->order_id !== null,
                    'order_id' => $loc->order_id,
                ];
            })->filter(fn ($c) => $c['lat'] && $c['lng'])->values(),
        ];
    }
};
