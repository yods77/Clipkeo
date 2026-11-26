<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Penarikan Dana</title>
    <style>
        @media (prefers-color-scheme: dark) {
            .dark-mode-bg { background-color: #1e293b !important; }
            .dark-mode-text { color: #ffffff !important; }
        }
        /* CSS KHUSUS MOBILE */
        @media screen and (max-width: 480px) {
            .mobile-amount {
                font-size: 32px !important; /* Mengecilkan angka dari 42px agar tidak pecah baris */
            }
            .mobile-padding {
                padding: 20px !important; /* Padding lebih ramping untuk layar kecil */
            }
            .mobile-title {
                font-size: 20px !important;
            }
            .mobile-detail {
                font-size: 13px !important;
            }
        }

        .gradient-background {
            background-color: #f8fafc;
            background-image: linear-gradient(to bottom, #eff6ff 0%, #f8fafc 100%);
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f8fcf8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="padding: 20px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%"
                    style="max-width: 600px; background-color: #ffffff; background-image: linear-gradient(to bottom, #baff3b91 0%, #eeeeeef5 80%); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">

                    <tr>
                        <td align="center" style="padding: 40px 20px 20px 20px;" class="mobile-padding">
                            <div style="margin-bottom: 20px;">
                                <a href="{{ url('/') }}" target="_blank" style="text-decoration: none;">
                                    <img src="https://res.cloudinary.com/df5cppjg8/image/upload/v1771224596/logo_notif_siluet_mnpqaf.png" alt="Logo Clipkeo"
                                        width="80" style="display: block; border: 0; outline: none; text-decoration: none; margin: 0 auto;">
                                    <p style="margin: 8px 0 0 0; color: #1e293b; font-size: 18px; font-weight: bold; letter-spacing: -0.5px;">Clipkeo Global Media</p>
                                </a>
                            </div>
                            <h1 class="mobile-title" style="margin: 0; color: #1e293b; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">
                                Invoice Withdrawal</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 40px 40px 40px; border-bottom: 1px dashed #4d5055;" class="mobile-padding">
                            <p style="margin: 0; color: #222425; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px;">
                                Total Diterima</p>
                            <h2 class="mobile-amount" style="margin: 10px 0 0 0; color: #1e293b; font-size: 42px; font-weight: 900; line-height: 1.2;">
                                Rp {{ number_format($withdrawal->amount_final, 0, ',', '.') }}
                            </h2>
                            
                            {{-- LOGIKA POTONGAN ADMIN (Pastikan $withdrawal memiliki data admin_fee) --}}
                            @if(isset($withdrawal->admin_fee) && $withdrawal->admin_fee > 0)
                                <div style="margin-top: 8px; background-color: #fee2e2; color: #b91c1c; display: inline-block; padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: bold;">
                                    Potongan Admin: Rp {{ number_format($withdrawal->admin_fee, 0, ',', '.') }}
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px;" class="mobile-padding">
                            <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 14px; font-weight: bold; letter-spacing: 1px;">
                                Rincian Transaksi</h3>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="mobile-detail" style="padding: 10px 0; color: #202429; font-size: 14px;">Tanggal</td>
                                    <td class="mobile-detail" align="right" style="padding: 10px 0; color: #1e293b; font-size: 14px; font-weight: bold;">
                                        {{ optional($withdrawal->transaction)->paid_at ? \Carbon\Carbon::parse($withdrawal->transaction->paid_at)->format('d M Y, H:i') : $withdrawal->created_at->format('d M Y, H:i') }} WIB
                                    </td>
                                </tr>
                                <tr>
                                    <td class="mobile-detail" style="padding: 10px 0; color: #202429; font-size: 14px;">Metode</td>
                                    <td class="mobile-detail" align="right" style="padding: 10px 0; color: #1e293b; font-size: 14px; font-weight: bold;">
                                        {{ optional($withdrawal->transaction)->payment_channel ?? 'Transfer Bank' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="mobile-detail" style="padding: 10px 0; color: #202429; font-size: 14px;">ID Transaksi</td>
                                    <td class="mobile-detail" align="right" style="padding: 10px 0; color: #1e293b; font-size: 14px; font-weight: bold;">
                                        {{ optional($withdrawal->transaction)->reference_id ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="mobile-detail" style="padding: 10px 0; color: #202429; font-size: 14px;">Admin Fee (10%)</td>
                                    <td class="mobile-detail" align="right" style="padding: 10px 0; color: #1e293b; font-size: 14px; font-weight: bold;">
                                        {{ number_format($withdrawal->admin_fee, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="mobile-detail" style="padding: 10px 0; color: #202429; font-size: 14px;">Status</td>
                                    <td class="mobile-detail" align="right" style="padding: 10px 0; color: #1e293b; font-size: 14px; font-weight: bold;">
                                        {{ $withdrawal->status }}
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top: 30px; background-color: #f8fafc; border-radius: 12px; padding: 20px; border: 1px dashed #c9c9c9;">
                                <p style="margin: 0 0 10px 0; color: #94a3b8; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                                    Rekening Tujuan
                                </p>
                                <div style="border-bottom: 1px solid #e2e8f0; margin-bottom: 15px; font-size: 1px; line-height: 1px;">&nbsp;</div>

                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td class="mobile-detail" style="padding: 5px 0; color: #64748b; font-size: 13px;">Bank</td>
                                        <td class="mobile-detail" align="right" style="padding: 5px 0; color: #1e293b; font-size: 13px; font-weight: bold;">
                                            {{ optional($withdrawal->transaction)->bank_code ?? $withdrawal->bank_info }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="mobile-detail" style="padding: 5px 0; color: #64748b; font-size: 13px;">No. Rekening</td>
                                        <td class="mobile-detail" align="right" style="padding: 5px 0; color: #1e293b; font-size: 13px; font-weight: bold; font-family: monospace;">
                                            {{ optional($withdrawal->transaction)->account_number ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="mobile-detail" style="padding: 5px 0; color: #64748b; font-size: 13px;">Pemilik</td>
                                        <td class="mobile-detail" align="right" style="padding: 5px 0; color: #1e293b; font-size: 13px; font-weight: bold;">
                                            {{ strtoupper(optional($withdrawal->transaction)->account_holder ?? '-') }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 0 40px 40px 40px;" class="mobile-padding">
                            <a href="{{ route('dashboard') }}"
                                style="background-color: #1e293b; color: #ffffff; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: bold; font-size: 14px; display: block; text-align: center;">Buka Dashboard</a>
                        </td>
                    </tr>
                </table>

                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin-top: 20px;">
                    <tr>
                        <td align="center" style="color: #94a3b8; font-size: 11px; line-height: 1.8;">
                            &copy; {{ date('Y') }} Clipkeo Global Media. All rights reserved.<br>
                            Jakarta, Indonesia.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>