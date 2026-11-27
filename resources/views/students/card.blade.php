<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Card — {{ $student->name_english }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruuy+Pro:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        :root {
            --brand-blue: #5583be;
            --brand-gold: #e1a63c;
            --text-dark: #18324f;
            --text-muted: #6b7d90;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem;
            background: #edf1f5;
            color: var(--text-dark);
            font-family: 'Poppins', 'Kantumruuy Pro', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        .card-preview { display: grid; justify-items: center; gap: 1rem; }
        .print-button {
            border: 0;
            border-radius: 999px;
            padding: .7rem 1.2rem;
            background: var(--brand-blue);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(85, 131, 190, .2);
        }
        .student-card {
            position: relative;
            display: flex;
            width: 260px;
            height: 422px;
            overflow: hidden;
            flex-direction: column;
            align-items: center;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .12);
        }
        .id-header { z-index: 2; width: 100%; padding: 16px 14px 6px; text-align: center; }
        .org-logo { width: 150px; height: auto; object-fit: contain; }
        .id-photo, .id-photo-placeholder {
            width: 120px;
            height: 120px;
            border: 5px solid var(--brand-blue);
            border-radius: 50%;
            background: #e9f0f8;
            object-fit: cover;
        }
        .portrait-wrap { position: relative; z-index: 2; margin-top: 6px; margin-bottom: 12px; }
        .id-photo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-blue);
            font-size: 2.55rem;
            font-weight: 700;
        }
        .divider { z-index: 2; width: 145px; height: 2.5px; margin-bottom: 9px; border-radius: 2px; background: var(--brand-gold); }
        .id-body { position: relative; z-index: 2; width: 100%; padding: 0 14px; text-align: center; }
        .id-name { overflow: hidden; margin: 0 0 4px; color: var(--text-dark); font-size: .9rem; font-weight: 700; letter-spacing: .035em; line-height: 1.25; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }
        .id-code { margin-bottom: 9px; color: var(--text-muted); font-size: .64rem; font-weight: 600; letter-spacing: .14em; }
        .id-field { margin-top: 7px; }
        .id-field-label { color: var(--text-muted); font-size: .5rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
        .id-field-value { overflow: hidden; color: var(--text-dark); font-size: .68rem; font-weight: 600; line-height: 1.35; text-overflow: ellipsis; white-space: nowrap; }
        .id-role { display: none; }
        .id-meta { display: grid; gap: 7px; margin-top: 4px; color: var(--text-dark); font-size: .68rem; font-weight: 600; line-height: 1.35; }
        .id-meta div { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .id-meta > div::before { display: block; color: var(--text-muted); font-size: .5rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
        /* .id-meta > div:first-child::before { content: 'Current Course'; }
        .id-meta > div:last-child::before { content: 'Campus'; } */
        .card-footer-waves {
            position: absolute;
            z-index: 1;
            right: 0;
            left: 0;
            bottom: 0;
            height: 97px;
            pointer-events: none;
        }
        .card-footer-waves svg { display: block; width: 100%; height: 100%; }
        .card-footer-info {
            position: absolute;
            z-index: 2;
            right: 15px;
            bottom: 12px;
            left: 15px;
            color: #fff;
            font-size: .52rem;
            font-weight: 600;
            letter-spacing: .12em;
            line-height: 1.3;
            text-align: center;
            text-transform: uppercase;
        }
        @media print {
            /* Everything prints on A4 now: the card keeps its real 54 × 85.6 mm size, centred on the
               sheet with a dashed outline showing where to cut. */
            @page { size: A4 portrait; margin: 12mm; }
            html, body { width: auto; min-height: 0; padding: 0; background: #fff; }
            body { display: block; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card-preview { display: block; }
            .print-button { display: none; }
            .student-card { width: 54mm; height: 85.6mm; margin: 0 auto; outline: .3mm dashed #98a6b5; outline-offset: 2.5mm; border-radius: 0; box-shadow: none; }
            .id-header { padding: 3.5mm 3mm 1mm; }
            .org-logo { width: 32mm; max-height: 9mm; }
            .portrait-wrap { margin-top: .7mm; margin-bottom: 2mm; }
            .id-photo, .id-photo-placeholder { width: 25mm; height: 25mm; border-width: 1mm; }
            .id-photo-placeholder { font-size: 18pt; }
            .divider { width: 30mm; height: .6mm; margin-bottom: 1mm; }
            .id-body { padding: 0 2.5mm; }
            .id-name { margin-bottom: .7mm; font-size: 8pt; }
            .id-code { margin-bottom: 1.7mm; font-size: 5.3pt; }
            .id-field { margin-top: 1.3mm; }
            .id-field-label { font-size: 4.1pt; }
            .id-field-value { font-size: 5.7pt; }
            .id-meta { gap: 1.3mm; margin-top: .8mm; font-size: 5.7pt; }
            .id-meta > div::before { font-size: 4.1pt; }
            .card-footer-waves { height: 20mm; }
            .card-footer-info { right: 2mm; bottom: 2.2mm; left: 2mm; font-size: 4.5pt; }
        }
    </style>
</head>
<body>
@php($course = $student->studentCourses->filter(fn ($enrollment) => $enrollment->isCurrent())->sortByDesc('id')->first())
<div class="card-preview">
    @unless(request()->boolean('preview'))
        <button type="button" class="print-button" onclick="window.print()">Print student card</button>
    @endunless
    <article class="student-card" aria-label="Student identification card">
        <div class="id-header">
            <img src="{{ asset('assets/i-tech-logo.png') }}" alt="i-techmedia" class="org-logo">
        </div>
        <div class="portrait-wrap">
            @if($student->photo)
                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name_english }}" class="id-photo">
            @else
                <div class="id-photo-placeholder">{{ strtoupper(substr($student->name_english, 0, 1)) }}</div>
            @endif
        </div>
        <div class="divider"></div>
        <div class="id-body">
            <div class="id-role">{{ \App\Models\Student::STAGE_LABELS[$student->student_stage] ?? 'Student' }} · {{ \App\Models\Student::GRADUATION_STATUS_LABELS[$student->graduation_status] ?? 'Not Graduated' }}</div>
            <div class="id-name">{{ $student->name_english }}</div>
            <div class="id-code">ID: {{ strtoupper($student->student_code) }}</div>
            <div class="id-meta">
                <div>{{ $course?->course?->name ?? 'Student' }}</div>
                <div>{{ $course?->campus?->name ?? \App\Models\Setting::get('center_name', 'Education Center') }}</div>
            </div>
        </div>
        <div class="card-footer-waves" aria-hidden="true">
            <svg viewBox="0 0 320 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 35 C70 35,120 75,230 75 C280 75,305 60,320 50 L320 120 L0 120 Z" fill="#e1a63c" />
                <path d="M0 90 C80 90,150 115,240 100 C280 93,305 85,320 80 L320 120 L0 120 Z" fill="#5583be" />
            </svg>
        </div>
            <div class="card-footer-info">Student ID Card · Issued {{ now()->format('M Y') }}</div>
    </article>
</div>
