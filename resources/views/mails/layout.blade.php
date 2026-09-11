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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        @media only screen and (max-width: 520px) {
            .email-card {
                width: 100% !important;
                border-radius: 0 !important;
                border-left: none !important;
                border-right: none !important;
            }
            .content-cell {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
        }
    </style>
</head>

<body style="margin:0; padding:0; background-color:#F4F5F8; font-family:'Montserrat', Arial, sans-serif; -webkit-font-smoothing:antialiased;">
    @hasSection('preheader')
        <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all; font-family:'Montserrat', Arial, sans-serif;">
            @yield('preheader')
        </div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#F4F5F8; width:100%; margin:0; padding:0; font-family:'Montserrat', Arial, sans-serif;">
        <tr>
            <td align="center" style="padding:48px 16px; font-family:'Montserrat', Arial, sans-serif;">

                <!-- Email Card -->
                <table class="email-card" role="presentation" width="480" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:480px; width:100%; background-color:#ffffff; border:1px solid #E5E7EB; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(14,15,59,0.06); font-family:'Montserrat', Arial, sans-serif;">
                    
                    <!-- Top Solid Navy Header Bar -->
                    <tr>
                        <td style="background-color:#0E0F3B; height:80px; line-height:80px; font-size:0; padding:0; mso-line-height-rule:exactly; font-family:'Montserrat', Arial, sans-serif;">
                            &nbsp;
                        </td>
                    </tr>

                    <!-- Main Content Area -->
                    <tr>
                        <td class="content-cell" align="center" style="padding:36px 36px 12px 36px; font-family:'Montserrat', Arial, sans-serif;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td align="center" style="padding:20px 36px 36px 36px; font-family:'Montserrat', Arial, sans-serif;">
                            <!-- Colored Logo -->
                            <!-- @php
                                $logoUrl = asset('assets/PLV-AlumNet-LETTERMARK-COLORED-2.png');
                            @endphp
                            <img src="{{ $logoUrl }}" alt="PLV-AlumNet"
                                 width="140" height="44"
                                style="display:block; width:140px; max-width:140px; height:auto; margin:0 auto 10px auto; border:0;"> -->
                            <!-- Copyright Notice -->
                            <p style="margin:0; font-family:'Montserrat', Arial, sans-serif; font-size:13px; font-weight:700; color:#0E0F3B; text-align:center;">
                                &copy;2026 PLV-AlumNet | All Rights Reserved
                            </p>
                            @hasSection('footer_disclaimer')
                                <p style="margin:10px 0 0 0; font-family:'Inter', Arial, sans-serif; font-size:11px; line-height:1.5; color:#71718A; text-align:center;">
                                    @yield('footer_disclaimer')
                                </p>
                            @elseif (isset($user) && is_object($user) && isset($user->user_role))
                                @php
                                    $fallbackRole = match($user->user_role) {
                                        'super_admin' => "as part of your super administrator privileges on PLV-AlumNet.",
                                        'admin' => "as part of your administrator privileges on PLV-AlumNet.",
                                        'employer' => "as part of your employer partner account on PLV-AlumNet.",
                                        'alumni' => "as part of the University's alumni network on PLV-AlumNet.",
                                        default => "as a registered user on PLV-AlumNet.",
                                    };
                                @endphp
                                <p style="margin:10px 0 0 0; font-family:'Inter', Arial, sans-serif; font-size:11px; line-height:1.5; color:#71718A; text-align:center;">
                                    You're receiving this email {{ $fallbackRole }}
                                </p>
                            @endif
                        </td>
                    </tr>

                </table>
                <!-- / Email Card -->

            </td>
        </tr>
    </table>
</body>

</html>