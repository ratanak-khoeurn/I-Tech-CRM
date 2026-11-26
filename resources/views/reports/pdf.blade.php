<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; color: #0d47a1; margin-bottom: 2px; }
        .meta { color: #666; font-size: 9px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #1565c0; color: #fff; padding: 5px 6px; text-align: left; font-size: 10px; }
        td { padding: 4px 6px; border-bottom: 1px solid #ddd; font-size: 10px; }
        tr:nth-child(even) td { background: #f4f8fd; }
        .footer { margin-top: 16px; color: #999; font-size: 8px; }
    </style>
</head>
<body>
    <h1>{{ \App\Models\Setting::get('center_name', 'Education Center') }}</h1>
    <div class="meta">Report: <strong>{{ $title }}</strong> · Generated {{ now()->format('M d, Y H:i') }} by {{ auth()->user()->name }}<br>Filters: {{ $filters }}</div>
    <table>
        <thead>
            <tr>@foreach($columns as $c)<th>{{ $c }}</th>@endforeach</tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($columns) }}">No data available.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Total rows: {{ count($rows) }} · Confidential — internal use only.</div>
</body>
</html>
