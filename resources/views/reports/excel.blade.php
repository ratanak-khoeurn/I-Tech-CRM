<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta charset="utf-8">
@verbatim
    <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
    <x:Name>@endverbatim{{ \Illuminate\Support\Str::limit($title, 25, '') }}@verbatim</x:Name>
    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
    </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
@endverbatim
    <style>
        table { border-collapse: collapse; }
        th { background: #1565c0; color: #fff; font-weight: bold; padding: 4px 8px; border: 1px solid #999; }
        td { padding: 3px 8px; border: 1px solid #ccc; mso-number-format: "\@"; }
    </style>
</head>
<body>
    <h3>{{ $title }}</h3>
    <p>Generated {{ now()->format('M d, Y H:i') }} — {{ \App\Models\Setting::get('center_name', 'Education Center') }}</p>
    <table>
        <thead>
            <tr>@foreach($columns as $c)<th>{{ $c }}</th>@endforeach</tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
