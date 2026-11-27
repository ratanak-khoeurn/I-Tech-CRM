<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card — {{ $employee->fullName() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Font for Khmer support -->
    <link href="https://fonts.googleapis.com/css2?family=Kantumruuy+Pro:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-blue: #5583be;
            --brand-gold: #e1a63c;
            --text-dark: #222222;
            --text-muted: #555555;
        }

        body {
            background: #edf1f5;
            margin: 0;
            font-family: 'Poppins', 'Kantumruuy Pro', Arial, sans-serif;
            color: var(--text-dark);
        }

        .id-card {
            position: relative;
            width: 260px;
            height: 422px;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .id-header {
            width: 100%;
            padding: 19px 16px 8px;
            text-align: center;
            z-index: 2;
        }

        .org-logo {
            width: 170px;
            height: auto;
            object-fit: contain;
        }

        .portrait-wrap {
            position: relative;
            z-index: 2;
            margin-top: 8px;
            margin-bottom: 16px;
        }

        .id-photo,
        .id-photo-placeholder {
            width: 134px;
            height: 134px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--brand-blue);
            box-shadow: none;
        }

        .id-photo-placeholder {
            background: #e9f0f8;
            color: var(--brand-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.85rem;
            font-weight: 700;
        }

        .divider {
            z-index: 2;
            width: 155px;
            height: 2.5px;
            background-color: var(--brand-gold);
            border-radius: 2px;
            margin-bottom: 13px;
        }

        .id-body {
            position: relative;
            z-index: 2;
            width: 100%;
            padding: 0 16px;
            text-align: center;
        }

        .id-khmer-name {
            font-size: .9rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .id-name {
            font-size: .82rem;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            margin-bottom: 10px;
        }

        .id-role {
            margin: 0;
            font-size: 0.74rem;
            font-weight: 600;
            color: #333333;
        }

        .id-code {
            margin-top: 4px;
            font-size: 0.74rem;
            font-weight: 600;
            color: #333333;
        }

        /* SVG Waves Container at bottom */
        .card-footer-waves {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 97px;
            z-index: 1;
            pointer-events: none;
        }

        @media print {
            /* Everything prints on A4 now: the card keeps its real 54 × 85.6 mm size, centred on the
               sheet with a dashed outline showing where to cut. */
            @page { size: A4 portrait; margin: 12mm; }
            html, body { width: auto; min-height: 0; margin: 0; padding: 0; background: #fff; }
            body { display: block; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .container { width: auto; max-width: none; margin: 0; padding: 0 !important; }
            .container > .d-flex.justify-content-center { display: block !important; }
            .no-print { display: none !important; }
            .id-card { width: 54mm; height: 85.6mm; margin: 0 auto; outline: .3mm dashed #98a6b5; outline-offset: 2.5mm; border-radius: 0; box-shadow: none; }
            .id-header { padding: 4mm 3mm 1.5mm; }
            .org-logo { width: 35mm; max-height: 10mm; }
            .portrait-wrap { margin-top: 1mm; margin-bottom: 2.5mm; }
            .id-photo, .id-photo-placeholder { width: 28mm; height: 28mm; border-width: 1mm; }
            .id-photo-placeholder { font-size: 20pt; }
            .divider { width: 32mm; height: .6mm; margin-bottom: 2mm; }
            .id-body { padding: 0 3mm; }
            .id-khmer-name { margin-bottom: .4mm; font-size: 7pt; }
            .id-name { margin-bottom: 1mm; font-size: 8pt; }
            .id-role, .id-code { font-size: 6.5pt; }
            .id-code { margin-top: 1mm; }
            .card-footer-waves { height: 20mm; }
        }
    </style>
</head>
<body>
<div class="container py-4">
    @unless(request()->boolean('preview'))
        <div class="no-print mb-3 d-flex justify-content-between align-items-center" style="max-width: 260px; margin: 0 auto;">
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    @endunless

    <div class="d-flex justify-content-center">
        <div class="id-card">
            <!-- Header Logo -->
            <div class="id-header">
                <img src="{{ asset('assets/i-tech-logo.png') }}" alt="i-techmedia" class="org-logo">
            </div>

            <!-- Profile Photo -->
            <div class="portrait-wrap">
                @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="Employee Photo" class="id-photo">
                @else
                    <div class="id-photo-placeholder">{{ strtoupper(substr($employee->first_name, 0, 1)) }}</div>
                @endif
            </div>

            <div class="divider"></div>

            <div class="id-body">
                @if(!empty($employee->khmer_name))
                    <div class="id-khmer-name">{{ $employee->khmer_name }}</div>
                @endif
                <div class="id-name">{{ strtoupper($employee->fullName()) }}</div>
                <p class="id-role">{{ $employee->position->title ?? 'Staff' }}</p>
                <div class="id-code">ID: {{ strtoupper($employee->employee_code ?? 'N/A') }}</div>
            </div>

            <!-- Bottom Vector Waves -->
            <div class="card-footer-waves">
                <svg viewBox="0 0 320 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: 100%; display: block;">
                    <path d="M0 35 C 70 35, 120 75, 230 75 C 280 75, 305 60, 320 50 L 320 120 L 0 120 Z" fill="#e1a63c" />
                    <path d="M0 90 C 80 90, 150 115, 240 100 C 280 93, 305 85, 320 80 L 320 120 L 0 120 Z" fill="#5583be" />
                </svg>
            </div>
        </div>
    </div>
</div>
</body>
</html>
