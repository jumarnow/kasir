<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>SLIP GAJI - {{ $payroll->period_month }}-{{ $payroll->period_year }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 13px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 3px 0;
        }

        .label {
            font-weight: bold;
            width: 120px;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .content-table th {
            text-align: left;
            border-bottom: 1px solid #ccc;
            padding: 5px;
            text-transform: uppercase;
            font-size: 11px;
        }

        .content-table td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }

        .amount {
            text-align: right;
            font-family: monospace;
            font-size: 14px;
        }

        .subtotal {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .total-box {
            margin-top: 20px;
            border: 2px solid #333;
            padding: 15px;
        }

        .total-label {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-amount {
            font-size: 20px;
            font-weight: bold;
            float: right;
        }

        .footer {
            margin-top: 40px;
            width: 100%;
        }

        .signature {
            width: 40%;
            float: right;
            text-align: center;
        }

        .date {
            margin-bottom: 60px;
        }

        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        .type-permanent {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .type-intern {
            background-color: #fef3c7;
            color: #92400e;
        }

        .type-internship {
            background-color: #d1fae5;
            color: #065f46;
        }

        .daily-info {
            background-color: #fef3c7;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .daily-info-text {
            font-size: 11px;
            color: #92400e;
        }
    </style>
</head>

<body>
    <div class="header">
        @if(isset($settings['store_logo']) && file_exists(public_path('storage/' . $settings['store_logo'])))
            <img src="{{ public_path('storage/' . $settings['store_logo']) }}"
                style="max-height: 60px; margin-bottom: 5px; width: auto;">
        @endif
        <h1 class="title">{{ $settings['store_name'] ?? 'Kasir Modern' }}</h1>
        <p class="subtitle">{{ $settings['store_address'] ?? '' }}</p>
        <div style="margin-top: 15px; border-top: 2px solid #333; padding-top: 10px;">
            <p class="subtitle" style="font-weight: bold; color: #333; font-size: 16px;">SLIP GAJI KARYAWAN</p>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Periode</td>
            <td>: {{ DateTime::createFromFormat('!m', $payroll->period_month)->format('F') }}
                {{ $payroll->period_year }}
            </td>
            <td class="label">No ID</td>
            <td>: {{ $payroll->employee->employee_id }}</td>
        </tr>
        <tr>
            <td class="label">Nama</td>
            <td>: {{ $payroll->employee->name }}</td>
            <td class="label">Jabatan</td>
            <td>: {{ $payroll->employee->position }}</td>
        </tr>
        <tr>
            <td class="label">Tipe Karyawan</td>
            <td>:
                @php
                    $typeClass = 'type-' . $payroll->employee_type;
                @endphp
                <span class="type-badge {{ $typeClass }}">{{ $payroll->employee_type_label }}</span>
            </td>
            <td class="label">Status</td>
            <td>: {{ strtoupper($payroll->status) }}</td>
        </tr>
        <tr>
            <td class="label">Tgl Gabung</td>
            <td>: {{ $payroll->employee->join_date ? $payroll->employee->join_date->format('d/m/Y') : '-' }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th width="60%">Keterangan</th>
                <th width="40%" style="text-align: right;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="font-weight: bold; padding-top: 10px;">PENDAPATAN</td>
            </tr>

            <tr>
                <td>{{ in_array($payroll->employee_type, ['intern', 'internship']) ? 'Tunjangan Magang' : 'Gaji Pokok' }}
                </td>
                <td class="amount">{{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
            </tr>

            @if($payroll->tunjangan_makan > 0)
                <tr>
                    <td>Tunjangan Makan</td>
                    <td class="amount">{{ number_format($payroll->tunjangan_makan, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($payroll->tunjangan_transport > 0)
                <tr>
                    <td>Tunjangan Transport</td>
                    <td class="amount">{{ number_format($payroll->tunjangan_transport, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($payroll->tunjangan_jabatan > 0)
                <tr>
                    <td>Tunjangan Jabatan</td>
                    <td class="amount">{{ number_format($payroll->tunjangan_jabatan, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($payroll->tunjangan_lembur > 0)
                <tr>
                    <td>Tunjangan Lembur</td>
                    <td class="amount">{{ number_format($payroll->tunjangan_lembur, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($payroll->bonus_kehadiran > 0)
                <tr>
                    <td>Bonus Kehadiran</td>
                    <td class="amount">{{ number_format($payroll->bonus_kehadiran, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($payroll->bonus_target > 0)
                <tr>
                    <td>Bonus Target</td>
                    <td class="amount">{{ number_format($payroll->bonus_target, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="subtotal">
                <td>Total Pendapatan</td>
                <td class="amount">
                    {{ number_format($payroll->basic_salary + $payroll->tunjangan_makan + $payroll->tunjangan_transport + $payroll->tunjangan_jabatan + $payroll->tunjangan_lembur + $payroll->bonus_kehadiran + $payroll->bonus_target, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td colspan="2" style="font-weight: bold; padding-top: 15px;">POTONGAN</td>
            </tr>
            @if($payroll->potongan > 0)
                <tr>
                    <td>Potongan Lainnya <span style="font-size: 10px; color: #666;">({{ $payroll->potongan_notes }})</span>
                    </td>
                    <td class="amount">- {{ number_format($payroll->potongan, 0, ',', '.') }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="2" style="font-style: italic; color: #999;">Tidak ada potongan</td>
                </tr>
            @endif
            <tr class="subtotal">
                <td>Total Potongan</td>
                <td class="amount" style="color: #c00;">- {{ number_format($payroll->potongan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <span class="total-label">GAJI BERSIH (TAKE HOME PAY)</span>
        <span class="total-amount">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</span>
    </div>

    <div class="footer">
        <div class="signature">
            <div class="date">
                {{ config('app.timezone') ? now()->setTimezone(config('app.timezone'))->format('d F Y') : date('d F Y') }}
            </div>
            <br><br><br>
            <div style="border-top: 1px solid #333; display: inline-block; padding: 5px 20px;">
                Finance
            </div>
        </div>
    </div>

</body>

</html>