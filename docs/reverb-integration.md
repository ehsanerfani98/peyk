# راهنمای یکپارچه‌سازی Laravel Reverb — پروژه پیک

## نمای کلی

Laravel Reverb یک سرور WebSocket اختصاصی برای لاراول است که ارتباط بی‌درنگ بین سرور و کلاینت‌ها را امکان‌پذیر می‌سازد. این راه‌حل جایگزین سرویس‌های شخص ثالث مانند Pusher با استفاده از پروتکل Pusher به صورت میزبانی شده توسط خودتان می‌شود.

این مستند شامل موارد زیر است:
1. تمام فایل‌های ایجاد شده/تغییر یافته
2. معماری و جریان داده
3. نحوه اجرای سرور Reverb
4. راهنمای یکپارچه‌سازی کلاینت Flutter

---

## ۱. فایل‌های ایجاد شده

### بک‌اند — رویدادهای پخش (Broadcast Events)

| فایل | کانال | نام رویداد | محموله (Payload) |
|------|---------|------------|---------|
| `app/Events/Order/OrderStatusChanged.php` | `private-order.{orderId}` | `order.status-changed` | `{ order_id, status, timestamp }` |
| `app/Events/Order/CourierOfferReceived.php` | `private-courier.{courierId}` | `courier.offer-received` | `{ order_id, pickup_address, delivery_address, price, distance_meters }` |
| `app/Events/Courier/CourierLocationUpdated.php` | `presence-courier.{courierId}` | `courier.location-updated` | `{ courier_id, lat, lng, timestamp }` |

### فرانت‌اند — جاوااسکریپت

| فایل | هدف |
|------|---------|
| `resources/js/echo.js` | راه‌اندازی Laravel Echo + Reverb با احراز هویت کوکی Sanctum |
| `resources/js/listeners/orderStatusListener.js` | `listenOrderStatus(orderId, callback)` — وضعیت سفارش به‌صورت بی‌درنگ |
| `resources/js/listeners/courierOfferListener.js` | `listenCourierOffers(courierId, callback)` — پیشنهادات سفارش جدید |
| `resources/js/listeners/courierLocationListener.js` | `trackCourierLocation(courierId, callback)` — موقعیت GPS بی‌درنگ پیک |
| `resources/js/app.js` | نقطه ورود برای وارد کردن Echo و صادر کردن تمام شنونده‌ها |

---

## ۲. فایل‌های تغییر یافته

### پیکربندی

| فایل | تغییر |
|------|--------|
| `.env` | `BROADCAST_CONNECTION=reverb` + `REVERB_APP_ID`، `REVERB_APP_KEY`، `REVERB_APP_SECRET`، `REVERB_HOST`، `REVERB_PORT`، `REVERB_SCHEME` + متغیرهای محیطی Vite |
| `config/broadcasting.php` | منتشر شده با درایور اتصال `reverb` |
| `config/reverb.php` | منتشر شده با پیکربندی سرور و اپلیکیشن Reverb |

### مجوزدهی کانال

| فایل | تغییر |
|------|--------|
| `routes/channels.php` | ۳ قانون مجوزدهی: `private-order.{orderId}`، `private-courier.{courierId}`، `presence-courier.{courierId}` |

### سرویس‌ها و کنترلرها (نقاط ارسال پخش)

| فایل | چه چیزی اضافه شده |
|------|---------------|
| `app/Services/Order/OrderFulfillmentService.php` | متد کمکی `broadcastStatusChange()` + فراخوانی بعد از `confirmPickup()` و `confirmDelivery()` |
| `app/Services/Order/CourierOfferService.php` | متد کمکی `broadcastStatusChange()` + فراخوانی بعد از `accept()` |
| `app/Services/Order/OrderCancellationService.php` | متد کمکی `broadcastStatusChange()` + فراخوانی بعد از `cancel()` |
| `app/Jobs/SearchCourierForOrderJob.php` | جایگزینی TODO پیامک با `CourierOfferReceived::dispatch()` |
| `app/Http/Controllers/Api/Courier/CourierLocationController.php` | `CourierLocationUpdated::dispatch()` بعد از هر `upsertLocation()` |

