<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }

        img {
            max-width: 120px;
            height: auto;
        }
    </style>
</head>

<body>
    <h2>Quiz Report</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Topic</th>
                <th>Grade</th>
                <th>Signature</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
            <tr>
                <td>{{ $report['name'] ?? '' }}</td>
                <td>{{ $report['email'] ?? '' }}</td>
                <td>{{ $report['topic'] ?? '' }}</td>
                <td>{{ $report['grade'] ?? '' }}</td>
                <td>
                    @if(!empty($report['signature_path']) && file_exists($report['signature_path']))
                    <img src="{{ $report['signature_path'] }}" width="80">
                    @else
                    Not signed
                    @endif
                </td>

                <td>{{ $report['date'] ?? '' }}</td>
            </tr>
            @endforeach

        </tbody>

    </table>
</body>

</html>