<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h3>{{ get_option('school_name') }} - Book Issue Report</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Book Code</th>
                <th>Library ID</th>
                <th>Book Name</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($issues as $issue)
                <tr>
                    <td>{{ $issue->id }}</td>
                    <td>{{ $issue->code }}</td>
                    <td>{{ $issue->library_id }}</td>
                    <td>{{ $issue->book_name }}</td>
                    <td>{{ $issue->issue_date ? date('d-M-Y', strtotime($issue->issue_date)) : '-' }}</td>
                    <td>{{ $issue->due_date ? date('d-M-Y', strtotime($issue->due_date)) : '-' }}</td>
                    <td>{{ $issue->return_date ? date('d-M-Y', strtotime($issue->return_date)) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
