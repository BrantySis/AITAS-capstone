@extends('layouts.mobile.mobile-app')

@section('header_title', 'Settings')

@section('content')

    <div class="space-y-6 pb-20"> {{-- pb-20 ensures space above the bottom navigation --}}
        
        {{-- 1. Profile Information Section --}}
        <div class="p-4 bg-white shadow-lg rounded-xl">
            <h3 class="text-xl font-bold text-gray-800 mb-4 pb-3 border-b border-gray-200">
                Personal Information
            </h3>
            
            {{-- Instructions: Apply .form-group and .primary-btn styling within this partial --}}
            @include('profile.partials.update-profile-information-form')
            
            {{-- Display status message after update (using Tailwind green) --}}
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" 
                   class="text-sm text-green-600 mt-2">
                    Saved.
                </p>
            @endif
        </div>

        {{-- 2. Update Password Section --}}
        <div class="p-4 bg-white shadow-lg rounded-xl">
            <h3 class="text-xl font-bold text-gray-800 mb-4 pb-3 border-b border-gray-200">
                Change Password
            </h3>
            
            {{-- Instructions: Apply .form-group and .primary-btn styling within this partial --}}
            @include('profile.partials.update-password-form')

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" 
                   class="text-sm text-green-600 mt-2">
                    Password Saved.
                </p>
            @endif
        </div>

        {{-- 3. Delete Account Section --}}
        <div class="p-4 bg-white shadow-lg rounded-xl">
            <h3 class="text-xl font-bold text-red-600 mb-4 pb-3 border-b border-gray-200">
                Delete Account
            </h3>
            
            {{-- Instructions: Ensure the button here uses .danger-btn styling --}}
            @include('profile.partials.delete-user-form')
        </div>

        {{-- 4. Logout (Aligned with your system's primary/danger button style) --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full pt-4">
            @csrf
            {{-- Using the danger-btn style for consistency --}}
            <button type="submit" 
                    class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-lg transition duration-150 ease-in-out">
                Log Out
            </button>
        </form>

    </div>

@endsection