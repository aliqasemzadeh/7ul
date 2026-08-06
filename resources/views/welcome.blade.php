<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Seven Up Link | 7UL.ir</title>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

        <style>
            body {
                font-family: 'Vazirmatn', sans-serif;
            }
            .gradient-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
        </style>
    </head>
    <body class="bg-gray-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex flex-col">

        <!-- Header/Navigation -->
        <header class="w-full max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold text-xl">7</span>
                </div>
                <span class="text-2xl font-black tracking-tight hidden sm:block">Seven Up <span class="text-indigo-600">Link</span></span>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-4 text-sm font-medium">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 rounded-full hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">پنل کاربری</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-indigo-600 transition">ورود</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 shadow-md transition">ثبت نام</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="flex-grow flex flex-col items-center justify-center px-6 py-12">
            <!-- Hero Section -->
            <div class="text-center max-w-3xl mb-12">
                <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight">
                    لینک‌های طولانی را <span class="text-indigo-600">کوتاه</span> و هوشمند کنید
                </h1>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">
                    با Seven Up Link، به راحتی لینک‌های خود را کوتاه کنید، آمار کلیک‌ها را دنبال کنید و برند خود را تقویت کنید.
                </p>

                <!-- Shortener Box -->
                <div class="bg-white dark:bg-zinc-900 p-2 rounded-2xl shadow-2xl flex flex-col md:flex-row gap-2 border border-zinc-200 dark:border-zinc-800">
                    <input type="url" placeholder="لینک خود را اینجا وارد کنید..." class="flex-grow px-6 py-4 bg-transparent outline-none text-lg rtl" required>
                    <button class="bg-indigo-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-indigo-700 transition shadow-lg flex items-center justify-center gap-2">
                        <span>کوتاه کن</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 14.14 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <p class="mt-4 text-xs text-zinc-500">با استفاده از خدمات ما، شما <a href="#" class="underline">قوانین و مقررات</a> ما را می‌پذیرید.</p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl w-full mt-12">
                <div class="p-8 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-100 dark:border-zinc-800 shadow-sm hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">آنالیز دقیق</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">تعداد کلیک‌ها، موقعیت جغرافیایی و دستگاه‌های کاربران را به صورت زنده مشاهده کنید.</p>
                </div>

                <div class="p-8 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-100 dark:border-zinc-800 shadow-sm hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">سرعت فوق‌العاده</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">لینک‌های ما با کمترین تاخیر ممکن کاربران را به مقصد هدایت می‌کنند.</p>
                </div>

                <div class="p-8 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-100 dark:border-zinc-800 shadow-sm hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">امن و مطمئن</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">امنیت لینک‌ها و حریم خصوصی شما اولویت اول ما در سون‌آپ لینک است.</p>
                </div>
            </div>
        </main>

        <footer class="w-full max-w-7xl mx-auto px-6 py-12 border-t border-zinc-200 dark:border-zinc-800 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex flex-col items-center md:items-start gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded flex items-center justify-center">
                        <span class="text-white font-bold text-sm">7</span>
                    </div>
                    <span class="font-bold">Seven Up Link</span>
                </div>
                <p class="text-sm text-zinc-500">تمامی حقوق برای 7UL.ir محفوظ است. &copy; {{ date('Y') }}</p>
            </div>

            <div class="flex gap-8 text-sm text-zinc-600 dark:text-zinc-400">
                <a href="#" class="hover:text-indigo-600 transition">درباره ما</a>
                <a href="#" class="hover:text-indigo-600 transition">تماس با ما</a>
                <a href="#" class="hover:text-indigo-600 transition">API</a>
                <a href="#" class="hover:text-indigo-600 transition">وبلاگ</a>
            </div>
        </footer>
    </body>
</html>
