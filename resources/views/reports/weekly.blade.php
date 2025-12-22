<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        .muted { color: #666; }
        .box { border:1px solid #ddd; padding:10px; margin:10px 0; border-radius:6px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ddd; padding:6px; vertical-align: top; }
        th { background:#f3f3f3; }
        .right { text-align:right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="muted">
        Periode: <b>{{ $start_date }}</b> s/d <b>{{ $end_date }}</b><br>
        @if($is_partial)
            Status: <b>PARTIAL</b> (data sampai <b>{{ $effective_end }}</b>)
        @else
            Status: <b>FINAL</b>
        @endif
    </div>

    <div class="box">
        <table>
            <tr>
                <th>Ringkasan</th>
                <th class="right">Nominal (Rp)</th>
            </tr>
            <tr>
                <td>Total Penjualan</td>
                <td class="right">{{ number_format($sales_total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Pembelian/Restock</td>
                <td class="right">{{ number_format($purchase_total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Profit Kasar (simple)</td>
                <td class="right">{{ number_format($gross_profit_simple, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <h3>Detail Penjualan</h3>
    <table>
        <tr>
            <th>Waktu</th>
            <th>Kasir</th>
            <th>Item</th>
            <th class="right">Total</th>
        </tr>
        @foreach($sales as $s)
            <tr>
                <td>{{ $s->sold_at }}</td>
                <td>{{ $s->created_by }}</td>
                <td>
                    @foreach($s->lines as $l)
                        - {{ $l->item->name ?? 'Item' }} x{{ $l->qty }} @ {{ number_format($l->unit_price,0,',','.') }}<br>
                    @endforeach
                </td>
                <td class="right">{{ number_format($s->total, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <h3 style="margin-top:16px;">Detail Pembelian/Restock</h3>
    <table>
        <tr>
            <th>Waktu</th>
            <th>Kasir</th>
            <th>Item</th>
            <th class="right">Total</th>
        </tr>
        @foreach($purchases as $p)
            <tr>
                <td>{{ $p->purchased_at }}</td>
                <td>{{ $p->created_by }}</td>
                <td>
                    @foreach($p->lines as $l)
                        - {{ $l->item->name ?? 'Item' }} +{{ $l->qty }} @ {{ number_format($l->unit_cost,0,',','.') }}<br>
                    @endforeach
                </td>
                <td class="right">{{ number_format($p->total, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

</body>
</html>
