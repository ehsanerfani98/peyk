# راهنمای راه‌اندازی سوکت در فلاتر پیک (Laravel Reverb + GetX)

> این سند مکمل `reverb-integration.md` است. آن سند معماری کلی Reverb و یک نمونه‌کد عمومی Flutter
> (بر پایه `StatefulWidget` خام) را توضیح می‌دهد. اینجا همان قابلیت را طوری پیاده می‌کنیم که با
> ساختار واقعی پروژه پیک (GetX، `ApiService`، `AuthController`، `AppConfig`) هماهنگ باشد و به‌جای
> اضافه‌کردن یک لایه موازی، جایگزین/مکمل polling فعلی در `OrderDetailController` و
> `CourierHomeController` شود.

---

## ۰. جمع‌بندی تغییرات لازم روی پروژه فعلی

| فایل | نوع تغییر |
|---|---|
| `pubspec.yaml` | افزودن `pusher_channels_flutter` |
| `lib/app/config/app_config.dart` | افزودن تنظیمات Reverb (host/port/key/authEndpoint) |
| `lib/app/core/services/reverb_service.dart` | **جدید** — سرویس اتصال به Reverb (هم‌الگو با `ApiService`) |
| `lib/app/bindings/app_bindings.dart` | ثبت `ReverbService` به‌صورت permanent |
| `lib/modules/auth/auth_controller.dart` | اتصال بعد از لاگین موفق/بازیابی نشست + قطع اتصال در logout |
| `lib/modules/order_detail/order_detail_controller.dart` | جایگزینی/تکمیل polling با `listenOrderStatus` |
| `lib/modules/courier/courier_controller.dart` | افزودن `listenCourierOffers` برای دریافت آنی پیشنهاد سفارش |

---

## ۱. وابستگی

در `pubspec.yaml`:

```yaml
dependencies:
  pusher_channels_flutter: ^2.2.1
```

(`http` از قبل در پروژه استفاده می‌شود، نیازی به اضافه‌کردن مجدد نیست.)

```bash
flutter pub get
```

---

## ۲. تنظیمات — اضافه‌کردن به `AppConfig` به‌جای هاردکد کردن

نکته‌ی مهم: در سند اصلی، هاست/پورت/کلید Reverb و آدرس authEndpoint مستقیم داخل
`ReverbService` هاردکد شده‌اند (`10.0.2.2`, `peyk-reverb-key`, ...). این کار باعث می‌شود
هر بار جابه‌جایی بین شبیه‌ساز/دستگاه فیزیکی/سرور استیجینگ نیاز به ویرایش کد سرویس داشته
باشد. به‌جایش این مقادیر را کنار `baseUrl` موجود در `AppConfig` تعریف می‌کنیم تا یک‌جا
مدیریت شوند:

```dart
// lib/app/config/app_config.dart — اضافه‌کردن به کلاس AppConfig موجود

// ═══════════════════════════════════════════════
//  Reverb (WebSocket)
// ═══════════════════════════════════════════════
/// کلید اپلیکیشن Reverb — باید با REVERB_APP_KEY در .env سرور یکی باشد
static const String reverbAppKey = 'peyk-reverb-key';

/// هاست سرور Reverb.
/// - شبیه‌ساز اندروید: 10.0.2.2
/// - شبیه‌ساز iOS: 127.0.0.1
/// - دستگاه فیزیکی / سرور واقعی: IP یا دامنه واقعی سرور
/// طبق محیط زیر تعیین می‌شود؛ برای override از --dart-define استفاده کنید:
///   flutter run --dart-define=REVERB_HOST=192.168.1.100
static const String reverbHost =
    String.fromEnvironment('REVERB_HOST', defaultValue: '10.0.2.2');

static const int reverbPort =
    int.fromEnvironment('REVERB_PORT', defaultValue: 8080);

static const bool reverbUseTLS =
    bool.fromEnvironment('REVERB_USE_TLS', defaultValue: false);

/// آدرس مجوزدهی کانال‌های private/presence — همان دامنه baseUrl،
/// چون /broadcasting/auth روی همان سرور لاراول است (نه سرور Reverb)
static String get broadcastingAuthUrl {
  // baseUrl چیزی مثل http://127.0.0.1:8000/api است؛ پسوند /api را حذف می‌کنیم
  final root = baseUrl.endsWith('/api')
      ? baseUrl.substring(0, baseUrl.length - 4)
      : baseUrl;
  return '$root/broadcasting/auth';
}
```

