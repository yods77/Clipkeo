<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Clipkeo</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f9fafb; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9fafb; padding: 40px 10px;">
        <tr>
            <td align="center">
                
                <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.03); border: 1px solid #edf2f7;" cellspacing="0" cellpadding="0" border="0">
                    
                    <tr>
                        <td align="center" style="padding: 40px 40px 10px 40px;">
                            <img src="https://img.freepik.com/free-vector/forgot-password-concept-illustration_114360-1095.jpg" alt="Security Illustration" width="220" style="display: block; height: auto; border: 0;">
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 10px 40px 20px 40px;">
                            <div style="font-size: 24px; font-weight: 800; color: #111111; letter-spacing: -0.5px; text-transform: uppercase;">
                                Clip<span style="color: #a3cc00;">keo</span>
                            </div>
                            <div style="height: 3px; width: 30px; background-color: #C8FF00; margin-top: 8px; border-radius: 2px;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 50px; text-align: center;">
                            <h2 style="color: #1a202c; font-size: 22px; font-weight: 700; margin: 0 0 10px 0;">Lupa Password?</h2>
                            <p style="color: #4a5568; font-size: 15px; line-height: 1.6; margin: 0;">
                                Jangan khawatir bro, itu biasa terjadi. Gunakan kode verifikasi (OTP) di bawah ini untuk mengatur ulang password akun kamu:
                            </p>
                            
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <div style="background-color: #f8fafc; border: 2px solid #e2e8f0; padding: 20px; border-radius: 16px; display: inline-block; min-width: 240px;">
                                            <span style="font-size: 40px; font-weight: 800; color: #111111; letter-spacing: 12px; font-family: 'Courier New', Courier, monospace;">
                                                {{ $otp }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #718096; font-size: 13px; line-height: 1.5;">
                                Kode ini berlaku selama <strong style="color: #2d3748;">15 menit</strong>. 
                                <br>Jika kamu tidak meminta reset password, abaikan saja email ini.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 50px; text-align: center;">
                            <div style="border-top: 1px solid #edf2f7; padding-top: 30px;">
                                <p style="color: #a0aec0; font-size: 12px; margin: 0;">
                                    &copy; {{ date('Y') }} Clipkeo Official. All rights reserved.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>