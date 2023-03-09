<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <style>
        td, th, div, p, a, h1, h2, h3, h4, h5, h6 {
            font-family: "Segoe UI", sans-serif;
            mso-line-height-rule: exactly;
        }
    </style>
    <![endif]-->
    <title>Confirmación Suscripción</title>
    <link
        href="https://fonts.googleapis.com/css?family=Montserrat:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700"
        rel="stylesheet" media="screen">
    <style>
        .hover-underline:hover {
            text-decoration: underline !important;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes ping {

            75%,
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        @keyframes pulse {
            50% {
                opacity: .5;
            }
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(-25%);
                animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
            }

            50% {
                transform: none;
                animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }
        }

        @media (max-width: 600px) {
            .sm-leading-32 {
                line-height: 32px !important;
            }

            .sm-px-24 {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }

            .sm-py-32 {
                padding-top: 32px !important;
                padding-bottom: 32px !important;
            }

            .sm-w-full {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; width: 100%; word-break: break-word; -webkit-font-smoothing: antialiased; --bg-opacity: 1; background-color: rgba(241, 240, 240, 0.38); background-color: rgba(241, 240, 240, 0.38);">
<div style="display: none;">Confirmación Suscripción</div>
<div role="article" aria-roledescription="email" aria-label="Verify Email Address" lang="es">
    <table style="font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif; width: 100%;" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="--bg-opacity: 1; background-color: #ffffff; background-color: #ffffff; font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif;" bgcolor="rgba(236, 239, 241, var(--bg-opacity))">
                <table class="sm-w-full" style="font-family: 'Montserrat',Arial,sans-serif; width: 600px;" width="600" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="sm-py-32 sm-px-24" style="font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif; padding: 48px; text-align: center;" align="center">
{{--                            <a href="{{ env('APP_URL_FRONT') }}">--}}
{{--                                <img src="{{ env('APP_URL') }}/assets/images/logo-saludWoM.png" width="300" alt="SaludWoM" style="border: 0; max-width: 100%; line-height: 100%; vertical-align: middle;">--}}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" class="sm-px-24" style="font-family: 'Montserrat',Arial,sans-serif;">
                            <table style="font-family: 'Montserrat',Arial,sans-serif; width: 100%;" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="sm-px-24"
                                        style="--bg-opacity: 1; background-color: #ffffff; background-color: rgba(255, 255, 255, var(--bg-opacity)); border-radius: 4px; font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif; font-size: 14px; line-height: 24px; padding: 48px; text-align: left; --text-opacity: 1; color: #626262; color: rgba(98, 98, 98, var(--text-opacity));"
                                        bgcolor="rgba(255, 255, 255, var(--bg-opacity))" align="left">
                                        <p style="font-weight: 600; font-size: 18px; margin-bottom: 0;color: #666666 !important;">
                                            Hola.</p>
                                        <p style="font-weight: 700; font-size: 20px; margin-top: 0; --text-opacity: 1; color: #D85C72; color: #D85C72;">
                                            {{ $user->name }}!</p>
                                        <p class="sm-leading-32"
                                           style="font-weight: 600; font-size: 20px; margin: 0 0 16px; --text-opacity: 1; color: #263238; color: #263238">
                                            Gracias por adquirir uno de nuestros planes. <strong>Crear ahora tu nuevo objetivo para ser un equipo y alcanzar tus exitos.</strong> 🤗
                                        </p>
                                        <p style="margin: 0 0 24px;color: #666666 !important;">
                                            En este momento tienes activa la suscripción del <strong>{{ $plan->name }}</strong>.
{{--                                            Tu plan vence {{ ucwords(\Jenssegers\Date\Date::parse($subscription->expiration_date)->locale('es')->format('F d Y')) }}.--}}

                                        </p>
                                        {{--                                        <p style="margin: 0 0 24px; color: #666666 !important;">--}}
                                        {{--                                            Si no te has suscrito, ignora este correo electrónico o contáctenos a--}}
                                        {{--                                            <a href="mailto:soporte@saludwom.com" class="hover-underline" style="--text-opacity: 1; color: #792141; color: #792141; text-decoration: none;">soporte@saludwom.com</a>--}}
                                        {{--                                        </p>--}}

                                        <table style="font-family: 'Montserrat',Arial,sans-serif;" cellpadding="0" cellspacing="0"
                                               role="presentation">
                                            <tr>
                                                <td style="mso-padding-alt: 16px 24px; --bg-opacity: 1; background-color: #D85C72; background-color: #D85C72; border-radius: 4px; font-family: Montserrat, -apple-system, 'Segoe UI', sans-serif;"
                                                    bgcolor="rgba(115, 103, 240, var(--bg-opacity))">
                                                    <a href="{{ env('APP_URL_FRONT') }}{{$link}}"
                                                       style="display: block; font-weight: 600; font-size: 14px; line-height: 100%; padding: 16px 24px; --text-opacity: 1; color: #ffffff; color: #ffffff; text-decoration: none;">Crear Nuevo Objetivo &rarr;</a>
                                                </td>
                                            </tr>
                                        </table>
                                        <table style="font-family: 'Montserrat',Arial,sans-serif; width: 100%;" width="100%"
                                               cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td style="font-family: 'Montserrat',Arial,sans-serif; padding-top: 32px; padding-bottom: 32px;">
                                                    <div
                                                        style="--bg-opacity: 1; background-color: #eceff1; background-color: rgba(236, 239, 241, var(--bg-opacity)); height: 1px; line-height: 1px;">
                                                        &zwnj;
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 0 0 16px; color: #666666 !important;">
                                            ¿No está seguro del porqué ha recibido este correo electrónico? Por favor
                                            <a href="mailto:soporte@saludwom.com" class="hover-underline"
                                               style="--text-opacity: 1; color: #792141; color: #792141; text-decoration: none;">háganos
                                                saber</a>.
                                        </p>
                                        <p style="margin: 0 0 16px; color: #666666 !important;">Gracias,
                                            <br>Equipo {{ env('APP_NAME') }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: 'Montserrat',Arial,sans-serif; height: 20px;" height="20"></td>
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
            </td>
        </tr>
    </table>
</div>
</body>

</html>