> ⚠️ همان‌طور که در بررسی قبلی پروژه اشاره شد، `AppConfig.baseUrl` هنوز روی
> `http://127.0.0.1:8000/api` (لوکال‌هاست) است. چون `broadcastingAuthUrl` از همین مقدار
> مشتق می‌شود، وقتی `baseUrl` را برای build واقعی عوض کنید آدرس auth هم خودکار درست می‌شود —
> دیگر نیازی نیست جدا مقداردهی شود.

برای اجرای واقعی روی شبیه‌ساز/دستگاه:

```bash
# شبیه‌ساز اندروید (پیش‌فرض همین است، نیازی به دستور جدا نیست)
flutter run

# دستگاه فیزیکی روی همان شبکه محلی
flutter run --dart-define=REVERB_HOST=192.168.1.100

# استیجینگ/پروداکشن با TLS
flutter run --dart-define=REVERB_HOST=ws.peyk.app --dart-define=REVERB_PORT=443 --dart-define=REVERB_USE_TLS=true
```

---

## ۳. سرویس `ReverbService`

این سرویس را هم‌الگو با `ApiService` می‌سازیم: یک `GetxService` که توکن را از بیرون می‌گیرد
(به‌جای این‌که خودش مدیریت کند)، تا تک منبع حقیقتِ توکن همان `ApiService.authToken` باشد.

```dart
// lib/app/core/services/reverb_service.dart
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:get/get.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'package:http/http.dart' as http;
import '../../config/app_config.dart';
import 'api_service.dart';

/// سرویس اتصال به Laravel Reverb — پخش بی‌درنگ وضعیت سفارش، پیشنهاد سفارش به پیک
/// و موقعیت لحظه‌ای پیک.
/// طبق مستند 'reverb-integration.md'، سه کانال پشتیبانی می‌شود:
///   private-order.{orderId}    → order.status-changed
///   private-courier.{courierId} → courier.offer-received
///   presence-courier.{courierId} → courier.location-updated
class ReverbService extends GetxService {
  final ApiService _api = Get.find<ApiService>();

  PusherChannelsFlutter? _pusher;
  final RxBool isConnected = false.obs;

  /// از اتصال دوباره در حین اتصال قبلی جلوگیری می‌کند
  bool _connecting = false;

  // ─────────────────────────────────────────────
  //  اتصال / قطع اتصال
  // ─────────────────────────────────────────────

  /// بعد از ورود موفق (verifyOtp) یا بازیابی نشست معتبر فراخوانی شود.
  /// idempotent است — اگر از قبل متصل باشد، دوباره وصل نمی‌شود.
  Future<void> connect() async {
    if (!_api.isLoggedIn) return;
    if (isConnected.value || _connecting) return;
    _connecting = true;

    try {
      _pusher = PusherChannelsFlutter();
      await _pusher!.init(
        apiKey: AppConfig.reverbAppKey,
        cluster: '', // با Reverb استفاده نمی‌شود
        host: AppConfig.reverbHost,
        port: AppConfig.reverbPort,
        useTLS: AppConfig.reverbUseTLS,
        onConnectionStateChange: _onConnectionStateChange,
        onError: _onError,
        onSubscriptionSucceeded: (channel, data) =>
            _log('اشتراک موفق: $channel'),
        onEvent: (event) {}, // مدیریت به‌صورت per-channel در bind انجام می‌شود
        onSubscriptionError: (channel, message, e) =>
            _log('خطای اشتراک $channel: $message'),
        onDecryptionFailure: (event, reason) =>
            _log('خطای رمزگشایی $event: $reason'),
        onMemberAdded: (channel, member) {},
        onMemberRemoved: (channel, member) {},
        onSubscriptionCount: (channel, count) {},
        authorizer: _channelAuthorizer,
      );
      await _pusher!.connect();
    } catch (e) {
      _log('اتصال ناموفق: $e');
      isConnected.value = false;
    } finally {
      _connecting = false;
    }
  }

  /// در logout و در 401 (نشست منقضی) فراخوانی شود
  Future<void> disconnect() async {
    try {
      await _pusher?.disconnect();
    } catch (_) {
    } finally {
      isConnected.value = false;
      _pusher = null;
    }
  }

  @override
  void onClose() {
    disconnect();
    super.onClose();
  }

  // ─────────────────────────────────────────────
  //  مجوزدهی کانال‌های private/presence
  // ─────────────────────────────────────────────

  /// از توکن Sanctum فعلیِ ApiService استفاده می‌کند — نه یک کپی جداگانه.
  /// این یعنی اگر کاربر logout کند یا توکن عوض شود، همیشه مقدار درست ارسال می‌شود.
  dynamic _channelAuthorizer(String channelName, String socketId, dynamic options) async {
    final token = _api.authToken;
    if (token == null) {
      throw Exception('کاربر لاگین نیست — امکان اشتراک در کانال $channelName نیست');
    }
    final response = await http.post(
      Uri.parse(AppConfig.broadcastingAuthUrl),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({'socket_id': socketId, 'channel_name': channelName}),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('احراز هویت کانال ناموفق (${response.statusCode})');
  }

  void _onConnectionStateChange(dynamic current, dynamic previous) {
    isConnected.value = current == 'CONNECTED';
    _log('اتصال: $previous → $current');
  }

  void _onError(String message, int? code, dynamic exception) {
    _log('خطا: $message (کد $code)');
  }

  void _log(String message) {
    // مثل بقیه پروژه، فقط در حالت دیباگ چاپ می‌شود
    if (kDebugMode) debugPrint('🔌 [Reverb] $message');
  }

  // ─────────────────────────────────────────────
  //  API عمومی — گوش‌دادن به رویدادها
  // ─────────────────────────────────────────────

  /// گوش‌دادن به تغییر وضعیت یک سفارش خاص.
  /// یک تابع لغو اشتراک برمی‌گرداند — حتماً در onClose کنترلر صدا زده شود.
  void Function() listenOrderStatus({
    required int orderId,
    required void Function(Map<String, dynamic> payload) onStatusChanged,
  }) {
    return _listen(
      channelName: 'private-order.$orderId',
      eventName: 'order.status-changed',
      onData: onStatusChanged,
    );
  }

  /// گوش‌دادن به پیشنهاد سفارش جدید برای یک پیک خاص.
  void Function() listenCourierOffers({
    required int courierId,
    required void Function(Map<String, dynamic> payload) onOfferReceived,
  }) {
    return _listen(
      channelName: 'private-courier.$courierId',
      eventName: 'courier.offer-received',
      onData: onOfferReceived,
    );
  }

  /// ردیابی موقعیت لحظه‌ای پیک (کانال presence).
  void Function() trackCourierLocation({
    required int courierId,
    required void Function(Map<String, dynamic> payload) onLocationUpdated,
  }) {
    return _listen(
      channelName: 'presence-courier.$courierId',
      eventName: 'courier.location-updated',
      onData: onLocationUpdated,
    );
  }

  void Function() _listen({
    required String channelName,
    required String eventName,
    required void Function(Map<String, dynamic>) onData,
  }) {
    if (_pusher == null) {
      _log('هشدار: تلاش برای اشتراک در $channelName قبل از connect()');
      return () {};
    }
    _pusher!.subscribe(channelName: channelName);
    _pusher!.bind(
      eventName: eventName,
      onEvent: (event) {
        try {
          final raw = event.data;
          final data = raw is String ? jsonDecode(raw) : raw;
          onData(Map<String, dynamic>.from(data as Map));
        } catch (e) {
          _log('خطا در پردازش رویداد $eventName: $e');
        }
      },
    );
    return () {
      _pusher?.unbind(eventName: eventName);
      _pusher?.unsubscribe(channelName: channelName);
    };
  }
}
```

