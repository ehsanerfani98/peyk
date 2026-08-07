<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'پیک') }} | ارسال سریع و مطمئن مرسولات</title>
    <meta name="description" content="سامانه هوشمند ارسال مرسولات با پیک؛ سفارش آنلاین، رهگیری لحظه‌ای و تحویل سریع در کمترین زمان.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-slate-800">

    {{-- ===== Navbar ===== --}}
    <header class="sticky top-0 z-50 w-full bg-white/80 backdrop-blur-md border-b border-slate-100">
        <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                {{-- Brand --}}
                <a href="/" class="flex items-center gap-2">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-purple-600 to-pink-500 text-white shadow-lg shadow-purple-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    <span class="text-2xl font-extrabold bg-gradient-to-l from-purple-600 to-pink-500 bg-clip-text text-transparent">
                        {{ config('app.name', 'پیک') }}
                    </span>
                </a>

                {{-- Desktop menu --}}
                <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#features" class="hover:text-purple-600 transition">امکانات</a>
                    <a href="#how" class="hover:text-purple-600 transition">نحوه کار</a>
                    <a href="#stats" class="hover:text-purple-600 transition">آمار</a>
                    <a href="#cta" class="hover:text-purple-600 transition">تماس</a>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-semibold text-slate-700 hover:text-purple-600 transition">
                        ورود
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center rounded-xl bg-gradient-to-l from-purple-600 to-pink-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:opacity-90 transition">
                        ثبت‌نام
                    </a>
                </div>
            </div>
        </nav>
    </header>

    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden">
        {{-- Decorative blobs --}}
        <div class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-purple-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute top-40 -right-24 h-96 w-96 rounded-full bg-pink-200/40 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                {{-- Text --}}
                <div class="text-center lg:text-right">
                    <span class="inline-flex items-center gap-2 rounded-full bg-purple-50 px-4 py-1.5 text-sm font-medium text-purple-700 ring-1 ring-purple-200">
                        <span class="h-2 w-2 rounded-full bg-purple-500 animate-pulse"></span>
                        ارسال سریع و مطمئن مرسولات
                    </span>

                    <h1 class="mt-6 text-4xl font-extrabold leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                        مرسولاتت را با
                        <span class="bg-gradient-to-l from-purple-600 to-pink-500 bg-clip-text text-transparent">پیک</span>
                        سریع‌تر برسان
                    </h1>

                    <p class="mt-6 text-lg leading-8 text-slate-600">
                        با سامانه هوشمند پیک، سفارش ارسال خود را آنلاین ثبت کنید، پیک را به‌صورت لحظه‌ای رهگیری کنید و مطمئن باشید مرسوله شما در کمترین زمان به دست مقصد می‌رسد.
                    </p>

                    <div class="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-center lg:justify-start">
                        <a href="{{ route('register') }}"
                           class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-l from-purple-600 to-pink-500 px-8 py-3.5 text-base font-bold text-white shadow-xl shadow-purple-500/30 hover:opacity-90 transition sm:w-auto">
                            شروع ارسال مرسوله
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="#how"
                           class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-8 py-3.5 text-base font-bold text-slate-700 hover:border-purple-300 hover:text-purple-600 transition sm:w-auto">
                            آشنایی با نحوه کار
                        </a>
                    </div>

                    {{-- Trust badges --}}
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 lg:justify-start">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-slate-600">تحویل در کمترین زمان</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-slate-600">رهگیری لحظه‌ای</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-slate-600">پرداخت امن آنلاین</span>
                        </div>
                    </div>
                </div>

                {{-- Visual card --}}
                <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                    <div class="rounded-3xl bg-gradient-to-br from-purple-600 to-pink-500 p-1 shadow-2xl shadow-purple-500/30">
                        <div class="rounded-[calc(1.5rem-4px)] bg-white p-6 sm:p-8">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-slate-900">سفارش ارسال</h3>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">فعال</span>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-600">مبدأ</label>
                                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-sm text-slate-500">خیابان آزادی، تهران</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-600">مقصد</label>
                                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-sm text-slate-500">میدان ونک، تهران</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between rounded-xl bg-purple-50 px-4 py-3">
                                    <span class="text-sm font-medium text-slate-600">هزینه ارسال</span>
                                    <span class="text-lg font-bold text-purple-700">۴۵,۰۰۰ تومان</span>
                                </div>
                            </div>

                            <button class="mt-6 w-full rounded-xl bg-gradient-to-l from-purple-600 to-pink-500 py-3 text-sm font-bold text-white shadow-lg shadow-purple-500/30 hover:opacity-90 transition">
                                ثبت سفارش
                            </button>
                        </div>
                    </div>

                    {{-- Floating status card --}}
                    <div class="absolute -bottom-6 -right-4 sm:-right-8 flex items-center gap-3 rounded-2xl bg-white p-4 shadow-xl ring-1 ring-slate-100">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs text-slate-500">پیک در راه است</p>
                            <p class="text-sm font-bold text-slate-900">رسیدن در ۱۵ دقیقه</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Features ===== --}}
    <section id="features" class="bg-slate-50 py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-sm font-bold text-purple-600">امکانات پیک</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900 sm:text-4xl">هر آنچه برای ارسال نیاز دارید</h2>
                <p class="mt-4 text-lg text-slate-600">تجربه‌ای ساده، سریع و مطمئن از ثبت سفارش تا تحویل مرسوله.</p>
            </div>

            <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Feature 1 --}}
                <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100 hover:shadow-lg hover:-translate-y-1 transition">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-100 text-purple-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">سفارش آنلاین</h3>
                    <p class="mt-3 leading-7 text-slate-600">در چند ثانیه سفارش ارسال خود را ثبت کنید؛ بدون نیاز به تماس تلفنی و در هر ساعت از شبانه‌روز.</p>
                </div>

                {{-- Feature 2 --}}
                <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100 hover:shadow-lg hover:-translate-y-1 transition">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-pink-100 text-pink-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">رهگیری لحظه‌ای</h3>
                    <p class="mt-3 leading-7 text-slate-600">موقعیت پیک را به‌صورت زنده روی نقشه دنبال کنید و از وضعیت مرسوله خود در هر لحظه باخبر باشید.</p>
                </div>

                {{-- Feature 3 --}}
                <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100 hover:shadow-lg hover:-translate-y-1 transition">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">پرداخت امن</h3>
                    <p class="mt-3 leading-7 text-slate-600">پرداخت آنلاین از طریق درگاه‌های معتبر و امن؛ بدون نگرانی از امنیت اطلاعات مالی خود.</p>
                </div>

                {{-- Feature 4 --}}
                <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100 hover:shadow-lg hover:-translate-y-1 transition">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">ارسال سریع</h3>
                    <p class="mt-3 leading-7 text-slate-600">با شبکه گسترده پیک‌های فعال، مرسوله شما در کوتاه‌ترین زمان ممکن به مقصد می‌رسد.</p>
                </div>

                {{-- Feature 5 --}}
                <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100 hover:shadow-lg hover:-translate-y-1 transition">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">پیک‌های حرفه‌ای</h3>
                    <p class="mt-3 leading-7 text-slate-600">پیک‌های ما آموزش‌دیده و مورد اعتماد هستند تا مرسوله شما با امنیت کامل تحویل داده شود.</p>
                </div>

                {{-- Feature 6 --}}
                <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100 hover:shadow-lg hover:-translate-y-1 transition">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">پشتیبانی ۲۴ ساعته</h3>
                    <p class="mt-3 leading-7 text-slate-600">تیم پشتیبانی ما در تمام ساعات شبانه‌روز آماده پاسخگویی به سوالات و مشکلات شماست.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== How it works ===== --}}
    <section id="how" class="py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-sm font-bold text-purple-600">نحوه کار</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900 sm:text-4xl">ارسال در ۳ قدم ساده</h2>
                <p class="mt-4 text-lg text-slate-600">از ثبت سفارش تا تحویل مرسوله، همه‌چیز ساده و سریع است.</p>
            </div>

            <div class="mt-14 grid gap-8 md:grid-cols-3">
                {{-- Step 1 --}}
                <div class="relative rounded-2xl border border-slate-100 bg-white p-8 text-center shadow-sm">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-600 to-pink-500 text-2xl font-extrabold text-white shadow-lg shadow-purple-500/30">۱</span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">ثبت سفارش</h3>
                    <p class="mt-3 leading-7 text-slate-600">مبدأ و مقصد را وارد کنید و سفارش ارسال خود را به‌صورت آنلاین ثبت کنید.</p>
                </div>

                {{-- Step 2 --}}
                <div class="relative rounded-2xl border border-slate-100 bg-white p-8 text-center shadow-sm">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-600 to-pink-500 text-2xl font-extrabold text-white shadow-lg shadow-purple-500/30">۲</span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">یافتن پیک</h3>
                    <p class="mt-3 leading-7 text-slate-600">سامانه به‌صورت هوشمند نزدیک‌ترین پیک در دسترس را برای مرسوله شما انتخاب می‌کند.</p>
                </div>

                {{-- Step 3 --}}
                <div class="relative rounded-2xl border border-slate-100 bg-white p-8 text-center shadow-sm">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-600 to-pink-500 text-2xl font-extrabold text-white shadow-lg shadow-purple-500/30">۳</span>
                    <h3 class="mt-6 text-xl font-bold text-slate-900">تحویل مرسوله</h3>
                    <p class="mt-3 leading-7 text-slate-600">پیک مرسوله شما را تحویل می‌گیرد و در کمترین زمان به مقصد می‌رساند.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Stats ===== --}}
    <section id="stats" class="bg-gradient-to-l from-purple-700 to-pink-600 py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 text-center text-white sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-4xl font-extrabold">+۵۰,۰۰۰</p>
                    <p class="mt-2 text-sm font-medium text-purple-100">مرسوله ارسال‌شده</p>
                </div>
                <div>
                    <p class="text-4xl font-extrabold">+۲,۵۰۰</p>
                    <p class="mt-2 text-sm font-medium text-purple-100">پیک فعال</p>
                </div>
                <div>
                    <p class="text-4xl font-extrabold">۹۸٪</p>
                    <p class="mt-2 text-sm font-medium text-purple-100">رضایت مشتریان</p>
                </div>
                <div>
                    <p class="text-4xl font-extrabold">۱۵ دقیقه</p>
                    <p class="mt-2 text-sm font-medium text-purple-100">میانگین زمان تحویل</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== CTA ===== --}}
    <section id="cta" class="py-20 lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-14 text-center shadow-2xl sm:px-14">
                <div class="pointer-events-none absolute -top-20 -right-20 h-64 w-64 rounded-full bg-purple-500/30 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-pink-500/30 blur-3xl"></div>

                <div class="relative">
                    <h2 class="text-3xl font-extrabold text-white sm:text-4xl">همین حالا ارسال را شروع کنید</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-slate-300">همین امروز حساب کاربری خود را بسازید و از ارسال سریع و مطمئن مرسولات لذت ببرید.</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('register') }}"
                           class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-l from-purple-500 to-pink-500 px-8 py-3.5 text-base font-bold text-white shadow-xl hover:opacity-90 transition sm:w-auto">
                            ثبت‌نام رایگان
                        </a>
                        <a href="{{ route('login') }}"
                           class="inline-flex w-full items-center justify-center rounded-xl border border-slate-600 px-8 py-3.5 text-base font-bold text-white hover:bg-slate-800 transition sm:w-auto">
                            ورود به حساب
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Footer ===== --}}
    <footer class="border-t border-slate-100 bg-slate-50 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-purple-600 to-pink-500 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    <span class="text-xl font-extrabold bg-gradient-to-l from-purple-600 to-pink-500 bg-clip-text text-transparent">
                        {{ config('app.name', 'پیک') }}
                    </span>
                </div>

                <p class="text-sm text-slate-500">© {{ date('Y') }} {{ config('app.name', 'پیک') }} — تمامی حقوق محفوظ است.</p>

                <div class="flex items-center gap-4">
                    <a href="#" class="text-slate-400 hover:text-purple-600 transition" aria-label="اینستاگرام">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-purple-600 transition" aria-label="تلگرام">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 01-1.93.07 4.28 4.28 0 004 2.98 8.521 8.521 0 01-5.33 1.84v.02c0 2.42 1.72 4.44 4 4.9-.63.17-1.29.26-1.97.26-.48 0-.95-.05-1.4-.14.96 3 3.75 5.18 7.05 5.24 2.58 0 4.88-1.05 6.55-2.75 1.67-1.7 2.6-3.96 2.6-6.5 0-.1 0-.2-.01-.3.9-.65 1.68-1.46 2.3-2.38z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
