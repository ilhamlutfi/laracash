<!DOCTYPE html>
<html>

<head>
    <title>Notifikasi Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #f4f5f7;
            padding: 20px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        /* Gaya Baru untuk Logo Ala Breeze */
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .laravel-logo {
            height: 50px;
            width: auto;
        }

        .header {
            background-color: #1e3a8a;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 6px 6px 0 0;
        }

        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            text-align: center;
            margin: 20px 0;
        }

        .amount.expense {
            color: #ef4444;
        }

        .amount.transfer {
            color: #3b82f6;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
        }

        .table td:first-child {
            font-weight: bold;
            color: #6b7280;
            width: 45%;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }

        .section-title {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e3a8a !important;
            padding: 8px 10px !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }

        .wallet-row td {
            background-color: #f8fafc;
            font-size: 13px;
            color: #334155;
        }

        .total-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #0f172a;
            border-top: 2px solid #94a3b8;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="logo-container">
            <a href="{{ config('app.url') }}" target="_blank">
                <img src="https://laravel.com/img/notification-logo-v2.1.png" alt="Laravel Logo" class="laravel-logo">
            </a>
        </div>

        <div class="header">
            <h2>NOTIFIKASI TRANSAKSI</h2>
        </div>

        @php
            // 1. Konversi tipe transaksi ke Bahasa Indonesia
            $tipeSistem = $transaction->type;
            $tipeIndo = 'Transaksi';
            $simbol = '';

            if ($tipeSistem === 'income') {
                $tipeIndo = 'Pemasukan';
                $simbol = '+';
            } elseif ($tipeSistem === 'expense') {
                $tipeIndo = 'Pengeluaran';
                $simbol = '-';
            } elseif ($tipeSistem === 'transfer') {
                $tipeIndo = 'Transfer';
                $simbol = '-';
            }

            // 2. Ambil semua list dompet aktif milik pengguna ini
            $semuaDompetAktif = \App\Models\Wallet::where('user_id', $transaction->user_id)
                ->where('is_active', true)
                ->get();

            // 3. Hitung total akumulasi saldo
            $totalSaldoGabungan = $semuaDompetAktif->sum('balance');
        @endphp

        <div class="amount {{ $tipeSistem }}">
            {{ $simbol }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
        </div>

        <table class="table">
            <tr>
                <td>Tipe Transaksi</td>
                <td>: {{ $tipeIndo }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</td>
            </tr>
            <tr>
                <td>Dompet Asal</td>
                <td>: {{ $transaction->wallet?->name ?? '-' }}</td>
            </tr>

            @if ($tipeSistem === 'transfer')
                <tr>
                    <td>Dompet Tujuan</td>
                    <td>: {{ $transaction->toWallet?->name ?? '-' }}</td>
                </tr>
            @else
                <tr>
                    <td>Kategori</td>
                    <td>: {{ $transaction->category?->name ?? '-' }}</td>
                </tr>
            @endif

            <tr>
                <td>Catatan</td>
                <td>: {{ $transaction->note ?? '-' }}</td>
            </tr>

            <tr>
                <td colspan="2" class="section-title">Saldo Dompet saat ini:</td>
            </tr>
            @foreach ($semuaDompetAktif as $wallet)
                <tr class="wallet-row">
                    <td>• Saldo {{ $wallet->name }}</td>
                    <td>: Rp {{ number_format($wallet->balance, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td>TOTAL SALDO</td>
                <td>: Rp {{ number_format($totalSaldoGabungan, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer">
            Ini adalah email otomatis dari sistem aplikasi CashApp Anda. Mohon untuk tidak membalas email ini.
            <br>
            <p style="font-size: 12px;">Copyright &copy; cash.mubatekno.com {{ date('Y') }}</p>
        </div>
    </div>
</body>

</html>