### وابستگی‌ها

| فایل | تغییر |
|------|--------|
| `composer.json` | افزودن `laravel/reverb` |
| `package.json` | افزودن `laravel-echo` و `pusher-js` |

---

## ۳. معماری و جریان داده

```
┌─────────────────────────────────────────────────────────────────┐
│                      بک‌اند لاراول                               │
│                                                                  │
│  OrderFulfillmentService ──► OrderStatusChanged ──┐              │
│  CourierOfferService ──────► OrderStatusChanged ──┤              │
│  OrderCancellationService ─► OrderStatusChanged ──┤              │
│  SearchCourierForOrderJob ─► CourierOfferReceived─┤              │
│  CourierLocationController─► CourierLocationUpdated┤             │
│                                                    ▼              │
│                                            Redis Pub/Sub         │
│                                                    │              │
└────────────────────────────────────────────────────┼──────────────┘
                                                     │
                                                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                   سرور وب‌سوکت REVERB                            │
│                  (php artisan reverb:start)                       │
│                                                                  │
│  اشتراک در Redis ──► ارسال به کلاینت‌های متصل وب‌سوکت           │
└────────────────────────────────────────────────────┼──────────────┘
                                                     │
                          ┌──────────────────────────┼──────────────────────────┐
                          │                          │                          │
                          ▼                          ▼                          ▼
                  ┌──────────────┐          ┌──────────────┐          ┌──────────────┐
                  │ اپلیکیشن مشتری│          │ اپلیکیشن پیک  │          │ پنل مدیریت   │
                  │ (Flutter/Web) │          │ (Flutter/Web) │          │   (Web)      │
                  └──────────────┘          └──────────────┘          └──────────────┘
```

### قوانین مجوزدهی کانال

| کانال | چه کسی می‌تواند گوش دهد |
|---------|---------------|
| `private-order.{orderId}` | مشتری (`customer_id`) و پیک تعیین‌شده (`courier_id`) |
| `private-courier.{courierId}` | فقط خود پیک |
| `presence-courier.{courierId}` | خود پیک + هر مشتری با سفارش فعال به این پیک |

---

## ۴. نحوه اجرا

### راه‌اندازی سرور Reverb

```bash
# توسعه (با خروجی اشکال‌زدایی)
php artisan reverb:start --debug

# تولید (اتصال به همه رابط‌ها)
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### ساخت دارایی‌های فرانت‌اند

```bash
# ساخت تولید
npm run build

# توسعه با بارگذاری مجدد خودکار
npm run dev
```

### استک کامل توسعه

```bash
# ترمینال ۱: سرور لاراول
php artisan serve

# ترمینال ۲: کارگر صف
php artisan queue:listen --tries=1

# ترمینال ۳: سرور وب‌سوکت Reverb
php artisan reverb:start --debug

# ترمینال ۴: سرور توسعه Vite
npm run dev
```

یا از اسکریپت توسعه داخلی استفاده کنید:
```bash
composer run dev
```

---

## ۵. راهنمای یکپارچه‌سازی کلاینت Flutter

### ۵.۱ وابستگی‌ها

به `pubspec.yaml` اضافه کنید:

```yaml
dependencies:
  pusher_channels_flutter: ^2.2.1    # کلاینت پروتکل Pusher برای Reverb
  http: ^1.2.0                        # برای درخواست‌های مجوزدهی کانال
  shared_preferences: ^2.2.0          # برای ذخیره توکن احراز هویت
```

### ۵.۲ راه‌اندازی اتصال Reverb

ایجاد `lib/services/reverb_service.dart`:

```dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

class ReverbService {
  static final ReverbService _instance = ReverbService._internal();
  factory ReverbService() => _instance;
  ReverbService._internal();

