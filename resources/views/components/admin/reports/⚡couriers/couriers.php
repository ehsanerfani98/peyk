<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('گزارش پیک‌ها')] class extends Component
{
    use Toast;

    public string $dateFrom = '';

    public string $dateTo = '';

    public function with(): array
    {
        $query = User::role('courier')
            ->with('courierProfile')
            ->withCount(['orders as orders_count' => function ($q) {
                if ($this->dateFrom) {
                    $q->whereDate('created_at', '>=', $this->dateFrom);
                }
                if ($this->dateTo) {
                    $q->whereDate('created_at', '<=', $this->dateTo);
                }
            }]);

        return [
            'couriers' => $query->get()->map(function ($courier) {
                // Get total distance from snapshots
                $distance = DB::select(
                    'SELECT COUNT(*) as snapshot_count FROM courier_location_snapshots WHERE courier_id = ?',
                    [$courier->id]
                )[0]->snapshot_count ?? 0;

                return [
                    'id' => $courier->id,
                    'name' => $courier->name,
                    'mobile' => $courier->mobile,
                    'vehicle_type' => $courier->courierProfile?->vehicle_type ?? '—',
                    'orders_count' => $courier->orders_count,
                    'rating' => $courier->courierProfile?->rating ?? 0,
                    'snapshot_count' => $distance,
                ];
            })->sortByDesc('orders_count')->values(),
        ];
    }

    public function exportCsv(): void
    {
        $couriers = $this->with()['couriers'];

        $csv = "Name,Mobile,Vehicle,Orders,Rating,Snapshots\n";
        foreach ($couriers as $c) {
            $csv .= "{$c['name']},{$c['mobile']},{$c['vehicle_type']},{$c['orders_count']},{$c['rating']},{$c['snapshot_count']}\n";
        }

        $this->dispatch('download-csv', csv: $csv, filename: 'couriers-report.csv');
    }
};
