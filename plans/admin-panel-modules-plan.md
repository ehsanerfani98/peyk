# پلن پیاده‌سازی ماژول‌های پنل مدیریت

## مقدمه

این سند بر اساس تحلیل کامل کدبیس پروژه PeykLaravel تهیه شده است. پروژه در حال حاضر دارای API کامل برای اپلیکیشن موبایل (سفارش‌گذاری، جستجوی پیک، تحویل، پرداخت، نظرسنجی) و پنل مدیریت پایه (داشبورد، کاربران، نقش‌ها) می‌باشد. آنچه کم است، ماژول‌های عملیاتی پنل مدیریت برای نظارت و مداخله دستی است.

---

## ۱. معماری کلی

### ۱.۱. ساختار کامپوننت‌ها (ادامه MFC فعلی)

پروژه از فرمت **Multi-File Component (MFC)** در `resources/views/components/` استفاده می‌کند. همه ماژول‌های جدید نیز از همین الگو پیروی می‌کنند:

```
resources/views/components/admin/
├── dashboard/⚡index/                    ← ✅ موجود
├── users/⚡index/                        ← ✅ موجود
├── roles/⚡index/                        ← ✅ موجود
│
├── orders/
│   ├── ⚡index/                          ← 🆕 لیست سفارش‌ها
│   ├── ⚡show/                           ← 🆕 جزئیات سفارش
│   └── ⚡manual-actions/                 ← 🆕 اقدامات دستی
│
├── couriers/
│   ├── ⚡index/                          ← 🆕 لیست پیک‌ها
│   ├── ⚡show/                           ← 🆕 پروفایل پیک
│   ├── ⚡create/                         ← 🆕 ثبت پیک جدید
│   └── ⚡live-map/                       ← 🆕 نقشه لحظه‌ای پیک‌ها
│
├── payments/
│   ├── ⚡index/                          ← 🆕 تراکنش‌ها
│   └── ⚡show/                           ← 🆕 جزئیات تراکنش
│
├── surveys/
│   ├── ⚡index/                          ← 🆕 نظرسنجی‌ها
│   └── ⚡show/                           ← 🆕 جزئیات نظرسنجی
│
├── reviews/
│   └── ⚡index/                          ← 🆕 نظرات
│
├── customers/
│   ├── ⚡index/                          ← 🆕 مشتریان
│   └── ⚡show/                           ← 🆕 پروفایل مشتری
│
├── settings/
│   ├── ⚡general/                        ← 🆕 تنظیمات عمومی
│   ├── ⚡courier-search/                 ← 🆕 تنظیمات جستجوی پیک
│   ├── ⚡payment/                        ← 🆕 تنظیمات پرداخت
│   └── ⚡sms/                            ← 🆕 تنظیمات پیامک
│
├── reports/
│   ├── ⚡orders/                         ← 🆕 گزارش سفارش‌ها
│   ├── ⚡financial/                      ← 🆕 گزارش مالی
│   └── ⚡couriers/                       ← 🆕 گزارش پیک‌ها
│
└── logs/
    └── ⚡activity/                       ← 🆕 لاگ فعالیت
```

### ۱.۲. مسیریابی (Routes)

```php
// routes/web.php — اضافه شدن به گروه admin موجود

Route::middleware(['auth', 'permission:manage orders'])->prefix('admin')->group(function () {
    Route::livewire('/orders', 'admin.orders.index')->name('admin.orders');
    Route::livewire('/orders/{order}', 'admin.orders.show')->name('admin.orders.show');
    Route::livewire('/orders/{order}/manual-actions', 'admin.orders.manual-actions')->name('admin.orders.manual-actions');
});

Route::middleware(['auth', 'permission:manage couriers'])->prefix('admin')->group(function () {
    Route::livewire('/couriers', 'admin.couriers.index')->name('admin.couriers');
    Route::livewire('/couriers/create', 'admin.couriers.create')->name('admin.couriers.create');
    Route::livewire('/couriers/{courier}', 'admin.couriers.show')->name('admin.couriers.show');
    Route::livewire('/couriers/live-map', 'admin.couriers.live-map')->name('admin.couriers.live-map');
});

Route::middleware(['auth', 'permission:manage payments'])->prefix('admin')->group(function () {
    Route::livewire('/payments', 'admin.payments.index')->name('admin.payments');
    Route::livewire('/payments/{order}', 'admin.payments.show')->name('admin.payments.show');
});

Route::middleware(['auth', 'permission:manage surveys'])->prefix('admin')->group(function () {
    Route::livewire('/surveys', 'admin.surveys.index')->name('admin.surveys');
    Route::livewire('/surveys/{survey}', 'admin.surveys.show')->name('admin.surveys.show');
});

Route::middleware(['auth', 'permission:manage reviews'])->prefix('admin')->group(function () {
    Route::livewire('/reviews', 'admin.reviews.index')->name('admin.reviews');
});

Route::middleware(['auth', 'permission:manage customers'])->prefix('admin')->group(function () {
    Route::livewire('/customers', 'admin.customers.index')->name('admin.customers');
    Route::livewire('/customers/{customer}', 'admin.customers.show')->name('admin.customers.show');
});

Route::middleware(['auth', 'permission:manage settings'])->prefix('admin')->group(function () {
    Route::livewire('/settings/general', 'admin.settings.general')->name('admin.settings.general');
    Route::livewire('/settings/courier-search', 'admin.settings.courier-search')->name('admin.settings.courier-search');
    Route::livewire('/settings/payment', 'admin.settings.payment')->name('admin.settings.payment');
    Route::livewire('/settings/sms', 'admin.settings.sms')->name('admin.settings.sms');
});

Route::middleware(['auth', 'permission:view reports'])->prefix('admin')->group(function () {
    Route::livewire('/reports/orders', 'admin.reports.orders')->name('admin.reports.orders');
    Route::livewire('/reports/financial', 'admin.reports.financial')->name('admin.reports.financial');
    Route::livewire('/reports/couriers', 'admin.reports.couriers')->name('admin.reports.couriers');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::livewire('/logs/activity', 'admin.logs.activity')->name('admin.logs.activity');
});
```

