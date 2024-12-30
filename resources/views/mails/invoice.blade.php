<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aloha!</title>

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
            width: 3.2rem;
            background-color: lightgray
        }
    </style>

</head>
<body>

<table width="100%">
    <tr>
        <td valign="top"><img src="{{ env('APP_URL') }}/assets/images/logo-saludWoM.png" width="300" alt="SaludWom"/></td>
        <td align="right">
            <h3>{{ env('APP_NAME') }}</h3>
            <pre>
                Salud WoM
                +34 640 847 411
                Barcelona - España
                info@saludwom.com
            </pre>
        </td>
    </tr>

</table>

<table width="100%">
    <tr>
        <td>
            <strong>Cliente:</strong> {{$user->name}} {{$user->last_name}} <br>
            <strong>Tipo de identificacnón:</strong> {{$user->identificationType->name}} <br>
            <strong>Nº Identificación:</strong> {{$user->document }} <br>
            <strong>Correo electrónico:</strong> {{$user->email}} <br>
            <strong>Teléfono:</strong> {{$user->phone}} <br>
        </td>
        <td>
            <strong>Orden de compra #</strong> {{ $invoice->id }} @if($subscription->name)<strong style="color: #D85C72 !important;">MANUAL</strong>@endif<br>
            <strong>Fecha:</strong> {{  ucwords(\Jenssegers\Date\Date::parse($invoice->created_at)->locale('es')->format('F d Y')) }}
        </td>
    </tr>

</table>

<br/>

<table width="100%">
    <thead style="background-color: lightgray;">
    <tr>
        <th>#</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Valor Unitario €</th>
        <th>Total €</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th scope="row">1</th>
        <td><strong>{{ $plan['name'] }}</strong> - {{ $plan['description'] }}.</td>
        <td align="right">1</td>
        <td align="right">{{ new \Akaunting\Money\Money($order->price_total, new \Akaunting\Money\Currency('EUR')) }}</td>
        <td align="right">{{ new \Akaunting\Money\Money($order->price_total, new \Akaunting\Money\Currency('EUR')) }}</td>
    </tr>
    </tbody>

    <tfoot>
    {{--{{--    <tr>--}}{{--
    --}}{{--        <td colspan="3"></td>--}}{{--
    --}}{{--        <td align="right">Subtotal $</td>--}}{{--
    --}}{{--        <td align="right">1635.00</td>--}}{{--
    --}}{{--    </tr>--}}{{--
    --}}{{--    <tr>--}}{{--
    --}}{{--        <td colspan="3"></td>--}}{{--
    --}}{{--        <td align="right">Tax $</td>--}}{{--
    --}}{{--        <td align="right">294.3</td>--}}{{--
    --}}{{--    </tr>--}}--}}
    <tr>
        <td colspan="3"></td>
        <td align="right">Costo Plan</td>
        <td align="right"
            class="gray">{{ new \Akaunting\Money\Money($order->price_total, new \Akaunting\Money\Currency('EUR')) }}</td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <td align="right">Descuento</td>
        <td align="right" class="gray">
            @php($totalDiscount = $order->price_total - $order->discount)
            {{ $order->discount ? new \Akaunting\Money\Money($totalDiscount, new \Akaunting\Money\Currency('EUR')) : new \Akaunting\Money\Money(0, new \Akaunting\Money\Currency('EUR')) }}
        </td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <td align="right">Costo Total</td>
        <td align="right" class="gray">{{ $order->discount ? new \Akaunting\Money\Money($order->discount, new \Akaunting\Money\Currency('EUR')) :  new \Akaunting\Money\Money($order->price_total, new \Akaunting\Money\Currency('EUR')) }}</td>
    </tr>
    </tfoot>
</table>

</body>
</html>
