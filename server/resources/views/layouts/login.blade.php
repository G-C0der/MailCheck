<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Title -->
    <title>{{ config("app.name") }} @isset($title) - {{ $title }}@endisset</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Backend assets -->
    <link href="{{ mix("css/login.css") }}" rel="stylesheet">
    <script type="text/javascript" src="{{ mix("js/login.js") }}"></script>
</head>
<body class="crm">
@yield('content')
</body>
</html>