### ۱.۳. Permissions جدید

```php
// در database/seeders/PermissionSeeder.php

$permissions = [
    // ... موجود
    'manage orders',
    'force cancel orders',
    'manual order actions',
    'manage couriers',
    'manage payments',
    'process refunds',
    'manage surveys',
    'manage reviews',
    'manage customers',
    'manage settings',
    'view reports',
];
```

### ۱.۴. Migration‌های جدید

```php
// 1. settings table
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('group'); // general, courier_search, payment, sms, etc.
    $table->timestamps();
});

// 2. refunds table
Schema::create('refunds', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->decimal('amount', 12, 2);
    $table->string('reason');
    $table->foreignId('processed_by')->constrained('users');
    $table->string('status'); // pending, completed, failed
    $table->string('gateway_refund_id')->nullable();
    $table->timestamp('refunded_at')->nullable();
    $table->timestamps();
});

// 3. admin_activity_logs table
Schema::create('admin_activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('admin_id')->constrained('users');
    $table->string('action'); // order.cancelled, order.status_changed, courier.created, etc.
    $table->nullableMorphs('subject');
    $table->json('old_data')->nullable();
    $table->json('new_data')->nullable();
    $table->string('ip', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
});

// 4. Add mobile column to users (for OTP auth and courier SMS)
// Already exists based on Fillable, but verify migration exists
// Check: 2026_07_09_194726_add_address_and_lat_lng_to_users_table.php
```

---

## ۲. ماژول‌ها — جزئیات پیاده‌سازی

### ۲.۱. ماژول مدیریت سفارش‌ها (Orders)

#### ۲.۱.۱. لیست سفارش‌ها — [`admin.orders.index`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/orders/⚡index/index.php`
- `resources/views/components/admin/orders/⚡index/index.blade.php`

**قابلیت‌ها:**
- جدول با ستون‌های: شماره سفارش (ID)، مشتری، فرستنده، گیرنده، وضعیت، مبلغ، روش پرداخت، وضعیت پرداخت، پیک، تاریخ ایجاد
- فیلترهای پیشرفته:
  - وضعیت (multi-select: CREATED, SEARCHING_COURIER, COURIER_ASSIGNED, PICKED_UP, IN_TRANSIT, DELIVERED, CANCELLED, ...)
  - بازه تاریخ (از/تا)
  - روش پرداخت (online / cash_on_delivery)
  - وضعیت پرداخت (pending / paid)
  - جستجو بر اساس: شماره سفارش، نام فرستنده، نام گیرنده، موبایل فرستنده، موبایل گیرنده
- مرتب‌سازی: تاریخ، مبلغ، وضعیت
- صفحه‌بندی
- لینک به صفحه جزئیات هر سفارش

**کلاس کامپوننت:**
```php
new #[Layout('layouts.app')] #[Title('مدیریت سفارش‌ها')] class extends Component
{
    use WithPagination;
    use Toast;

    #[Url]
    public string $search = '';

    #[Url]
    public array $statusFilter = [];

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $paymentMethod = '';

    #[Url]
    public string $paymentStatus = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function with(): array
    {
        return [
            'orders' => Order::query()
                ->with(['customer', 'courier'])
                ->when($this->search, fn($q) => $q->where(function($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                      ->orWhere('sender_name', 'like', "%{$this->search}%")
                      ->orWhere('receiver_name', 'like', "%{$this->search}%")
                      ->orWhere('sender_mobile', 'like', "%{$this->search}%")
                      ->orWhere('receiver_mobile', 'like', "%{$this->search}%");
                }))
                ->when($this->statusFilter, fn($q) => $q->whereIn('status', $this->statusFilter))
                ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->when($this->paymentMethod, fn($q) => $q->where('payment_method', $this->paymentMethod))
                ->when($this->paymentStatus, fn($q) => $q->where('payment_status', $this->paymentStatus))
                ->orderBy(...array_values($this->sortBy))
                ->paginate(15),
        ];
    }
}
```

