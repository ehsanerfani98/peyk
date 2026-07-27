<?php

namespace App\Services\Admin;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;

final class ActivityLogger
{
    /**
     * Log an admin activity.
     *
     * @param  string  $action  e.g. 'order.cancelled', 'courier.created'
     * @param  Model  $subject  The model that was acted upon
     * @param  array|null  $oldData  Previous state data (optional)
     * @param  array|null  $newData  New state data (optional)
     */
    public function log(
        string $action,
        Model $subject,
        ?array $oldData = null,
        ?array $newData = null,
    ): void {
        AdminActivityLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
