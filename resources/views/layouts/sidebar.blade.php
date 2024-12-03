<div :class="{ 'block': open, 'hidden': !open }" 
@class="console.log('Sidebar class changed:', open)" class="sidebar bg-[#D5C4F3] w-64 fixed h-screen border-gray-100 transform transition-transform">
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
        <!-- Logo -->
        <div class="flex items-center">
            @if (Auth::user()->isStudent())
                <x-nav-link :href="route('students.dashboard')" :active="request()->routeIs('student.dashboard')">
                    <img src="{{ asset('image.png') }}" class="block h-9 w-auto" alt="Application Logo">
                </x-nav-link>
            @elseif (Auth::user()->isSupervisor())
                <x-nav-link :href="route('supervisor.dashboard')" :active="request()->routeIs('supervisor.dashboard')">
                    <img src="{{ asset('image.png') }}" class="block h-9 w-auto" alt="Application Logo">
                </x-nav-link>
            @elseif (Auth::user()->isCoordinator())
                <x-nav-link :href="route('coordinator.dashboard')" :active="request()->routeIs('coordinator.dashboard')">
                    <img src="{{ asset('image.png') }}" class="block h-9 w-auto" alt="Application Logo">
                </x-nav-link>
            @endif
        </div>
        <button @click="console.log('Toggling Sidebar:', open); open = !open" class="flex items-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
        
    </div>

    <!-- Sidebar Content -->
<!-- Sidebar Content -->
<div class="flex-1 px-4 pb-4">
    <nav class="flex flex-col mt-4 space-y-2">
        @if (Auth::check() && Auth::user()->isStudent())
            <div @click.stop>
            <x-nav-link 
                    :href="route('students.dashboard')" 
                    :active="request()->routeIs('student.dashboard')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
                           hover:bg-gray-200 {{ request()->routeIs('student.dashboard') ? 'text-black bg-blue-600' : 'text-gray-500' }}">
                    {{ __('Dashboard') }}
                </x-nav-link>
            </div>
            <div>
            <div @click.stop>   
            <x-nav-link 
            href="javascript:void(0)" 
             id="appointmentsNavLink"
             class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
              hover:bg-gray-200 {{ request()->routeIs('students.appointments.index') ? 'text-white bg-blue-600' : '' }}">
        {{ __('Appointments') }}
        </x-nav-link>
    </a>
