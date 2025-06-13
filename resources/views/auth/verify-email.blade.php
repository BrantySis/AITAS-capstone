<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - University of Cebu</title>
            <style>
        @keyframes snow-fall {
            0% {
                transform: translateY(-10vh);
                opacity: 0.8;
            }
            100% {
                transform: translateY(110vh);
                opacity: 0;
            }
        }

        .snow-dot {
            position: absolute;
            top: 0;
            border-radius: 9999px;
            background-color: white;
            opacity: 0.7;
            animation-name: snow-fall;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }
    </style>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-blue-500 to-blue-700 relative">
        <!-- Snowfall Background -->
        <div class="absolute inset-0 z-0 pointer-events-none">
           @for ($i = 0; $i < 50; $i++)
    @php
        $duration = rand(10, 20); // seconds
        $delay = 0; // no delay for immediate fall
        $size = rand(2, 10); // snowflake size in px
        $left = rand(0, 100); // % from left
        $top = rand(-100, 100); // random vertical start
        $drift = rand(3, 6); // drift duration
    @endphp
    <div class="snow-dot"
        style="
            width: {{ $size }}px;
            height: {{ $size }}px;
            left: {{ $left }}%;
            top: {{ $top }}vh;
            animation: snow-fall {{ $duration }}s linear 0s infinite,
                       snow-drift {{ $drift }}s ease-in-out 0s infinite;
        ">
    </div>
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
