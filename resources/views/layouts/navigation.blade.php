<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @auth
        @php
            $unreadNotificationCount = Auth::user()->unreadNotifications->count();
        @endphp
    @endauth

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('books.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('books.index')" :active="request()->routeIs('books.index')">
                        {{ __('書籍一覧') }}
                    </x-nav-link>
                    <x-nav-link :href="route('ranking.index')" :active="request()->routeIs('ranking.index')">
                        {{ __('ランキング') }}
                    </x-nav-link>
                    <x-nav-link :href="route('books.create')" :active="request()->routeIs('books.create')">
                        {{ __('書籍登録') }}
                    </x-nav-link>
                    <x-nav-link :href="route('favorites.index')" :active="request()->routeIs('favorites.index')">
                        {{ __('お気に入り') }}
                    </x-nav-link>
                    <x-nav-link :href="route('genres.index')" :active="request()->routeIs('genres.*')">
                        {{ __('ジャンル管理') }}
                    </x-nav-link>
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        {{ __('マイレポート') }}
                    </x-nav-link>
                    <x-nav-link :href="route('reading-plans.index')" :active="request()->routeIs('reading-plans.*')">
                        {{ __('読書計画') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <!-- 通知ベルアイコン -->
                    <!-- ★ここから差し替え -->
                    <div x-data="{ 
                        isOpen: false, 
                        notifications: [],
                        unreadCount: {{ Auth::check() ? Auth::user()->unreadNotifications->count() : 0 }},
                        async fetchNotifications() {
                            try {
                                const response = await fetch('{{ route('notifications.index') }}', { headers: { 'Accept': 'application/json' } });
                                this.notifications = await response.json();
                                this.unreadCount = this.notifications.filter(n => !n.read_at).length;
                            } catch (error) { console.error('通知の取得に失敗しました', error); }
                        },
                        async markAsRead(id) {
                            try {
                                const response = await fetch(`/notifications/${id}/read`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                });
                                if (response.ok) {
                                    this.notifications = this.notifications.map(n => n.id === id ? { ...n, read_at: new Date().toISOString() } : n);
                                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                                }
                            } catch (error) { console.error('既読化に失敗しました', error); }
                        }
                    }" @click.away="isOpen = false" class="relative inline-block text-left me-2">
                        <button @click="isOpen = !isOpen; if(isOpen) fetchNotifications()" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition">
                            <svg xmlns="http://w3.org" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full"></span>
                        </button>
                        <div x-show="isOpen" x-transition class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden z-50" style="display: none;">
                            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100 flex justify-between items-center"><span class="font-semibold text-sm text-gray-700">通知一覧</span><a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">すべて見る</a></div>
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                <template x-if="notifications.length === 0"><div class="p-6 text-center text-sm text-gray-400">通知はありません。</div></template>
                                <template x-for="notif in notifications" :key="notif.id">
                                    <div :class="notif.read_at ? 'bg-white' : 'bg-blue-50/40'" class="relative p-4 flex items-start hover:bg-gray-50 transition-colors">
                                        <template x-if="!notif.read_at"><span :class="{'bg-blue-500': notif.data.timing === 'three_days_before', 'bg-yellow-500': notif.data.timing === 'on_due_date', 'bg-red-500': notif.data.timing === 'three_days_after', 'bg-gray-300': !['three_days_before', 'on_due_date', 'three_days_after'].includes(notif.data.timing)}" class="absolute inset-y-0 left-0 w-1"></span></template>
                                        <div class="flex-shrink-0">
                                            <span :class="{'bg-blue-100 text-blue-600': !notif.read_at && notif.data.timing === 'three_days_before', 'bg-yellow-100 text-yellow-700': !notif.read_at && notif.data.timing === 'on_due_date', 'bg-red-100 text-red-600': !notif.read_at && notif.data.timing === 'three_days_after', 'bg-gray-100 text-gray-400': notif.read_at || !['three_days_before', 'on_due_date', 'three_days_after'].includes(notif.data.timing)}" class="inline-flex h-9 w-9 items-center justify-center rounded-full">
                                                <template x-if="notif.data.timing === 'three_days_before'"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></template>
                                                <template x-if="notif.data.timing === 'on_due_date'"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></template>
                                                <template x-if="notif.data.timing === 'three_days_after'"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></template>
                                                <template x-if="!['three_days_before', 'on_due_date', 'three_days_after'].includes(notif.data.timing)"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg></template>
                                            </span>
                                        </div>
                                        <div class="ml-3 flex-1 min-w-0">
                                            <div class="flex items-center space-x-1.5"><template x-if="!notif.read_at"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700">未読</span></template><p class="text-xs font-semibold text-gray-900 truncate" x-text="notif.data.title || '通知'"></p></div>
                                            <p class="mt-0.5 text-xs text-gray-600 line-clamp-2" x-text="notif.data.body || ''"></p>
                                            <p class="mt-1 text-[10px] text-gray-400" x-text="notif.created_at_human"></p>
                                        </div>
                                        <template x-if="!notif.read_at"><div class="ml-2 flex-shrink-0"><button @click.stop="markAsRead(notif.id)" class="text-xs font-medium text-blue-600 hover:text-blue-800 hover:underline">既読</button></div></template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <!-- ★ここまで差し替え -->


                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" novalidate>
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('ログアウト') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 underline hover:text-gray-900">ログイン</a>
                    <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline hover:text-gray-900">新規登録</a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('books.index')" :active="request()->routeIs('books.index')">
                {{ __('書籍一覧') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('ranking.index')" :active="request()->routeIs('ranking.index')">
                {{ __('ランキング') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('books.create')" :active="request()->routeIs('books.create')">
                {{ __('書籍登録') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('favorites.index')" :active="request()->routeIs('favorites.index')">
                {{ __('お気に入り') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('genres.index')" :active="request()->routeIs('genres.*')">
                {{ __('ジャンル管理') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                {{ __('マイレポート') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reading-plans.index')" :active="request()->routeIs('reading-plans.*')">
                {{ __('読書計画') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                    {{ __('通知') }}@if($unreadNotificationCount > 0) <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">{{ $unreadNotificationCount }}</span>@endif
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}" novalidate>
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('ログアウト') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('ログイン') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('新規登録') }}
                    </x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>
