<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'AITAS Admin') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="[background-color:#ECFAFF] min-h-screen">

    <!-- Topbar -->
    @include('partials.topbar') {{-- If you created a separate topbar partial --}}

    <!-- Layout -->
    <div class="flex">
        <!-- Sidebar -->
        @include('partials.sidebar') {{-- Include your existing sidebar here --}}

        <!-- Main Content -->
        <main class="flex-1 p-10">
            {{ $slot }}
        </main>
    </div>

</body>
</html>
