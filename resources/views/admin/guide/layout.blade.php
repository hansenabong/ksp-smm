<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Panduan KSP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
            font-size: 14px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 { font-size: 24px; color: #1e3a5f; margin-bottom: 4px; }
        .header .subtitle { font-size: 14px; color: #64748b; }
        .header .role-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 8px;
        }

        .toc {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .toc h3 { font-size: 15px; margin-bottom: 10px; color: #334155; }
        .toc ol { padding-left: 20px; }
        .toc li { margin-bottom: 4px; }
        .toc a { color: #2563eb; text-decoration: none; }

        h2 {
            font-size: 18px;
            color: #1e3a5f;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin: 30px 0 15px;
        }
        h3 {
            font-size: 15px;
            color: #334155;
            margin: 20px 0 10px;
        }

        .step-box {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 12px 16px;
            margin: 10px 0;
            border-radius: 0 6px 6px 0;
        }
        .step-box .step-num {
            display: inline-block;
            background: #2563eb;
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            text-align: center;
            font-size: 12px;
            line-height: 22px;
            margin-right: 8px;
            font-weight: 600;
        }

        .warning-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            margin: 10px 0;
            border-radius: 0 6px 6px 0;
        }
        .warning-box strong { color: #92400e; }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            margin: 10px 0;
            border-radius: 0 6px 6px 0;
        }

        .menu-path {
            font-family: monospace;
            background: #e2e8f0;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 13px;
        }
        table th {
            background: #f1f5f9;
            text-align: left;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            font-weight: 600;
        }
        table td {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
        }

        ul, ol { padding-left: 20px; margin: 8px 0; }
        li { margin-bottom: 4px; }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
        }

        .no-print { margin-bottom: 20px; }
        .no-print a {
            display: inline-block;
            padding: 8px 20px;
            background: #2563eb;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            margin-right: 8px;
        }
        .no-print a:hover { background: #1d4ed8; }
        .no-print a.secondary { background: #64748b; }

        @media print {
            body { padding: 20px; }
            .no-print { display: none !important; }
            .step-box, .warning-box, .info-box {
                break-inside: avoid;
            }
            h2 { break-after: avoid; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="javascript:window.print()">Cetak / Simpan PDF</a>
    <a href="{{ route('admin.guide.index') }}" class="secondary">Kembali ke Daftar Panduan</a>
</div>

@yield('content')

<div class="footer">
    Panduan Pengguna Sistem KSP &mdash; Versi 1.0 &mdash; {{ now()->format('F Y') }}
</div>

</body>
</html>