#### ۲.۱.۲. جزئیات سفارش — [`admin.orders.show`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/orders/⚡show/show.php`
- `resources/views/components/admin/orders/⚡show/show.blade.php`

**قابلیت‌ها:**
- نمایش کامل اطلاعات سفارش:
  - اطلاعات فرستنده (نام، موبایل، آدرس، موقعیت روی نقشه)
  - اطلاعات گیرنده (نام، موبایل، آدرس، موقعیت روی نقشه)
  - مشخصات بسته (توضیحات، وزن، اندازه)
  - اطلاعات مالی (مبلغ، روش پرداخت، وضعیت پرداخت، درگاه)
  - اطلاعات پیک (نام، موبایل، وسیله نقلیه)
- **نقشه OpenStreetMap** با نمایش:
  - مبدا (فرستنده) — مارکر سبز
  - مقصد (گیرنده) — مارکر قرمز
  - اسنپ‌شات‌های موقعیت پیک در طول مسیر — مارکرهای آبی با خط مسیر
  - با استفاده از Leaflet.js (کتابخانه متن‌باز OpenStreetMap)
- **تاریخچه وضعیت‌ها** — جدول زمانی از `order_status_histories` با نام تغییردهنده
- **تاریخچه تاییدها** — از `order_verifications`
- **نظرات ثبت‌شده** — از `reviews`
- **لینک به صفحه اقدامات دستی**

**نقشه (Leaflet.js):**
```blade
{{-- در فایل show.blade.php --}}
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

<div wire:ignore id="order-map" style="height: 400px; width: 100%;"></div>

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('order-map').setView([{{ $order->sender_lat }}, {{ $order->sender_lng }}], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // مبدا
        L.marker([{{ $order->sender_lat }}, {{ $order->sender_lng }}])
            .addTo(map)
            .bindPopup('مبدا: {{ $order->sender_address }}');

        // مقصد
        L.marker([{{ $order->receiver_lat }}, {{ $order->receiver_lng }}])
            .addTo(map)
            .bindPopup('مقصد: {{ $order->receiver_address }}');

        // اسنپ‌شات‌ها
        const snapshots = @json($snapshots);
        const latlngs = [];
        snapshots.forEach(s => {
            const marker = L.marker([s.latitude, s.longitude])
                .addTo(map)
                .bindPopup(`نوع: ${s.snapshot_type}<br>زمان: ${s.created_at}`);
            latlngs.push([s.latitude, s.longitude]);
        });

        // خط مسیر
        if (latlngs.length > 0) {
            L.polyline(latlngs, { color: 'blue' }).addTo(map);
        }
    </script>
@endpush
```

#### ۲.۱.۳. اقدامات دستی مدیر — [`admin.orders.manual-actions`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/orders/⚡manual-actions/manual-actions.php`
- `resources/views/components/admin/orders/⚡manual-actions/manual-actions.blade.php`

**قابلیت‌ها (بخش‌های مجزا در یک صفحه):**

| بخش | اکشن | توضیح |
|------|------|--------|
| **لغو سفارش** | `cancelOrder(reason)` | لغو دستی با `cancelled_by = 'admin'` + ثبت دلیل |
| **تغییر وضعیت** | `changeStatus(newStatus)` | تغییر دستی وضعیت به هر وضعیت مجاز |
| **تخصیص پیک** | `assignCourier(courierId)` | تخصیص دستی یک پیک خاص به سفارش |
| **تغییر قیمت** | `updatePrice(newPrice)` | ویرایش مبلغ سفارش |
| **بازپرداخت** | `processRefund(amount, reason)` | ثبت بازپرداخت در جدول `refunds` |
| **تایید دستی تحویل** | `forceConfirmDelivery()` | تایید تحویل بدون نیاز به کد |
| **ارسال مجدد پیامک** | `resendVerificationSms(type)` | ارسال مجدد لینک تایید به فرستنده/گیرنده |

