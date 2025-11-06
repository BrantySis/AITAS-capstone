<!-- Snowfall -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        @for ($i = 0; $i < 50; $i++)
        @php
            $duration = rand(10, 20);
            $size = rand(2, 10);
            $left = rand(0, 100);
            $top = rand(-100, 100);
        @endphp
        <div class="snow-dot"
            style="width: {{ $size }}px; height: {{ $size }}px; left: {{ $left }}%; top: {{ $top }}vh; animation: snow-fall {{ $duration }}s linear 0s infinite;">
        </div>
        @endfor
    </div>