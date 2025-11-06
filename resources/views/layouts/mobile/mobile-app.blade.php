<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AITAS Portal')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <script src="https://cdn.tailwindcss.com"></script>
    
<style>
    /* 1. Full-Screen Gradient Background */
    body {
        background: radial-gradient(circle, #337BB6 60%, #1D4A80 100%);
        min-height: 100vh;
        width: 100%;
        margin: 0;
        /* Remove all centering/flex properties from body */
        display: block; 
    }
    
    /* 2. Responsive Mobile Viewport Container */
    .mobile-container {
        /* Set the container to occupy the full screen */
        width: 100%; 
        min-height: 100vh; 
        position: relative; /* Important: Context for absolute/fixed items if needed */
        overflow: hidden; /* Prevents the main page from scrolling */
        /* Remove flex-direction: column; */
    }
    
    /* 3. The White Board Content Area */
    .white-board {
        /* Positioned absolutely or relatively to fill the space between navs */
        position: relative; /* Keeps it in the flow, but we control the boundaries */
        height: 100vh; /* Takes full viewport height */
        
        /* 💡 KEY FIX: Use padding to push content out from behind the fixed navs */
        padding-top: 100px;  /* Height of top-nav-fixed */
        padding-bottom: 75px; /* Height of bottom-nav-fixed */
        
        background-color: #EEEEEE; 
        overflow-y: auto; /* 🔑 This makes ONLY the white-board content scrollable */
    }
    
    /* 4. Fixed Top Header */
    .top-nav-fixed {
        position: fixed; /* 🔑 KEY FIX: Pins to viewport */
        top: 0;
        height: 100px;
        width: 100%;
        max-width: inherit; /* Ensure it respects any max-width on the body/container */
        z-index: 20;
        background: radial-gradient(circle, #337BB6 60%, #1D4A80 100%); 
    }

    /* 5. Fixed Bottom Nav Styling */
    .bottom-nav-fixed {
        position: fixed; /* 🔑 KEY FIX: Pins to viewport */
        bottom: 0;
        height: 75px;
        width: 100%;
        max-width: inherit; /* Ensure it respects any max-width on the body/container */
        z-index: 30;
        background: radial-gradient(circle, #337BB6 60%, #1D4A80 100%); 
    }

    /* 6. Active Nav Item Styling (Example) */
    .nav-active {
         color: #EBF8FF;
    }
</style>
</head>
<body>

    <div class="mobile-container">
        
        <nav class="top-nav-fixed flex items-center justify-between px-4">
            <div class="text-xs font-bold text-white uppercase">AITAS LOGO</div>
            
            <h1 class="text-xl font-semibold text-white">@yield('header_title', 'Dashboard')</h1>
            
            <a href="{{ route('teacher.notifications') }}" class="text-white hover:text-blue-200">
                <img src="{{ asset('images/aitas-icons/Dashboard/bells.png') }}" class="w-6 h-6" alt="Notifications Icon">
            </a>
        </nav>

        <main class="white-board p-4">
            <div class="px-4 pt-[100px] pb-[75px]">
            @yield('content')
        </main>

        <nav class="bottom-nav-fixed border-t border-gray-200 shadow-lg">
            <div class="h-full">
                <div class="flex h-full text-white"> 
                    
                    <a href="{{ route('dashboard.teacher') }}" class="flex flex-col flex-1 items-center justify-center @if(Request::routeIs('dashboard.teacher')) nav-active @endif">
                        <img src="{{ asset('images/aitas-icons/Dashboard/dashboard.png') }}" class="w-6 h-6" alt="Dashboard Icon">
                        <span class="text-xs mt-1">Dashboard</span>
                    </a>

                    <a href="{{ route('teacher.attendance.index') }}" class="flex flex-col flex-1 items-center justify-center hover:text-blue-200 @if(Request::routeIs('teacher.attendance.index')) nav-active @endif">
                        <img src="{{ asset('images/aitas-icons/Dashboard/user-check.png') }}" class="w-6 h-6" alt="Attendance Icon">
                        <span class="text-xs mt-1">Attendance</span>
                    </a>
                    
                    <a href="{{ route('teacher.load') }}" class="flex flex-col flex-1 items-center justify-center hover:text-blue-200 @if(Request::routeIs('teacher.load')) nav-active @endif">
                        <img src="{{ asset('images/aitas-icons/Dashboard/calendar-clock.png') }}" class="w-6 h-6" alt="Schedules Icon">
                        <span class="text-xs mt-1">Schedules</span>
                    </a>

                    <a href="{{ route('teacher.history') }}" class="flex flex-col flex-1 items-center justify-center hover:text-blue-200 @if(Request::routeIs('teacher.history')) nav-active @endif">
                        <img src="{{ asset('images/aitas-icons/Dashboard/time-past.png') }}" class="w-6 h-6" alt="History Icon">
                        <span class="text-xs mt-1">History</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" class="flex flex-col flex-1 items-center justify-center hover:text-blue-200 @if(Request::routeIs('profile.edit')) nav-active @endif">
                        <img src="{{ asset('images/aitas-icons/Dashboard/settings.png') }}" class="w-6 h-6" alt="Settings Icon">
                        <span class="text-xs mt-1">Settings</span>
                    </a>

                </div>
            </div>
        </nav>
        
    </div>

</body>
</html>