**کلاس کامپوننت:**
```php
new #[Layout('layouts.app')] #[Title('اقدامات دستی')] class extends Component
{
    use Toast;

    public Order $order;

    // لغو
    public string $cancelReason = '';

    // تغییر وضعیت
    public string $newStatus = '';
    public array $availableStatuses = [];

    // تخصیص پیک
    public ?int $selectedCourierId = null;
    public array $availableCouriers = [];

    // تغییر قیمت
    public ?float $newPrice = null;

    // بازپرداخت
    public ?float $refundAmount = null;
    public string $refundReason = '';

    // ارسال مجدد پیامک
    public string $verificationType = '';

    public function mount(): void
    {
        $this->newPrice = $this->order->price;
        $this->availableStatuses = [
            'CREATED', 'SEARCHING_COURIER', 'COURIER_ASSIGNED',
            'WAITING_PICKUP', 'PICKED_UP', 'IN_TRANSIT',
            'DELIVERED', 'CANCELLED',
        ];
        $this->availableCouriers = User::role('courier')->get()->toArray();
    }

    public function cancelOrder(): void
    {
        abort_unless(auth()->user()->can('force cancel orders'), 403);

        DB::transaction(function () {
            $this->order->update([
                'cancelled_by' => 'admin',
                'cancel_reason' => $this->cancelReason,
                'cancelled_at' => now(),
            ]);
            $this->order->changeStatus('CANCELLED', auth()->id());
        });

        $this->logActivity('order.cancelled', $this->order);
        $this->success('سفارش با موفقیت لغو شد');
    }

    public function changeStatus(): void
    {
        abort_unless(auth()->user()->can('manual order actions'), 403);

        $this->order->changeStatus($this->newStatus, auth()->id());
        $this->logActivity('order.status_changed', $this->order);
        $this->success('وضعیت سفارش تغییر کرد');
    }

    public function assignCourier(): void
    {
        abort_unless(auth()->user()->can('manual order actions'), 403);

        $this->order->update([
            'courier_id' => $this->selectedCourierId,
            'assigned_at' => now(),
        ]);
        $this->order->changeStatus('COURIER_ASSIGNED', auth()->id());
        $this->logActivity('order.courier_assigned', $this->order);
        $this->success('پیک با موفقیت تخصیص یافت');
    }

    // ... سایر متدها
}
```

---

### ۲.۲. ماژول مدیریت پیک‌ها (Couriers)

**نکته مهم:** پیک‌ها توسط مدیر از داخل پنل ساخته می‌شوند. هیچ پیکی نمی‌تواند خودش ثبت‌نام کند.

#### ۲.۲.۱. لیست پیک‌ها — [`admin.couriers.index`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/couriers/⚡index/index.php`
- `resources/views/components/admin/couriers/⚡index/index.blade.php`

**قابلیت‌ها:**
- جدول با ستون‌های: نام، موبایل، کد ملی، وسیله نقلیه، شماره وسیله، وضعیت (online/offline)، امتیاز، تعداد سفارش، آخرین فعالیت
- فیلتر: وضعیت (online/offline)، وسیله نقلیه
- جستجو: نام، موبایل، کد ملی
- دکمه "پیک جدید" → هدایت به صفحه ثبت پیک
- لینک به پروفایل هر پیک
- دکمه "نقشه لحظه‌ای" → هدایت به صفحه نقشه

#### ۲.۲.۲. ثبت پیک جدید — [`admin.couriers.create`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/couriers/⚡create/create.php`
- `resources/views/components/admin/couriers/⚡create/create.blade.php`

**فرم ثبت:**
```php
new #[Layout('layouts.app')] #[Title('ثبت پیک جدید')] class extends Component
{
    use Toast;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $mobile = '';
    public string $national_code = '';
    public string $vehicle_type = '';
    public string $vehicle_number = '';

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'mobile' => 'required|string|max:20|unique:users,mobile',
            'national_code' => 'required|string|max:20|unique:courier_profiles,national_code',
            'vehicle_type' => 'required|string|max:50',
            'vehicle_number' => 'required|string|max:50',
        ]);

        DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'mobile' => $this->mobile,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('courier');

            CourierProfile::create([
                'user_id' => $user->id,
                'national_code' => $this->national_code,
                'vehicle_type' => $this->vehicle_type,
                'vehicle_number' => $this->vehicle_number,
                'status' => 'offline',
            ]);

            // ایجاد رکورد موقعیت برای پیک
            CourierCurrentLocation::create([
                'courier_id' => $user->id,
                'location' => DB::raw("ST_GeomFromText('POINT(0 0)', 4326)"),
            ]);
        });

        $this->logActivity('courier.created', $user);
        $this->success('پیک با موفقیت ثبت شد');
        $this->redirect(route('admin.couriers'));
    }
}
```

#### ۲.۲.۳. پروفایل پیک — [`admin.couriers.show`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/couriers/⚡show/show.php`
- `resources/views/components/admin/couriers/⚡show/show.blade.php`

**قابلیت‌ها:**
- اطلاعات پیک (نام، موبایل، کد ملی، وسیله نقلیه، امتیاز)
- وضعیت فعلی (online/offline) + امکان تغییر وضعیت توسط مدیر
- موقعیت لحظه‌ای روی نقشه OpenStreetMap (با استفاده از `CourierCurrentLocation`)
- تاریخچه سفارش‌های انجام‌شده توسط این پیک
- مسیرهای طی‌شده (از `courier_location_snapshots`)
- دکمه‌های اقدام: فعال/غیرفعال، ویرایش اطلاعات، حذف

