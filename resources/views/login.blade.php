<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HRVision</title>
    <link rel="stylesheet" href="{{ asset('css/hrvision.css') }}">
</head>
<body class="auth-body">
    <main class="auth-page">
        <section class="auth-card">
            <p class="small-label">HRVision Access</p>
            <h1>Sign in to HRVision</h1>
            <p class="muted-text">Use your account email and password to open the dashboard.</p>

            @if($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="form-grid">
                @csrf

                <label class="field-block">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </label>

                <label class="field-block">
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>

                <button type="submit" class="main-button">Sign In</button>
            </form>
        </section>
    </main>
</body>
</html>
