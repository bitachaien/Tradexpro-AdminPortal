<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        @media only screen and (max-width: 600px) {
		.main {
			width: 320px !important;
		}

		.top-image {
			width: 100% !important;
		}
		.inside-footer {
			width: 320px !important;
		}
		table[class="contenttable"] { 
            width: 320px !important;
            text-align: left !important;
        }
        td[class="force-col"] {
	        display: block !important;
	    }
	     td[class="rm-col"] {
	        display: none !important;
	    }
		.mt {
			margin-top: 15px !important;
		}
		*[class].width300 {width: 255px !important;}
		*[class].block {display:block !important;}
		*[class].blockcol {display:none !important;}
		.emailButton{
            width: 100% !important;
        }

        .emailButton a {
            display:block !important;f
            font-size:18px !important;
        }

	}
    </style>
</head>

<body link="#00a5b5" vlink="#00a5b5" alink="#00a5b5">

    <table class=" main contenttable" align="center" style="font-weight: normal;border-collapse: collapse;border: 0;margin-left: auto;margin-right: auto;padding: 0;font-family: Arial, sans-serif;color: #555559;background-color: white;font-size: 16px;line-height: 26px;width: 600px;">
            <tr>
                <td class="border" style="border-collapse: collapse;border: 1px solid #eeeff0;margin: 0;padding: 0;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;">
                    <table style="font-weight: normal;border-collapse: collapse;border: 0;margin: 0;padding: 0;font-family: Arial, sans-serif;">
                        <tr>
                            <td colspan="4" valign="top" class="image-section" 
                                style="border-collapse: collapse;border: 0;margin: 0;
                                        padding: 0;-webkit-text-size-adjust: none;color: #555559;
                                        font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;
                                        background-color: #fff;border-bottom: 4px solid #00a5b5;
                                        ">
                                <a href="{{ settings('exchange_url') }}">
                                    <img class="top-image" 
                                     style="line-height: 1;width: 150px; max-width: 150px; max-height: 60px;" alt="Tenable Network Security"
                                     @if(!empty(allSetting()['logo'])) src="{{asset(path_image().allSetting()['logo'])}}" @else src="{{asset('assets/user/images/logo.svg')}}" @endif>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" class="side title" 
                                style="border-collapse: collapse;border: 0;margin: 0;padding: 20px;-webkit-text-size-adjust: none;
                                    color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;
                                    vertical-align: top;background-color: white;border-top: none;">
                                <table style="font-weight: normal;border-collapse: collapse;border: 0;margin: 0;padding: 0;
                                    font-family: Arial, sans-serif;">
                                    <tr>
                                        <td class="head-title" style="border-collapse: collapse;border: 0;margin: 0;padding: 0;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 28px;line-height: 34px;font-weight: bold; text-align: center;">
                                            <div class="mktEditable" id="main_title">
                                                {{ __('Welcome to')}} {{ settings('company_name') }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="sub-title" style="border-collapse: collapse;border: 0;margin: 0;padding: 0;padding-top:5px;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 18px;line-height: 29px;font-weight: bold;text-align: center;">
                                        <div class="mktEditable" id="intro_title">
                                        </div></td>
                                    </tr>
                                    <tr>
                                        <td class="top-padding" style="border-collapse: collapse;border: 0;margin: 0;padding: 5px;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;"></td>
                                    </tr>
                                    <tr>
                                        <td class="grey-block" style="border-collapse: collapse;border: 0;margin: 0;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;background-color: #fff; text-align:center;">
                                        <div class="mktEditable" id="cta">
                                        
                                            <strong>Date:</strong> {{date('d M,Y')}}<br>
                                             
                                        </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="top-padding" style="border-collapse: collapse;border: 0;margin: 0;padding: 15px 0;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 21px;">
                                            <hr size="1" color="#eeeff0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text" style="border-collapse: collapse;border: 0;margin: 0;padding: 0;-webkit-text-size-adjust: none;color: #555559;font-family: Arial, sans-serif;font-size: 16px;line-height: 26px;">
                                            <div class="mktEditable" id="main_text">
                                                {{-- Hello Safayet,<br><br>
        
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.<br><br> --}}
                                                
                                            