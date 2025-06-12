<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University of Cebu - Web Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-blue-500 to-blue-700 relative overflow-hidden">
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

        <div class="z-10 bg-white rounded-md shadow-lg p-8 w-full max-w-md text-center">
            <!-- Logo -->
            <div class="mb-6">
                <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="mx-auto h-16 mb-2">
                <h1 class="text-md font-semibold text-blue-800">University of Cebu</h1>
                <h2 class="text-sm text-blue-600">Lapu-Lapu and Mandaue</h2>
                <p class="text-xs text-gray-500 mt-1">WEB PORTAL</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-4 text-red-600 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('status'))
                <div class="mb-4 text-green-600 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4 text-left">
                @csrf

                <!-- Email or ID -->
                <div>
                    <label for="email" class="block text-gray-700 text-sm mb-1">ID Number or Email</label>
                    <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @if($errors->has('email'))
                        <div class="mt-2 text-red-600 text-sm">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-gray-700 text-sm mb-1">Password</label>
                    <input id="password" type="password" name="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @if($errors->has('password'))
                        <div class="mt-2 text-red-600 text-sm">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="text-blue-600 rounded border-gray-300 shadow-sm focus:ring-blue-500">
                    <label for="remember_me" class="ml-2 text-sm text-gray-600">Remember Me</label>
                </div>

                <!-- Login Button -->
                <div class="mt-4">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="white" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512">
                            <path
                                d="M192 128c0-35.3 28.7-64 64-64s64 28.7 64 64v48h-32v-48c0-17.7-14.3-32-32-32s-32 14.3-32 32v48h-32v-48zm96 192c0 17.7-14.3 32-32 32s-32-14.3-32-32V224h-48l80-80 80 80h-48v96z" />
                        </svg>
                        Login
                    </button>
                </div>

                <!-- Links -->
                <div class="mt-4 text-sm text-center text-blue-700">
                    <a href="{{ route('register') }}" class="hover:underline">Register</a> |
                    <a href="{{ route('password.request') }}" class="hover:underline">Forgot Your Password?</a> |
                    <a href="#" class="hover:underline">Need Help?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
