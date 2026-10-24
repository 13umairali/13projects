<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Certificate</title>
</head>

<body style="margin:0; padding:0; font-family:Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f4f4">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff"
                    style="margin:40px 0; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#3F51B5; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:22px;">🎓 {{ $certificateTypeLabel }}</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333333; font-size:15px; line-height:1.6;">
                            <p>Dear {{ $studentName }},</p>

                            <p>
                                Please find your <strong>{{ $certificateTypeLabel }}</strong>
                                attached to this email as a PDF.
                            </p>

                            <p>
                                If you have any questions about this document, please contact
                                your institute's administration office.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f4f4f4; padding:15px; text-align:center; font-size:12px; color:#999999;">
                            This is an automated message. Please do not reply directly to this email.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
