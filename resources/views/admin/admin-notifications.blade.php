@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Notifications')

@section('content')
<div class="p-4 space-y-4">
    <p class="text-sm text-gray-400 mb-4 text-center">
        Stay updated on all system activities (e.g., added schedules, updated teachers, new deans)
    </p>

    @forelse($notifications as $notification)
        <div class="rounded-lg shadow-lg p-4 border-l-4
                    @if(!$notification->read_at)
                        bg-[#00477F] text-white border-yellow-400
                    @else
                        bg-white text-[#00477F] border-transparent
                    @endif">
            
            {{-- Header --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                    {{-- Dynamic icon based on type --}}
                    @switch($notification->type)
                        @case('teacher')
                            <svg class="w-5 h-5 @if(!$notification->read_at) text-white opacity-90 @else text-[#00477F] opacity-90 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 1.79-8 4v2h16v-2c0-2.21-3.582-4-8-4z" />
                            </svg>
                            @break
                        @case('dean')
                            <svg class="w-5 h-5 @if(!$notification->read_at) text-white opacity-90 @else text-[#00477F] opacity-90 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 14l9-5-9-5-9 5 9 5z" />
                            </svg>
                            @break
                        @case('room')
                            <svg class="w-5 h-5 @if(!$notification->read_at) text-white opacity-90 @else text-[#00477F] opacity-90 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h18v10H3V10z M5 10V6h14v4" />
                            </svg>
                            @break
                        @case('subject')
                            <svg class="w-5 h-5 @if(!$notification->read_at) text-white opacity-90 @else text-[#00477F] opacity-90 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 20h9M12 4h9M12 12h9M3 6h.01M3 12h.01M3 18h.01" />
                            </svg>
                            @break
                        @default
                            <svg class="w-5 h-5 @if(!$notification->read_at) text-white opacity-90 @else text-[#00477F] opacity-90 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                            </svg>
                    @endswitch

                    <h2 class="text-lg font-semibold @if($notification->read_at) text-[#00477F] @endif">{{ $notification->title }}</h2>

                    {{-- Type badge --}}
                    @if($notification->type)
                        <span class="ml-2 text-xs uppercase px-2 py-0.5 rounded
                                     @if(!$notification->read_at) bg-blue-600 text-white @else bg-blue-100 text-[#00477F] @endif">
                            {{ $notification->type }}
                        </span>
                    @endif
                </div>

                <span class="text-xs @if(!$notification->read_at) text-blue-300 @else text-[#00477F] @endif">{{ $notification->created_at->diffForHumans() }}</span>
            </div>

            {{-- Message --}}
            <p class="text-sm mb-3 @if($notification->read_at) text-[#00477F] @endif">
                {{ $notification->message }}
            </p>

            {{-- Footer --}}
            <div class="flex justify-between items-center text-xs">
                <span class="@if(!$notification->read_at) text-blue-300 @else text-[#00477F] @endif">
                    @if($notification->user)
                        Created by: <span class="font-medium">{{ $notification->user->name }}</span>
                    @else
                        System
                    @endif
                </span>

                @if(!$notification->read_at)
                    <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-500 text-white py-1 px-3 rounded-md uppercase font-medium">
                            Mark as Read
                        </button>
                    </form>
                @else
                    <span class="text-gray-400 italic">Read</span>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center text-gray-400 py-10">
            <p>No notifications yet.</p>
        </div>
    @endforelse

    {{-- Mark All as Read --}}
    @if($notifications->count() > 0)
        <div class="flex justify-center mt-4">
            <form method="POST" action="{{ route('admin.notifications.readAll') }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="bg-blue-700 hover:bg-blue-600 text-white py-2 px-4 rounded-lg font-medium uppercase">
                    Mark All as Read
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
