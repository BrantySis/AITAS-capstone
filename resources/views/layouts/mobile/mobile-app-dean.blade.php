<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>@yield('title', 'AITAS Dean Portal')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <script src="https://cdn.tailwindcss.com"></script>

<style>
    /* 1. Body Styling */
    body {
        margin: 0;
        background-color: #EEEEEE; 
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
    }

    /* 2. Main Content Area (Wrapper for top-nav and white-board) */
    .main-content {
        margin-left: 280px; /* Width of the sidebar */
        position: relative;
        min-height: 100vh;
    }

    /* 3. The White Board Content Area (Modified) */
    .white-board {
        position: relative; 
        height: 100vh;
        padding-top: 100px;  /* Height of top-nav-fixed */
        background-color: #EEEEEE; 
        overflow-y: auto; 
    }

    /* 4. Fixed Top Header (Modified) */
    .top-nav-fixed {
        position: fixed; 
        top: 0;
        left: 280px;     /* <-- KEY: Starts AFTER sidebar */
        right: 0;        /* <-- KEY: Extends to edge */
        height: 100px;
        z-index: 20;
        background: radial-gradient(circle, #337BB6 60%, #1D4A80 100%); 
    }

    /* 5. Active Nav Item Styling (Unchanged) */
    .nav-active {
          color: #EBF8FF;
    }

    /* 6. Sidebar Styling */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 280px;
        background-color: #142D54;
        z-index: 40;
        color: white;
    }

    .sidebar-divider {
        border-top: 1px solid #4A5568; /* gray-600 */
        margin: 1rem 0;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.625rem 1rem; /* py-2.5 px-4 */
        border-radius: 0.375rem; /* rounded-md */
        color: #CBD5E0; /* gray-400 */
        text-decoration: none;
        margin-bottom: 0.25rem; /* mb-1 */
    }
    .sidebar-link:hover {
        background-color: #476881; /* gray-800 */
        color: white;
    }
    .sidebar-link.active {
        background-color: #00477F; /* Your light blue */
        color: white;
        font-weight: 500; /* medium */
    }

    .sidebar-link img {
        width: 1.25rem; /* w-5 */
        height: 1.25rem; /* h-5 */
        margin-right: 0.75rem; /* mr-3 */
        /* This filter inverts the white icon to make it visible on the dark background */
        filter: invert(100%) sepia(100%) saturate(0%) hue-rotate(240deg) brightness(105%) contrast(101%);
    }

    .sidebar-badge {
        margin-left: auto;
        background-color: #ffffff;
        color: black;
        font-size: 0.75rem; /* text-xs */
        font-weight: 600; /* semibold */
        padding: 0.125rem 0.5rem; /* py-0.5 px-2 */
        border-radius: 3.75px; /* rounded-full */
    }

    /* Simple Toggle Switch */
    .toggle-bg {
        background-color: #4A5568;
    }
    .toggle-bg.on {
        background-color: #337BB6;
    }
    .toggle-dot {
        transform: translateX(0.125rem);
    }
    .toggle-bg.on .toggle-dot {
        transform: translateX(1.375rem);
    }

    /* 7. NEW: Transitions for animation */
    .sidebar, .main-content, .top-nav-fixed, #sidebar-toggle {
        transition: all 0.3s ease-in-out;
    }

    /* 8. NEW: Sidebar Toggle Button Style */
    #sidebar-toggle {
        position: fixed;
        top: 80px; /* 5rem (top-20) */
        left: 280px; /* Starts at the edge of the open sidebar */
        width: 36px;  /* w-9 */
        height: 36px; /* h-9 */
        background-color: #142D54;
        color: white;
        border-radius: 0.375rem; /* rounded-md */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 50;
        /* This transform makes it center-aligned on the border */
        transform: translateX(-50%); 
    }
    #sidebar-toggle:hover {
        background-color: #00477F;
    }

    /* 9. NEW: "Closed" state classes */
    .sidebar.closed {
        transform: translateX(-100%);
    }
    .main-content.closed {
        margin-left: 0;
    }
    .top-nav-fixed.closed {
        left: 0;
    }
    #sidebar-toggle.closed {
        left: 0; /* Moves to the left edge of the screen */
        transform: translateX(50%); /* Re-centers it on the new edge */
    }

