@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Settings')

@section('content')

{{-- 
  This outer container adds space between the cards.
  The -m-6 and p-4 with a light blue background gives the 
  "page" a different color from the "white-board" background.
--}}
<div class="bg-[#EBF8FF] -m-6 p-4 min-h-screen space-y-6">

    {{-- 1. Personal Information Card --}}
    <div class="bg-[#142D54] text-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-bold">Personal Information</h3>
        <p class="text-sm text-gray-300 mt-1 mb-4">Update your account's profile information and email address.</p>
        
        {{-- You would wrap this in a <form> tag --}}
        <form action="#" method="POST" class="space-y-4">
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-300">Full name</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    value="Admin User" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="admin@uclm.e" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>
            
            <div class="flex justify-end">
                <button 
                    type="submit" 
                    class="mt-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
                >
                    SAVE
                </button>
            </div>
        </form>
    </div>

    {{-- 2. Change Password Card --}}
    <div class="bg-[#142D54] text-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-bold">Change Password</h3>
        <p class="text-sm text-gray-300 mt-1 mb-4">Ensure your account is using a long, random password to stay secure.</p>
        
        <form action="#" method="POST" class="space-y-4">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-300">Current Password</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Current password"
                >
            </div>
            
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-300">New Password</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="New password"
                >
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Confirm new password"
                >
            </div>
            
            <div class="flex justify-end">
                <button 
                    type="submit" 
                    class="mt-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
                >
                    SAVE
                </button>
            </div>
        </form>
    </div>

    {{-- 3. Delete Account Card --}}
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-bold text-red-600">Delete Account</h3>
        <p class="text-sm text-gray-700 mt-1 mb-4">
            Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
        </p>
        
        <button 
            type="button" 
            class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg shadow-md transition duration-200"
        >
            DELETE ACCOUNT
        </button>
    </div>

    {{-- 4. Log Out Button (Separate Component) --}}
    <div class="pt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button 
                type="submit" 
                class="w-full px-6 py-3 bg-red-700 hover:bg-red-800 text-white text-lg font-bold rounded-lg shadow-lg transition duration-200"
            >
                LOG OUT
            </button>
        </form>
    </div>

</div>

@endsection