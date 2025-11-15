<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>@yield('title', 'AITAS Portal')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <script src="https://cdn.tailwindcss.com"></script>
    
<style>
/* ===== BODY & FONT ===== */
body {
    margin: 0;
    background-color: #EEEEEE; 
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
}

/* ===== SIDEBAR ===== */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    background-color: #142D54;
    z-index: 50;
    color: white;
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    transition: all 0.3s ease-in-out;
}
.sidebar.closed {
    transform: translateX(-100%);
}
.sidebar-divider {
    border-top: 1px solid #4A5568;
    margin: 1rem 0;
}
.sidebar-link {
    display: flex;
    align-items: center;
    padding: 0.625rem 1rem;
    border-radius: 0.375rem;
    color: #CBD5E0;
    text-decoration: none;
    margin-bottom: 0.25rem;
    position: relative;
    z-index: 51;
    transition: all 0.3s;
}
.sidebar-link:hover {
    background-color: #476881;
    color: white;
}
.sidebar-link.active {
    background-color: #00477F;
    color: white;
    font-weight: 500;
}
.sidebar-link img {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
    filter: invert(100%) sepia(100%) saturate(0%) hue-rotate(240deg) brightness(105%) contrast(101%);
}
.sidebar-badge {
    margin-left: auto;
    background-color: #ffffff;
    color: black;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.125rem 0.5rem;
    border-radius: 3.75px;
    pointer-events: none;
}

/* ===== TOP NAV ===== */
.top-nav-fixed {
    position: fixed;
    top: 0;
    left: 280px;
    right: 0;
    height: 100px;
    z-index: 30;
    background: radial-gradient(circle, #337BB6 60%, #1D4A80 100%);
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    transition: all 0.3s ease-in-out;
}
.top-nav-fixed.closed {
    left: 0;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    margin-left: 280px;
    position: relative;
    min-height: 100vh;
    transition: all 0.3s ease-in-out;
}
.main-content.closed {
    margin-left: 0;
}
.white-board {
    position: relative; 
    height: 100vh;
    padding-top: 100px;
    background-color: #EEEEEE; 
    overflow-y: auto; 
}

/* ===== SIDEBAR TOGGLE ===== */
#sidebar-toggle {
    position: fixed;
    top: 80px;
    left: 280px;
    width: 36px;
    height: 36px;
    background-color: #142D54;
    color: white;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 50;
    transform: translateX(-50%);
    transition: all 0.3s ease-in-out;
}
#sidebar-toggle.closed {
    left: 0;
    transform: translateX(50%);
}
#sidebar-toggle:hover {
    background-color: #00477F;
}
#sidebar-arrow {
    transition: transform 0.3s ease-in-out;
}
#sidebar-toggle.closed #sidebar-arrow {
    transform: rotate(180deg);
}

/* ===== NOTIFICATION POPOVER ===== */
#notification-popover {
     position: absolute;
    top: 80px;
    right: 4px;
    z-index: 40;
    width: 300px;
    background-color: white;
    border-radius: 0.75rem; /* rounded corners */
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    max-height: 70vh;
    overflow-y: auto;
    transition: all 0.3s ease-in-out;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    #notification-popover {
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        margin: 0 0.5rem;
        width: auto;
        max-width: calc(100% - 1rem);
        max-height: 80vh;
        border-radius: 1rem;
        padding: 0.5rem;
    }
}    

