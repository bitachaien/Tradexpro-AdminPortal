
</td>
</tr>
</tbody>
</table>
<table class="s-10 w-full" role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
<tbody>
<tr>
<td style="line-height: 40px; font-size: 40px; width: 100%; height: 40px; margin: 0;" align="left" width="100%" height="40">
  &#160;
</td>
</tr>
</tbody>
</table>
<table class="ax-center" role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
<tbody>
<tr>
<td style="line-height: 24px; font-size: 16px; margin: 0;" align="left">
  <h1>{{settings('company_name')}}</h1>
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
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</body>
</html>
