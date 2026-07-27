<?php

use App\Models\User;
use App\Services\Admin\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('پروفایل پیک')] class extends Component
{
    use Toast;

    public User $courier;

    public function mount(): void
    {
        $this->courier->load('courierProfile');
    }

    public function with(): array
    {
        // Get current location
        $location = null;
        $loc = DB::select(
            'SELECT ST_X(location) as lng, ST_Y(location) as lat FROM courier_current_locations WHERE courier_id = ?',
            [$this->courier->id]
        );
        if (! empty($loc)) {
            $location = ['lat' => $loc[0]->lat, 'lng' => $loc[0]->lng];
        }

        // Get order history
        $orders = $this->courier->orders()
            ->where('courier_id', $this->courier->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Get location snapshots for routes
        $snapshots = DB::select(
            'SELECT ST_X(location) as lng, ST_Y(location) as lat, snapshot_type, created_at
             FROM courier_location_snapshots
             WHERE courier_id = ?
             ORDER BY created_at DESC
             LIMIT 100',
            [$this->courier->id]
        );

        return [
            'currentLocation' => $location,
            'orders' => $orders,
            'routeSnapshots' => $snapshots,
        ];
    }

    public function toggleStatus(): void
    {
        abort_unless(auth()->user()->can('manage couriers'), 403);

        $profile = $this->courier->courierProfile;
        $newStatus = $profile->status === 'online' ? 'offline' : 'online';
        $profile->update(['status' => $newStatus]);

        app(ActivityLogger::class)->log('courier.status_changed', $this->courier, [
            'old_status' => $profile->status,
        ], [
            'new_status' => $newStatus,
        ]);

        $this->success('وضعیت پیک تغییر کرد', position: 'toast-bottom toast-end');
    }

    public function deleteCourier(): void
    {
        abort_unless(auth()->user()->can('manage couriers'), 403);

        $this->courier->delete();
        $this->success('پیک با موفقیت حذف شد', position: 'toast-bottom toast-end');
        $this->redirect(route('admin.couriers'));
    }
};
