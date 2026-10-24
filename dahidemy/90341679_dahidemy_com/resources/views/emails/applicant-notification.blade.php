<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:32px 0;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="background:linear-gradient(120deg,#4f46e5,#7c3aed);padding:28px 32px;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;">{{ $schoolName }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;color:#0f172a;">Hi {{ $recipientName }},</p>

                            @foreach ($lines as $line)
                                <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">{{ $line }}</p>
                            @endforeach

                            <p style="margin:24px 0 0;font-size:13px;color:#94a3b8;">
                                This is an automated message from {{ $schoolName }}. Please do not reply directly to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
