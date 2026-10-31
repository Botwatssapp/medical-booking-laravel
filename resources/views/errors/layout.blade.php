<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SantéConnect — {{ $title }}</title>
    @include('partials.head-icons')
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, system-ui, sans-serif;
            background: #faf8ff;
            color: #131b2e;
            display: flex;
            flex-direction: column;
        }
        header, main, footer { width: 100%; }
        header {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #e2e7ff;
            background: rgba(250, 248, 255, 0.92);
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: inherit;
            text-decoration: none;
            font-weight: 700;
        }
        .mark {
            width: 2rem;
            height: 2rem;
            border-radius: 0.6rem;
            background: #2563eb;
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 0.85rem;
        }
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .card {
            width: 100%;
            max-width: 34rem;
            background: #fff;
            border-radius: 1.25rem;
            padding: 1.75rem 1.5rem;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
        }
        .code {
            display: inline-flex;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            background: #e2e7ff;
            color: #004ac6;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        h1 { margin: 0.75rem 0 0.5rem; font-size: clamp(1.4rem, 4vw, 1.85rem); }
        p { margin: 0; color: #434655; line-height: 1.55; }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 1.4rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: 0.6rem 1rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-primary { background: #0b1220; color: #fff; }
        .btn-secondary { background: #f2f3ff; color: #131b2e; }
        footer {
            padding: 1rem;
            text-align: center;
            color: #737686;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <header>
        <a class="brand" href="{{ url('/') }}">
            <span class="mark" aria-hidden="true">♥</span>
            SantéConnect
        </a>
    </header>
    <main>
        <div class="card">
            <span class="code">{{ $code }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
            <div class="actions">
                @isset($primaryHref)
                    <a class="btn btn-primary" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>
                @endisset
                <a class="btn btn-secondary" href="{{ url('/') }}">Retour à l’accueil</a>
            </div>
        </div>
    </main>
    <footer>© {{ now()->year }} SantéConnect</footer>
</body>
</html>