</div>
            <div @click.stop>
                <x-nav-link 
                    :href="route('students.projects.index_all')" 
                    :active="request()->routeIs('students.projects.index_all')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
                           hover:bg-gray-200 {{ request()->routeIs('students.projects.index_all') ? 'text-black bg-blue-600' : 'text-gray-500' }}">
                    {{ __('Projects') }}
                </x-nav-link>
            </div>
            <div @click.stop>
                <x-nav-link 
                    :href="route('students.projects.my_project')" 
                    :active="request()->routeIs('students.projects.my_project')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
                           hover:bg-gray-200 {{ request()->routeIs('students.projects.my_project') ? 'text-black bg-blue-600' : 'text-gray-500' }}">
                    {{ __('MyProject') }}
                </x-nav-link>
            </div>
            <div @click.stop>
                <x-nav-link 
                    :href="route('students.logbook.index')" 
                    :active="request()->routeIs('students.logbook.index')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
                           hover:bg-gray-200 {{ request()->routeIs('students.logbook.index') ? 'text-black bg-blue-600' : 'text-gray-500' }}">
                    {{ __('Logbook') }}
                </x-nav-link>
            </div>
        @elseif (Auth::check() && Auth::user()->isSupervisor())
            <div @click.stop>
                <x-nav-link 
                    :href="route('supervisor.dashboard')" 
                    :active="request()->routeIs('supervisor.dashboard')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out  w-full text:black
                           hover:bg-gray-200 {{ request()->routeIs('supervisor.dashboard') ? 'text-white bg-white' : '' }}">
                    {{ __('Dashboard') }}
                </x-nav-link>
            </div>
            <div @click.stop>
                <x-nav-link 
                     href="javascript:void(0)" 
             id="appointmentsNavLink"
                    
                    :active="request()->routeIs('slots.index')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full 
                           hover:bg-gray-200 {{ request()->routeIs('slots.index') ? 'text-white bg-blue-600' : '' }}">
                    {{ __('Appointments') }}
                </x-nav-link>
            </div>
            <div @click.stop>
                <x-nav-link 
                    :href="route('supervisor.students.projects.index')" 
                    :active="request()->routeIs('supervisor.students.projects.index')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
                           hover:bg-gray-200 {{ request()->routeIs('supervisor.students.projects.index') ? 'text-white bg-blue-600' : '' }}">
                    {{ __('Students') }}
                </x-nav-link>
            </div>
            <div @click.stop>
                <x-nav-link 
                    :href="route('supervisor.projects.index')" 
                    :active="request()->routeIs('supervisor.projects.index')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
                           hover:bg-gray-200 {{ request()->routeIs('supervisor.projects.index') ? 'text-white bg-blue-600' : '' }}">
                    {{ __('Projects') }}
                </x-nav-link>
            </div>
            @if (Auth::user()->supervisor && Auth::user()->supervisor->is_coordinator)
                <div @click.stop>
                    <x-nav-link 
                        :href="route('coordinator.dashboard')" 
                        :active="request()->routeIs('coordinator.dashboard')"
                        class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
                               hover:bg-gray-200 {{ request()->routeIs('coordinator.dashboard') ? 'text-white bg-blue-600' : '' }}">
                        {{ __('Coordinator Dashboard') }}
                    </x-nav-link>
                </div>
            @endif
        @elseif (Auth::check() && Auth::user()->isAdmin())
            <div @click.stop>
                <x-nav-link 
                    :href="route('admin.index')" 
                    :active="request()->routeIs('admin.index')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full 
                           hover:bg-gray-200 {{ request()->routeIs('admin.index') ? 'text-white bg-blue-600' : '' }}">
                    {{ __('Manage Users') }}
                </x-nav-link>
            </div>
            <div @click.stop>
                <x-nav-link 
                    :href="route('feedback.index')" 
                    :active="request()->routeIs('feedback.index')"
                    class="font-bold p-2 rounded-lg transition duration-150 ease-in-out w-full
                           hover:bg-gray-200 {{ request()->routeIs('feedback.index') ? 'text-white bg-blue-600' : '' }}">
                    {{ __('Manage Feedback') }}
                </x-nav-link>
            </div>
        @endif
    </nav>
</div>



    <!-- Sidebar Footer -->
    <div class="px-4 py-4 border-t border-gray-100">
        <div class="text-sm text-gray-500">
            UMCONNECT @2024
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let googleAuthShown = {{ session('google_auth_shown') ? 'true' : 'false' }};
        const userRole = "{{ Auth::user()->role ?? '' }}"; // Get the current user's role

        console.log('Initial googleAuthShown:', googleAuthShown);
        console.log('User Role:', userRole);

        const appointmentsLink = document.getElementById('appointmentsNavLink');
        appointmentsLink.addEventListener('click', function (event) {
            if (!googleAuthShown) {
                console.log('Google Auth modal will be shown.');
                event.preventDefault(); // Prevent navigation

                // Show the Google Auth modal
                const modal = new bootstrap.Modal(document.getElementById('googleAuthModal'));
                modal.show();

                // Update session to prevent showing the modal again
                fetch("{{ route('session.update') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ google_auth_shown: true })
                }).then(response => {
                    if (response.ok) {
                        console.log('Session updated successfully.');
                        googleAuthShown = true; // Update the flag locally
                    } else {
                        console.error('Failed to update session.');
                    }
                });
            } else {
                console.log('Google Auth modal already shown, navigating directly.');
                // Navigate based on the user's role
                if (userRole === 'supervisor') {
                    window.location.href = "{{ route('slots.index') }}";
                } else {
                    window.location.href = "{{ route('students.appointments.index') }}";
                }
            }
        });
    });
</script>


<style>
.sidebar {
    transform: translateX(-90%);
    transition: transform 0.3s ease-in-out;
}
.sidebar.block {
    transform: translateX(0);
}
</style>
