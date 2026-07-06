<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .header h1 {
            color: #0d9488;
            margin: 0;
        }
        .content {
            padding: 20px 0;
            color: #374151;
            line-height: 1.6;
        }
        .details {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        .details strong {
            display: inline-block;
            width: 150px;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(to right, #0d9488, #059669);
            background-color: #0d9488; /* Fallback for clients not supporting gradients */
            border-radius: 6px;
            font-weight: bold;
            box-shadow: 0 4px 6px -1px rgba(20, 184, 166, 0.2);
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EarScope AI</h1>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $consultation->patient->name }}</strong>,</p>
            <p>Permintaan konsultasi Anda dengan <strong>{{ $consultation->doctor->name }}</strong> telah berhasil dijadwalkan. Berikut adalah rincian jadwal Anda:</p>

            <div class="details">
                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($consultation->scheduled_date)->translatedFormat('l, d F Y') }}</p>
                <p><strong>Antrean:</strong> {{ $consultation->queue_number }}</p>
                <p><strong>Keluhan:</strong> {{ $consultation->complaint }}</p>
            </div>

            <p>Mohon untuk hadir tepat waktu atau mengabari kami jika ada kendala.</p>

            <div class="button-container">
                <a href="{{ route('patient.dashboard') }}" class="button" style="color: #ffffff; text-decoration: none;">Lihat Detail di Aplikasi</a>
            </div>
        </div>
        <div class="footer">
            <p>Terima kasih telah menggunakan layanan EarScope AI.</p>
            <p>&copy; {{ date('Y') }} EarScope AI. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
