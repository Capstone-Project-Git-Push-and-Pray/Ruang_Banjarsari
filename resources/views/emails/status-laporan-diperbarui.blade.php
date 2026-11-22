<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Status Laporan Desa Banjarsari</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; color: #1f2937; margin: 0; padding: 24px 16px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb;">
        <!-- Header -->
        <tr>
            <td style="background-color: #1F6B4F; padding: 24px 32px; text-align: left;">
                <h1 style="color: #ffffff; font-size: 18px; margin: 0; font-weight: bold; letter-spacing: -0.5px;">
                    Pemerintah Desa Banjarsari
                </h1>
                <p style="color: #d1fae5; font-size: 12px; margin: 4px 0 0 0;">
                    Sistem Layanan Pengaduan & Aspirasi Warga
                </p>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 32px;">
                <h2 style="font-size: 16px; color: #111827; margin: 0 0 12px 0;">
                    Yth. Bapak/Ibu {{ $pengaduan->user->nama_lengkap }},
                </h2>
                <p style="font-size: 14px; line-height: 1.6; color: #4b5563; margin: 0 0 20px 0;">
                    Kami informasikan bahwa laporan yang Anda ajukan telah mengalami pembaruan status penanganan oleh aparatur Desa Banjarsari:
                </p>

                <!-- Status Card -->
                <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <table border="0" cellpadding="4" cellspacing="0" width="100%" style="font-size: 13px;">
                        <tr>
                            <td style="color: #6b7280; width: 140px; vertical-align: top;">Judul Laporan:</td>
                            <td style="font-weight: bold; color: #111827; vertical-align: top;">{{ $pengaduan->judul }}</td>
                        </tr>
                        <tr>
                            <td style="color: #6b7280; vertical-align: top;">Jenis & Kategori:</td>
                            <td style="color: #374151; vertical-align: top;">
                                {{ $pengaduan->jenis->label() }} &bull; {{ $pengaduan->kategori->nama ?? 'Umum' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #6b7280; vertical-align: top;">Status Terkini:</td>
                            <td style="vertical-align: top;">
                                <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-weight: bold; font-size: 12px; color: #ffffff;
                                    @if ($statusLaporan === \App\Enums\StatusLaporan::Selesai || $statusLaporan === \App\Enums\StatusLaporan::Direalisasikan)
                                        background-color: #1F6B4F;
                                    @elseif ($statusLaporan === \App\Enums\StatusLaporan::Ditolak || $statusLaporan === \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti)
                                        background-color: #dc2626;
                                    @elseif ($statusLaporan === \App\Enums\StatusLaporan::Diproses)
                                        background-color: #4f46e5;
                                    @elseif ($statusLaporan === \App\Enums\StatusLaporan::Ditinjau)
                                        background-color: #2563eb;
                                    @elseif ($statusLaporan === \App\Enums\StatusLaporan::DalamPembahasan)
                                        background-color: #0284c7;
                                    @elseif ($statusLaporan === \App\Enums\StatusLaporan::Ditampung)
                                        background-color: #3f3f46;
                                    @else
                                        background-color: #d97706;
                                    @endif">
                                    {{ $statusLaporan->label() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #6b7280; vertical-align: top;">Waktu Pembaruan:</td>
                            <td style="color: #374151; vertical-align: top;">{{ now()->format('d M Y, H:i') }} WIB</td>
                        </tr>
                    </table>

                    @if (! empty($catatan))
                        <div style="margin-top: 16px; padding-top: 14px; border-top: 1px dashed #d1d5db;">
                            <strong style="display: block; font-size: 12px; color: #374151; margin-bottom: 4px;">Catatan Aparatur Desa:</strong>
                            <p style="margin: 0; font-size: 13px; color: #1f2937; background-color: #ffffff; padding: 10px 12px; border-radius: 8px; border: 1px solid #e5e7eb; white-space: pre-line;">{{ $catatan }}</p>
                        </div>
                    @endif
                </div>

                <!-- Call to Action -->
                <div style="text-align: center; margin: 28px 0;">
                    <a href="{{ route('warga.pengaduan.show', $pengaduan) }}" style="display: inline-block; background-color: #1F6B4F; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: bold; padding: 12px 28px; border-radius: 10px;">
                        Buka Detail Laporan di SILAFA Banjarsari &rarr;
                    </a>
                </div>

                <p style="font-size: 12px; line-height: 1.5; color: #6b7280; margin: 0;">
                    Terima kasih atas kepedulian dan partisipasi Anda dalam membangun Desa Banjarsari yang lebih tertata dan sejahtera.
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f9fafb; padding: 20px 32px; border-top: 1px solid #e5e7eb; text-align: center;">
                <p style="font-size: 11px; color: #9ca3af; margin: 0;">
                    &copy; {{ date('Y') }} Pemerintah Desa Banjarsari. Email pemberitahuan ini dikirim otomatis oleh sistem karena akun Anda terdaftar di SILAFA Banjarsari.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