</style>
</head>
<body>

<aside class="sidebar flex flex-col p-6">
    <div class="flex flex-col items-center text-center">
        {{-- **PROFILE SECTION: Updated for Dean** --}}
        {{-- Assuming you'll replace the image path with a dynamic one: asset('images/aitas-icons/uic-logo.png') --}}
        <img src="https://via.placeholder.com/96/CCCCCC/888888?text=Dean" alt="User Avatar" class="w-24 h-24 rounded-full mb-3 bg-white border-4 border-white shadow-lg"> 
        <h2 class="font-semibold">{{ auth()->user()->name ?? 'Dean Name' }}</h2>
        <p class="text-sm text-gray-400">Dean of Academics</p>
    </div>

    <hr class="sidebar-divider my-4">

    {{-- **MAIN NAVIGATION: Filtered Links for Dean (Dashboard, Teachers)** --}}
    <nav class="flex-1">
        <span class="text-xs uppercase text-gray-400 tracking-wider">Main</span>
        <ul class="mt-2 space-y-1">
            {{-- 1. Dashboard --}}
            <li>
                <a href="{{ route('dean.dashboard') }}" class="sidebar-link @if(Request::routeIs('dean.dashboard')) active @endif">
                    <span class="w-8 flex justify-center">
                        {{-- Dashboard Icon SVG --}}
                        <svg width="25" height="21" viewBox="0 0 25 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.74655 20.0494H22.2534C23.7267 18.2357 24.6506 16.0482 24.9188 13.739C25.1869 11.4297 24.7883 9.09271 23.7689 6.99726C22.7495 4.90181 21.1509 3.13318 19.1571 1.8952C17.1634 0.65721 14.8557 0.000242154 12.5 0C10.1443 0.000242154 7.83664 0.65721 5.84289 1.8952C3.84915 3.13318 2.25047 4.90181 1.2311 6.99726C0.211719 9.09271 -0.186875 11.4297 0.0812466 13.739C0.349368 16.0482 1.27329 18.2357 2.74655 20.0494ZM12.5 1.54226C13.3597 1.54226 14.0631 2.23628 14.0631 3.08452C14.0631 3.93276 13.3597 4.62678 12.5 4.62678C11.6403 4.62678 10.9369 3.93276 10.9369 3.08452C10.9369 2.23628 11.6403 1.54226 12.5 1.54226ZM6.24779 4.62678C7.10747 4.62678 7.81084 5.3208 7.81084 6.16904C7.81084 7.01728 7.10747 7.7113 6.24779 7.7113C5.38811 7.7113 4.68474 7.01728 4.68474 6.16904C4.68474 5.3208 5.38811 4.62678 6.24779 4.62678ZM18.7522 4.62678C19.6119 4.62678 20.3153 5.3208 20.3153 6.16904C20.3153 7.01728 19.6119 7.7113 18.7522 7.7113C17.8925 7.7113 17.1892 7.01728 17.1892 6.16904C17.1892 5.3208 17.8925 4.62678 18.7522 4.62678ZM10.3586 13.1863L15.6261 6.16904V15.4226C15.6261 17.1191 14.2194 18.5071 12.5 18.5071C10.7806 18.5071 9.37389 17.1191 9.37389 15.4226C9.37389 14.5435 9.74903 13.757 10.3586 13.1863ZM3.12168 10.7958C3.98136 10.7958 4.68474 11.4898 4.68474 12.3381C4.68474 13.1863 3.98136 13.8803 3.12168 13.8803C2.262 13.8803 1.55863 13.1863 1.55863 12.3381C1.55863 11.4898 2.262 10.7958 3.12168 10.7958ZM21.8783 10.7958C22.738 10.7958 23.4414 11.4898 23.4414 12.3381C23.4414 13.1863 22.738 13.8803 21.8783 13.8803C21.0186 13.8803 20.3153 13.1863 20.3153 12.3381C20.3153 11.4898 21.0186 10.7958 21.8783 10.7958ZM14.0631 15.4226C14.0631 14.5744 13.3597 13.8803 12.5 13.8803C11.6403 13.8803 10.9369 14.5744 10.9369 15.4226C10.9369 16.2708 11.6403 16.9649 12.5 16.9649C13.3597 16.9649 14.0631 16.2708 14.0631 15.4226Z" fill="#EFF2F4"/></svg>
                    </span>
                    <span class="ml-4">Dashboard</span>
                </a>
            </li>
            
            {{-- 2. Teachers Section (Used 'teacher.attendance.index' icon as placeholder for Teachers List/Management) --}}
            <li>
                <a href="{{ route('dean.teachers') }}" class="sidebar-link @if(Request::routeIs('dean.teachers')) active @endif">
                    <span class="w-8 flex justify-center">
                        {{-- New Icon for Teachers - using a modified person icon --}}
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C8.67 14 6 15.67 6 18V20H18V18C18 15.67 15.33 14 12 14Z" fill="#EFF2F4"/>
                        </svg>
                    </span>
                    <span class="ml-4">Teachers</span>
                    {{-- Badge for pending new teacher requests/applications if applicable --}}
                    <span class="sidebar-badge">{{ $pendingTeachersCount ?? 3 }}</span>
                </a>
            </li>

            {{-- 3. Notifications (Kept for consistency in the Main/Top area) --}}
            <li>
                <a href="#" class="sidebar-link @if(Request::routeIs('#')) active @endif">
                    <span class="w-8 flex justify-center">
                        {{-- Notifications Icon SVG --}}
                        <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.6426 13.8085C16.5692 13.7192 16.4971 13.6299 16.4263 13.5438C15.4532 12.3558 14.8644 11.6388 14.8644 8.27589C14.8644 6.53482 14.4517 5.10625 13.6383 4.03482C13.0385 3.2433 12.2277 2.64286 11.159 2.19911C11.1452 2.19139 11.133 2.18126 11.1227 2.1692C10.7383 0.870089 9.68645 0 8.50011 0C7.31377 0 6.26234 0.870089 5.87795 2.16786C5.8677 2.17949 5.85559 2.1893 5.84212 2.19688C3.34823 3.23304 2.13623 5.22098 2.13623 8.27455C2.13623 11.6388 1.54837 12.3558 0.574347 13.5424C0.503574 13.6286 0.431473 13.7161 0.358045 13.8071C0.168372 14.038 0.0481989 14.3189 0.0117471 14.6165C-0.0247047 14.9141 0.024091 15.2161 0.152359 15.4866C0.42528 16.067 1.00695 16.4272 1.6709 16.4272H15.3342C15.995 16.4272 16.5727 16.0674 16.8465 15.4897C16.9754 15.2191 17.0246 14.917 16.9885 14.619C16.9523 14.321 16.8323 14.0397 16.6426 13.8085ZM8.50011 20C9.1393 19.9995 9.76643 19.8244 10.315 19.4932C10.8636 19.1621 11.3131 18.6873 11.6159 18.1192C11.6302 18.092 11.6372 18.0615 11.6364 18.0307C11.6355 17.9999 11.6268 17.9699 11.611 17.9435C11.5953 17.9171 11.573 17.8953 11.5464 17.8802C11.5199 17.865 11.4898 17.8571 11.4593 17.8571H5.54177C5.51122 17.857 5.48116 17.8649 5.45452 17.88C5.42787 17.8951 5.40556 17.9169 5.38975 17.9433C5.37394 17.9697 5.36517 17.9998 5.36429 18.0306C5.36341 18.0614 5.37046 18.0919 5.38474 18.1192C5.68754 18.6872 6.137 19.162 6.68548 19.4931C7.23395 19.8242 7.86099 19.9994 8.50011 20Z" fill="#EFF2F4"/></svg>
                    </span>
                    <span class="ml-4">Notifications</span>
                    <span class="sidebar-badge">{{ $notificationCount ?? 8 }}</span>
                </a>
            </li>
        </ul>
    </nav>

    <hr class="sidebar-divider my-4">

    <div>
        <span class="text-xs uppercase text-gray-400 tracking-wider">Settings</span>
        <ul class="mt-2 space-y-1">
            {{-- Settings --}}
            <li>
                <a href="{{ route('profile.edit') }}" class="sidebar-link @if(Request::routeIs('profile.edit')) active @endif">
                    <span class="w-8 flex justify-center">
                        {{-- Settings Icon SVG --}}
                        <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.9127 2.50879C16.859 2.50879 17.7157 3.03562 18.1889 3.81334C18.419 4.18965 18.5725 4.65376 18.5341 5.14297C18.5086 5.51928 18.6236 5.89559 18.8282 6.24682C19.4804 7.31303 20.9254 7.71443 22.0507 7.11233C23.3167 6.3848 24.9151 6.82383 25.644 8.06565L26.5008 9.54581C27.2425 10.7876 26.8333 12.3807 25.5545 13.0957C24.4676 13.7354 24.0839 15.1529 24.7361 16.2316C24.9407 16.5703 25.1709 16.8588 25.5289 17.0344C25.9765 17.2727 26.3218 17.6491 26.5647 18.0254C27.0379 18.8031 26.9995 19.7564 26.5392 20.5968L25.644 22.1021C25.1709 22.9049 24.2885 23.4066 23.3806 23.4066C22.9331 23.4066 22.4343 23.2812 22.0251 23.0303C21.6927 22.8171 21.309 22.7418 20.8998 22.7418C19.6339 22.7418 18.5725 23.7829 18.5341 25.0248C18.5341 26.4673 17.3577 27.5962 15.8871 27.5962H14.148C12.6646 27.5962 11.4882 26.4673 11.4882 25.0248C11.4626 23.7829 10.4012 22.7418 9.13527 22.7418C8.71328 22.7418 8.32965 22.8171 8.00996 23.0303C7.60076 23.2812 7.08926 23.4066 6.65448 23.4066C5.73378 23.4066 4.85144 22.9049 4.3783 22.1021L3.49595 20.5968C3.02281 19.7815 2.99724 18.8031 3.47038 18.0254C3.67498 17.6491 4.05861 17.2727 4.49338 17.0344C4.85144 16.8588 5.08161 16.5703 5.299 16.2316C5.93838 15.1529 5.55475 13.7354 4.46781 13.0957C3.20184 12.3807 2.79264 10.7876 3.52153 9.54581L4.3783 8.06565C5.11997 6.82383 6.70563 6.3848 7.98439 7.11233C9.09691 7.71443 10.5419 7.31303 11.1941 6.24682C11.3987 5.89559 11.5138 5.51928 11.4882 5.14297C11.4626 4.65376 11.6033 4.18965 11.8462 3.81334C12.3194 3.03562 13.1761 2.53388 14.1096 2.50879H15.9127ZM15.0303 11.5152C13.0227 11.5152 11.3987 13.0957 11.3987 15.065C11.3987 17.0344 13.0227 18.6024 15.0303 18.6024C17.038 18.6024 18.6236 17.0344 18.6236 15.065C18.6236 13.0957 17.038 11.5152 15.0303 11.5152Z" fill="#EFF2F4"/></svg>
                    </span>
                    <span class="ml-4">Settings</span>
                </a>
            </li>
            {{-- Light/Dark Mode Toggle --}}
            <li class="flex items-center justify-between text-gray-400 px-4 py-2.5">
                <div class="flex items-center">
                    <span class="w-8 flex justify-center">
                        {{-- Light Mode Icon SVG --}}
                        <svg width="22" height="26" viewBox="0 0 22 26" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.3083 25.0617C16.1047 25.0617 19.572 23.3032 21.8691 20.4218C22.209 19.9955 21.8384 19.3728 21.3133 19.4746C15.3417 20.6325 9.85782 15.9711 9.85782 9.83399C9.85782 6.29882 11.7167 3.04799 14.7378 1.29763C15.2035 1.02782 15.0863 0.309013 14.5572 0.2095C13.8153 0.070238 13.0626 0.000114361 12.3083 0C5.51425 0 0 5.60516 0 12.5309C0 19.4478 5.50559 25.0617 12.3083 25.0617Z" fill="#EFF2F4"/></svg>
                    </span>
                    <span class="ml-4">Light Mode</span>
                </div>
                <div class="flex items-center cursor-pointer">
                    {{-- The 'on' class here determines the initial state (ON/Light Mode) --}}
                    <div class="toggle-bg on w-11 h-6 rounded-full p-0.5 transition-colors duration-300 ease-in-out">
                        <div class="toggle-dot w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300 ease-in-out"></div>
                    </div>
                    <span class="text-xs text-white ml-2">ON</span>
                </div>
            </li>
        </ul>
    </div>
    
    {{-- **LOGOUT SECTION** --}}
    <div class="mt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link justify-center font-medium w-full text-left bg-red-600 hover:bg-red-700 active:bg-red-800">
                <span class="w-8 flex justify-center">
                    {{-- Logout Icon SVG --}}
                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.8781 0C14.9845 0 17.5179 2.49005 17.5179 5.55569V11.5493H9.87931C9.33187 11.5493 8.89902 11.9748 8.89902 12.5128C8.89902 13.0384 9.33187 13.4763 9.87931 13.4763H17.5179V19.4574C17.5179 22.5231 14.9845 25.0256 11.8526 25.0256H5.65259C2.53348 25.0256 0 22.5356 0 19.4699V5.5682C0 2.49005 2.54621 0 5.66532 0H11.8781ZM20.6965 8.19615C21.0718 7.80825 21.685 7.80825 22.0604 8.18363L25.7141 11.8249C25.9018 12.0126 26.0019 12.2503 26.0019 12.5131C26.0019 12.7633 25.9018 13.0136 25.7141 13.1888L22.0604 16.83C21.8727 17.0177 21.6224 17.1178 21.3847 17.1178C21.1344 17.1178 20.8841 17.0177 20.6965 16.83C20.3211 16.4546 20.3211 15.8415 20.6965 15.4661L22.6985 13.4766H17.5182V11.5496H22.6985L20.6965 9.56004C20.3211 9.18466 20.3211 8.57153 20.6965 8.19615Z" fill="white"/></svg>
                </span>
                <span class="ml-4">Logout</span>
            </button>
        </form>
    </div>
</aside>

{{-- **SIDEBAR TOGGLE BUTTON** --}}
<button id="sidebar-toggle" class="-mt-7">
    <svg class="hidden h-6 w-6" id="icon-close" width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path 
            transform="rotate(180, 8.5, 8.5)" 
            d="M5.5892 11.5836C5.33023 11.8453 5.33245 12.2674 5.59416 12.5264C5.85588 12.7853 6.27798 12.7831 6.53696 12.5214L10.5159 8.50041C10.7748 8.2387 10.7726 7.81659 10.5109 7.55762L6.4899 3.57872C6.22818 3.31975 5.80608 3.32197 5.5471 3.58368C5.28813 3.8454 5.29035 4.2675 5.55207 4.52648L9.09918 8.03646L5.5892 11.5836Z" 
            fill="white" 
            fill-opacity="0.8"
        />
    </svg>

    <svg class="h-6 w-6" id="icon-open" width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M5.5892 11.5836C5.33023 11.8453 5.33245 12.2674 5.59416 12.5264C5.85588 12.7853 6.27798 12.7831 6.53696 12.5214L10.5159 8.50041C10.7748 8.2387 10.7726 7.81659 10.5109 7.55762L6.4899 3.57872C6.22818 3.31975 5.80608 3.32197 5.5471 3.58368C5.28813 3.8454 5.29035 4.2675 5.55207 4.52648L9.09918 8.03646L5.5892 11.5836Z" fill="white" fill-opacity="0.8"/>
    </svg>

</button>

{{-- **MAIN CONTENT WRAPPER** --}}
<div class="main-content">

    {{-- **FIXED TOP NAVIGATION** --}}
    <nav class="top-nav-fixed flex items-center justify-between px-4">
        <div class="w-6">
            {{-- Spacer or icon if needed --}}
        </div>

        <h1 class="text-xl font-semibold text-white">@yield('header_title', 'Dean Dashboard')</h1>

        {{-- **NOTIFICATION BELL (Top Right)** --}}
        <a href="#" class="relative text-white hover:text-blue-200">
            <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.6426 13.8085C16.5692 13.7192 16.4971 13.6299 16.4263 13.5438C15.4532 12.3558 14.8644 11.6388 14.8644 8.27589C14.8644 6.53482 14.4517 5.10625 13.6383 4.03482C13.0385 3.2433 12.2277 2.64286 11.159 2.19911C11.1452 2.19139 11.133 2.18126 11.1227 2.1692C10.7383 0.870089 9.68645 0 8.50011 0C7.31377 0 6.26234 0.870089 5.87795 2.16786C5.8677 2.17949 5.85559 2.1893 5.84212 2.19688C3.34823 3.23304 2.13623 5.22098 2.13623 8.27455C2.13623 11.6388 1.54837 12.3558 0.574347 13.5424C0.503574 13.6286 0.431473 13.7161 0.358045 13.8071C0.168372 14.038 0.0481989 14.3189 0.0117471 14.6165C-0.0247047 14.9141 0.024091 15.2161 0.152359 15.4866C0.42528 16.067 1.00695 16.4272 1.6709 16.4272H15.3342C15.995 16.4272 16.5727 16.0674 16.8465 15.4897C16.9754 15.2191 17.0246 14.917 16.9885 14.619C16.9523 14.321 16.8323 14.0397 16.6426 13.8085ZM8.50011 20C9.1393 19.9995 9.76643 19.8244 10.315 19.4932C10.8636 19.1621 11.3131 18.6873 11.6159 18.1192C11.6302 18.092 11.6372 18.0615 11.6364 18.0307C11.6355 17.9999 11.6268 17.9699 11.611 17.9435C11.5953 17.9171 11.573 17.8953 11.5464 17.8802C11.5199 17.865 11.4898 17.8571 11.4593 17.8571H5.54177C5.51122 17.857 5.48116 17.8649 5.45452 17.88C5.42787 17.8951 5.40556 17.9169 5.38975 17.9433C5.37394 17.9697 5.36517 17.9998 5.36429 18.0306C5.36341 18.0614 5.37046 18.0919 5.38474 18.1192C5.68754 18.6872 6.137 19.162 6.68548 19.4931C7.23395 19.8242 7.86099 19.9994 8.50011 20Z" fill="#EFF2F4"/></svg>
            @if(($notificationCount ?? 0) > 0)
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                    {{ $notificationCount ?? 8 }}
                </span>
            @endif
        </a>
    </nav>

    {{-- **PAGE CONTENT YIELD AREA** --}}
    <main class="white-board">
        <div class="p-6">
            @yield('content')
        </div>
    </main>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get elements
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const topNav = document.querySelector('.top-nav-fixed');
        const toggleButton = document.getElementById('sidebar-toggle');
        const iconClose = document.getElementById('icon-close');
        const iconOpen = document.getElementById('icon-open');
        const lightModeToggle = document.querySelector('.toggle-bg');

        // Initial check for 'closed' state if stored in local storage or other mechanism
        // For simplicity, we assume 'open' on load.

        // Add click listener for sidebar toggle
        toggleButton.addEventListener('click', function() {
            // Toggle the 'closed' class on all elements
            sidebar.classList.toggle('closed');
            mainContent.classList.toggle('closed');
            topNav.classList.toggle('closed');
            toggleButton.classList.toggle('closed');

            // Toggle the button's icons
            iconClose.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden');
        });

        // Add click listener for Light/Dark Mode toggle
        lightModeToggle.addEventListener('click', function() {
            const isDarkMode = lightModeToggle.classList.toggle('on');
            
            // This is where you would implement the actual light/dark mode logic.
            // e.g., Toggling classes on the <body> tag or storing preference.
            const toggleText = lightModeToggle.nextElementSibling;
            if (isDarkMode) {
                toggleText.textContent = 'ON';
                // Example: document.body.classList.remove('dark');
            } else {
                toggleText.textContent = 'OFF';
                // Example: document.body.classList.add('dark');
            }
        });
    });
</script>

</body>
</html>