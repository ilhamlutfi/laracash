<!DOCTYPE html>
<html lang="id" x-data="appTheme()" :class="{ 'dark': isDark }" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'CashApp' }} — CashApp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 font-sans antialiased transition-colors duration-300">

    {{-- Desktop Sidebar + Mobile Bottom Nav --}}
    <div class="flex h-full">

        {{-- ===== DESKTOP SIDEBAR ===== --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 z-40">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center shadow-lg shadow-brand-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">CashApp</span>
            </div>

            {{-- Nav Links --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @php
                    $navItems = [
                        ['route' => 'dashboard',    'icon' => 'home',       'label' => 'Dashboard'],
                        ['route' => 'transactions', 'icon' => 'arrows',     'label' => 'Transaksi'],
                        ['route' => 'archive',      'icon' => 'archive',    'label' => 'Arsip Bulanan'],
                        ['route' => 'wallets',      'icon' => 'wallet',     'label' => 'Dompet'],
                        ['route' => 'goals',        'icon' => 'target',     'label' => 'Target Tabungan'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
                           'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400' => request()->routeIs($item['route']),
                           'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' => !request()->routeIs($item['route']),
                       ])>
                        {{-- <x-icon :name="$item['icon']" class="w-5 h-5" /> --}}
                            <i class="fas fa-book w-5 h-5"></i>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Bottom: User + Dark Mode --}}
            <div class="px-3 py-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
                {{-- Dark Mode Toggle --}}
                <button @click="toggle()" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <span x-show="!isDark" class="w-5 h-5">🌙</span>
                    <span x-show="isDark"  class="w-5 h-5">☀️</span>
                    <span x-text="isDark ? 'Mode Terang' : 'Mode Gelap'"></span>
                </button>

                {{-- User Info --}}
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ===== MAIN CONTENT ===== --}}
        <main class="flex-1 lg:pl-64 min-h-full pb-20 lg:pb-0">
            {{ $slot }}
        </main>
    </div>

    {{-- ===== MOBILE BOTTOM NAVIGATION ===== --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 safe-bottom">
        <div class="flex items-center justify-around px-2 py-2">
            @php
                $mobileNav = [
                    ['route' => 'dashboard',    'icon' => 'home',    'label' => 'Home'],
                    ['route' => 'transactions', 'icon' => 'swap',    'label' => 'Transaksi'],
                    ['route' => 'archive',      'icon' => 'archive', 'label' => 'Arsip'],
                    ['route' => 'wallets',      'icon' => 'wallet',  'label' => 'Dompet'],
                    ['route' => 'goals',        'icon' => 'target',  'label' => 'Target'],
                ];
            @endphp

            @foreach($mobileNav as $item)
                <a href="{{ route($item['route']) }}"
                   @class([
                       'flex flex-col items-center gap-0.5 px-3 py-1 rounded-xl transition-all',
                       'text-brand-600 dark:text-brand-400' => request()->routeIs($item['route']),
                       'text-slate-400 dark:text-slate-500' => !request()->routeIs($item['route']),
                   ])>
                    {{-- <x-icon :name="$item['icon']" @class(['w-6 h-6', 'scale-110' => request()->routeIs($item['route'])]) /> --}}
                        <i class="fas fa-book w-6 h-6 {{ request()->routeIs($item['route']) ? 'scale-110' : '' }}"></i>
                    <span class="text-[10px] font-medium">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    {{-- ===== TOAST NOTIFICATION ===== --}}
    <div
        x-data="toastManager()"
        @notify.window="show($event.detail)"
        class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-red-500'"
                class="flex items-center gap-2 text-white text-sm font-medium px-4 py-3 rounded-2xl shadow-lg pointer-events-auto min-w-[200px]"
            >
                <span x-text="toast.type === 'success' ? '✓' : '✕'" class="font-bold"></span>
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts

    <script>
        function appTheme() {
            return {
                isDark: localStorage.getItem('theme') === 'dark' ||
                    (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggle() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            }
        }

        function toastManager() {
            return {
                toasts: [],
                show({ message, type = 'success' }) {
                    const id = Date.now();
                    this.toasts.push({ id, message, type, visible: true });
                    setTimeout(() => {
                        const toast = this.toasts.find(t => t.id === id);
                        if (toast) toast.visible = false;
                        setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
                    }, 3000);
                }
            }
        }
    </script>
</body>
</html>
