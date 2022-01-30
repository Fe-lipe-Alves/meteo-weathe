<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
{{--    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>--}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0"/>
    <title>{{ config('app.name') }} 25</title>
{{--    <link rel="preconnect" href="https://fonts.googleapis.com">--}}
{{--    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@100;400;600;700&family=Roboto:wght@100;400;700&display=swap" rel="stylesheet" >--}}
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet"/>
    <script src="{{ mix('/js/app.js') }}" defer></script>
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
