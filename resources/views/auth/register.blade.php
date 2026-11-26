<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--crm-blue-darker) 0%, var(--crm-blue) 60%, #42a5f5 100%);
            padding: 1rem;
        }
        .login-card { width: 100%; max-width: 460px; border-radius: 1rem; border: none; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
        .login-logo { width: 64px; height: 64px; border-radius: 50%; background: var(--crm-blue-light); color: var(--crm-blue-dark); display: inline-flex; align-items: center; justify-content: center; font-size: 1.9rem; }
    </style>
</head>
<body>
<div class="login-page">
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <span class="login-logo"><i class="bi bi-person-plus-fill"></i></span>
                <h1 class="h4 mt-3 mb-1" style="color: var(--crm-blue-dark); font-weight: 800;">Create Account</h1>
                <p class="text-muted small mb-0">Request access to {{ config('app.name') }}</p>
            </div>

            <div class="alert alert-info py-2 small">
                <i class="bi bi-info-circle me-1"></i>
                New accounts are created as <strong>read-only</strong> and stay <strong>pending</strong> until an
                administrator approves them. You'll be able to sign in once activated.
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.attempt') }}" data-loading>
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Full name <span class="required-mark">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address <span class="required-mark">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="required-mark">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="8">
                        <button class="btn btn-outline-secondary" type="button" onclick="const p=document.getElementById('password'); p.type = p.type==='password' ? 'text':'password';"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="form-text">Minimum 8 characters.</div>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm password <span class="required-mark">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" style="background: var(--crm-blue);">
                    <i class="bi bi-person-check me-1"></i> Create Account
                </button>
            </form>

            <div class="text-center mt-3 small">
                <span class="text-muted">Already have an account?</span>
                <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Sign in</a>
            </div>
        </div>
    </div>
</div>
<div class="loading-overlay" id="loadingOverlay"><div class="spinner-border text-primary"></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
