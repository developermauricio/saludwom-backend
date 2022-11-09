<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title>Factura</title>
    <!--[if mso]>
    <xml><o:officedocumentsettings><o:pixelsperinch>96</o:pixelsperinch></o:officedocumentsettings></xml>
    <![endif]-->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700" rel="stylesheet" media="screen">
    <style>
        .hover-underline:hover {
            text-decoration: underline !important;
        }
        @media (max-width: 600px) {
            .sm-w-full {
                width: 100% !important;
            }
            .sm-px-24 {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
            .sm-py-32 {
                padding-top: 32px !important;
                padding-bottom: 32px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; width: 100%; padding: 0; word-break: break-word; -webkit-font-smoothing: antialiased;">
<div style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; display: none;">Esta es una factura por su compra.</div>
<div role="article" aria-roledescription="email" aria-label="" lang="es" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;">
    <table style="width: 100%; font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif;" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="mso-line-height-rule: exactly; background-color: #ffffff; font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif;">
                <table class="sm-w-full" style="width: 600px;" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="sm-py-32 sm-px-24" style="font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif; padding: 48px; text-align: center;" align="center">
                            <a href="{{ env('APP_URL_FRONT') }}">
                                <img src="{{ env('APP_URL') }}/assets/images/logo-saludWoM.png" width="300" alt="SaludWoM" style="border: 0; max-width: 100%; line-height: 100%; vertical-align: middle;">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" class="sm-px-24" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;">
                            <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="sm-px-24" style="mso-line-height-rule: exactly; border-radius: 4px; background-color: #ffffff; padding: 48px; text-align: left; font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif; font-size: 16px; line-height: 24px; color: #626262;">
                                        <p style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin-bottom: 0; font-size: 20px; font-weight: 600;">Hola.</p>
                                        <p style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin-top: 0; font-size: 24px; font-weight: 700; color: #ff5850;">
                                            {{ $user->name }}!</p>
                                        <p style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 24px;">
                                            Gracias por comprar uno de nuestro planes. Esta es su orden de compra.
                                        </p>
{{--                                        <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">--}}
{{--                                            <tr>--}}
{{--                                                <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; padding: 16px; font-size: 16px;">--}}
{{--                                                    <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">--}}
{{--                                                        <tr>--}}
{{--                                                            <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; font-size: 16px;"><strong>Amount Due:</strong> $56.00</td>--}}
{{--                                                        </tr>--}}
{{--                                                        <tr>--}}
{{--                                                            <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; font-size: 16px;">--}}
{{--                                                                <strong>Due By:</strong> 18th June 2020--}}
{{--                                                            </td>--}}
{{--                                                        </tr>--}}
{{--                                                    </table>--}}
{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                        </table>--}}
                                        <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;">
                                                    <h3 style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin-top: 0; text-align: left; font-size: 14px; font-weight: 700;">#{{ $invoice->id }}</h3>
                                                </td>
                                                <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;">
                                                    <h3 style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin-top: 0; text-align: right; font-size: 14px; font-weight: 700;">
                                                        {{ \Carbon\Carbon::parse($invoice->created_at)->format('M d Y')  }}
                                                    </h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;">
                                                    <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">
                                                        <tr>
                                                            <th align="left" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; padding-bottom: 8px;">
                                                                <p style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;">Descripción</p>
                                                            </th>
                                                            <th align="right" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; padding-bottom: 8px;">
                                                                <p style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;">Costo</p>
                                                            </th>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; width: 80%; padding-top: 10px; padding-bottom: 10px; font-size: 16px;">
                                                                {{ $plan->name }}
                                                            </td>
                                                            <td align="right" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; width: 20%; text-align: right; font-size: 16px;">
                                                                {{ new \Akaunting\Money\Money($order->price_total, new \Akaunting\Money\Currency('EUR')) }}</td>
                                                        </tr>
{{--                                                        <tr>--}}
{{--                                                            <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; width: 80%; padding-top: 10px; padding-bottom: 10px; font-size: 16px;">--}}
{{--                                                                Frest – Admin Dashboard & UI Kit Sketch Template--}}
{{--                                                            </td>--}}
{{--                                                            <td align="right" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; width: 20%; text-align: right; font-size: 16px;">$24.00</td>--}}
{{--                                                        </tr>--}}
                                                        <tr>
                                                            <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; width: 80%;">
                                                                <p align="right" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin: 0; padding-right: 16px; text-align: right; font-size: 16px; font-weight: 700; line-height: 24px;">
                                                                    Total
                                                                </p>
                                                            </td>
                                                            <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; width: 20%;">
                                                                <p align="right" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin: 0; text-align: right; font-size: 16px; font-weight: 700; line-height: 24px;">
                                                                    {{ new \Akaunting\Money\Money($order->price_total, new \Akaunting\Money\Currency('EUR')) }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
{{--                                        <table align="right" style="margin-left: auto; margin-right: auto; width: 100%; text-align: center;" cellpadding="0" cellspacing="0" role="presentation">--}}
{{--                                            <tr>--}}
{{--                                                <td align="right" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;">--}}
{{--                                                    <table style="margin-top: 24px; margin-bottom: 24px;" cellpadding="0" cellspacing="0" role="presentation">--}}
{{--                                                        <tr>--}}
{{--                                                            <td align="right" style="mso-line-height-rule: exactly; mso-padding-alt: 16px 24px; border-radius: 4px; background-color: #7367f0; font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif;">--}}
{{--                                                                <a href="https://example.com" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; display: block; padding-left: 24px; padding-right: 24px; padding-top: 16px; padding-bottom: 16px; font-size: 16px; font-weight: 600; line-height: 100%; color: #ffffff; text-decoration: none;">Pay Invoice &rarr;</a>--}}
{{--                                                            </td>--}}
{{--                                                        </tr>--}}
{{--                                                    </table>--}}
{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                        </table>--}}
                                        <p style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin-top: 6px; margin-bottom: 20px; font-size: 16px; line-height: 24px;">
                                            Si tiene alguna pregunta sobre esta orden de compra, simplemente responda a este correo electrónico o póngase en contacto con nosotros a
                                            <a href="mailto:soporte@saludwom.com" style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly;color: #792141; color: #792141;">suporte@saludwom.com</a>.
                                        </p>
                                        <p style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; margin-top: 6px; margin-bottom: 20px; font-size: 16px; line-height: 24px;">
                                            Gracias,
                                            <br>Equipo {{ env('APP_NAME') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-family: 'Montserrat', sans-serif; mso-line-height-rule: exactly; height: 20px;"></td>
                    </tr>
                    <tr style="text-align: center">
                        <td style="font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif; font-size: 12px; padding-left: 48px; padding-right: 48px; --text-opacity: 1; text-align: center !important; color: rgba(241, 240, 240, 0.38); color: rgba(236, 239, 241, var(--text-opacity));">
                            <p align="center" style="cursor: default; margin-bottom: 16px; text-align: center !important;">
                                <a href="https://www.facebook.com/saludwom" style="--text-opacity: 1; color: #263238; color: rgba(38, 50, 56, var(--text-opacity)); text-decoration: none;"><img src="{{ env('APP_URL') }}/assets/images/facebook-2.png" width="20" alt="Facebook" style="border: 0; max-width: 100%; line-height: 100%; vertical-align: middle; margin-right: 12px;"></a>
                                &bull;
                                <a href="#" style="--text-opacity: 1; color: #263238; color: rgba(38, 50, 56, var(--text-opacity)); text-decoration: none;"><img src="{{ env('APP_URL') }}/assets/images/twitter.png" width="20" alt="Twitter" style="border: 0; max-width: 100%; line-height: 100%; vertical-align: middle; margin-right: 12px;"></a>
                                &bull;
                                <a href="https://www.instagram.com/saludwom/" style="--text-opacity: 1; color: #263238; color: rgba(38, 50, 56, var(--text-opacity)); text-decoration: none;"><img src="{{ env('APP_URL') }}/assets/images/instagram-3.png" width="20" alt="Instagram" style="border: 0; max-width: 100%; line-height: 100%; vertical-align: middle; margin-right: 12px;"></a>
                            </p>
                            <p style="--text-opacity: 1; color: #666666 !important; color: #666666 !important; text-align: center !important;">
                                El uso de nuestro servicio y sitio web está sujeto a nuestros
                                <a href="#" class="hover-underline" style="--text-opacity: 1; color: #792141; color: #792141; text-decoration: none;">Términos</a> y
                                <a href="#" class="hover-underline" style="--text-opacity: 1; color: #792141; color: #792141; text-decoration: none;">Políticas de Privacidad</a>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-family: 'Montserrat',Arial,sans-serif; height: 16px;" height="16"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>

