{{-- if dark mode show image 1 esle image 2 --}}
@if (session('theme') === 'dark')
    <img src="{{ asset('images/logo/logo-dark.png') }}" alt="Logo">
@else
    <img src="{{ asset('images/logo/logo-light.png') }}" alt="Logo">
@endif
