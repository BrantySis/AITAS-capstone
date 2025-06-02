<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Password - University of Cebu</title>
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
            <!-- Logo and Header -->
            <div class="mb-6">
                <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="mx-auto h-16 mb-2">
                <h1 class="text-md font-semibold text-blue-800">University of Cebu</h1>
                <h2 class="text-sm text-blue-600">Lapu-Lapu and Mandaue</h2>
                <p class="text-xs text-gray-500 mt-1">WEB PORTAL - CONFIRM PASSWORD</p>
            </div>

            <!-- Message -->
            <div class="mb-4 text-sm text-gray-600">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('password.confirm') }}" class="text-left space-y-4">
                @csrf

                <!-- Password -->
                <div>
                    <label for="password" class="block text-gray-700 text-sm mb-1">Password</label>
                    <input id="password" type="password" name="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        autocomplete="current-password">
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Button -->
                <div class="mt-4">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="white" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512">
                            <path d="M256 48C141.1 48 48 141.1 48 256s93.1 208 208 208 208-93.1 208-208S370.9 48 256 48zm96 232H160v-48h192v48z"/>
                        </svg>
                        Confirm Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