> نکته نسخه: امضای دقیق callbackهای `pusher_channels_flutter` (مثلاً پارامتر `onEvent` در
> `bind` که ممکن است `PusherEvent` بدهد نه `dynamic`) بین نسخه‌ها کمی فرق دارد. قبل از build
> نهایی، امضای متدها را با نسخه‌ی دقیقی که در `pubspec.lock` قفل می‌شود چک کنید
> (`flutter pub deps` یا مستندات pub.dev همان نسخه).

---

## ۴. ثبت سرویس در `AppBindings`

مثل `ApiService`، به‌صورت permanent ثبت می‌شود اما **اتصال واقعی اینجا برقرار نمی‌شود** —
چون در این مرحله هنوز مشخص نیست کاربر لاگین است یا نه. اتصال را در `AuthController`
مدیریت می‌کنیم (بخش بعد).

```dart
// lib/app/bindings/app_bindings.dart
import '../core/services/reverb_service.dart';

class AppBindings extends Bindings {
  @override
  void dependencies() {
    // ... کد فعلی (ApiService, بازیابی توکن, AuthController) بدون تغییر ...

    Get.put<ReverbService>(ReverbService(), permanent: true);
  }
}
```

---

## ۵. اتصال/قطع اتصال در چرخه‌ی احراز هویت

در `auth_controller.dart` سه نقطه باید تغییر کند: بعد از ورود موفق، بعد از بازیابی موفق
نشست، و در logout / انقضای نشست.