  late PusherChannelsFlutter _pusher;
  bool _isConnected = false;

  // پیکربندی Reverb — مطابق با مقادیر فایل .env
  static const String _appKey = 'peyk-reverb-key';
  static const String _host = '10.0.2.2'; // شبیه‌ساز اندروید → ماشین میزبان
  // static const String _host = 'localhost'; // شبیه‌ساز iOS
  static const int _port = 8080;
  static const String _scheme = 'http';

  // آدرس پایه API لاراول برای احراز هویت کانال
  static const String _authEndpoint = 'http://10.0.2.2:8000/broadcasting/auth';

  String? _authToken;

  /// مقداردهی اولیه و اتصال به Reverb.
  /// این متد را بعد از ورود کاربر و دریافت توکن Sanctum فراخوانی کنید.
  Future<void> connect({required String authToken}) async {
    _authToken = authToken;

    _pusher = PusherChannelsFlutter();

    await _pusher.init(
      apiKey: _appKey,
      cluster: '', // با Reverb استفاده نمی‌شود
      host: _host,
      port: _port,
      useTLS: _scheme == 'https',
      onConnectionStateChange: _onConnectionStateChange,
      onError: _onError,
      onSubscriptionSucceeded: _onSubscriptionSucceeded,
      onEvent: _onEvent,
      onSubscriptionError: _onSubscriptionError,
      onDecryptionFailure: _onDecryptionFailure,
      onMemberAdded: _onMemberAdded,
      onMemberRemoved: _onMemberRemoved,
      onSubscriptionCount: _onSubscriptionCount,
      // مجوزدهی سفارشی برای کانال‌های خصوصی/حضوری
      authorizer: _channelAuthorizer,
    );

    await _pusher.connect();
  }

  /// قطع اتصال از Reverb
  Future<void> disconnect() async {
    await _pusher.disconnect();
    _isConnected = false;
  }

  bool get isConnected => _isConnected;

  // ─── مجوزدهی کانال ───────────────────────────────────

