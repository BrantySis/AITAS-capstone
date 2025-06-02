<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - University of Cebu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-blue-500 to-blue-700 relative">
        <!-- Floating dots background -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            @for ($i = 0; $i < 40; $i++)
                <div class="absolute w-1 h-1 bg-white rounded-full opacity-50 animate-pulse"
                     style="top: {{ rand(0, 100) }}%; left: {{ rand(0, 100) }}%;"></div>
            @endfor
        </div>

        <!-- Main Card -->
        <div class="relative z-10 bg-white shadow-lg rounded-lg p-8 max-w-md w-full text-center">
            <!-- UC Header -->
            <div class="mb-6">
                <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="h-16 mx-auto mb-2">
                <h1 class="text-lg font-semibold text-blue-800">University of Cebu</h1>
                <h2 class="text-sm text-blue-600">Lapu-Lapu and Mandaue</h2>
                <p class="text-xs text-gray-500 mt-1">WEB PORTAL - VERIFY EMAIL</p>
            </div>

            <!-- Message -->
            <div class="text-sm text-gray-600 mb-4">
                {{ __('Thanks for signing up! Before getting started, please verify your email address by clicking the link we just emailed to you.') }}
                {{ __("If you didn’t receive the email, we’ll gladly send you another.") }}
            </div>

            <!-- Success Flash Message -->
            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ __('A new verification link has been sent to your email address.') }}
                </div>
            @endif

            <!-- Forms -->
            <div class="mt-6 flex flex-col space-y-3">
                <!-- Resend -->
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md font-semibold">
                        Resend Verification Email
                    </button>
                </form>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-sm text-gray-600 underline hover:text-gray-900">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
