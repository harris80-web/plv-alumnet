<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'PLV-AlumNet')</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
</head>

<body style="margin:0; padding:0; background-color:#0E0F3B; font-family:'Poppins', Arial, sans-serif;">
    @hasSection('preheader')
        <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
            @yield('preheader')
        </div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#0E0F3B; background-image:url('{{ str_replace(' ', '%20', asset('assets/alumnetBackground.svg')) }}'); background-repeat:no-repeat; background-size:cover; background-position:center top;">
        <tr>
            <td align="center" style="padding:56px 16px;">

                <!-- White Card -->
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:480px; width:100%; background-color:#ffffff; border-radius:16px; box-shadow:0 20px 45px rgba(14,15,59,0.35);">
                    <tr>
                        <td align="center" style="padding:40px 36px 32px 36px;">

                            <!-- Logo -->
                            <img src="{{ str_replace(' ', '%20', asset('assets/PLV-AlumNet LETTERMARK_COLORED 2.png')) }}" alt="PLV-AlumNet"
                                width="190" style="display:block; width:190px; max-width:100%; height:auto; margin:0 0 28px 0;">

                            @yield('content')

                        </td>
                    </tr>
                </table>
                <!-- / White Card -->

                <!-- Footer -->
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" border="0" style="max-width:480px; width:100%; margin-top:36px;">
                    <tr>
                        <td align="center">
                            <img src="{{ str_replace(' ', '%20', asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg')) }}" alt="" width="28" height="28" style="display:inline-block; vertical-align:middle; width:28px; height:28px; margin-right:8px;">
                            <img src="{{ str_replace(' ', '%20', asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png')) }}" alt="PLV-AlumNet" width="140" style="display:inline-block; vertical-align:middle; width:140px; max-width:140px; height:auto;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top:12px;">
                            <p style="margin:0; font-family:'Montserrat', Arial, sans-serif; font-size:13px; font-weight:600; color:#ffffff;">
                                &copy;2025 PLV-AlumNet | All Rights Reserved
                            </p>
                        </td>
                    </tr>
                </table>
                <!-- / Footer -->

            </td>
        </tr>
    </table>
</body>

</html>