  /// مجوزدهی سفارشی که درخواست POST /broadcasting/auth
  /// را با توکن Sanctum برای احراز هویت ارسال می‌کند.
  dynamic _channelAuthorizer(String channelName, String socketId) async {
    try {
      final response = await http.post(
        Uri.parse(_authEndpoint),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $_authToken',
        },
        body: jsonEncode({
          'socket_id': socketId,
          'channel_name': channelName,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('احراز هویت کانال ناموفق: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('خطای احراز هویت کانال: $e');
    }
  }

  // ─── کالبک‌ها ───────────────────────────────────────────────

  void _onConnectionStateChange(dynamic currentState, dynamic previousState) {
    _isConnected = currentState == 'CONNECTED';
    print('[Reverb] اتصال: $previousState → $currentState');
  }

  void _onError(String message, int? code, dynamic exception) {
    print('[Reverb] خطا: $message (کد: $code)');
  }

  void _onSubscriptionSucceeded(String channelName, dynamic data) {
    print('[Reverb] اشتراک در کانال: $channelName');
  }

  void _onEvent(dynamic event) {
    // از طریق شنونده‌های هر کانال مدیریت می‌شود
  }

  void _onSubscriptionError(String message, dynamic exception) {
    print('[Reverb] خطای اشتراک: $message');
  }

  void _onDecryptionFailure(String event, String reason) {
    print('[Reverb] خطای رمزگشایی: $event — $reason');
  }

  void _onMemberAdded(String channelName, dynamic member) {
    print('[Reverb] عضو اضافه شد به $channelName: $member');
  }

  void _onMemberRemoved(String channelName, dynamic member) {
    print('[Reverb] عضو حذف شد از $channelName: $member');
  }

  void _onSubscriptionCount(String channelName, int count) {
    print('[Reverb] $channelName تعداد اعضا: $count');
  }

  // ─── API عمومی ──────────────────────────────────────────────

  /// گوش دادن به تغییرات وضعیت سفارش.
  /// یک تابع برای لغو اشتراک برمی‌گرداند.
  void Function() listenOrderStatus({
    required int orderId,
    required void Function(Map<String, dynamic> payload) onStatusChanged,
  }) {
    const eventName = 'order.status-changed';
    final channelName = 'private-order.$orderId';

    _pusher.subscribe(channelName: channelName);
    _pusher.bind(eventName: eventName, callback: (dynamic data) {
      // Pusher رویداد را با پیشوند نقطه می‌پیچد
      onStatusChanged(Map<String, dynamic>.from(data));
    });

    return () {
      _pusher.unbind(eventName: eventName);
      _pusher.unsubscribe(channelName: channelName);
    };
  }

  /// گوش دادن به پیشنهادات سفارش جدید برای پیک.
  /// یک تابع برای لغو اشتراک برمی‌گرداند.
  void Function() listenCourierOffers({
    required int courierId,
    required void Function(Map<String, dynamic> payload) onOfferReceived,
  }) {
    const eventName = 'courier.offer-received';
    final channelName = 'private-courier.$courierId';

    _pusher.subscribe(channelName: channelName);
    _pusher.bind(eventName: eventName, callback: (dynamic data) {
      onOfferReceived(Map<String, dynamic>.from(data));
    });

    return () {
      _pusher.unbind(eventName: eventName);
      _pusher.unsubscribe(channelName: channelName);
    };
  }

  /// ردیابی موقعیت لحظه‌ای پیک.
  /// یک تابع برای توقف ردیابی برمی‌گرداند.
  void Function() trackCourierLocation({
    required int courierId,
    required void Function(Map<String, dynamic> payload) onLocationUpdated,
  }) {
    const eventName = 'courier.location-updated';
    final channelName = 'presence-courier.$courierId';

    _pusher.subscribe(channelName: channelName);
    _pusher.bind(eventName: eventName, callback: (dynamic data) {
      onLocationUpdated(Map<String, dynamic>.from(data));
    });

    return () {
      _pusher.unbind(eventName: eventName);
      _pusher.unsubscribe(channelName: channelName);
    };
  }
}
```

### ۵.۳ نمونه‌های استفاده در Flutter

#### اتصال در شروع برنامه (بعد از ورود)

```dart
// در بلاک احراز هویت یا main.dart بعد از ورود موفق
final reverb = ReverbService();

await reverb.connect(
  authToken: user.sanctumToken, // توکن دریافتی از POST /auth/otp/verify
);
```

#### گوش دادن به تغییرات وضعیت سفارش (اپلیکیشن مشتری)

```dart
// در صفحه جزئیات سفارش
late void Function() _unsubscribe;

@override
void initState() {
  super.initState();

  _unsubscribe = ReverbService().listenOrderStatus(
    orderId: widget.order.id,
    onStatusChanged: (payload) {
      // payload = { order_id: 42, status: "PICKED_UP", timestamp: "2026-..." }
      setState(() {
        _currentStatus = payload['status'];
      });

      // نمایش اعلان محلی یا به‌روزرسانی UI
      _showStatusNotification(payload['status']);
    },
  );
}

@override
void dispose() {
  _unsubscribe(); // پاکسازی هنگام خروج از صفحه
  super.dispose();
}
```

#### گوش دادن به پیشنهادات سفارش جدید (اپلیکیشن پیک)

```dart
// در داشبورد پیک
late void Function() _unsubscribe;

@override
void initState() {
  super.initState();

  _unsubscribe = ReverbService().listenCourierOffers(
    courierId: currentUser.id,
    onOfferReceived: (payload) {
      // payload = {
      //   order_id: 42,
      //   pickup_address: "تهران، خیابان ولیعصر...",
      //   delivery_address: "تهران، میدان ونک...",
      //   price: 150000.0,
      //   distance_meters: 3200.5
      // }

      // نمایش دیالوگ پیشنهاد با دکمه‌های پذیرش/رد
      showDialog(
        context: context,
        builder: (_) => CourierOfferDialog(
          orderId: payload['order_id'],
          pickupAddress: payload['pickup_address'],
          deliveryAddress: payload['delivery_address'],
          price: payload['price'],
          distance: payload['distance_meters'],
        ),
      );
    },
  );
}

@override
void dispose() {
  _unsubscribe();
  super.dispose();
}
```

#### ردیابی موقعیت پیک (اپلیکیشن مشتری — صفحه نقشه)

```dart
// در صفحه نقشه ردیابی زنده
late void Function() _unsubscribe;

@override
void initState() {
  super.initState();

  _unsubscribe = ReverbService().trackCourierLocation(
    courierId: widget.order.courierId,
    onLocationUpdated: (payload) {
      // payload = { courier_id: 7, lat: 35.6892, lng: 51.3890, timestamp: "..." }

      // به‌روزرسانی موقعیت نشانگر روی نقشه
      _updateCourierMarker(
        lat: payload['lat'],
        lng: payload['lng'],
      );
    },
  );
}

@override
void dispose() {
  _unsubscribe();
  super.dispose();
}
```

### ۵.۴ نکات مهم برای Flutter

۱. **شبیه‌ساز اندروید**: به جای `localhost` از `10.0.2.2` برای دسترسی به سرور Reverb ماشین میزبان استفاده کنید.

۲. **شبیه‌ساز iOS**: `localhost` مستقیماً کار می‌کند.

۳. **دستگاه فیزیکی**: از آدرس IP کامپیوتر خود استفاده کنید (مثلاً `192.168.1.100`).

۴. **توکن Sanctum**: توکن دریافتی از `POST /api/auth/otp/verify` باید به صورت `Bearer` در هدر `Authorization` برای احراز هویت کانال ارسال شود.

۵. **HTTPS در تولید**: `REVERB_SCHEME=https` را در فایل `.env` تنظیم و در کلاینت Flutter از `useTLS: true` استفاده کنید.

۶. **اتصال مجدد**: پکیج `pusher_channels_flutter` اتصال مجدد خودکار را مدیریت می‌کند. می‌توانید با گوش دادن به `onConnectionStateChange` UI را به‌روزرسانی کنید.

---

## ۶. مرجع محموله رویدادها

### `order.status-changed` (در `private-order.{orderId}`)

```json
{
  "order_id": 42,
  "status": "PICKED_UP",
  "timestamp": "2026-07-16T20:00:00.000000Z"
}
```

مقادیر احتمالی وضعیت: `CREATED`، `SEARCHING_COURIER`، `COURIER_OFFERED`، `COURIER_ACCEPTED`، `COURIER_ASSIGNED`، `WAITING_PICKUP`، `PICKED_UP`، `IN_TRANSIT`، `DELIVERED`، `CANCELLED`

### `courier.offer-received` (در `private-courier.{courierId}`)

```json
{
  "order_id": 42,
  "pickup_address": "تهران، خیابان ولیعصر، پلاک ۱۰۰",
  "delivery_address": "تهران، میدان ونک، برج نگار",
  "price": 150000.0,
  "distance_meters": 3200.5
}
```

### `courier.location-updated` (در `presence-courier.{courierId}`)

```json
{
  "courier_id": 7,
  "lat": 35.6892523,
  "lng": 51.3890427,
  "timestamp": "2026-07-16T20:00:05.000000Z"
}
```

---

## ۷. مرجع متغیرهای محیطی

```env
# پخش
BROADCAST_CONNECTION=reverb

# سرور Reverb
REVERB_APP_ID=peyk-app
REVERB_APP_KEY=peyk-reverb-key
REVERB_APP_SECRET=peyk-reverb-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite (برای فرانت‌اند وب)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```