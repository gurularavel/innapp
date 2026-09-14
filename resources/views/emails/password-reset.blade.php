<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifrə sıfırlama — {{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#edf4fd;font-family:'Segoe UI',Arial,sans-serif;color:#1e2d3d">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#edf4fd;padding:32px 12px">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(30,45,61,.12)">
                <tr>
                    <td style="background:#2d4a6e;padding:22px 32px;color:#ffffff;font-size:20px;font-weight:800;letter-spacing:-.3px">
                        {{ config('app.name') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px">
                        <h1 style="margin:0 0 12px;font-size:20px;color:#1e2d3d">Salam, {{ $user->name }}!</h1>
                        <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#3c4a5a">
                            Hesabınız üçün şifrə sıfırlama tələbi aldıq. Yeni şifrə təyin etmək üçün aşağıdakı düyməyə klikləyin.
                        </p>
                        <p style="margin:0 0 24px;text-align:center">
                            <a href="{{ $url }}"
                               style="display:inline-block;background:#4a6fa5;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:13px 28px;border-radius:10px">
                                Şifrəni sıfırla
                            </a>
                        </p>
                        <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#6b7a8c">
                            Bu link <strong>{{ $expire }} dəqiqə</strong> ərzində etibarlıdır.
                            Əgər siz şifrə sıfırlama tələb etməmisinizsə, bu məktubu nəzərə almayın — şifrəniz dəyişdirilməyəcək.
                        </p>
                        <hr style="border:none;border-top:1px solid #e8f0fb;margin:24px 0">
                        <p style="margin:0;font-size:12px;line-height:1.6;color:#8a97a8;word-break:break-all">
                            Düymə işləmirsə, bu ünvanı brauzerə kopyalayın:<br>
                            <a href="{{ $url }}" style="color:#4a6fa5">{{ $url }}</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 32px;background:#f6f9fd;font-size:12px;color:#8a97a8;text-align:center">
                        © {{ date('Y') }} {{ config('app.name') }} — Randevu İdarəetmə Sistemi
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
