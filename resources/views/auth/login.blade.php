<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            margin: 0;
            height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            background-image:
                linear-gradient(rgba(15, 23, 42, 0.70),
                    rgba(15, 23, 42, 0.70)),
                url('{{ asset('assets/i-tech-logo.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            overflow: hidden;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .logo {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 18px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.25);
        }

        .login-card {
            width: 420px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            animation: fadeIn 0.6s ease;
        }

        .login-logo {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--crm-blue-light);
            color: var(--crm-blue-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.9rem;
        }

        .demo-creds {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: .5rem;
            font-size: .78rem;
        }
    </style>
</head>

<body>
    <div class="login-page">
        <div class="card login-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="text-center">
                        <img src="{{ asset('assets/logo.png') }}" class="logo" alt="Logo">
                        <h2 class="title">
                            <span style="color: #FBAD3F; font-weight: 800;">I-Tech</span><span style="color: #2389CA; font-weight: 800;"> Media</span>
                        </h2>
                    </div>
                    <p class="text-muted small mb-0">Alumni Relationship Management System</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success py-2 small">
                        <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" data-loading>
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address <span
                                class="required-mark">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="required-mark">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="const p=document.getElementById('password'); p.type = p.type==='password' ? 'text':'password';"><i
                                    class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    @if($campuses->count() > 1)
                        <div class="mb-3">
                            <label for="campus_id" class="form-label">Campus <span class="text-muted small">(optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-building"></i></span>
                                <select class="form-select @error('campus_id') is-invalid @enderror" id="campus_id" name="campus_id">
                                    <option value="">My default campus</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" {{ (int) old('campus_id') === (int) $campus->id ? 'selected' : '' }}>
                                            {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('campus_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @endif
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold"
                        style="background: var(--crm-blue);">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </button>
                </form>

                <div class="text-center mt-3 small">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Create one</a>
                </div>
            </div>
        </div>
    </div>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