#### ۲.۲.۴. نقشه لحظه‌ای پیک‌ها — [`admin.couriers.live-map`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/couriers/⚡live-map/live-map.php`
- `resources/views/components/admin/couriers/⚡live-map/live-map.blade.php`

**این ماژول از Reverb برای نمایش بلادرنگ موقعیت پیک‌ها روی نقشه استفاده می‌کند.**

**معماری بلادرنگ:**
```
اپ پیک (موبایل)
    ↓ HTTP PUT /api/courier/location  (هر N ثانیه)
    ↓
CourierLocationController@update
    ↓
CourierLocationUpdated event  (روی presence channel)
    ↓
Reverb WebSocket
    ↓
پنل مدیریت (مرورگر ادمین)
    ↓ Echo.join('presence-courier.{courierId}')
    ↓ نمایش مارکر روی نقشه + حرکت بلادرنگ
```

**قابلیت‌ها:**
- نقشه OpenStreetMap با Leaflet.js
- نمایش همه پیک‌های آنلاین با مارکر روی نقشه
- به‌روزرسانی بلادرنگ موقعیت پیک‌ها از طریق Reverb (Presence Channel)
- کلیک روی هر پیک → نمایش اطلاعات (نام، وسیله نقلیه، سفارش جاری)
- فیلتر: نمایش همه پیک‌ها / فقط پیک‌های آنلاین / فقط پیک‌های مشغول
- قابلیت drag روی نقشه برای زوم و جابجایی

**کلاس کامپوننت:**
```php
new #[Layout('layouts.app')] #[Title('نقشه لحظه‌ای پیک‌ها')] class extends Component
{
    use Toast;

    public string $filter = 'online'; // all, online, busy

    public function with(): array
    {
        $query = CourierCurrentLocation::query()
            ->with('courier.user');

        if ($this->filter === 'online') {
            $query->whereHas('courier', fn($q) => $q->where('status', 'online'));
        } elseif ($this->filter === 'busy') {
            $query->whereNotNull('order_id');
        }

        return [
            'couriers' => $query->get()->map(function ($loc) {
                // استخراج lat/lng از location (geometry)
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
            }),
        ];
    }
}
```

**جاوااسکریپت سمت کلاینت برای نقشه بلادرنگ:**
```javascript
// در resources/js/listeners/adminCourierMapListener.js
import { trackCourierLocation } from './courierLocationListener';

export function initAdminCourierMap(couriersData, mapElementId) {
    const map = L.map(mapElementId).setView([35.6892, 51.3890], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markers = {};

    // نمایش اولیه همه پیک‌ها
    couriersData.forEach(c => {
        if (c.lat && c.lng) {
            const marker = L.marker([c.lat, c.lng])
                .addTo(map)
                .bindPopup(`<b>${c.name}</b><br>وضعیت: ${c.status}<br>وسیله: ${c.vehicle}`);
            markers[c.id] = marker;
        }
    });

    // گوش دادن به به‌روزرسانی موقعیت هر پیک
    couriersData.forEach(c => {
        trackCourierLocation(c.id, (payload) => {
            if (markers[c.id]) {
                markers[c.id].setLatLng([payload.lat, payload.lng]);
            } else {
                const marker = L.marker([payload.lat, payload.lng]).addTo(map);
                markers[c.id] = marker;
            }
        });
    });
}
```

---

### ۲.۳. ماژول مدیریت پرداخت‌ها (Payments)

#### ۲.۳.۱. لیست تراکنش‌ها — [`admin.payments.index`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/payments/⚡index/index.php`
- `resources/views/components/admin/payments/⚡index/index.blade.php`

**قابلیت‌ها:**
- جدول با ستون‌های: سفارش، مشتری، مبلغ، روش (online/cash)، درگاه (zarinpal/zibal/-)، وضعیت (pending/paid)، تاریخ پرداخت، ref ID
- فیلتر: روش پرداخت، وضعیت پرداخت، درگاه، بازه تاریخ
- جستجو: شماره سفارش، ref ID
- لینک به جزئیات سفارش

#### ۲.۳.۲. جزئیات تراکنش — [`admin.payments.show`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/payments/⚡show/show.php`
- `resources/views/components/admin/payments/⚡show/show.blade.php`

**قابلیت‌ها:**
- نمایش کامل اطلاعات تراکنش (authority, refId, driver, paid_at)
- دکمه "ثبت دستی پرداخت" (برای مواردی که پرداخت خارج از سیستم انجام شده)
- دکمه "بازپرداخت" (در صورت لغو سفارش بعد از پرداخت)
- تاریخچه بازپرداخت‌ها از جدول `refunds`

---