/* ===== TOGGLE SWITCH ===== */
.toggle-bg { background-color: #4A5568; }
.toggle-bg.on { background-color: #337BB6; }
.toggle-dot { transform: translateX(0.125rem); }
.toggle-bg.on .toggle-dot { transform: translateX(1.375rem); }

/* ===== TRANSITIONS ===== */
.sidebar, .main-content, .top-nav-fixed, #sidebar-toggle { transition: all 0.3s ease-in-out; }
</style>
</head>
<body>

<aside class="sidebar flex flex-col p-6">
    <div class="flex flex-col items-center text-center">
        <!-- <img src="{{ asset('images/aitas-icons/uic-logo.png') }}" alt="UIC Logo" class="w-24 h-24 rounded-full mb-3"> -->
         <h2 class="font-bold">ADMIN</h2>
    </div>

    <hr class="sidebar-divider my-4">

    <nav class="flex-1">
        <span class="text-xs uppercase text-gray-400 tracking-wider">Main</span>
        <ul class="mt-2 space-y-1">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link @if(Request::routeIs('admin.dashboard')) active @endif">
                    <span class="w-8 flex justify-center">
                        <svg width="25" height="21" viewBox="0 0 25 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.74655 20.0494H22.2534C23.7267 18.2357 24.6506 16.0482 24.9188 13.739C25.1869 11.4297 24.7883 9.09271 23.7689 6.99726C22.7495 4.90181 21.1509 3.13318 19.1571 1.8952C17.1634 0.65721 14.8557 0.000242154 12.5 0C10.1443 0.000242154 7.83664 0.65721 5.84289 1.8952C3.84915 3.13318 2.25047 4.90181 1.2311 6.99726C0.211719 9.09271 -0.186875 11.4297 0.0812466 13.739C0.349368 16.0482 1.27329 18.2357 2.74655 20.0494ZM12.5 1.54226C13.3597 1.54226 14.0631 2.23628 14.0631 3.08452C14.0631 3.93276 13.3597 4.62678 12.5 4.62678C11.6403 4.62678 10.9369 3.93276 10.9369 3.08452C10.9369 2.23628 11.6403 1.54226 12.5 1.54226ZM6.24779 4.62678C7.10747 4.62678 7.81084 5.3208 7.81084 6.16904C7.81084 7.01728 7.10747 7.7113 6.24779 7.7113C5.38811 7.7113 4.68474 7.01728 4.68474 6.16904C4.68474 5.3208 5.38811 4.62678 6.24779 4.62678ZM18.7522 4.62678C19.6119 4.62678 20.3153 5.3208 20.3153 6.16904C20.3153 7.01728 19.6119 7.7113 18.7522 7.7113C17.8925 7.7113 17.1892 7.01728 17.1892 6.16904C17.1892 5.3208 17.8925 4.62678 18.7522 4.62678ZM10.3586 13.1863L15.6261 6.16904V15.4226C15.6261 17.1191 14.2194 18.5071 12.5 18.5071C10.7806 18.5071 9.37389 17.1191 9.37389 15.4226C9.37389 14.5435 9.74903 13.757 10.3586 13.1863ZM3.12168 10.7958C3.98136 10.7958 4.68474 11.4898 4.68474 12.3381C4.68474 13.1863 3.98136 13.8803 3.12168 13.8803C2.262 13.8803 1.55863 13.1863 1.55863 12.3381C1.55863 11.4898 2.262 10.7958 3.12168 10.7958ZM21.8783 10.7958C22.738 10.7958 23.4414 11.4898 23.4414 12.3381C23.4414 13.1863 22.738 13.8803 21.8783 13.8803C21.0186 13.8803 20.3153 13.1863 20.3153 12.3381C20.3153 11.4898 21.0186 10.7958 21.8783 10.7958ZM14.0631 15.4226C14.0631 14.5744 13.3597 13.8803 12.5 13.8803C11.6403 13.8803 10.9369 14.5744 10.9369 15.4226C10.9369 16.2708 11.6403 16.9649 12.5 16.9649C13.3597 16.9649 14.0631 16.2708 14.0631 15.4226Z" fill="#EFF2F4"/>
                        </svg>
                    </span>
                    <span class="ml-4">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.teachers.index') }}" class="sidebar-link @if(Request::routeIs('admin.teachers.index')) active @endif">
                    <span class="w-8 flex justify-center">
                        <svg width="25" height="22" viewBox="0 0 25 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.5 0L0 7.36815L12.5 14.7363L22.9167 8.59652V16.3152H25V7.36815L12.5 0ZM4.16563 12.0943V16.8415C5.13534 18.1494 6.39339 19.2109 7.83993 19.9417C9.28647 20.6726 10.8817 21.0527 12.499 21.0518C14.1164 21.0528 15.7118 20.6728 17.1586 19.9419C18.6053 19.2111 19.8635 18.1495 20.8333 16.8415V12.0953L12.5 17.0078L4.16563 12.0943Z" fill="#EFF2F4"/>
                        </svg>
                    </span>
                    <span class="ml-4">Teachers</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.schedules.index') }}" class="sidebar-link @if(Request::routeIs('admin.schedules.index')) active @endif">
                    <span class="w-8 flex justify-center">
                        <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.47619 0V2.10041H1.09524C0.804763 2.10041 0.526185 2.21106 0.320788 2.40801C0.115391 2.60496 0 2.87209 0 3.15062V19.9539C0 20.2324 0.115391 20.4996 0.320788 20.6965C0.526185 20.8935 0.804763 21.0041 1.09524 21.0041H9.58881C8.24077 19.3896 7.56112 17.3551 7.67997 15.2901C7.79882 13.2251 8.70783 11.2746 10.233 9.81222C11.7581 8.3498 13.7922 7.47816 15.9457 7.36419C18.0993 7.25023 20.2211 7.90194 21.9048 9.19455V3.15062C21.9048 2.87209 21.7894 2.60496 21.584 2.40801C21.3786 2.21106 21.1 2.10041 20.8095 2.10041H16.4286V0H14.2381V2.10041H7.66667V0H5.47619ZM23 15.7531C23 16.5806 22.83 17.4 22.4998 18.1645C22.1695 18.929 21.6855 19.6236 21.0753 20.2087C20.4651 20.7939 19.7406 21.258 18.9433 21.5747C18.1461 21.8913 17.2915 22.0543 16.4286 22.0543C15.5656 22.0543 14.7111 21.8913 13.9138 21.5747C13.1165 21.258 12.3921 20.7939 11.7819 20.2087C11.1717 19.6236 10.6876 18.929 10.3574 18.1645C10.0271 17.4 9.85714 16.5806 9.85714 15.7531C9.85714 14.0819 10.5495 12.4792 11.7819 11.2974C13.0143 10.1157 14.6857 9.45185 16.4286 9.45185C18.1714 9.45185 19.8429 10.1157 21.0753 11.2974C22.3077 12.4792 23 14.0819 23 15.7531ZM15.3333 11.5523V16.1879L17.8447 18.596L19.3934 17.111L17.5238 15.3183V11.5523H15.3333Z" fill="#EFF2F4"/>
                        </svg> 
                    </span>
                    <span class="ml-4">Schedules</span>
                    <span class="sidebar-badge">1</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.rooms.index') }}" class="sidebar-link @if(Request::routeIs('admin.rooms.index')) active @endif">
                    <span class="w-8 flex justify-center">
                        <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.6 6.9H16.1V4.6H4.6V6.9ZM17.25 23C15.8508 23 14.6291 22.5641 13.5849 21.6924C12.5407 20.8207 11.8841 19.7233 11.615 18.4H13.3975C13.6467 19.2433 14.1212 19.9333 14.8212 20.47C15.5212 21.0067 16.3308 21.275 17.25 21.275C18.3617 21.275 19.3104 20.8821 20.0962 20.0962C20.8821 19.3104 21.275 18.3617 21.275 17.25C21.275 16.1383 20.8821 15.1896 20.0962 14.4037C19.3104 13.6179 18.3617 13.225 17.25 13.225C16.6942 13.225 16.1767 13.3258 15.6975 13.5274C15.2183 13.7291 14.7967 14.0116 14.4325 14.375H16.1V16.1H11.5V11.5H13.225V13.1387C13.7425 12.6404 14.3463 12.2429 15.0363 11.9462C15.7263 11.6495 16.4642 11.5008 17.25 11.5C18.8408 11.5 20.1971 12.0608 21.3187 13.1824C22.4403 14.3041 23.0008 15.6599 23 17.25C22.9992 18.8401 22.4384 20.1963 21.3176 21.3187C20.1967 22.4411 18.8408 23.0015 17.25 23ZM8.71125 20.7H2.3C1.6675 20.7 1.12623 20.475 0.6762 20.0249C0.226167 19.5749 0.000766667 19.0333 0 18.4V2.3C0 1.6675 0.2254 1.12623 0.6762 0.6762C1.127 0.226167 1.66827 0.000766667 2.3 0H18.4C19.0325 0 19.5741 0.2254 20.0249 0.6762C20.4757 1.127 20.7008 1.66827 20.7 2.3V8.71125C20.1442 8.50042 19.5787 8.3375 19.0037 8.2225C18.4287 8.1075 17.8442 8.05 17.25 8.05C16.445 8.05 15.6687 8.15043 14.9212 8.3513C14.1737 8.55217 13.4646 8.83507 12.7937 9.2H4.6V11.5H10.0625C9.79417 11.845 9.545 12.2092 9.315 12.5925C9.085 12.9758 8.88375 13.3783 8.71125 13.8H4.6V16.1H8.1075C8.06917 16.2917 8.05 16.4787 8.05 16.6612V17.25C8.05 17.8442 8.1075 18.4287 8.2225 19.0037C8.3375 19.5787 8.50042 20.1442 8.71125 20.7Z" fill="#EFF2F4"/>
                        </svg>
                    </span>
                    <span class="ml-4">Rooms</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.subjects.index') }}" class="sidebar-link @if(Request::routeIs('admin.subjects.index')) active @endif">
                    <span class="w-8 flex justify-center">
                        <svg width="17" height="21" viewBox="0 0 17 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.30971 0V1.32242H0.629795C0.463342 1.32242 0.303706 1.38929 0.186006 1.50834C0.0683063 1.62738 0.00218307 1.78883 0.00218307 1.95718C-0.00504952 2.04465 0.00573498 2.13269 0.0338552 2.21574C0.0619754 2.29878 0.10682 2.37503 0.165556 2.43965C0.224292 2.50427 0.295641 2.55588 0.375098 2.59119C0.454554 2.62651 0.540389 2.64478 0.62718 2.64484H1.30971V3.96725H0.629795C0.547376 3.96725 0.465764 3.98367 0.389618 4.01557C0.313473 4.04747 0.244285 4.09423 0.186006 4.15317C0.127727 4.21211 0.0814975 4.28209 0.0499571 4.3591C0.0184167 4.43611 0.00218307 4.51866 0.00218307 4.60202C-0.00504952 4.68949 0.00573498 4.77753 0.0338552 4.86057C0.0619754 4.94362 0.10682 5.01986 0.165556 5.08449C0.224292 5.14911 0.295641 5.20071 0.375098 5.23603C0.454554 5.27135 0.540389 5.28961 0.62718 5.28967H1.30971V6.61209H0.629795C0.463342 6.61209 0.303706 6.67897 0.186006 6.79801C0.0683063 6.91705 0.00218307 7.0785 0.00218307 7.24685C-0.00504952 7.33433 0.00573498 7.42237 0.0338552 7.50541C0.0619754 7.58845 0.10682 7.6647 0.165556 7.72932C0.224292 7.79395 0.295641 7.84555 0.375098 7.88086C0.454554 7.91618 0.540389 7.93445 0.62718 7.93451H1.30971V9.25693H0.629795C0.463342 9.25693 0.303706 9.3238 0.186006 9.44284C0.0683063 9.56188 0.00218307 9.72334 0.00218307 9.89169C-0.00504952 9.97916 0.00573498 10.0672 0.0338552 10.1502C0.0619754 10.2333 0.10682 10.3095 0.165556 10.3742C0.224292 10.4388 0.295641 10.4904 0.375098 10.5257C0.454554 10.561 0.540389 10.5793 0.62718 10.5793H1.30971V11.9018H0.629795C0.463342 11.9018 0.303706 11.9686 0.186006 12.0877C0.0683063 12.2067 0.00218307 12.3682 0.00218307 12.5365C0.00218307 12.7049 0.0683063 12.8663 0.186006 12.9854C0.303706 13.1044 0.463342 13.1713 0.629795 13.1713H1.30971V14.4937H0.629795C0.463342 14.4937 0.303706 14.5606 0.186006 14.6796C0.0683063 14.7987 0.00218307 14.9601 0.00218307 15.1285C0.00218307 15.2968 0.0683063 15.4583 0.186006 15.5773C0.303706 15.6963 0.463342 15.7632 0.629795 15.7632H1.30971V17.0856H0.629795C0.463342 17.0856 0.303706 17.1525 0.186006 17.2716C0.0683063 17.3906 0.00218307 17.5521 0.00218307 17.7204C0.00218307 17.8888 0.0683063 18.0502 0.186006 18.1692C0.303706 18.2883 0.463342 18.3552 0.629795 18.3552H1.30971V21H17V0H1.30971ZM3.27099 18.5139C3.09761 18.5139 2.93132 18.4442 2.80871 18.3202C2.68611 18.1962 2.61723 18.028 2.61723 17.8526C2.61723 17.6773 2.68611 17.5091 2.80871 17.3851C2.93132 17.2611 3.09761 17.1914 3.27099 17.1914C3.44438 17.1914 3.61067 17.2611 3.73327 17.3851C3.85588 17.5091 3.92476 17.6773 3.92476 17.8526C3.92476 18.028 3.85588 18.1962 3.73327 18.3202C3.61067 18.4442 3.44438 18.5139 3.27099 18.5139ZM3.27099 15.869C3.09761 15.869 2.93132 15.7994 2.80871 15.6754C2.68611 15.5514 2.61723 15.3832 2.61723 15.2078C2.61723 15.0324 2.68611 14.8643 2.80871 14.7403C2.93132 14.6163 3.09761 14.5466 3.27099 14.5466C3.44438 14.5466 3.61067 14.6163 3.73327 14.7403C3.85588 14.8643 3.92476 15.0324 3.92476 15.2078C3.92476 15.3832 3.85588 15.5514 3.73327 15.6754C3.61067 15.7994 3.44438 15.869 3.27099 15.869ZM3.27099 13.2242C3.09761 13.2242 2.93132 13.1545 2.80871 13.0305C2.68611 12.9065 2.61723 12.7383 2.61723 12.563C2.61723 12.3876 2.68611 12.2194 2.80871 12.0954C2.93132 11.9714 3.09761 11.9018 3.27099 11.9018C3.44438 11.9018 3.61067 11.9714 3.73327 12.0954C3.85588 12.2194 3.92476 12.3876 3.92476 12.563C3.92476 12.7383 3.85588 12.9065 3.73327 13.0305C3.61067 13.1545 3.44438 13.2242 3.27099 13.2242ZM3.27099 10.5793C3.09761 10.5793 2.93132 10.5097 2.80871 10.3857C2.68611 10.2617 2.61723 10.0935 2.61723 9.91814C2.61723 9.74277 2.68611 9.57459 2.80871 9.45059C2.93132 9.32659 3.09761 9.25693 3.27099 9.25693C3.44438 9.25693 3.61067 9.32659 3.73327 9.45059C3.85588 9.57459 3.92476 9.74277 3.92476 9.91814C3.92476 10.0935 3.85588 10.2617 3.73327 10.3857C3.61067 10.5097 3.44438 10.5793 3.27099 10.5793ZM3.27099 7.93451C3.09761 7.93451 2.93132 7.86485 2.80871 7.74084C2.68611 7.61684 2.61723 7.44866 2.61723 7.2733C2.61723 7.09794 2.68611 6.92975 2.80871 6.80575C2.93132 6.68175 3.09761 6.61209 3.27099 6.61209C3.44438 6.61209 3.61067 6.68175 3.73327 6.80575C3.85588 6.92975 3.92476 7.09794 3.92476 7.2733C3.92476 7.44866 3.85588 7.61684 3.73327 7.74084C3.61067 7.86485 3.44438 7.93451 3.27099 7.93451ZM3.27099 5.28967C3.09761 5.28967 2.93132 5.22001 2.80871 5.09601C2.68611 4.97201 2.61723 4.80383 2.61723 4.62846C2.61723 4.4531 2.68611 4.28492 2.80871 4.16092C2.93132 4.03692 3.09761 3.96725 3.27099 3.96725C3.44438 3.96725 3.61067 4.03692 3.73327 4.16092C3.85588 4.28492 3.92476 4.4531 3.92476 4.62846C3.92476 4.80383 3.85588 4.97201 3.73327 5.09601C3.61067 5.22001 3.44438 5.28967 3.27099 5.28967ZM3.27099 2.64484C3.09761 2.64484 2.93132 2.57517 2.80871 2.45117C2.68611 2.32717 2.61723 2.15899 2.61723 1.98363C2.61723 1.80826 2.68611 1.64008 2.80871 1.51608C2.93132 1.39208 3.09761 1.32242 3.27099 1.32242C3.44438 1.32242 3.61067 1.39208 3.73327 1.51608C3.85588 1.64008 3.92476 1.80826 3.92476 1.98363C3.92476 2.15899 3.85588 2.32717 3.73327 2.45117C3.61067 2.57517 3.44438 2.64484 3.27099 2.64484ZM14.385 7.93451H6.5398V3.96725H14.385V7.93451Z" fill="#EFF2F4"/>
                        </svg>
                    </span>
                    <span class="ml-4">Subjects</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.notifications.index') }}" class="sidebar-link @if(Request::routeIs('admin.notifications.index')) active @endif">
                    <span class="w-8 flex justify-center">
                        <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.6426 13.8085C16.5692 13.7192 16.4971 13.6299 16.4263 13.5438C15.4532 12.3558 14.8644 11.6388 14.8644 8.27589C14.8644 6.53482 14.4517 5.10625 13.6383 4.03482C13.0385 3.2433 12.2277 2.64286 11.159 2.19911C11.1452 2.19139 11.133 2.18126 11.1227 2.1692C10.7383 0.870089 9.68645 0 8.50011 0C7.31377 0 6.26234 0.870089 5.87795 2.16786C5.8677 2.17949 5.85559 2.1893 5.84212 2.19688C3.34823 3.23304 2.13623 5.22098 2.13623 8.27455C2.13623 11.6388 1.54837 12.3558 0.574347 13.5424C0.503574 13.6286 0.431473 13.7161 0.358045 13.8071C0.168372 14.038 0.0481989 14.3189 0.0117471 14.6165C-0.0247047 14.9141 0.024091 15.2161 0.152359 15.4866C0.42528 16.067 1.00695 16.4272 1.6709 16.4272H15.3342C15.995 16.4272 16.5727 16.0674 16.8465 15.4897C16.9754 15.2191 17.0246 14.917 16.9885 14.619C16.9523 14.321 16.8323 14.0397 16.6426 13.8085ZM8.50011 20C9.1393 19.9995 9.76643 19.8244 10.315 19.4932C10.8636 19.1621 11.3131 18.6873 11.6159 18.1192C11.6302 18.092 11.6372 18.0615 11.6364 18.0307C11.6355 17.9999 11.6268 17.9699 11.611 17.9435C11.5953 17.9171 11.573 17.8953 11.5464 17.8802C11.5199 17.865 11.4898 17.8571 11.4593 17.8571H5.54177C5.51122 17.857 5.48116 17.8649 5.45452 17.88C5.42787 17.8951 5.40556 17.9169 5.38975 17.9433C5.37394 17.9697 5.36517 17.9998 5.36429 18.0306C5.36341 18.0614 5.37046 18.0919 5.38474 18.1192C5.68754 18.6872 6.137 19.162 6.68548 19.4931C7.23395 19.8242 7.86099 19.9994 8.50011 20Z" fill="#EFF2F4"/>
                        </svg>
                    </span>
                    <span class="ml-4">Notifications</span>
                    <span class="sidebar-badge">5</span>
                </a>
            </li>
        </ul>
    </nav>

    <hr class="sidebar-divider my-4">

    <div>
        <span class="text-xs uppercase text-gray-400 tracking-wider">Settings</span>
        <ul class="mt-2 space-y-1">
            <li>
                <a href="{{ route('profile.edit') }}" class="sidebar-link @if(Request::routeIs('profile.edit')) active @endif">
                    <span class="w-8 flex justify-center">
                        <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.9127 2.50879C16.859 2.50879 17.7157 3.03562 18.1889 3.81334C18.419 4.18965 18.5725 4.65376 18.5341 5.14297C18.5086 5.51928 18.6236 5.89559 18.8282 6.24682C19.4804 7.31303 20.9254 7.71443 22.0507 7.11233C23.3167 6.3848 24.9151 6.82383 25.644 8.06565L26.5008 9.54581C27.2425 10.7876 26.8333 12.3807 25.5545 13.0957C24.4676 13.7354 24.0839 15.1529 24.7361 16.2316C24.9407 16.5703 25.1709 16.8588 25.5289 17.0344C25.9765 17.2727 26.3218 17.6491 26.5647 18.0254C27.0379 18.8031 26.9995 19.7564 26.5392 20.5968L25.644 22.1021C25.1709 22.9049 24.2885 23.4066 23.3806 23.4066C22.9331 23.4066 22.4343 23.2812 22.0251 23.0303C21.6927 22.8171 21.309 22.7418 20.8998 22.7418C19.6339 22.7418 18.5725 23.7829 18.5341 25.0248C18.5341 26.4673 17.3577 27.5962 15.8871 27.5962H14.148C12.6646 27.5962 11.4882 26.4673 11.4882 25.0248C11.4626 23.7829 10.4012 22.7418 9.13527 22.7418C8.71328 22.7418 8.32965 22.8171 8.00996 23.0303C7.60076 23.2812 7.08926 23.4066 6.65448 23.4066C5.73378 23.4066 4.85144 22.9049 4.3783 22.1021L3.49595 20.5968C3.02281 19.7815 2.99724 18.8031 3.47038 18.0254C3.67498 17.6491 4.05861 17.2727 4.49338 17.0344C4.85144 16.8588 5.08161 16.5703 5.299 16.2316C5.93838 15.1529 5.55475 13.7354 4.46781 13.0957C3.20184 12.3807 2.79264 10.7876 3.52153 9.54581L4.3783 8.06565C5.11997 6.82383 6.70563 6.3848 7.98439 7.11233C9.09691 7.71443 10.5419 7.31303 11.1941 6.24682C11.3987 5.89559 11.5138 5.51928 11.4882 5.14297C11.4626 4.65376 11.6033 4.18965 11.8462 3.81334C12.3194 3.03562 13.1761 2.53388 14.1096 2.50879H15.9127ZM15.0303 11.5152C13.0227 11.5152 11.3987 13.0957 11.3987 15.065C11.3987 17.0344 13.0227 18.6024 15.0303 18.6024C17.038 18.6024 18.6236 17.0344 18.6236 15.065C18.6236 13.0957 17.038 11.5152 15.0303 11.5152Z" fill="#EFF2F4"/>
                        </svg>
                    </span>
                    <span class="ml-4">Settings</span>
                </a>
            </li>
            <li class="flex items-center justify-between text-gray-400 px-4 py-2.5">
                <div class="flex items-center">
                    <span class="w-8 flex justify-center">
                        <svg width="22" height="26" viewBox="0 0 22 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.3083 25.0617C16.1047 25.0617 19.572 23.3032 21.8691 20.4218C22.209 19.9955 21.8384 19.3728 21.3133 19.4746C15.3417 20.6325 9.85782 15.9711 9.85782 9.83399C9.85782 6.29882 11.7167 3.04799 14.7378 1.29763C15.2035 1.02782 15.0863 0.309013 14.5572 0.2095C13.8153 0.070238 13.0626 0.000114361 12.3083 0C5.51425 0 0 5.60516 0 12.5309C0 19.4478 5.50559 25.0617 12.3083 25.0617Z" fill="#EFF2F4"/>
                        </svg>
                    </span>
                    <span class="ml-4">Light Mode</span>
                </div>
                <div class="flex items-center cursor-pointer">
                    <div class="toggle-bg on w-11 h-6 rounded-full p-0.5 transition-colors duration-300 ease-in-out">
                        <div class="toggle-dot w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300 ease-in-out"></div>
                    </div>
                    <span class="text-xs text-white ml-2">ON</span>
                </div>
            </li>
        </ul>
    </div>
    
    <div class="mt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link active justify-center font-medium w-full text-left">
                <span class="w-8 flex justify-center">
                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.8781 0C14.9845 0 17.5179 2.49005 17.5179 5.55569V11.5493H9.87931C9.33187 11.5493 8.89902 11.9748 8.89902 12.5128C8.89902 13.0384 9.33187 13.4763 9.87931 13.4763H17.5179V19.4574C17.5179 22.5231 14.9845 25.0256 11.8526 25.0256H5.65259C2.53348 25.0256 0 22.5356 0 19.4699V5.5682C0 2.49005 2.54621 0 5.66532 0H11.8781ZM20.6965 8.19615C21.0718 7.80825 21.685 7.80825 22.0604 8.18363L25.7141 11.8249C25.9018 12.0126 26.0019 12.2503 26.0019 12.5131C26.0019 12.7633 25.9018 13.0136 25.7141 13.1888L22.0604 16.83C21.8727 17.0177 21.6224 17.1178 21.3847 17.1178C21.1344 17.1178 20.8841 17.0177 20.6965 16.83C20.3211 16.4546 20.3211 15.8415 20.6965 15.4661L22.6985 13.4766H17.5182V11.5496H22.6985L20.6965 9.56004C20.3211 9.18466 20.3211 8.57153 20.6965 8.19615Z" fill="white"/>
                    </svg>
                </span>
                <span class="ml-4">Logout</span>
            </button>
        </form>
    </div>
</aside>

<div id="sidebar-toggle" class="-mt-7">
    <svg id="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
</div>

<div class="main-content">
    
    <nav class="top-nav-fixed flex items-center justify-between px-4 h-24">
    <!-- Placeholder for left spacing -->
    <div class="w-6"></div>

    <!-- Header Title -->
    <h1 class="text-xl font-semibold text-white">@yield('header_title', 'Dashboard')</h1>

    <!-- Notification Button -->
    <div class="relative">
        <button id="notification-btn" class="text-white hover:text-blue-200 relative p-2 focus:outline-none">
            <!-- Bell Icon -->
            <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16.6426 13.8085C16.5692 13.7192 16.4971 13.6299 16.4263 13.5438C15.4532 12.3558 14.8644 11.6388 14.8644 8.27589C14.8644 6.53482 14.4517 5.10625 13.6383 4.03482C13.0385 3.2433 12.2277 2.64286 11.159 2.19911C11.1452 2.19139 11.133 2.18126 11.1227 2.1692C10.7383 0.870089 9.68645 0 8.50011 0C7.31377 0 6.26234 0.870089 5.87795 2.16786C5.8677 2.17949 5.85559 2.1893 5.84212 2.19688C3.34823 3.23304 2.13623 5.22098 2.13623 8.27455C2.13623 11.6388 1.54837 12.3558 0.574347 13.5424C0.503574 13.6286 0.431473 13.7161 0.358045 13.8071C0.168372 14.038 0.0481989 14.3189 0.0117471 14.6165C-0.0247047 14.9141 0.024091 15.2161 0.152359 15.4866C0.42528 16.067 1.00695 16.4272 1.6709 16.4272H15.3342C15.995 16.4272 16.5727 16.0674 16.8465 15.4897C16.9754 15.2191 17.0246 14.917 16.9885 14.619C16.9523 14.321 16.8323 14.0397 16.6426 13.8085ZM8.50011 20C9.1393 19.9995 9.76643 19.8244 10.315 19.4932C10.8636 19.1621 11.3131 18.6873 11.6159 18.1192C11.6302 18.092 11.6372 18.0615 11.6364 18.0307C11.6355 17.9999 11.6268 17.9699 11.611 17.9435C11.5953 17.9171 11.573 17.8953 11.5464 17.8802C11.5199 17.865 11.4898 17.8571 11.4593 17.8571H5.54177C5.51122 17.857 5.48116 17.8649 5.45452 17.88C5.42787 17.8951 5.40556 17.9169 5.38975 17.9433C5.37394 17.9697 5.36517 17.9998 5.36429 18.0306C5.36341 18.0614 5.37046 18.0919 5.38474 18.1192C5.68754 18.6872 6.137 19.162 6.68548 19.4931C7.23395 19.8242 7.86099 19.9994 8.50011 20Z" fill="#EFF2F4"/>
            </svg>

            <!-- Notification Count -->
            <span id="notification-count" class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full text-white text-xs text-center hidden"></span>
        </button>

        <!-- Notification Popover -->
        <div id="notification-popover" class="bg-white rounded-xl shadow-xl hidden overflow-hidden w-80 max-w-full">
            <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50">
                <h3 class="font-bold text-lg text-gray-800">Notifications</h3>
                <button id="close-popover" class="text-gray-400 text-2xl hover:text-gray-600 leading-none p-1">&times;</button>
            </div>

            <div class="max-h-80 overflow-y-auto px-2 py-1">
                <ul id="notification-list" class="space-y-2"></ul>
            </div>

            <div class="p-2 border-t text-center">
                <a href="{{ route('admin.notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All Notifications</a>
            </div>
        </div>

    </div>
</nav>

    <main class="white-board">
        <div class="p-6">
            @yield('content')
        </div>
    </main>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const topNav = document.querySelector('.top-nav-fixed');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const arrowIcon = toggleBtn.querySelector('svg');

    // --- Notification Elements ---
    const notificationBtn = document.getElementById('notification-btn');
    const notificationPopover = document.getElementById('notification-popover');
    const closePopoverBtn = document.getElementById('close-popover');
    const notificationList = document.getElementById('notification-list');
    const notificationCount = document.getElementById('notification-count');

    // --- Example notifications array ---
    const notifications = [
        { id: 1, type: 'teacher', title: 'New Teacher Added', message: 'John Doe has been added.', read: false, user: {name: 'Admin'}, created_at: '2025-11-15T00:00:00Z' },
        { id: 2, type: 'room', title: 'Room Updated', message: 'Room 101 schedule updated.', read: true, user: null, created_at: '2025-11-14T12:00:00Z' },
        { id: 3, type: 'schedule', title: 'MWF Schedule Assigned', message: 'New MWF schedule assigned.', read: false, user: {name: 'Dean Smith'}, created_at: '2025-11-15T08:00:00Z' }
    ];

    // --- Helper: format time ago ---
    function timeAgo(dateStr) {
        const date = new Date(dateStr);
        const diff = Math.floor((new Date() - date) / 1000); // seconds
        if(diff < 60) return `${diff}s ago`;
        if(diff < 3600) return `${Math.floor(diff/60)}m ago`;
        if(diff < 86400) return `${Math.floor(diff/3600)}h ago`;
        return `${Math.floor(diff/86400)}d ago`;
    }

    // --- Update notification badge ---
    function updateNotificationCount() {
        const unreadCount = notifications.filter(n => !n.read).length;
        if(unreadCount > 0){
            notificationCount.textContent = unreadCount;
            notificationCount.classList.remove('hidden');
            notificationCount.style.display = 'inline-block';
        } else {
            notificationCount.classList.add('hidden');
            notificationCount.style.display = 'none';
        }
    }

    // --- Populate notification list ---
    function populateNotifications() {
        notificationList.innerHTML = '';
        notifications.forEach(n => {
            const li = document.createElement('li');
            li.className = `rounded-lg p-2 border-l-4 cursor-pointer mb-2 ${
                n.read ? 'bg-white text-[#00477F] border-transparent' : 'bg-[#00477F] text-white border-yellow-400'
            }`;

            // Icon based on type
            let iconHtml = '';
            switch(n.type) {
                case 'teacher':
                    iconHtml = '<svg class="w-5 h-5 '+(n.read?'text-[#00477F]':'text-white')+'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 1.79-8 4v2h16v-2c0-2.21-3.582-4-8-4z"/></svg>';
                    break;
                case 'room':
                    iconHtml = '<svg class="w-5 h-5 '+(n.read?'text-[#00477F]':'text-white')+'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18v10H3V10z M5 10V6h14v4"/></svg>';
                    break;
                default:
                    iconHtml = '<svg class="w-5 h-5 '+(n.read?'text-[#00477F]':'text-white')+'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>';
            }

            li.innerHTML = `
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center space-x-2">${iconHtml}<span class="font-medium">${n.title}</span></div>
                    <span class="text-xs ${n.read?'text-[#00477F]':'text-blue-300'}">${timeAgo(n.created_at)}</span>
                </div>
                <p class="text-sm mb-1 ${n.read?'text-[#00477F]':''}">${n.message}</p>
                <div class="flex justify-between items-center text-xs">
                    <span class="${n.read?'text-[#00477F]':'text-blue-300'}">${n.user ? 'Created by: '+n.user.name : 'System'}</span>
                    <span class="${n.read?'text-gray-400 italic':'font-medium'}">${n.read ? 'Read' : 'Unread'}</span>
                </div>
            `;

            li.addEventListener('click', () => {
                n.read = true;
                populateNotifications();
                updateNotificationCount();
            });

            notificationList.appendChild(li);
        });
    }

    // --- Popover toggle ---
    notificationBtn.addEventListener('click', e => {
        e.stopPropagation();
        notificationPopover.classList.toggle('hidden');
    });

    closePopoverBtn.addEventListener('click', () => {
        notificationPopover.classList.add('hidden');
    });

    document.addEventListener('click', e => {
        if(!notificationBtn.contains(e.target) && !notificationPopover.contains(e.target)) {
            notificationPopover.classList.add('hidden');
        }
    });

    // --- Initialize ---
    populateNotifications();
    updateNotificationCount();

    // --- Sidebar toggle ---
    let isOpen = true;
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('closed');
        topNav.classList.toggle('closed');
        toggleBtn.classList.toggle('closed');

        isOpen = !isOpen;
        arrowIcon.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    arrowIcon.style.transition = 'transform 0.3s ease';
    arrowIcon.style.transform = 'rotate(180deg)';
});
</script>
</body>
</html>