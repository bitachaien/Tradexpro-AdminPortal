{{-- <nav class="header">

        <a class="imgFluid" href="{{route('adminDashboard')}}">
            <img src="{{show_image(Auth::user()->id,'logo')}}"  alt="img">
        </a>

    <div class="mobile_bar">
        <ul class="flex-grow justify-end pr-2 ">

            <li>
                <a href="{{ route('adminDashboard') }}" >
                    @include('translation::icons.dashboard')
                    {{ __('Dashboard') }}
                </a>
            </li>
            <li>
                <a href="{{ route('languages.index') }}" class="{{ set_active('') }}{{ set_active('/create') }}">
                    @include('translation::icons.globe')
                    {{ __('translation::translation.languages') }}
                </a>
            </li>
            <li>
                <a href="{{ route('languages.translations.index', config('app.locale')) }}" class="{{ set_active('*/translations') }}">
                    @include('translation::icons.translate')
                    {{ __('translation::translation.translations') }}
                </a>
            </li>
        </ul>
    </div>

</nav> --}}

<nav class="header headerLanguage">

    <a class="imgFluid" href="{{route('adminDashboard')}}">
        <img src="{{show_image(Auth::user()->id,'logo')}}"  alt="img">
    </a>

    <div id="mobileButton"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
</svg>
</div>

    <div class="mobile_bar">
        <ul class="flex-grow justify-end pr-2 ">

            <li>
                <a href="{{ route('adminDashboard') }}" >
                    @include('translation::icons.dashboard')
                    {{ __('Dashboard') }}
                </a>
            </li>
            <li>
                <a href="{{ route('languages.index') }}" class="{{ set_active('') }}{{ set_active('/create') }}">
                    @include('translation::icons.globe')
                    {{ __('translation::translation.languages') }}
                </a>
            </li>
            <li>
                <a href="{{ route('languages.translations.index', config('app.locale')) }}" class="{{ set_active('*/translations') }}">
                    @include('translation::icons.translate')
                    {{ __('translation::translation.translations') }}
                </a>
            </li>
        </ul>
    </div>

</nav>
<div id="myDropdown" class="mobileMenu">
    <ul class="flex-grow justify-end pr-2 mobileMenuAll">
        <li>
            <a href="{{ route('adminDashboard') }}" >
                @include('translation::icons.dashboard')
                {{ __('Dashboard') }}
            </a>
        </li>
        <li>
            <a href="{{ route('languages.index') }}" class="{{ set_active('') }}{{ set_active('/create') }}">
                @include('translation::icons.globe')
                {{ __('translation::translation.languages') }}
            </a>
        </li>
        <li>
            <a href="{{ route('languages.translations.index', config('app.locale')) }}" class="{{ set_active('*/translations') }}">
                @include('translation::icons.translate')
                {{ __('translation::translation.translations') }}
            </a>
        </li>
    </ul>
</div>
