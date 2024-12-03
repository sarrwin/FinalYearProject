<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'UMConnect') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
          rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.9.6/dist/tagify.css" rel="stylesheet" type="text/css" />

    <!-- Third-Party Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.9.6/dist/tagify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.5/dist/cdn.min.js" defer></script>

    <!-- Laravel Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Styles -->

</head>
<body x-data="{
        open: localStorage.getItem('sidebarOpen') === 'true',
        toggleSidebar() {
            this.open = !this.open;
            localStorage.setItem('sidebarOpen', this.open);
        },
        handleNavClick(url) {
            console.log('Navigating to:', url);
            localStorage.setItem('sidebarOpen', this.open); // Preserve sidebar state
            window.location.href = url; // Perform navigation
        }
    }" class="font-sans antialiased">
    <!-- Main Wrapper -->
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div :class="{'translate-x-0': open, '-translate-x-full': !open}" 
            class="bg-[#584f7a] w-64 fixed h-full transition-transform transform 'text-black bg-blue-600'">
            @include('layouts.sidebar')

            
        </div>


        <!-- Main Content -->
        <div :class="{'ml-64': open, 'ml-0': !open}" 
        class="flex-1 flex flex-col transition-all duration-300">
            <!-- Navigation -->
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-grow p-6 bg-[]">
                @yield('content')
            </main>
        </div>
    </div>
    <div class="modal fade" id="googleAuthModal" tabindex="-1" aria-labelledby="googleAuthModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="googleAuthModalLabel">Google Sign-In Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>To view the appointment details, please sign in to Google to sync with Google Calendar.</p>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('google.auth') }}" class="btn btn-primary">Sign in with Google</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if ("{{ session('warning') }}") {
                alert("{{ session('warning') }}");
            }
        });
    </script>

<style>
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.open {
        transform: translateX(0);
    }
    .main-content {
        margin-left: 0;
        background-color:forestgreen !important ;
    }
    .main-content.open {
        margin-left: 250px;
    }
}
</style>
</body>


</html>
