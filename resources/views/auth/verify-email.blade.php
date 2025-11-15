@extends('layouts.mobile.mobile-loginSignupUI')

@section('title', 'Verify Email')

@section('content')

    {{-- ✅ Include message modal component --}}
    <x-message-modal />

    <div class="z-10 bg-white rounded-xl shadow-2xl p-6 w-11/12 max-w-xs mx-auto text-center">

        {{-- 🔹 Logo and Title --}}
        <div class="mb-8">
            <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="mx-auto h-20 -mt-4">
            <h2 class="text-[9px] text-blue-700 font-extrabold -mt-4">LAPU-LAPU AND MANDAUE</h2>

            {{-- 🔹 Web Portal Label --}}
            <div class="flex items-center mt-4 mb-4">
                <span class="flex-1 border-t border-gray-300 mr-3"></span>
                <span class="text-sm text-gray-500 font-medium whitespace-nowrap">WEB PORTAL</span>
                <span class="flex-1 border-t border-gray-300 ml-3"></span>
            </div>

            {{-- 🔹 Alert about verification --}}
            <div class="p-3 bg-yellow-100 border-yellow-500 text-yellow-800 text-sm mb-6 rounded-lg" role="alert">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.427 2.723-1.427 3.488 0l7.25 13.5c.76 1.417-.234 3.099-1.895 3.099H2.895c-1.66 0-2.655-1.682-1.895-3.099l7.25-13.5zM10 13a1 1 0 100-2 1 1 0 000 2zm0 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <strong class="font-bold">Email Verification Required:</strong>
                        <p class="mt-1">Please check your inbox and click the verification link sent via email.</p>
                        <p class="mt-1 text-xs text-gray-500">Didn't receive it? You can resend below.</p>
                    </div>
                </div>
            </div>

            {{-- 🔹 Session Status (Triggers Message Modal) --}}
            @if (session('status'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        window.dispatchEvent(new CustomEvent('show-modal', {
                            detail: {
                                type: 'success',
                                message: @json(session('status'))
                            }
                        }));
                    });
                </script>
            @endif

            {{-- 🔹 Form Actions --}}
            <div class="mt-6 flex flex-col space-y-3">
                {{-- Resend Verification Email --}}
                <form method="POST" action="{{ route('email.resend') }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md font-semibold">
                        Resend Verification Email
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 🔹 Logout Button --}}
    <div class="mt-6 text-sm text-center text-white z-10 flex flex-col items-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="text-sm font-medium underline ml-1" style="color: #79BAFD;">
                Log Out
            </button>
        </form>
    </div>

@endsection