### ۲.۴. ماژول مدیریت نظرسنجی‌ها و نظرات

#### ۲.۴.۱. نظرسنجی‌ها — [`admin.surveys.index`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/surveys/⚡index/index.php`
- `resources/views/components/admin/surveys/⚡index/index.blade.php`

**قابلیت‌ها:**
- جدول توکن‌های نظرسنجی: سفارش، نوع (customer/sender/receiver)، وضعیت (منتظر/استفاده‌شده/منقضی)، تاریخ انقضا
- فیلتر: وضعیت، نوع
- آمار: تعداد کل نظرسنجی‌ها، درصد مشارکت، میانگین امتیاز

#### ۲.۴.۲. نظرات — [`admin.reviews.index`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/reviews/⚡index/index.php`
- `resources/views/components/admin/reviews/⚡index/index.blade.php`

**قابلیت‌ها:**
- جدول نظرات: سفارش، امتیاز، نظر، نوع نظر‌دهنده، تاریخ
- امکان حذف نظر نامناسب توسط مدیر

---

### ۲.۵. ماژول مدیریت مشتریان (Customers)

#### ۲.۵.۱. لیست مشتریان — [`admin.customers.index`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/customers/⚡index/index.php`
- `resources/views/components/admin/customers/⚡index/index.blade.php`

**قابلیت‌ها:**
- جدول: نام، موبایل، ایمیل، تعداد سفارش‌ها، مجموع پرداخت‌ها، تاریخ ثبت‌نام
- جستجو: نام، موبایل، ایمیل
- لینک به پروفایل مشتری

#### ۲.۵.۲. پروفایل مشتری — [`admin.customers.show`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/customers/⚡show/show.php`
- `resources/views/components/admin/customers/⚡show/show.blade.php`

**قابلیت‌ها:**
- اطلاعات مشتری
- تاریخچه سفارش‌ها با وضعیت‌ها
- مجموع هزینه سفارش‌ها
- دکمه مسدودسازی/رفع مسدودیت

---

### ۲.۶. ماژول تنظیمات سیستم (Settings)

#### ۲.۶.۱. تنظیمات عمومی — [`admin.settings.general`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/settings/⚡general/general.php`
- `resources/views/components/admin/settings/⚡general/general.blade.php`

**فیلدها:**
- نام سایت (ذخیره در `settings` با کلید `site_name`)
- شماره پشتیبانی (`support_phone`)
- آدرس (`site_address`)

#### ۲.۶.۲. تنظیمات جستجوی پیک — [`admin.settings.courier-search`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/settings/⚡courier-search/courier-search.php`
- `resources/views/components/admin/settings/⚡courier-search/courier-search.blade.php`

**فیلدها (بر اساس [`config/courier_search.php`](config/courier_search.php)):**
- فاصله بین تلاش‌ها (ثانیه) — `courier_search_interval`
- سقف زمانی جستجو (دقیقه) — `courier_search_timeout`
- حداکثر شعاع (متر) — `courier_search_max_distance`
- مهلت پاسخ پیک (ثانیه) — `courier_offer_timeout`

#### ۲.۶.۳. تنظیمات پرداخت — [`admin.settings.payment`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/settings/⚡payment/payment.php`
- `resources/views/components/admin/settings/⚡payment/payment.blade.php`

**فیلدها (بر اساس [`config/payment.php`](config/payment.php)):**
- درگاه پیش‌فرض (zarinpal/zibal)
- کلیدهای API (فقط نمایش، چون در env هستند)
- واحد پول

#### ۲.۶.۴. تنظیمات پیامک — [`admin.settings.sms`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/settings/⚡sms/sms.php`
- `resources/views/components/admin/settings/⚡sms/sms.blade.php`

**فیلدها (بر اساس [`config/ippanel.php`](config/ippanel.php)):**
- کد پترن‌های پیامک (برای هر نوع پیامک یک فیلد جدا)
- شماره فرستنده (فقط نمایش)

---

### ۲.۷. ماژول گزارش‌گیری (Reports)

#### ۲.۷.۱. گزارش سفارش‌ها — [`admin.reports.orders`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/reports/⚡orders/orders.php`
- `resources/views/components/admin/reports/⚡orders/orders.blade.php`

**قابلیت‌ها:**
- انتخاب بازه زمانی (امروز، این هفته، این ماه، امسال، بازه دلخواه)
- کارت‌های آمار: تعداد کل سفارش‌ها، در انتظار، در حال انجام، تحویل‌شده، لغوشده
- نمودار وضعیت سفارش‌ها (نمودار دایره‌ای با Chart.js)
- نمودار روند روزانه/ماهانه (نمودار خطی)
- جدول تفکیک وضعیت‌ها
- دکمه خروجی CSV

#### ۲.۷.۲. گزارش مالی — [`admin.reports.financial`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/reports/⚡financial/financial.php`
- `resources/views/components/admin/reports/⚡financial/financial.blade.php`

