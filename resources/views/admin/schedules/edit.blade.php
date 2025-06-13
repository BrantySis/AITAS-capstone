<!-- schedules edit -->
<x-app-layout>
     <!-- Back Button -->
     <div class="max-w-7xl -ml-1 mx-auto mb-6">
        <a href="{{ route('dashboard.admin') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>
    
    <div class="max-w-2xl mx-auto">
        <h2 class="text-xl font-bold mb-4">Edit Schedule</h2>
        
        <!-- Show custom conflict error if it exists -->
        @if ($errors->has('conflict'))
            <div class="bg-red-100 text-red-700 px-4 py-2 mb-4 rounded">
                {{ $errors->first('conflict') }}
            </div>
        @endif
        
        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <!-- Pass :users and :rooms (not :teacher) -->
            <x-schedule-form :schedule="$schedule" :users="$teachers" :rooms="$rooms" />
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        </form>
    </div>
</x-app-layout>
