<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | HRVision</title>
    <link rel="stylesheet" href="{{ asset('css/hrvision.css') }}?v={{ filemtime(public_path('css/hrvision.css')) }}">
</head>
<body class="auth-body auth-scroll-page">
    <main class="auth-page">
        <section class="auth-shell">
            <div class="auth-form-panel">
                <a href="{{ route('login') }}" class="auth-logo-pill">
                    <img src="{{ asset('images/HR-VISION.png') }}" alt="HRVision">
                </a>

                <div class="auth-copy-block">
                    <h1>Create an account</h1>
                    <p>Register your employee login and continue to your HRVision dashboard.</p>
                </div>

                @if($errors->any())
                    <div class="error-box">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}" class="auth-form-stack">
                    @csrf

                    <label class="field-block">
                        <span>Full name</span>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required>
                    </label>

                    <label class="field-block">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="employee@example.com" required>
                    </label>

                    <label class="field-block">
                        <span>Phone</span>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+212600000000">
                    </label>

                    <label class="field-block">
                        <span>Password</span>
                        <input type="password" name="password" placeholder="At least 8 characters" required>
                    </label>

                    <label class="field-block">
                        <span>Confirm password</span>
                        <input type="password" name="password_confirmation" placeholder="Repeat your password" required>
                    </label>

                    <button type="submit" class="auth-submit-button">Create Account</button>
                </form>

                <div class="auth-bottom-row">
                    <p>
                        Already have an account?
                        <a href="{{ route('login') }}">Sign in</a>
                    </p>
                </div>
            </div>

            <div class="auth-image-panel">
                <img
                    src="{{ asset('images/auth-side.jpg') }}"
                    alt="HRVision employee workspace"
                    class="auth-side-image"
                    onerror="this.remove()"
                >

                <div class="auth-floating-card top-card">
                    <span>People data</span>
                    <strong>Organized employee records</strong>
                </div>

                <div class="auth-floating-card bottom-card">
                    <span>Secure access</span>
                    <strong>Role-based HR dashboard</strong>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
