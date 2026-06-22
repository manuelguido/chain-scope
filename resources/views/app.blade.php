<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'ChainScope') }}</title>

    <link rel="icon" type="image/png" href="/favicon.png">

    <link rel="stylesheet" href="https://use.typekit.net/ccz3qha.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>