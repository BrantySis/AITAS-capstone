<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UC Web Portal')</title>
    <link rel="stylesheet" href="{{ asset('css/snow.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- 1. Import Open Sans from Google Fonts (for regular weight) --}}
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- 2. Apply Open Sans Globally to the body --}}
    <style>
      body {
        font-family: 'Open Sans', sans-serif;
      }

      #messageModal {
      opacity: 0;
      transition: opacity 0.3s ease;
      }

      #messageModal:not(.hidden) {
      opacity: 1;
      }

    </style>

    @yield('head')
</head>
<body>
    {{-- REUSABLE BACKGROUND WRAPPER with Radial Gradient --}}
    <div class="min-h-screen flex flex-col items-center justify-center 
                relative overflow-hidden" 
         style="background: radial-gradient(circle, #337BB6 60%, #1D4A80 100%);">
        
        <x-snowfall-background/>

        {{-- PAGE-SPECIFIC CONTENT WILL BE INJECTED HERE --}}
        @yield('content') 
        
    </div>
</body>
</html>