```dart
// lib/modules/auth/auth_controller.dart

// بالای کلاس، کنار _api:
ReverbService get _reverb => Get.find<ReverbService>();

// در verifyOtp(), دقیقاً همان‌جایی که پیام موفقیت نمایش داده می‌شود
// (بعد از AppSnackbar.success('خوش آمدید', ...))
_reverb.connect(); // fire-and-forget — نیازی نیست UI منتظرش بماند

// در _restoreSession(), داخل بلوک "نشست با موفقیت بازیابی شد"
if (currentUser.value != null) {
  _reverb.connect();
  Future.delayed(const Duration(milliseconds: 100), () { ... });
}

// در _clearAuth() یا همان‌جایی که handleAuthError/logout صدا زده می‌شود
void _clearAuth() {
  _api.setAuthToken(null);
  _reverb.disconnect();
  // ... بقیه‌ی کد فعلی بدون تغییر ...
}
```

با این کار اتصال Reverb دقیقاً هم‌زمان با معتبر بودن توکن Sanctum برقرار/قطع می‌شود و نیازی
به مدیریت جدا نیست.

---

## ۶. جایگزینی/تکمیل Polling با سوکت

### ۶.۱ صفحه جزئیات سفارش (مشترک مشتری/پیک)

`OrderDetailController` الان هر ۲۰ ثانیه با `Timer.periodic` وضعیت را poll می‌کند
(کامنت `ponytail: Bug 12`). با سوکت، بهترین حالت **poll را کاملاً حذف نکنید** — به‌عنوان
fallback نگه دارید (اگر سوکت قطع شد یا کاربر کانکشن ضعیف داشت) ولی بازه‌اش را زیاد کنید
چون سوکت بار اصلی را می‌کشد:

```dart
// lib/modules/order_detail/order_detail_controller.dart
ReverbService get _reverb => Get.find<ReverbService>();
void Function()? _unsubscribeOrderStatus;

@override
void onInit() {
  super.onInit();
  loadOrder();
  _subscribeToLiveUpdates();
  _startPolling(); // fallback — بازه را از 20s به مثلاً 60s افزایش دهید
}

void _subscribeToLiveUpdates() {
  _unsubscribeOrderStatus = _reverb.listenOrderStatus(
    orderId: orderId,
    onStatusChanged: (payload) {
      // به‌جای اعتماد کور به payload، همیشه از سرور reload کنید
      // چون رویداد فقط شامل status/timestamp است، نه کل آبجکت سفارش
      loadOrder(silent: true);
    },
  );
}

@override
void onClose() {
  _pollTimer?.cancel();
  _unsubscribeOrderStatus?.call();
  super.onClose();
}
```

### ۶.۲ داشبورد پیک — دریافت آنیِ پیشنهاد سفارش

این مهم‌ترین بخش است: در بررسی قبلی پروژه گفته شد که `CourierHomeController` اصلاً polling
ندارد و پیک باید دستی صفحه را refresh کند تا سفارش پیشنهادی را ببیند. کانال
`private-courier.{courierId}` دقیقاً همین مشکل را حل می‌کند:

```dart
// lib/modules/courier/courier_controller.dart
class CourierHomeController extends GetxController {
  final ApiService _api = Get.find<ApiService>();
  ReverbService get _reverb => Get.find<ReverbService>();
  AuthController get _auth => Get.find<AuthController>();

  void Function()? _unsubscribeOffers;

  @override
  void onInit() {
    super.onInit();
    loadActiveOrder();
    _subscribeToOffers();
  }

  void _subscribeToOffers() {
    final courierId = _auth.currentUser.value?.id;
    if (courierId == null) return;
    _unsubscribeOffers = _reverb.listenCourierOffers(
      courierId: courierId,
      onOfferReceived: (payload) {
        // سریع‌ترین کار: همان لحظه سفارش فعال را از سرور رفرش کن
        // تا activeOrder با وضعیت COURIER_OFFERED و همه‌ی فیلدهای لازم پر شود
        loadActiveOrder();
        // اختیاری: صدای اعلان یا لرزش گوشی برای جلب توجه پیک
      },
    );
  }

  @override
  void onClose() {
    _unsubscribeOffers?.call();
    super.onClose();
  }

  // ... بقیه‌ی متدهای فعلی بدون تغییر ...
}
```

> با توجه به `Bug 10` که قبلاً در همین کنترلر مستند شده بود (`Get.find` به‌جای `Get.put`
> برای جلوگیری از ثبت تکراری)، توجه کنید که `_subscribeToOffers` نباید در جایی جز
> `onInit` صدا زده شود، وگرنه با هر rebuild چند بار روی یک کانال subscribe می‌کنید.

