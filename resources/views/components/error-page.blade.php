@props(['code', 'title', 'message'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#022c22">

    <title>{{ $code }} · {{ $title }} · {{ config('app.name') }}</title>

    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f5f5f4;
            color: #292524;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { width: 100%; max-width: 30rem; }
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            font-weight: 700;
            color: #1c1917;
        }
        .mark {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: #059669;
            color: #fff;
            font-size: 18px;
        }
        .card {
            border: 1px solid #e7e5e4;
            border-radius: 18px;
            background: #fff;
            padding: 32px 28px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(28, 25, 23, .05);
        }
        .code {
            margin: 0;
            font-size: 44px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.03em;
            color: #059669;
        }
        h1 { margin: 14px 0 0; font-size: 20px; color: #1c1917; }
        .message { margin: 10px 0 0; font-size: 14px; line-height: 1.6; color: #57534e; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 24px; }
        .actions a {
            display: inline-block;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }
        .actions a.primary { background: #059669; color: #fff; }
        .actions a.primary:hover { background: #047857; }
        .actions a.secondary { border: 1px solid #d6d3d1; background: #fff; color: #44403c; }
        .actions a.secondary:hover { background: #fafaf9; }
        .foot { margin: 18px 0 0; text-align: center; font-size: 12px; color: #a8a29e; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">
            <span class="mark" aria-hidden="true">ک</span>
            <span>{{ config('app.name') }}</span>
        </div>

        <main class="card">
            <p class="code">{{ $code }}</p>
            <h1>{{ $title }}</h1>
            <p class="message">{{ $message }}</p>

            <div class="actions">
                {{ $slot }}
            </div>
        </main>

        <p class="foot">Smart Digital Khata · University of Malakand</p>
    </div>
</body>
</html>
