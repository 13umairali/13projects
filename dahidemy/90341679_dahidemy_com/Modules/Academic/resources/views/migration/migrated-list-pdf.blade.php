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
    <h3>{{ get_option('school_name') }} - Migrated List</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Admission No</th>
                <th>Migrated From</th>
                <th>Migrated To</th>
                <th>Prev Roll</th>
                <th>New Roll</th>
                <th>Type</th>
                <th>School (if external)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($migratedLists as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ trim(($row->first_name ?? '').' '.($row->last_name ?? '')) }}</td>
                    <td>{{ $row->registration_no ?? '-' }}</td>
                    <td>{{ optional($row->class)->class_name ?? '-' }} / {{ optional($row->section)->section_name ?? '-' }}</td>
                    <td>{{ optional($row->migratedToClass)->class_name ?? '-' }} / {{ optional($row->migratedToSection)->section_name ?? '-' }}</td>
                    <td>{{ $row->prev_roll ?? '-' }}</td>
                    <td>{{ $row->new_roll ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $row->type)) }}</td>
                    <td>{{ $row->transferred_to_school_name ?? '-' }}</td>
                    <td>{{ optional($row->migrated_at)->format('Y-m-d') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
