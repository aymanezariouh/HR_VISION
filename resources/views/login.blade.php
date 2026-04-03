<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HRVision</title>
    <link rel="stylesheet" href="{{ asset('css/hrvision.css') }}?v={{ filemtime(public_path('css/hrvision.css')) }}">
</head>
<body class="auth-body">
    <main class="auth-page">
        <section class="auth-shell">
            <div class="auth-form-panel">
                <a href="{{ route('login') }}" class="auth-logo-pill">
                    <img src="{{ asset('images/HR-VISION.png') }}" alt="HRVision">
                </a>

                <div class="auth-copy-block">
                    <h1>Sign in to HRVision</h1>
                    <p>Use your account email and password to open your dashboard.</p>
                </div>

                @if(session('success'))
                    <div class="success-box">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="error-box">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="auth-form-stack">
                    @csrf

                    <label class="field-block">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="hr@example.com" required>
                    </label>

                    <label class="field-block">
                        <span>Password</span>
                        <input type="password" name="password" placeholder="password" required>
                    </label>

                    <button type="submit" class="auth-submit-button">Sign In</button>
                </form>

                <div class="auth-bottom-row">
                    <p>
                        Don't have an account?
                        <a href="{{ route('register') }}">Create one</a>
                    </p>
                </div>
            </div>

            <div class="auth-image-panel">
                <img
                    src="{{ asset('images/auth-side.jpg') }}"
                    alt="HRVision team workspace"
                    class="auth-side-image"
                    onerror="this.remove()"
                >

                <div class="auth-floating-card top-card">
                    <span>Team workflow</span>
                    <strong>Simple HR operations</strong>
                </div>

                <div class="auth-floating-card bottom-card">
                    <span>Quick access</span>
                    <strong>Employees, payroll, expenses</strong>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
