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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&family=Cairo:wght@300;400;600;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
@vite(['resources/css/app.css','resources/js/app.js'])

</head>
<style>
    :root{
      --font-ltr: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
      --font-rtl: "Tajawal", "Cairo", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Tahoma, Arial, sans-serif;
    }
  
    body{
      font-family: var(--font-ltr);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      text-rendering: optimizeLegibility;
    }
  
    html[dir="rtl"] body{
      font-family: var(--font-rtl);
      font-weight: 400;
      line-height: 1.7;
      letter-spacing: 0;
    }
    
    html[dir="rtl"] h1,
    html[dir="rtl"] h2,
    html[dir="rtl"] h3,
    html[dir="rtl"] h4,
    html[dir="rtl"] h5,
    html[dir="rtl"] h6 {
      font-family: "Cairo", "Tajawal", sans-serif;
      font-weight: 700;
      line-height: 1.4;
      letter-spacing: 0;
    }
    
    html[dir="rtl"] button,
    html[dir="rtl"] .btn,
    html[dir="rtl"] [type="button"],
    html[dir="rtl"] [type="submit"] {
      font-family: "Tajawal", "Cairo", sans-serif;
      font-weight: 500;
      letter-spacing: 0;
    }
    
    html[dir="rtl"] input,
    html[dir="rtl"] textarea,
    html[dir="rtl"] select {
      font-family: "Tajawal", "Cairo", sans-serif;
      font-weight: 400;
    }
    
    html[dir="rtl"] a {
      font-weight: 500;
    }
  </style>
  
  
<x-ui.toast />
<body class="antialiased {{ $isRtl ? 'font-ar' : 'font-sans' }}">
    {{ $slot }}
</body>
</html>