**قابلیت‌ها:**
- کارت‌های آمار: مجموع درآمد (نقدی + آنلاین)، درآمد آنلاین، درآمد نقدی، تعداد تراکنش‌ها
- نمودار روند درآمد روزانه/ماهانه
- تفکیک درآمد بر اساس درگاه (zarinpal/zibal)
- تفکیک درآمد بر اساس روش (online/cash)
- دکمه خروجی CSV

#### ۲.۷.۳. گزارش پیک‌ها — [`admin.reports.couriers`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/reports/⚡couriers/couriers.php`
- `resources/views/components/admin/reports/⚡couriers/couriers.blade.php`

**قابلیت‌ها:**
- جدول رتبه‌بندی پیک‌ها: نام، تعداد سفارش، میانگین امتیاز، مجموع درآمد، مسافت طی‌شده
- فیلتر بازه زمانی
- دکمه خروجی CSV

---

### ۲.۸. ماژول لاگ فعالیت مدیران — [`admin.logs.activity`](routes/web.php)

**فایل‌ها:**
- `resources/views/components/admin/logs/⚡activity/activity.php`
- `resources/views/components/admin/logs/⚡activity/activity.blade.php`

**قابلیت‌ها:**
- جدول: مدیر، اکشن (مثلاً "لغو سفارش #42")، موضوع (نوع + ID)، IP، تاریخ
- فیلتر: مدیر، نوع اکشن، بازه تاریخ
- جستجو: متن آزاد
- صفحه‌بندی
- **نیاز به سرویس ثبت لاگ:**

```php
// app/Services/Admin/ActivityLogger.php
final class ActivityLogger
{
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
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

---

## ۳. دیاگرام معماری کلی

```mermaid
graph TD
    subgraph "پنل مدیریت Livewire + MFC"
        A[admin.orders.index] --> B[admin.orders.show]
        B --> C[admin.orders.manual-actions]
        D[admin.couriers.index] --> E[admin.couriers.show]
        D --> F[admin.couriers.create]
        D --> G[admin.couriers.live-map]
        H[admin.payments.index] --> I[admin.payments.show]
        J[admin.surveys.index] --> K[admin.surveys.show]
        L[admin.reviews.index]
        M[admin.customers.index] --> N[admin.customers.show]
        O[admin.settings.*]
        P[admin.reports.*]
        Q[admin.logs.activity]
    end

    subgraph "Reverb WebSocket"
        R[Presence Channel<br/>presence-courier.{id}]
        S[Private Channel<br/>private-order.{id}]
    end

    subgraph "API Mobile App"
        T[Courier App] -->|PUT location| U[CourierLocationController]
        U -->|dispatch| V[CourierLocationUpdated]
        V -->|broadcast| R
    end

    R -->|listen| G
    S -->|listen| B

    subgraph "Database"
        W[(orders)]
        X[(courier_profiles)]
        Y[(courier_current_locations)]
        Z[(courier_location_snapshots)]
        AA[(order_status_histories)]
        AB[(reviews)]
        AC[(survey_tokens)]
        AD[(order_verifications)]
        AE[(refunds)]
        AF[(settings)]
        AG[(admin_activity_logs)]
    end

    A --> W
    B --> W & X & Z & AA & AB & AD
    C --> W & AE
    D --> X & Y
    E --> X & Y & Z & W
    F --> X & Y
    G --> Y
    H --> W & AE
    J --> AC
    L --> AB
    M --> W
    O --> AF
    Q --> AG
