<nav x-data="{ open: false }" class="bg-gray-800 border-b border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}">
                        <img src="{{ asset('storage/images/logo.png') }}" alt="Craftify Logo" class="block h-12 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @php
                        $isHomeActive = request()->routeIs('welcome');
                    @endphp
                    <a href="{{ route('welcome') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none text-gray-300 hover:text-white focus:text-white"> {{-- Removed border classes from <a> --}}
                        <span class="{{ $isHomeActive ? 'border-b-2 border-white focus:border-white' : 'border-b-2 border-transparent hover:border-gray-700 focus:border-gray-700' }}"> {{-- Apply border to span --}}
                            {{ __('Home') }}
                        </span>
                    </a>

                    <!-- Projects Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        @php
                            $isProjectsActive = request()->routeIs('public.index') || (Auth::check() && request()->routeIs('projects.index'));
                        @endphp
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none text-gray-300 hover:text-white focus:text-white"> {{-- Removed border classes from <button> --}}
                                    <span class="{{ $isProjectsActive ? 'border-b-2 border-white focus:border-white' : 'border-b-2 border-transparent hover:border-gray-700 focus:border-gray-700' }}"> {{-- Apply border to span --}}
                                        {{ __('Projects') }}
                                    </span>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('public.index')">
                                    {{ __('Public Projects') }}
                                </x-dropdown-link>
                                @auth
                                    <x-dropdown-link :href="route('projects.index')">
                                        {{ __('My Projects') }}
                                    </x-dropdown-link>
                                @endauth
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Management Dropdown -->
                    @if(Auth::check() && Auth::user()->is_admin)
                        @php
                            $isManagementActive = request()->routeIs('admin.users.index') || request()->routeIs('admin.projects.index') || request()->routeIs('tools.index') || request()->routeIs('categories.index');
                        @endphp
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none text-gray-300 hover:text-white focus:text-white"> {{-- Removed border classes from <button> --}}
                                        <span class="{{ $isManagementActive ? 'border-b-2 border-white focus:border-white' : 'border-b-2 border-transparent hover:border-gray-700 focus:border-gray-700' }}"> {{-- Apply border to span --}}
                                            {{ __('Management') }}
                                        </span>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.users.index')">{{ __('User Management') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.projects.index')">{{ __('Project Management') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('tools.index')">{{ __('Manage Tools') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('categories.index')">{{ __('Manage Categories') }}</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Side Of Navbar -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Language Switcher -->
                <div class="flex items-center">
                    <a href="{{ route('language.switch', 'en') }}" class="text-sm font-semibold {{ app()->getLocale() == 'en' ? 'text-white underline' : 'text-gray-400 hover:text-white' }}">EN</a>
                    <span class="mx-1 text-gray-600">|</span>
                    <a href="{{ route('language.switch', 'lv') }}" class="text-sm font-semibold {{ app()->getLocale() == 'lv' ? 'text-white underline' : 'text-gray-400 hover:text-white' }}">LV</a>
                </div>

                <!-- Profile Dropdown / Login -->
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="ml-4 flex items-center">
                                <img src="{{ Auth::user()->profile_image_url }}" alt="{{ Auth::user()->name }}" class="h-8 w-8 rounded-full object-cover">
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="ml-4 text-sm text-gray-400 hover:text-white font-semibold">{{ __('Log in') }}</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-400 hover:text-white font-semibold">{{ __('Register') }}</a>
                    @endif
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white focus:outline-none focus:bg-gray-700 focus:text-white transition duration-150 ease-in-out">
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
            <x-responsive-nav-link :href="route('welcome')" :active="request()->routeIs('welcome')">{{ __('Home') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('public.index')" :active="request()->routeIs('public.index')">{{ __('Public Projects') }}</x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.index')">{{ __('My Projects') }}</x-responsive-nav-link>
                @if(Auth::user()->is_admin)
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">{{ __('User Management') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.projects.index')" :active="request()->routeIs('admin.projects.index')">{{ __('Project Management') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('tools.index')" :active="request()->routeIs('tools.index')">{{ __('Manage Tools') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.index')">{{ __('Manage Categories') }}</x-responsive-nav-link>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-600">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-400">{{ Auth::user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="pt-2 pb-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">{{ __('Log in') }}</x-responsive-nav-link>
                    @if (Route::has('register'))
                        <x-responsive-nav-link :href="route('register')">{{ __('Register') }}</x-responsive-nav-link>
                    @endif
                </div>
            @endauth
        </div>
    </div>
</nav>
