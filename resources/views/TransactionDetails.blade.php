<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Transaction Detail</title>

    <style type="text/css">
        * {
            font-family: Verdana, Arial, sans-serif;
        }

        table {
            font-size: x-small;
        }

        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }

        .gray {
            background-color: lightgray
        }
    </style>

</head>

<body>

    <table width="100%">
        <tr>
            <td valign="top"><img src="https://via.placeholder.com/150" alt="" width="150" /></td>
            <td align="right">
                <h3>RUMAH DRONE</h3>
                <pre>
                Rumah Drone
                PT Odo Multi Aero
                Jalan Kwoka Q2-6 Perumahan Tidar Permai,
                Sukun Kota Malang
            </pre>
            </td>
        </tr>

    </table>

    <table width="100%">
        <tr>
            <td><strong>Created by</strong></td>
            <td>:</td>
            <td>{{ $transaction->user->name }}</td>
            {{-- hr --}}
            <td><strong>Approved by</strong></td>
            <td>:</td>
            <td>{{ $transaction->approvedBy->name }}</td>
            {{-- hr --}}
            <td><strong>Transaction code</strong></td>
            <td>:</td>
            <td>{{ $transaction->transaction_id }}</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>:</td>
            <td>{{ $transaction->user->email }}</td>
            {{-- hr --}}
            <td><strong>Email</strong></td>
            <td>:</td>
            <td>{{ $transaction->approvedBy->email }}</td>
            {{-- hr --}}
            <td><strong>Transaction type</strong></td>
            <td>:</td>
            <td>{{ $transaction->type->transaction_name }}</td>
        </tr>
        <tr>
            <td><strong>Created at</strong></td>
            <td>:</td>
            <td>{{ $transaction->created_at->format('l, d/m/Y') }}</td>
            {{-- hr --}}
            <td><strong>Approved at</strong></td>
            <td>:</td>
            <td>{{ $transaction->updated_at->format('l, d/m/Y') }}</td>
            {{-- hr --}}
            <td><strong>Status</strong></td>
            <td>:</td>
            @if ($transaction->is_approved == 'Approved')
                <td style="background-color:#DEF7EC; color:#03543F">
                    {{ $transaction->is_approved }}
                </td>
            @else
                <td style="background-color:#FDF6B2; color:#723B13">
                    {{ $transaction->is_approved }}
                </td>
            @endif

        </tr>

    </table>

    <br />

    <table width="100%">
        <thead style="background-color: lightgray;">
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Product Code</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->detail as $index => $item)
                <tr>
                    <th scope="row">{{ $index + 1 }}</th>
                    <td>{{ $item->product->product_name }}</td>
                    <td>{{ $item->product->product_code }}</td>
                    <td align="right">{{ $item->qty }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="2"></td>
                <td align="right">Total Pcs</td>
                <td align="right" class="gray">{{ $total }}</td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
