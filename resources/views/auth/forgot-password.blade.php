<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - University of Cebu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-blue-500 to-blue-700 relative">
        <!-- Floating Dots Animation -->
        <div class="absolute inset-0 overflow-hidden z-0">
            <div class="animate-pulse space-y-6">
                @for ($i = 0; $i < 40; $i++)
                    <div class="absolute w-1 h-1 bg-white rounded-full opacity-60" style="top: {{ rand(0,100) }}%; left: {{ rand(0,100) }}%;"></div>
                @endfor
            </div>
        </div>

        <div class="z-10 bg-white rounded-md shadow-lg p-8 w-full max-w-md text-center">
            <!-- Logo -->
            <div class="mb-6">
                <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="mx-auto h-16 mb-2">
                <h1 class="text-md font-semibold text-blue-800">University of Cebu</h1>
                <h2 class="text-sm text-blue-600">Lapu-Lapu and Mandaue</h2>
                <p class="text-xs text-gray-500 mt-1">WEB PORTAL - PASSWORD RESET</p>
            </div>

            <!-- Info Message -->
            <div class="mb-4 text-sm text-gray-600">
                {{ __('Forgot your password? No problem. Just enter your email and we will send you a reset link.') }}
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 text-green-600 font-medium text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.email') }}" class="text-left space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-gray-700 text-sm mb-1">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="mt-4">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="white" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512">
                            <path d="M256 0C114.624 0 0 114.624 0 256s114.624 256 256 256 256-114.624 256-256S397.376 0 256 0zm0 96c26.51 0 48 21.49 48 48s-21.49 48-48 48-48-21.49-48-48 21.49-48 48-48zm0 368c-39.764 0-75.251-16.197-101.054-42.256C179.805 382.799 215.38 368 256 368s76.195 14.799 101.054 53.744C331.251 447.803 295.764 464 256 464z"/>
                        </svg>
                        Email Password Reset Link
                    </button>
                </div>

                <!-- Back to Login -->
                <div class="mt-4 text-sm text-center text-blue-700">
                    <a href="{{ route('login') }}" class="hover:underline">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
