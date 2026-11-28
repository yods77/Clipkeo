<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Deposit Clipkeo</title>
</head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    @php
        $paidAt = $transaction->paid_at ?: $transaction->created_at;
        $dateLabel = $paidAt
            ? \Carbon\Carbon::parse($paidAt)->format('d M Y, H:i') . ' WIB'
            : '-';
        $statusLabel = match ($status) {
            'success', 'approved' => 'Berhasil',
            'pending' => 'Menunggu pembayaran',
            'rejected' => 'Ditolak',
            default => ucfirst((string) $status),
        };
        $amount = $transaction->amount ?? 0;
    @endphp

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="padding:32px 28px;text-align:center;background:#f8fafc;">
                            <img src="https://res.cloudinary.com/df5cppjg8/image/upload/v1771224596/logo_notif_siluet_mnpqaf.png" alt="Clipkeo" width="64" style="display:block;margin:0 auto 12px;">
                            <h1 style="margin:0;font-size:22px;">Konfirmasi Deposit</h1>
                            <p style="margin:8px 0 0;color:#64748b;">Clipkeo Global Media</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 8px;color:#64748b;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Nominal deposit</p>
                            <p style="margin:0 0 24px;font-size:32px;font-weight:700;">Rp {{ number_format((float) $amount, 0, ',', '.') }}</p>

                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Status</td>
                                    <td align="right" style="padding:12px 0;border-bottom:1px solid #e2e8f0;font-weight:700;">{{ $statusLabel }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Tanggal</td>
                                    <td align="right" style="padding:12px 0;border-bottom:1px solid #e2e8f0;font-weight:700;">{{ $dateLabel }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Metode pembayaran</td>
                                    <td align="right" style="padding:12px 0;border-bottom:1px solid #e2e8f0;font-weight:700;">{{ $transaction->payment_channel ?: 'Xendit' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0;color:#64748b;">Referensi transaksi</td>
                                    <td align="right" style="padding:12px 0;font-weight:700;word-break:break-all;">{{ $transaction->reference_id ?: '-' }}</td>
                                </tr>
                            </table>

                            @if ($reason)
                                <p style="margin:20px 0 0;padding:12px;background:#fff7ed;border-radius:8px;color:#9a3412;">Keterangan: {{ $reason }}</p>
                            @endif

                            <p style="margin:28px 0 0;text-align:center;">
                                <a href="{{ route('wallet.index') }}" style="display:inline-block;padding:13px 22px;background:#0f172a;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;">Buka Dompet Clipkeo</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="margin:18px 0 0;color:#94a3b8;font-size:12px;text-align:center;">&copy; {{ date('Y') }} Clipkeo Global Media. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