```

---

## ۴. اولویت‌بندی پیاده‌سازی (Todo List)

### فاز ۱ — حیاتی (هسته عملیاتی)

| # | ماژول | کامپوننت | وابستگی |
|---|-------|----------|---------|
| 1 | Migration‌های جدید | `settings`, `refunds`, `admin_activity_logs` | هیچ |
| 2 | Permission‌های جدید | به‌روزرسانی `PermissionSeeder` | #1 |
| 3 | سرویس ActivityLogger | `app/Services/Admin/ActivityLogger.php` | #1 |
| 4 | **مدیریت سفارش‌ها** | `admin.orders.index` | #2 |
| 5 | **جزئیات سفارش + نقشه** | `admin.orders.show` | #4 |
| 6 | **اقدامات دستی مدیر** | `admin.orders.manual-actions` | #5 |
| 7 | **مدیریت پیک‌ها** | `admin.couriers.index` | #2 |
| 8 | **ثبت پیک جدید** | `admin.couriers.create` | #7 |
| 9 | **پروفایل پیک** | `admin.couriers.show` | #7 |

### فاز ۲ — مهم

| # | ماژول | کامپوننت | وابستگی |
|---|-------|----------|---------|
| 10 | **نقشه لحظه‌ای پیک‌ها** | `admin.couriers.live-map` | #7, Reverb |
| 11 | **مدیریت پرداخت‌ها** | `admin.payments.index` + `show` | #2 |
| 12 | **مدیریت مشتریان** | `admin.customers.index` + `show` | #2 |

### فاز ۳ — تکمیلی

| # | ماژول | کامپوننت | وابستگی |
|---|-------|----------|---------|
| 13 | **تنظیمات سیستم** | `admin.settings.*` | #1 |
| 14 | **نظرسنجی‌ها و نظرات** | `admin.surveys.*` + `admin.reviews.*` | #2 |
| 15 | **گزارش‌گیری** | `admin.reports.*` | #4, #7, #11 |
| 16 | **لاگ فعالیت مدیران** | `admin.logs.activity` | #3 |

---

## ۵. نکات فنی مهم

### ۵.۱. نقشه OpenStreetMap

- از کتابخانه **Leaflet.js** (متن‌باز) استفاده شود
- تایل‌ها از `https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`
- نیازی به API key نیست
- برای نمایش بلادرنگ موقعیت پیک‌ها، از Presence Channel Reverb استفاده شود

### ۵.۲. Reverb برای نقشه لحظه‌ای

- ایونت [`CourierLocationUpdated`](app/Events/Courier/CourierLocationUpdated.php) از قبل وجود دارد
- روی Presence Channel `presence-courier.{courierId}` منتشر می‌شود
- پنل مدیریت با `Echo.join('presence-courier.{courierId}')` به آن گوش می‌دهد
- برای گوش دادن به همه پیک‌ها، باید به ازای هر پیک آنلاین یک Presence Channel مجزا join شود

### ۵.۳. ثبت پیک توسط مدیر

- پیک‌ها فقط توسط مدیر از پنل ساخته می‌شوند
- نقش `courier` باید در [`RoleSeeder`](database/seeders/RoleSeeder.php) اضافه شود
- پس از ثبت، یک رکورد در `courier_profiles` و `courier_current_locations` ایجاد می‌شود
- پیک لاگین/out می‌کند و وضعیتش در `courier_profiles.status` تغییر می‌کند

### ۵.۴. استفاده از کتابخانه Mary UI

پروژه از [Mary UI](https://mary-ui.com/) برای کامپوننت‌های Blade استفاده می‌کند. در کامپوننت‌های جدید از این کامپوننت‌ها استفاده شود:
- `x-header` — عنوان صفحه
- `x-table` — جداول با مرتب‌سازی و صفحه‌بندی
- `x-card` — کارت‌ها
- `x-modal` — مودال‌ها
- `x-button` — دکمه‌ها
- `x-input` — فیلدهای ورودی
- `x-select` — dropdownها
- `x-badge` — برچسب‌ها
- `x-icon` — آیکون‌ها
- `x-toast` — نوتیفیکیشن‌ها (از طریق `Mary\Traits\Toast`)

### ۵.۵. ثبت لاگ فعالیت

همه اکشن‌های مهم مدیر باید از طریق `ActivityLogger` ثبت شوند:
- لغو سفارش
- تغییر وضعیت سفارش
- تخصیص پیک
- ثبت پیک جدید
- ویرایش کاربر
- بازپرداخت
- تغییر تنظیمات

### ۵.۶. Permission-based Access

همه روت‌ها و اکشن‌ها باید با permission gate محافظت شوند:
```php
// در کامپوننت‌ها
abort_unless(auth()->user()->can('manage orders'), 403);

// در route
Route::middleware(['auth', 'permission:manage orders'])
```

---

## ۶. خلاصه فایل‌های مورد نیاز

### Migration‌ها (۳ فایل)
1. `database/migrations/xxxx_xx_xx_xxxxxx_create_settings_table.php`
2. `database/migrations/xxxx_xx_xx_xxxxxx_create_refunds_table.php`
3. `database/migrations/xxxx_xx_xx_xxxxxx_create_admin_activity_logs_table.php`

### Models (۳ مدل جدید)
1. `app/Models/Setting.php`
2. `app/Models/Refund.php`
3. `app/Models/AdminActivityLog.php`

### Services (۱ سرویس جدید)
1. `app/Services/Admin/ActivityLogger.php`

### Livewire Components (۲۱ کامپوننت MFC)
- هر کامپوننت = ۲ فایل (`.php` + `.blade.php`) = ۴۲ فایل

### JavaScript Listeners (۱ فایل جدید)
1. `resources/js/listeners/adminCourierMapListener.js`

### به‌روزرسانی فایل‌های موجود
1. `routes/web.php` — اضافه کردن روت‌های جدید
2. `database/seeders/PermissionSeeder.php` — اضافه کردن permissions جدید
3. `database/seeders/RoleSeeder.php` — اضافه کردن نقش `courier`
4. `resources/js/app.js` — import کردن `adminCourierMapListener.js`
