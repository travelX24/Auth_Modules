@props(['title' => null])

@php
    $locale = app()->getLocale();
    $isRtl  = in_array($locale, ['ar', 'ar_YE', 'ar-SA', 'ar-EG']);
@endphp

<!doctype html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
@vite(['resources/css/app.css','resources/js/app.js'])

</head>
<style>
    :root{
      --font-ltr: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
      --font-rtl: "Cairo", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
    }
  
    body{
      font-family: var(--font-ltr);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      text-rendering: optimizeLegibility;
    }
  
    html[dir="rtl"] body{
      font-family: var(--font-rtl);
      letter-spacing: 0;
    }
  </style>
  
  
<x-ui.toast />
<body class="antialiased {{ $isRtl ? 'font-ar' : 'font-sans' }}">
    {{ $slot }}
</body>
</html>
