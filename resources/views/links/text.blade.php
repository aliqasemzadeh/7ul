<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.shortener.content_title') }}</title>
    <style>
        body {
            margin: 0;
            padding: 1.5rem;
            font-family: Vazirmatn, system-ui, sans-serif;
            line-height: 1.7;
            background: #f8fafc;
            color: #0f172a;
        }
        .content {
            max-width: 48rem;
            margin: 0 auto;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="content">{{ $content }}</div>
</body>
</html>
