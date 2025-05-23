{{-- if dark mode show image 1 esle image 2 --}}
@if (session('theme') === 'dark')
    <img src="{{ asset('images/logo/dark-secondary-logo.png') }}" alt="Logo" class="h-[100px]">
@else
    {{-- <img src="{{ asset('images/logo/logo-light.png') }}" alt="Logo"> --}}
    <img src="{{ asset('images/logo/secondary-dark-logo.png') }}" alt="Logo" class="h-[100px]">
@endif
