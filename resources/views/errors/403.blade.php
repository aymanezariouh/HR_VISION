<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #111827;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            max-width: 520px;
            width: 100%;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        .code {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #dc2626;
        }

        h1 {
            margin: 16px 0 0;
            font-size: 2rem;
        }

        p {
            margin: 16px 0 0;
            line-height: 1.7;
            color: #6b7280;
        }

        a {
            display: inline-block;
            margin-top: 28px;
            padding: 14px 22px;
            border-radius: 14px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="card">
            <div class="code">403 Forbidden</div>
            <h1>Access denied</h1>
            <p>You do not have permission to access this page or perform this action.</p>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}">
                Go back
            </a>
        </section>
    </main>
</body>
</html>