### ۶.۳ ردیابی زنده موقعیت پیک (اختیاری، فاز بعدی)

اگر/وقتی صفحه‌ی نقشه‌ی زنده برای مشتری اضافه شد (نمایش موقعیت پیک روی نقشه در حین
`IN_TRANSIT`)، از `trackCourierLocation` در همان صفحه استفاده کنید و مارکر پیک را با
`MapController.move` (مشابه الگوی موجود در `map_picker_widget.dart`) به‌روزرسانی کنید.
این سند فعلاً فقط زیرساخت را فراهم می‌کند؛ پیاده‌سازی UI آن یک تسک جدا است.

---

## ۷. چک‌لیست تست دستی

- [ ] `php artisan reverb:start --debug` روی سرور در حال اجراست
- [ ] از شبیه‌ساز اندروید با `REVERB_HOST` پیش‌فرض (`10.0.2.2`) اتصال برقرار می‌شود
      (لاگ `🔌 [Reverb] اتصال: ... → CONNECTED` در کنسول دیده می‌شود)
- [ ] بعد از ورود، درخواست `POST /broadcasting/auth` با کد ۲۰۰ برمی‌گردد (نه ۴۰۱/۴۰۳)
- [ ] یک سفارش تست می‌سازید → پیک را از پنل/سرور offer می‌کنید → داشبورد پیک بدون
      refresh دستی سفارش را نشان می‌دهد
- [ ] تحویل‌گیری/تحویل را تایید می‌کنید → صفحه‌ی جزئیات سفارش سمت مشتری بدون رفرش دستی
      وضعیت جدید را نشان می‌دهد
- [ ] اپ را با وای‌فای خاموش می‌کنید و روباز می‌کنید → `isConnected` به `false`/`true`
      برمی‌گردد و polling fallback همچنان کار می‌کند
- [ ] logout می‌کنید → دیگر رویدادی از کانال‌های قبلی دریافت نمی‌شود (یعنی
      `unsubscribe`/`disconnect` واقعاً اجرا شده)

---

## ۸. نکات و ریسک‌های خاص این پروژه

1. **هاست 10.0.2.2 هاردکد نشود** — همان‌طور که در بخش ۲ توضیح داده شد، از
   `AppConfig.reverbHost` قابل‌override با `--dart-define` استفاده کنید، نه ثابت در کد.
2. **`print()` خام نگذارید** — در بررسی قبلی پروژه، استفاده از `print()` به‌جای
   `debugPrint()`/گیت‌شده با `kDebugMode` در `deep_link_service.dart` به‌عنوان مورد نظافتی
   علامت خورده بود؛ همان اشتباه را در `ReverbService` تکرار نکنید (کد بالا از ابتدا
   `kDebugMode` را رعایت کرده است).
3. **کانال presence نیاز به احراز هویت متفاوتی دارد** — طبق جدول مجوزدهی سند اصلی،
   `presence-courier.{courierId}` هم به خودِ پیک هم به مشتری‌های دارای سفارش فعال با آن
   پیک اجازه می‌دهد. یعنی `authorizer` باید همان endpoint را صدا بزند ولی پاسخ سرور شامل
   `channel_data` (اطلاعات عضو) هم خواهد بود — این را در تست دستی جداگانه بررسی کنید،
   چون رفتار presence channel در `pusher_channels_flutter` با private channel کمی فرق دارد.
4. **یک نمونه واحد سرویس** — چون `ReverbService` را `permanent: true` ثبت کردیم، بین
   صفحات مختلف (مثلاً وقتی هم `OrderDetailController` هم `CourierHomeController` فعال
   هستند) یک اتصال سوکت مشترک استفاده می‌شود، نه اتصال جدا برای هر صفحه — این عمداً است
   تا تعداد اتصالات هم‌زمان به سرور Reverb زیاد نشود.
5. **همیشه از payload سوکت به‌عنوان "تریگر" استفاده کنید، نه منبع نهایی داده** — همان‌طور
   که در `_subscribeToLiveUpdates` بالا نشان داده شد، بعد از دریافت رویداد دوباره
   `loadOrder()`/`loadActiveOrder()` را صدا می‌زنیم به‌جای این‌که مستقیم payload را در
   `PeykOrder` بریزیم، چون payload رویدادها (طبق بخش ۶ سند اصلی) فقط فیلدهای محدودی
   دارند (مثلاً فقط `status` و `timestamp`، نه کل آبجکت سفارش).