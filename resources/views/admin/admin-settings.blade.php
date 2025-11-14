@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Settings')

@section('content')

{{-- Clean light gray background for the page container --}}
<div class="bg-gray-150 -m-6 p-4 min-h-screen space-y-6">

    {{-- Header for the section --}}
    <h2 class="text-2xl font-semibold text-gray-800 tracking-tight flex items-center mb-6">
        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.525.322 1.017.37 1.442.302z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        Account Settings
    </h2>
    
    {{-- 1. Personal Information Card (Clean White Card) --}}
    <div class="bg-white rounded-xl shadow-xl p-6 border-t-4 border-blue-600">
        <h3 class="text-xl font-bold text-gray-900">Personal Information</h3>
        <p class="text-sm text-gray-500 mt-1 mb-4">Update your account's profile information and email address.</p>
        
        <form action="#" method="POST" class="space-y-4">
            <div>
                {{-- Labels are slightly darker gray for better contrast --}}
                <label for="full_name" class="block text-sm font-medium text-gray-700">Full name</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    value="Admin User" 
                    {{-- Inputs are white background with professional focus ring --}}
                    class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm p-3 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-150"
                >
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="admin@uclm.e" 
                    class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm p-3 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-150"
                >
            </div>
            
            <div class="flex justify-end pt-2">
                <button 
                    type="submit" 
                    {{-- Button uses stronger shadow and hover effects --}}
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition duration-200 uppercase"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- 2. Change Password Card (Clean White Card) --}}
    <div class="bg-white rounded-xl shadow-xl p-6 border-t-4 border-blue-500">
        <h3 class="text-xl font-bold text-gray-900">Change Password</h3>
        <p class="text-sm text-gray-500 mt-1 mb-4">Ensure your account is using a long, random password to stay secure.</p>
        
        <form action="#" method="POST" class="space-y-4">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm p-3 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-150"
                    placeholder="Current password"
                >
            </div>
            
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm p-3 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-150"
                    placeholder="New password"
                >
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm p-3 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-150"
                    placeholder="Confirm new password"
                >
            </div>
            
            <div class="flex justify-end pt-2">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition duration-200 uppercase"
                >
                    Update Password
                </button>
            </div>
        </form>
    </div>

    {{-- 3. Delete Account Card (Danger Zone) --}}
    <div class="bg-white rounded-xl shadow-xl p-6 border-t-4 border-red-600">
        <h3 class="text-xl font-bold text-red-600">Danger Zone: Delete Account</h3>
        <p class="text-sm text-gray-700 mt-1 mb-6">
            Once your account is deleted, all of its resources and data will be permanently deleted. **This action cannot be undone.** Before deleting your account, please download any data or information that you wish to retain.
        </p>
        
        <button 
            type="button" 
            {{-- Delete button is full red and bold --}}
            class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-lg font-bold rounded-lg shadow-lg transition duration-200 uppercase tracking-wider"
        >
            Delete Account
        </button>
    </div>

    {{-- 4. Log Out Button (Separate Card/Component Look) --}}
    <div class="pt-2">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button 
                type="submit" 
                {{-- Log Out button is less aggressive than Delete, using a secondary red/gray shade --}}
                class="w-full px-6 py-3 bg-gray-100 hover:bg-red-50 text-red-600 text-lg font-bold rounded-xl border border-gray-300 hover:border-red-600 shadow-md transition duration-200 uppercase tracking-wider"
            >
                Log Out
            </button>
        </form>
    </div>

</div>

@endsection