<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    @include('commons.styles')
    @livewireStyles()

</head>

<body>
    @include('commons.header')

    {{-- @livewire('app-toast') --}}

    <livewire:app-toast  />

    <livewire:alert-box  />


    {{-- <x-features::alert-box /> --}}

    {{-- @include('features::test') --}}


    <main class="d-flex flex-nowrap">

        @include('commons.left')

        <div class="container-fluid">
            <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 1200px;">
                @yield('content')
            </div>
        </div>

    </main>

    @include('commons.footer')

    @include('commons.scripts')
    @yield('add-js-script')
    @livewireScripts()

</body>

</html>