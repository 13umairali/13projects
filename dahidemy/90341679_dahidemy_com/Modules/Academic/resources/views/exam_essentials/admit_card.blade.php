<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Exam Admit Card</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            width: 100%;
            margin: 0 auto;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #1a1a1a;
        }

        .admit-card {
            border: 4px double #14532d;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 10px auto 24px auto;
            max-width: 780px;
            page-break-after: always;
        }

        /* Header */
        .card-header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #14532d;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .card-header .logo-cell,
        .card-header .school-cell,
        .card-header .photo-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .card-header .logo-cell {
            width: 70px;
        }

        .card-header .logo-cell img {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }

        .card-header .school-cell {
            text-align: center;
            padding: 0 10px;
        }

        .card-header .school-cell h1 {
            font-size: 21px;
            margin: 0;
            color: #14532d;
            letter-spacing: 0.5px;
        }

        .card-header .school-cell p {
            font-size: 12px;
            margin: 2px 0 0 0;
        }

        .card-header .photo-cell {
            width: 90px;
            text-align: center;
        }

        .card-header .photo-cell img.student-photo {
            width: 78px;
            height: 90px;
            object-fit: cover;
            border: 1px solid #14532d;
        }

        .card-header .photo-cell .qr-wrap {
            margin-top: 4px;
        }

        .card-title-banner {
            text-align: center;
            background-color: #14532d;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 5px 0;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        /* Student info */
        .student-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .student-info-table td {
            padding: 3px 4px;
        }

        .student-info-table td.label {
            width: 32%;
            font-weight: 600;
            color: #333;
        }

        .student-info-table td.sep {
            width: 2%;
        }

        /* Exam routine */
        .section-label {
            background-color: #f0f4f0;
            border: 1px solid #14532d;
            color: #14532d;
            font-weight: 700;
            font-size: 13px;
            padding: 4px 8px;
            margin-bottom: 0;
        }

        .routine-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .routine-table th,
        .routine-table td {
            border: 1px solid #14532d;
            padding: 4px 6px;
            text-align: center;
        }

        .routine-table th {
            background: #e9f0e9;
            color: #14532d;
        }

        /* Instructions */
        .instructions-box {
            border: 1px solid #999;
            border-radius: 3px;
            padding: 8px 10px;
            font-size: 11px;
            margin-bottom: 14px;
        }

        .instructions-box ol {
            margin: 4px 0 0 16px;
            padding: 0;
        }

        .instructions-box li {
            margin-bottom: 3px;
        }

        /* Signatures */
        .signature-row {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .signature-row .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            font-size: 11px;
            padding-top: 22px;
        }

        .signature-cell .sign-line {
            border-top: 1px solid #333;
            width: 85%;
            margin: 0 auto 3px auto;
        }

        .footer-credit {
            text-align: center;
            font-size: 9px;
            color: #888;
            margin-top: 8px;
        }
    </style>
</head>

<body>

    <div class="noprint" style="text-align:center; padding: 12px; margin-bottom: 10px;">
        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
            {{ _lang('Print') }}
        </button>
        <a href="{{ route('exam-essentials.admit-card.export-pdf', ['class_id' => $classId ?? '', 'section_id' => $sectionId ?? '', 'from_roll' => $fromRoll ?? '', 'to_roll' => $toRoll ?? '']) }}"
            class="btn btn-danger btn-sm">
            {{ _lang('Download PDF') }}
        </a>
        <form action="{{ route('exam-essentials.admit-card.email') }}" method="POST" style="display:inline;"
            onsubmit="return confirm('{{ _lang('Email every admit card on this page to each student\'s own registered email address?') }}');">
            @csrf
            <input type="hidden" name="class_id" value="{{ $classId ?? '' }}">
            <input type="hidden" name="section_id" value="{{ $sectionId ?? '' }}">
            <input type="hidden" name="from_roll" value="{{ $fromRoll ?? '' }}">
            <input type="hidden" name="to_roll" value="{{ $toRoll ?? '' }}">
            <button type="submit" class="btn btn-success btn-sm">
                {{ _lang('Send through Email') }}
            </button>
        </form>
    </div>

    <style>
        @media print {
            .noprint {
                display: none !important;
            }
        }
    </style>

    @foreach ($data as $single)
        @php
            $compulsorySubjects = \Modules\Academic\Models\Subject::select('*', 'subjects.id AS id')
                ->join('classes', 'classes.id', '=', 'subjects.class_id')
                ->whereNull('classes.deleted_at')
                ->where('subjects.class_id', $single->class_id)
                ->whereNull('subjects.group_id')
                ->get();

            $studentSubjects = \Modules\Academic\Models\Subject::select('*', 'subjects.id AS id')
                ->join('classes', 'classes.id', '=', 'subjects.class_id')
                ->whereNull('classes.deleted_at')
                ->where('subjects.class_id', (int) $single->class_id)
                ->where('subjects.group_id', (int) $single->group_id)
                ->get();

            $subjects = $compulsorySubjects->merge($studentSubjects);

            $examSchedule = \Illuminate\Support\Facades\DB::table('exam_schedules')->first();
            $examName = null;
            if ($examSchedule) {
                $examName = \Illuminate\Support\Facades\DB::table('exams')
                    ->where('id', $examSchedule->exam_id)
                    ->first();
            }

            $routineRows = $subjects
                ->map(function ($subject) {
                    $schedule = \Illuminate\Support\Facades\DB::table('exam_schedules')
                        ->where('subject_id', $subject->id)
                        ->first();

                    return [
                        'date' => isset($schedule->date) && $schedule->date
                            ? \Carbon\Carbon::parse($schedule->date)->format('d/m/Y')
                            : '',
                        'subject_name' => $subject->subject_name,
                        'subject_code' => $subject->subject_code,
                        'sort' => $schedule->date ?? '9999-99-99',
                    ];
                })
                ->sortBy('sort')
                ->values();
        @endphp

        <!-- Single Admit-Card Start -->
        <div class="admit-card">
            <div class="card-header">
                <div class="logo-cell">
                    <img src="{{ asset('/' . get_logo_file_path()) }}" alt="Logo">
                </div>
                <div class="school-cell">
                    <h1>{{ get_option('school_name') }}</h1>
                    <p>{{ get_option('address') }}</p>
                </div>
                <div class="photo-cell">
                    <img src="{{ asset('storage/users/' . $single->student?->user?->image) }}" alt="Photo"
                        class="student-photo">
                    <div class="qr-wrap">
                        @php
                            $texts = "ID: $single->student_id\n";
                            $texts .= 'Name: ' . $single->student->first_name . " \n";
                        @endphp
                        {!! QrCode::size(50)->encoding('UTF-8')->generate($texts) !!}
                    </div>
                </div>
            </div>

            <div class="card-title-banner">ADMIT CARD</div>

            <table class="student-info-table">
                <tr>
                    <td class="label">Name</td>
                    <td class="sep">:</td>
                    <td><strong>{{ $single->student->first_name }}</strong></td>
                    <td class="label">Roll No.</td>
                    <td class="sep">:</td>
                    <td><strong>{{ $single->roll }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Student ID</td>
                    <td class="sep">:</td>
                    <td>{{ $single->student->id }}</td>
                    <td class="label">Registration No.</td>
                    <td class="sep">:</td>
                    <td>{{ $single->student_online_registration?->registration_number }}</td>
                </tr>
                <tr>
                    <td class="label">Class</td>
                    <td class="sep">:</td>
                    <td>{{ $single->class->class_name ?? '' }}</td>
                    <td class="label">Section</td>
                    <td class="sep">:</td>
                    <td>{{ $single->section->section_name }}</td>
                </tr>
                <tr>
                    <td class="label">Group</td>
                    <td class="sep">:</td>
                    <td>{{ $single->group?->group_name }}</td>
                    <td class="label">Session</td>
                    <td class="sep">:</td>
                    <td>{{ $single->session->session }}</td>
                </tr>
                <tr>
                    <td class="label">Exam Name</td>
                    <td class="sep">:</td>
                    <td colspan="4"><strong>{{ $examName->name ?? '' }}</strong> &nbsp;
                        (Exam Start Time: 10:00 AM)</td>
                </tr>
            </table>

            <div class="section-label">Exam Routine</div>
            <table class="routine-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">Date</th>
                        <th>Subject</th>
                        <th style="width: 20%;">Subject Code</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($routineRows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['subject_name'] }}</td>
                            <td>{{ $row['subject_code'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="instructions-box">
                <strong>Instructions:</strong>
                <ol>
                    <li>Examinee must enter the exam hall at least 15 minutes before the exam starts.</li>
                    <li>No extra papers will be allowed except the admit and registration card.</li>
                    <li>Examinee must carry all necessary essentials for the exam, e.g. pen, pencil.</li>
                    <li>Authority reserves all rights to expel the examinee due to unfair means in the exam.</li>
                </ol>
            </div>

            <div class="signature-row">
                <div class="signature-cell">
                    <div class="sign-line"></div>
                    Class Teacher
                </div>
                <div class="signature-cell">
                    <div class="sign-line"></div>
                    Controller of Examinations
                </div>
                <div class="signature-cell">
                    <div class="sign-line"></div>
                    Principal
                </div>
            </div>

            <div class="footer-credit">Powered by: CodeNicheBD</div>
        </div>
        <!-- Single Admit-Card End -->
    @endforeach

</body>

</html>