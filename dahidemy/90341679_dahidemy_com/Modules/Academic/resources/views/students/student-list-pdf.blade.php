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
    <h3>{{ get_option('school_name') }} - Student List</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Admission No</th>
                <th>Roll</th>
                <th>Class</th>
                <th>Section</th>
                <th>Group</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $data)
                <tr>
                    <td>{{ $data['id'] }}</td>
                    <td>{{ trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')) }}</td>
                    <td>{{ $data['registration_no'] ?? '-' }}</td>
                    <td>{{ $data['roll'] ?? '-' }}</td>
                    <td>{{ $data['class_name'] ?? '-' }}</td>
                    <td>{{ $data['section_name'] ?? '-' }}</td>
                    <td>{{ $data['group_name'] ?? '-' }}</td>
                    <td>{{ $data['phone'] ?? '-' }}</td>
                    <td>{{ $data['gender'] ?? '-' }}</td>
                    <td>{{ ($data['status'] ?? null) == 1 ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
