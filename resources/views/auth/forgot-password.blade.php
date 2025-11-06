@extends('layouts.mobile.mobile-loginSignupUI')

@section('title', 'Forgot Password')

@section('content')

<div class="z-10 bg-white rounded-xl shadow-2xl p-6 w-11/12 max-w-xs mx-auto text-center">

    {{-- LOGO & HEADER --}}
    <div class="mb-8">
        <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="mx-auto h-20 -mt-4">
        <h2 class="text-[9px] text-blue-700 font-extrabold -mt-4">LAPU-LAPU AND MANDAUE</h2>

        <div class="flex items-center mt-4 mb-4">
            <span class="flex-1 border-t border-gray-300 mr-3"></span>
            <span class="text-sm text-gray-500 font-medium whitespace-nowrap">WEB PORTAL</span>
            <span class="flex-1 border-t border-gray-300 ml-3"></span>
        </div>

        {{-- Laravel session success --}}
        @if (session('status'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showMessageModal('success', 'Email Sent', `{!! addslashes(session('status')) !!}`);
                });
            </script>
        @endif

        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showMessageModal('error', 'Error', `{!! addslashes($errors->first()) !!}`);
                });
            </script>
        @endif

        {{-- FORM --}}
        <form method="POST" action="{{ route('password.email') }}" class="text-left space-y-4">
            @csrf

            {{-- EMAIL FIELD --}}
            <div class="relative">
                <img src="{{ asset('images/aitas-icons/Login&SignUp/envelope.png') }}" 
                    alt="Email Icon"
                    class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 object-contain">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="Your Email"
                    class="w-full -mt-2 pl-10 pr-6 py-5 border border-[#888888] rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-7" /> 
            </div>

            {{-- INFO MESSAGE --}}
            <div class="mb-4 text-xs text-gray-600">
                Enter the email associated with your account. A password reset link will be sent to your email.
            </div>

            {{-- SUBMIT BUTTON --}}
            <div class="mt-4">
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold flex items-center justify-center">
                    Send Reset Link
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/paper-plane.png') }}" 
                        alt="Send Reset Icon"
                        class="w-4 h-4 ml-2 object-contain">
                </button>
            </div>
        </form>
    </div>
</div>

{{-- BACK TO LOGIN --}}
<div class="mt-6 text-sm text-center text-white z-10 flex flex-col items-center">
    <a href="{{ route('login') }}" class="text-sm font-medium underline ml-1" style="color: #79BAFD;">
        Back to login
    </a>
</div>

{{-- REUSABLE MESSAGE MODAL --}}
@include('components.message-modal')

{{-- JS LOGIC TO SHOW MODAL --}}
<script>
function showMessageModal(type, title, body) {
    const modal = document.getElementById('messageModal');
    const iconContainer = document.getElementById('messageModalIcon');
    const titleEl = document.getElementById('messageModalTitle');
    const bodyEl = document.getElementById('messageModalBody');
    const okButton = document.getElementById('messageModalOkButton');

    // Reset content
    iconContainer.innerHTML = '';
    titleEl.textContent = title;
    bodyEl.textContent = body;

    // Success or error styles
    if (type === 'success') {
        iconContainer.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto text-green-600" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 6L9 17l-5-5"/>
            </svg>`;
        okButton.style.backgroundColor = '#43A047';
        okButton.style.setProperty('--tw-bg-opacity', '1');
        okButton.onmouseover = () => okButton.style.backgroundColor = '#388E3C';
        okButton.onmouseleave = () => okButton.style.backgroundColor = '#43A047';
    } else if (type === 'error') {
        iconContainer.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto text-red-600" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>`;
        okButton.style.backgroundColor = '#E53935';
        okButton.onmouseover = () => okButton.style.backgroundColor = '#D32F2F';
        okButton.onmouseleave = () => okButton.style.backgroundColor = '#E53935';
    }

    modal.classList.remove('hidden');
}
</script>

@endsection
