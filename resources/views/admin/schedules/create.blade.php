<!-- schedules create -->
<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <h2 class="text-xl font-bold mb-4">Add Schedule</h2>

        <!-- Show custom conflict error if it exists -->
        @if ($errors->has('conflict'))
            <div class="bg-red-100 text-red-700 px-4 py-2 mb-4 rounded">
                {{ $errors->first('conflict') }}
            </div>
        @endif
        
        <form action="{{ route('admin.schedules.store') }}" method="POST" class="space-y-4">
            @csrf
            <!-- Pass :users and :rooms -->
            <x-schedule-form :users="$teachers" :rooms="$rooms" />
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </form>
    </div>
</x-app-layout>
