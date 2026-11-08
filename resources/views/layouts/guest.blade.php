<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} · SISMAT</title>
    <link rel="stylesheet" href="{{ asset('vendor/admina/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/admina/remixicon.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="auth-page">
    {{ $slot }}
    @livewireScripts
</body>

</html>
