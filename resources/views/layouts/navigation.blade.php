<nav class="bg-[#584f7a] border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Section: Hamburger and Logo -->
            <div class="flex items-center">
                <!-- Hamburger Menu -->
                <button @click="console.log('Toggling Sidebar:', open); open = !open" class="flex items-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        
                    </svg>
                </button>

                <!-- Logo -->
                <div class="shrink-0 flex items-center ml-3">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('image.png') }}" class="block h-9 w-auto" alt="Application Logo">
                    </a>
                </div>
            </div>

            <!-- Right Section: Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Notification Bell -->
                <div class="dropdown me-3">
                    <a class="nav-link dropdown-toggle" href="#" role="button" id="notificationBell" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        @if(auth()->user()->unreadNotifications->count())
                            <span class="badge badge-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationBell">
                        <form action="{{ route('notifications.markAsRead') }}" method="POST">
                            @csrf
                            @foreach(auth()->user()->unreadNotifications as $notification)
                                <a class="dropdown-item">
                                    @if (isset($notification->data['message']))
                                        {{ $notification->data['message'] }}
                                    @else
                                        @if (isset($notification->data['status']) && $notification->data['status'] === 'cancelled')
                                            Appointment on {{ $notification->data['date'] }} has been cancelled. Reason: {{ $notification->data['request_reason'] }}
                                        @elseif (isset($notification->data['status']) && $notification->data['status'] === 'accepted')
                                            Your appointment request for {{ $notification->data['date'] }} at {{ $notification->data['start_time'] }} has been accepted.
                                        @elseif (isset($notification->data['status']) && $notification->data['status'] === 'declined')
                                            Your appointment request for {{ $notification->data['date'] }} at {{ $notification->data['start_time'] }} has been declined. Reason: {{ $notification->data['request_reason'] }}
                                        @else
                                            You have an upcoming appointment on {{ $notification->data['date'] }} at {{ $notification->data['start_time'] }}.
                                        @endif
                                    @endif
                                </a>
                            @endforeach
                            <div class="dropdown-divider"></div>
                            <button type="submit" class="dropdown-item text-center">Mark all as read</button>
                        </form>
                    </div>
                </div>

                <!-- Feedback Icon -->
                <div class="ms-3">
                    @if (Auth::user()->role === 'admin')
                        <a href="{{ route('feedback.index') }}" class="nav-link" title="Manage Feedback">
                            <i class="fas fa-comment-dots text-gray-500 hover:text-blue-600"></i>
                        </a>
                    @else
                        <a href="{{ route('feedback.create') }}" class="nav-link" title="Submit Feedback">
                            <i class="fas fa-comment-dots text-gray-500 hover:text-blue-600"></i>
                        </a>
                    @endif
                </div>

                <!-- User Dropdown -->
                <x-dropdown width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 011.414 0L10 10.586l3.293-3.293a1 1 011.414 1.414l-4 4a1 1 01-1.414 0l-4-4a1 1 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link>
                            @if (Auth::user()->isStudent())
                                <x-responsive-nav-link :href="route('students.profile.edit')" :active="request()->routeIs('students.profile.edit')">
                                    {{ __('Profile') }}
                                </x-responsive-nav-link>
                            @elseif (Auth::user()->isSupervisor())
                                <x-responsive-nav-link :href="route('supervisor.profile.edit')" :active="request()->routeIs('supervisor.profile.edit')">
                                    {{ __('Profile') }}
                                </x-responsive-nav-link>
                            @endif
                        </x-dropdown-link>
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                         this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
