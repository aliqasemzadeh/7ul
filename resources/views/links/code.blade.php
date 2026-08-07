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
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #0f172a;
            color: #e2e8f0;
        }
        pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <pre>{{ $content }}</pre>
</body>
</html>
