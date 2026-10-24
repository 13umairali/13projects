<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Admit Card</title>
</head>

<body style="margin:0; padding:0; font-family:Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f4f4">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff"
                    style="margin:40px 0; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color:#14532d; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:22px;">Exam Admit Card</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px; color:#333333; font-size:15px; line-height:1.6;">
                            <p>Dear {{ $studentName }},</p>
                            <p>Please find your exam admit card attached to this email as a PDF.</p>
                            <p>Bring a printed or digital copy with you to the examination hall.</p>
                        </td>
                    </tr>
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
