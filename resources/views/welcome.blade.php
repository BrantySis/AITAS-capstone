@extends('layouts.mobile.mobile-loginSignupUI')

@section('title', 'UCLM Login')

@section('content')

    {{-- 
        RESPONSIVE CARD WRAPPER:
        w-11/12 ensures good margins on small screens (mobile-responsive).
        max-w-xs prevents the card from stretching too wide on desktops.
        z-10 keeps it above the snow.
    --}}
    <div class="z-10 bg-white rounded-xl shadow-2xl p-6 w-11/12 max-w-xs mx-auto text-center">
        
        {{-- Logo and Title Block --}}
        <div class="mb-8">
            {{-- Logo size set to h-20 --}}
            <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="mx-auto h-20 -mt-4">
            
            {{-- Campus Name - FONT SIZE, COLOR, BOLDNESS, AND NEGATIVE MARGIN applied --}}
            <h2 class="text-[9px] text-blue-700 font-extrabold -mt-4">LAPU-LAPU AND MANDAUE</h2>

            {{-- 
                WEB PORTAL DIVIDER:
                Uses Flexbox to center text between two stretching lines.
            --}}
            <div class="flex items-center mt-4 mb-4">
                {{-- Left Line (grows to fill space) --}}
                <span class="flex-1 border-t border-gray-300 mr-3"></span>
                
                {{-- Text Label --}}
                <span class="text-sm text-gray-500 font-medium whitespace-nowrap">WEB PORTAL</span>
                
                {{-- Right Line (grows to fill space) --}}
                <span class="flex-1 border-t border-gray-300 ml-3"></span>
            </div>
        </div>

        {{-- Status and Error Messages --}}
        @if(session('status'))
            <div class="mb-4 text-green-600 text-sm">
                {{ session('status') }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}" class="space-y-4 text-left">
            @csrf
            
            {{-- Email/ID Input --}}
            <div class="mb-4">
                {{-- 
                    FIX: Inner wrapper for Icon and Input. 
                    This ensures the icon's vertical centering is based ONLY on the input's height (h-7).
                --}}
                <div class="relative">
                    {{-- Icon size is w-4 h-4 --}}
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/user.png') }}" 
                        alt="User Icon"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 object-contain">
                    {{-- w-full ensures input is responsive --}}
                    <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="Email"
                        class="w-full pl-10 pr-6 py-5 border border-[#888888] rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-7" /> 
                </div>
                {{-- Error message is placed OUTSIDE the inner wrapper --}}
                @if($errors->has('email'))
                    <div class="mt-1 text-red-600 text-xs">{{ $errors->first('email') }}</div>
                @endif
            </div>

            {{-- Password Input --}}
            <div class="mb-4">
                {{-- 
                    FIX: Inner wrapper for Icon and Input. 
                    This ensures the icon's vertical centering is based ONLY on the input's height (h-7).
                --}}
                <div class="relative">
                    {{-- Icon size is w-4 h-4 --}}
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/password.png') }}" 
                        alt="Password Icon"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 object-contain">
                    {{-- w-full ensures input is responsive --}}
                    <input id="password" type="password" name="password" required
                        placeholder="Password"
                        class="w-full -mt-2 pl-10 pr-6 py-5 border border-[#888888] rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-7" /> 
                </div>
                {{-- Error message is placed OUTSIDE the inner wrapper --}}
                @if($errors->has('password'))
                    <div class="mt-1 text-red-600 text-xs">{{ $errors->first('password') }}</div>
                @endif
            </div>

            {{-- Remember Me / Forgot Password --}}
            <div class="flex items-center justify-between mt-4 mb-6">
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="text-blue-600 rounded border-gray-300 shadow-sm focus:ring-blue-500 w-4 h-4">
                    <label for="remember_me" class="ml-2 text-xs text-gray-700">Remember Me</label>
                </div>
                <div>
                    <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:underline font-medium">Forgot Password?</a>
                </div>
            </div>

            {{-- Login Button (w-full for responsiveness) --}}
            <div class="mt-4">
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md font-medium text-sm flex items-center justify-center h-10"> 
                    Login
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/entrance.png') }}"
                        alt="Login Arrow Icon"
                        class="w-4 h-4 ml-2 object-contain">
                </button>
            </div>
            
        </form>
    </div>

    {{-- Register Link (This is placed outside the white card but still within 'content' section) --}}
    <div class="mt-6 text-sm text-center text-white z-10 flex flex-col items-center">
        <span class="text-xs">Don't have an account?</span>
        <a href="{{ route('register') }}"class="text-sm font-medium underline ml-1" style="color: #79BAFD;">Register</a>
    </div>

@endsection
