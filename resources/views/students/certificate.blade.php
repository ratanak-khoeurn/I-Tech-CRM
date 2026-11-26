<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate — {{ $student->name_english }}</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        @font-face {
            font-family: 'CertTitle';
            src: url('{{ str_replace('\\', '/', storage_path('fonts/UnifrakturMaguntia.ttf')) }}') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'CertScript';
            src: url('{{ str_replace('\\', '/', storage_path('fonts/GreatVibes.ttf')) }}') format('truetype');
            font-weight: normal;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #6388bd; }
        .page { position: relative; width: 297mm; height: 210mm; overflow: hidden; background: #fbfcff; }
        .artwork { position: absolute; top: 0; left: 0; width: 297mm; height: 210mm; }
        .brand { position: absolute; z-index: 2; top: 15mm; right: 18mm; text-align: right; color: #5482bd; font-size: 19pt; font-weight: bold; letter-spacing: .2px; }
        .brand .orange { color: #e4a72f; }
        .tagline { display: block; margin-top: -1mm; color: #5482bd; font-size: 5.5pt; letter-spacing: 1px; }
        .content { position: absolute; z-index: 2; top: 31mm; left: 65mm; width: 167mm; text-align: center; color: #6488bd; }
        .title { margin: 0; color: #507bb7; font-family: 'CertTitle', 'DejaVu Serif', serif; font-size: 48pt; line-height: 1.05; font-weight: normal; }
        .subtitle { margin-top: 1mm; font-family: DejaVu Serif, serif; font-size: 17pt; line-height: 1.25; letter-spacing: .5px; }
        .ribbon { display: inline-block; margin-top: 1.6mm; padding: 2mm 5mm; background: #dfa932; color: #fff; font-family: DejaVu Serif, serif; font-size: 11pt; font-weight: bold; }
        .name { margin: 1mm 0 0; color: #5b83bc; font-family: 'CertScript', 'DejaVu Serif', serif; font-size: 40pt; font-style: normal; line-height: 1.1; }
        .description { margin: 1mm auto 0; max-width: 136mm; color: #6689bd; font-family: DejaVu Serif, serif; font-size: 9pt; font-style: italic; line-height: 1.65; }
        .skills { margin-top: 1mm; }
        .issued { margin-top: 4.5mm; font-family: DejaVu Serif, serif; font-size: 9pt; font-style: italic; }
        .issued-line { display: inline-block; width: 60mm; margin-left: 1mm; padding: 0 1mm 1mm; border-bottom: 1px solid #6488bd; }
        .signatures { position: absolute; z-index: 2; left: 40mm; right: 40mm; bottom: 20mm; display: table; width: 217mm; table-layout: fixed; color: #6488bd; font-family: DejaVu Serif, serif; font-size: 8pt; font-style: italic; text-align: center; }
        .signature { display: table-cell; width: 50%; vertical-align: bottom; }
        .signature-line { width: 55mm; margin: 0 auto 3mm; border-top: 1px solid #6488bd; }
        .role { margin-bottom: 1mm; }
        .person { margin-top: 1mm; }
    </style>
</head>
<body>
    <div class="page">
        <img class="artwork" src="{{ str_replace('\\', '/', public_path('assets/certificate-bg.jpg')) }}" alt="">

        <div class="brand"><span class="orange">i-tech</span>media<span class="tagline">We Own The Future Of Digital</span></div>

        <div class="content">
            <h1 class="title">Certificate</h1>
            <div class="subtitle">OF COMPLETION</div>
            <div class="ribbon">THIS IS TO CERTIFY THAT</div>
            <div class="name">{{ $student->name_english }}</div>
            <div class="description">
                @php
                    $courseModel = $course?->course;
                    $centerName = $course?->center_name ?: \App\Models\Setting::get('center_name', 'I-Tech Media Center');
                    $certText = $courseModel?->certificate_text;
                @endphp
                @if($certText)
                    {!! nl2br(e(strtr($certText, ['{course}' => $courseModel->name ?? '', '{center}' => $centerName]))) !!}
                @else
                    Has successfully completed the {{ $courseModel?->name ?? 'Course of Study' }} course at<br>
                    {{ $centerName }}.
                    @if($courseModel?->description)
                        <div class="skills">The student has demonstrated practical skills in {{ \Illuminate\Support\Str::limit(strip_tags($courseModel->description), 180) }}.</div>
                    @endif
                @endif
            </div>
            <div class="issued">Issued on:<span class="issued-line">{{ $course?->graduation_date?->format('F j, Y') ?? now()->format('F j, Y') }}</span></div>
        </div>

        <div class="signatures">
            <div class="signature">
                <div class="signature-line"></div>
                <div class="role">Course Instructor</div>
                <div class="person">{{ $course?->instructor_name ?: \App\Models\Setting::get('course_instructor_name', '') }}</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div class="role">{{ \App\Models\Setting::get('center_name', 'I-Tech Media Center') }} Manager</div>
                <div class="person">{{ \App\Models\Setting::get('center_manager_name', '') }}</div>
            </div>
        </div>
    </div>
</body>
</html>
