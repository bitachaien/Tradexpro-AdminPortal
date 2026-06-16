<table class="s-6 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
    <tbody>
    <tr>
        <td style="line-height: 24px; font-size: 24px; width: 100%; height: 24px; margin: 0;" align="left" width="100%" height="24">
        &#160;
        </td>
    </tr>
    </tbody>
</table>
<table class="hr" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
    <tbody>
    <tr>
        <td style="line-height: 24px; font-size: 16px; border-top-width: 1px; border-top-color: #e2e8f0; border-top-style: solid; height: 1px; width: 100%; margin: 0;" align="left">
        </td>
    </tr>
    </tbody>
</table>
<table class="s-6 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
    <tbody>
    <tr>
        <td style="line-height: 24px; font-size: 24px; width: 100%; height: 24px; margin: 0;" align="left" width="100%" height="24">
        &#160;
        </td>
    </tr>
    </tbody>
</table>
<p style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;text-align: center;" align="left"> 
    <a href="{{ settings('exchange_url') }}" style="color: #0d6efd;">
        <span>
            {{settings('company_name')}}
        </span>
    </a>
</p>
</td>
</tr>
<tr>
    @php($social_media_list = \App\Model\SocialMedia::where('status',STATUS_ACTIVE)->get())

    <td valign="top" align="center" style="border-collapse: collapse;border: 0;margin: 0;padding: 0;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;">
        <table style="font-weight: normal;border-collapse: collapse;border: 0;margin: 0;padding: 0;font-family: Arial, sans-serif;">
            <tr>
                <td align="center" valign="middle" class="social" style="border-collapse: collapse;border: 0;margin: 0;padding: 10px;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;text-align: center;">
                    <table style="font-weight: normal;border-collapse: collapse;border: 0;margin: 0;padding: 0;font-family: Arial, sans-serif;">
                        <tr>
                            @if (isset($social_media_list))
                                @foreach ($social_media_list as $social_media)
                                    <td style="border-collapse: collapse;border: 0;margin: 0;padding: 5px;-webkit-text-size-adjust: none;
                                        color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;">
                                        <a href="{{ $social_media->media_link}}">
                                            <img src="{{asset(path_image().$social_media->media_icon)}}" style="width: 25px; max-width: 25px; height: 25px; max-height: 25px">
                                        </a>
                                    </td>
                                @endforeach
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
</tbody>
</table>
<table class="s-6 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
<tbody>
<tr>
  <td style="line-height: 24px; font-size: 24px; width: 100%; height: 24px; margin: 0;" align="left" width="100%" height="24">
    &#160;
  </td>
</tr>

</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<!--[if (gte mso 9)|(IE)]>
</td>
</tr>
</tbody>
</table>
<![endif]-->
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script></body>
</html>