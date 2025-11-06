@props(['type' => null, 'title' => null, 'body' => null])

@php
    // Color palette
    $greenBase = '#43A047';
    $greenHover = '#388E3C';
    $redBase = '#E53935';
    $redHover = '#D32F2F';
@endphp

<div 
    id="messageModal"
    class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 p-4"
    style="font-family: Inter, sans-serif;"
>
    {{-- MODAL CARD --}}
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-xs text-center transition-all duration-300 transform scale-95" id="messageModalCard">
        
        {{-- ICON --}}
        <div id="messageModalIcon" class="mb-4"></div>

        {{-- TITLE --}}
        <h3 id="messageModalTitle" class="text-xl font-bold mb-2 text-gray-800">
            {{ $title ?? '' }}
        </h3>

        {{-- BODY --}}
        <p id="messageModalBody" class="text-sm text-gray-700 mb-6 px-4">
            {{ $body ?? '' }}
        </p>

        {{-- OK BUTTON --}}
        <button 
            type="button"
            id="messageModalOkButton"
            onclick="closeMessageModal()"
            class="w-full max-w-[100px] bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-full font-medium text-base transition-colors duration-150 shadow-md hover:shadow-lg"
        >
            OK
        </button>
    </div>
</div>

{{-- ✅ JS Section --}}
<script>
    // Close modal
    function closeMessageModal() {
        const modal = document.getElementById('messageModal');
        const card = document.getElementById('messageModalCard');
        card.classList.remove('scale-100');
        card.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 150);
    }

    // Show modal dynamically
    window.addEventListener('show-modal', event => {
        const { type, message } = event.detail || {};
        const modal = document.getElementById('messageModal');
        const title = document.getElementById('messageModalTitle');
        const body = document.getElementById('messageModalBody');
        const icon = document.getElementById('messageModalIcon');
        const button = document.getElementById('messageModalOkButton');
        const card = document.getElementById('messageModalCard');

        // Update title/body
        title.textContent = type === 'error' ? 'Error' : 'Success';
        body.textContent = message || '';

        // Choose icon and color
        if (type === 'error') {
            icon.innerHTML = `
                <svg class="w-14 h-14 mx-auto text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>`;
            button.style.backgroundColor = '{{ $redBase }}';
            button.onmouseover = () => button.style.backgroundColor = '{{ $redHover }}';
            button.onmouseout = () => button.style.backgroundColor = '{{ $redBase }}';
        } else {
            icon.innerHTML = `
                <svg class="w-14 h-14 mx-auto text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>`;
            button.style.backgroundColor = '{{ $greenBase }}';
            button.onmouseover = () => button.style.backgroundColor = '{{ $greenHover }}';
            button.onmouseout = () => button.style.backgroundColor = '{{ $greenBase }}';
        }

        // Animate modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }, 50);
    });
</script>
