<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi — SILAFA Banjarsari</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F3F6F4; color: #1f2937; margin: 0; padding: 32px 16px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 560px;">
        <!-- Brand -->
        <tr>
            <td style="text-align: center; padding-bottom: 20px;">
                <img src="{{ $message->embed(public_path('images/Logo_DesaBanjarsari.png')) }}" alt="Logo Desa Banjarsari" width="52" height="52" style="display: inline-block; width: 52px; height: 52px;">
                <p style="font-size: 16px; font-weight: bold; color: #111827; margin: 8px 0 0 0;">SILAFA Banjarsari</p>
                <p style="font-size: 12px; color: #6b7280; margin: 2px 0 0 0;">Pemerintah Desa Banjarsari</p>
            </td>
        </tr>

        <!-- Kartu utama -->
        <tr>
            <td style="background-color: #ffffff; border-radius: 20px; border: 1px solid #E2E8E4; overflow: hidden;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="height: 6px; background-color: #1F6B4F; background-image: linear-gradient(90deg, #1A5B42, #1F6B4F, #34d399); font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding: 36px 36px 28px 36px;">
                            <h1 style="font-size: 22px; color: #111827; margin: 0 0 8px 0; letter-spacing: -0.3px;">Atur Ulang Kata Sandi</h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #4b5563; margin: 0 0 6px 0;">
                                Halo{{ $namaPenerima ? ', '.$namaPenerima : '' }},
                            </p>
                            <p style="font-size: 14px; line-height: 1.6; color: #4b5563; margin: 0 0 28px 0;">
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun SILAFA Banjarsari Anda. Klik tombol di bawah ini untuk membuat kata sandi baru.
                            </p>

                            <!-- Tombol -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $tautanReset }}" target="_blank" style="display: inline-block; background-color: #1F6B4F; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: bold; padding: 14px 36px; border-radius: 12px;">
                                            Buat Kata Sandi Baru &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info kedaluwarsa -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 28px;">
                                <tr>
                                    <td style="background-color: #EAF5EE; border: 1px solid #cfe6d8; border-radius: 12px; padding: 14px 16px; font-size: 13px; line-height: 1.5; color: #174B39;">
                                        &#9200;&nbsp; Tautan ini berlaku selama <strong>{{ $menitKedaluwarsa }} menit</strong> dan hanya dapat digunakan satu kali.
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 13px; line-height: 1.6; color: #6b7280; margin: 24px 0 0 0;">
                                Tidak merasa meminta atur ulang kata sandi? Abaikan email ini. Kata sandi Anda tidak akan berubah.
                            </p>
                        </td>
                    </tr>

                    <!-- Tautan cadangan -->
                    <tr>
                        <td style="padding: 18px 36px 26px 36px; border-top: 1px solid #F0F2F1;">
                            <p style="font-size: 12px; line-height: 1.5; color: #9ca3af; margin: 0 0 6px 0;">
                                Jika tombol tidak berfungsi, salin dan buka tautan berikut di browser Anda:
                            </p>
                            <p style="font-size: 12px; line-height: 1.5; margin: 0; word-break: break-all;">
                                <a href="{{ $tautanReset }}" target="_blank" style="color: #1F6B4F;">{{ $tautanReset }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="text-align: center; padding: 22px 12px 0 12px;">
                <p style="font-size: 12px; color: #6b7280; margin: 0 0 4px 0;">
                    Butuh bantuan? Hubungi WhatsApp Balai Desa
                    <a href="https://wa.me/6289662893128" target="_blank" style="color: #1F6B4F; font-weight: bold; text-decoration: none;">0896-6289-3128</a>
                </p>
                <p style="font-size: 11px; color: #9ca3af; margin: 0;">
                    &copy; {{ date('Y') }} Pemerintah Desa Banjarsari. Email ini dikirim otomatis, mohon tidak membalas.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
