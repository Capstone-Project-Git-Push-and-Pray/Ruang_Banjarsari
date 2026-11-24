<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-xs">
    <!-- Primary Navigation Menu -->
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Left: Logo Desa & Nama Layanan (Pojok Paling Kiri) -->
            <div class="flex items-center shrink-0 min-w-0">
                <a href="{{ Auth::user()->isWarga() && !Auth::user()->isAktif() ? route('pendaftaran.status') : route('dashboard') }}" class="flex items-center space-x-3 group">
                    <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-9 h-9 object-contain">
                    <span class="font-bold text-gray-900 text-sm sm:text-base group-hover:text-[#1F6B4F] transition-colors whitespace-nowrap">
                        SILAFA Banjarsari
                    </span>
                </a>
            </div>

            @if (!Auth::user()->isWarga())
                <!-- Center: Navigation Links Sesuai Peran (Bagian Tengah) -->
                <div class="hidden sm:flex items-center justify-center flex-1 px-4">
                    <div class="flex items-center space-x-1.5 md:space-x-2">
                        @if (Auth::user()->isAdminDesa())
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('*.dashboard')">
                                Dashboard
                            </x-nav-link>
                            <x-nav-link :href="route('admin.kependudukan.index')" :active="request()->routeIs('admin.kependudukan.*')">
                                Data Kependudukan
                            </x-nav-link>
                            <x-nav-link :href="route('admin.pendaftaran.index')" :active="request()->routeIs('admin.pendaftaran.*')">
                                Tinjau Pendaftaran
                            </x-nav-link>
                            <x-nav-link :href="route('admin.pengaduan.index')" :active="request()->routeIs('admin.pengaduan.*')">
                                Kelola Pengaduan
                            </x-nav-link>
                            <x-nav-link :href="route('admin.aspirasi.index')" :active="request()->routeIs('admin.aspirasi.*')">
                                Kelola Aspirasi
                            </x-nav-link>
                            <x-nav-link :href="route('admin.rekapitulasi.index')" :active="request()->routeIs('admin.rekapitulasi.*')">
                                Rekapitulasi
                            </x-nav-link>
                            <x-nav-link :href="route('admin.kategori.index')" :active="request()->routeIs('admin.kategori.*')">
                                Kategori
                            </x-nav-link>
                            <x-nav-link :href="route('admin.reset-akun.index')" :active="request()->routeIs('admin.reset-akun.*')">
                                Reset Akun
                            </x-nav-link>
                        @elseif (Auth::user()->isKepalaDesa())
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('*.dashboard')">
                                Dashboard
                            </x-nav-link>
                            <x-nav-link :href="route('kepala.pengaduan.index')" :active="request()->routeIs('kepala.pengaduan.*')">
                                Monitoring Pengaduan
                            </x-nav-link>
                        @endif
                    </div>
                </div>

                <!-- Right: Settings / Profile Dropdown (Pojok Paling Kanan) -->
                <div class="hidden sm:flex items-center justify-end shrink-0">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-gray-200/80 hover:border-gray-300 text-sm leading-4 font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150 cursor-pointer shadow-2xs">
                                <div class="flex items-center space-x-2">
                                    <span class="font-semibold text-gray-900">{{ Auth::user()->nama_lengkap ?? Auth::user()->name }}</span>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-[#1F6B4F] font-semibold border border-emerald-200/60">
                                        {{ Auth::user()->role->label() }}
                                    </span>
                                </div>

                                <div class="ms-1.5 text-gray-400">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                Profil Saya
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    if (confirm('Apakah Anda yakin ingin keluar dari akun Anda?')) { this.closest('form').submit(); }">
                                    Keluar
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
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
            @endif
        </div>
    </div>

    @if (!Auth::user()->isWarga())
        <!-- Responsive Navigation Menu -->
        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                @if (Auth::user()->isAdminDesa())
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('*.dashboard')">
                        Dashboard
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.kependudukan.index')" :active="request()->routeIs('admin.kependudukan.*')">
                        Data Kependudukan
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.pendaftaran.index')" :active="request()->routeIs('admin.pendaftaran.*')">
                        Tinjau Pendaftaran
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.pengaduan.index')" :active="request()->routeIs('admin.pengaduan.*')">
                        Kelola Pengaduan
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.aspirasi.index')" :active="request()->routeIs('admin.aspirasi.*')">
                        Kelola Aspirasi
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.rekapitulasi.index')" :active="request()->routeIs('admin.rekapitulasi.*')">
                        Rekapitulasi
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.kategori.index')" :active="request()->routeIs('admin.kategori.*')">
                        Kategori
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.reset-akun.index')" :active="request()->routeIs('admin.reset-akun.*')">
                        Reset Akun
                    </x-responsive-nav-link>
                @elseif (Auth::user()->isKepalaDesa())
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('*.dashboard')">
                        Dashboard
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('kepala.pengaduan.index')" :active="request()->routeIs('kepala.pengaduan.*')">
                        Monitoring Pengaduan
                    </x-responsive-nav-link>
                @endif
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->nama_lengkap ?? Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }} ({{ Auth::user()->role->label() }})</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        Profil Saya
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            if (confirm('Apakah Anda yakin ingin keluar dari akun Anda?')) { this.closest('form').submit(); }">
                            Keluar
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    @endif
</nav>
