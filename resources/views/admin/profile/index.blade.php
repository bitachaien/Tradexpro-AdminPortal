@extends('admin.master',['menu'=>'profile'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Profile')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management profile pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-3">
                <ul class="nav user-management-nav profile-nav mb-3" id="pills-tab" role="tablist">
                    <li>
                        <a class=" @if(isset($tab) && $tab=='profile') active @endif nav-link " data-id="profile" data-toggle="pill" role="tab" data-controls="profile" aria-selected="true" href="#profile">
                            <img src="{{asset('assets/admin/images/user-management-icons/user.svg')}}" class="img-fluid" alt="">
                            <span>{{__('My Profile')}}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if(isset($tab) && $tab=='edit_profile') active @endif nav-link  " data-id="edit_profile" data-toggle="pill" role="tab" data-controls="edit_profile" aria-selected="true" href="#edit_profile">
                            <img src="{{asset('assets/admin/images/edit-profile.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Edit Profile')}}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if(isset($tab) && $tab=='two_factor') active @endif nav-link  " data-id="two_factor" data-toggle="pill" role="tab" data-controls="two_factor" aria-selected="true" href="#two_factor">
                            <img src="{{asset('assets/admin/images/check-square.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Two Factor Auth')}}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if(isset($tab) && $tab=='change_pass') active @endif nav-link  " data-id="change_pass" data-toggle="pill" role="tab" data-controls="change_pass" aria-selected="true" href="#change_pass">
                            <img src="{{asset('assets/admin/images/reset-pass.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Change Password')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9">
                <div class="tab-content tab-pt-n" id="tabContent">
                    <!-- genarel-setting start-->
                    <div class="tab-pane fade show @if(isset($tab) && $tab=='profile')  active @endif " id="profile" role="tabpanel" aria-labelledby="general-setting-tab">
                        <div class="form-area plr-65">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="profile-img-area text-center">
                                        <div class="prifile-img">
                                            <img width="100" src="{{show_image(Auth::user()->id,'user')}}" alt="profile">
                                        </div>
                                        <div class="profile-name">
                                            <h3>{!! clean(Auth::user()->first_name.' '.Auth::user()->last_name) !!}</h3>
                                            <span>{!! clean(Auth::user()->email) !!}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="profile-info">
                                        <div class="">
                                            <table class="table table-striped">
                                                <tbody>
                                                <tr>
                                                    <td>{{__('Nick Name')}}</td>
                                                    <td>:</td>
                                                    <td><span>{!! clean(Auth::user()->nickname) !!}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('Name')}}</td>
                                                    <td>:</td>
                                                    <td><span>{!! clean(Auth::user()->first_name.' '.Auth::user()->last_name) !!}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('Role')}}</td>
                                                    <td>:</td>
                                                    <td><span>{{userRole($user->role)}}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('Email')}}</td>
                                                    <td>:</td>
                                                    <td><span>{!! clean(Auth::user()->email) !!}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('Email verification')}}</td>
                                                    <td>:</td>
                                                    <td><span class="color">{!! statusAction($user->is_verified) !!}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('Contact')}}</td>
                                                    <td>:</td>
                                                    <td><span>{{\Illuminate\Support\Facades\Auth::user()->phone}}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('Country')}}</td>
                                                    <td>:</td>
                                                    <td><span>{{ !empty($user->country)?country($user->country):'' }}</span></td>
                                                </tr>

                                                <tr>
                                                    <td>{{__('Status')}}</td>
                                                    <td>:</td>
                                                    <td><span>{!! statusAction($user->status) !!}</span></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade @if(isset($tab) && $tab=='edit_profile')show active @endif" id="edit_profile" role="tabpanel" aria-labelledby="apisetting-tab">
                        <div class="form-area">
                            <h4 class="mb-4">{{__('Edit Profile')}}</h4>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="profile-img-area text-center">
                                        <div class="uplode-img">
                                            <form enctype="multipart/form-data" method="post" action="{{route('uploadProfileImage')}}">
                                                @csrf
                                                <div id="file-upload" class="section-p">
                                                    <input type="file" name="file_one" value="" id="file" ref="file" class="dropify" data-default-file="{{show_image(Auth::user()->id,'user')}}" />
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="profile-info-form form-area  p-0">
                                        <form action="{{route('UserProfileUpdate')}}" method="post">
                                            @csrf
                                            <div class="form-group">
                                                <label for="firstname">{{__('Nick Name')}}</label>
                                                <input name="nickname" value="{{old('nickname',Auth::user()->nickname)}}" type="text" class="form-control"  placeholder="{{__('Nick name')}}">
                                                @error('nickname')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="firstname">{{__('First Name')}}</label>
                                                <input name="first_name" value="{{old('first_name',Auth::user()->first_name)}}" type="text" class="form-control" id="firstname" placeholder="{{__('First name')}}">
                                                @error('first_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="lastname">{{__('Last Name')}}</label>
                                                <input name="last_name" value="{{old('last_name',Auth::user()->last_name)}}" type="text" class="form-control" id="lastname" placeholder="{{__('Last name')}}">
                                                @error('last_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="email">{{__('Phone Number')}}</label>
                                                <input name="phone"   type="text" value="{{old('phone',Auth::user()->phone)}}" class="form-control" id="phoneVerify" placeholder="{{__('01999999999')}}">
                                                @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="email">{{__('Email')}}</label>
                                                <input name="email" type="email" value="{{old('email',Auth::user()->email)}}" class="form-control" id="email" placeholder="{{__('email')}}">
                                                @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="country">{{__('Country')}} </label>
                                                <div class="cp-select-area customSelect">
                                                    <select name="country" id="country_code" class="selectpicker" title="{{ __('Select Country') }}" data-live-search="true" data-width="100%"
                                                        data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                                    @if(isset($countries))
                                                        @foreach($countries as $key=>$value)
                                                            <option class="bg-light" value="{{$key}}"
                                                            @if(isset(Auth::user()->country)) {{$key == Auth::user()->country ? 'selected' :' '}}
                                                            @endif
                                                            >{{$value}} </option>
                                                        @endforeach
                                                    @endif
                                                    </select>
                                                </div>
                                                @error('country')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <button type="submit" class="btn theme-btn">{{__('Update')}}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade @if(isset($tab) && $tab=='change_pass')show active @endif" id="change_pass" role="tabpanel" aria-labelledby="braintree-tab">
                        <div class="form-area ">
                            <h4 class="mb-4">{{__('Change Password')}}</h4>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="profile-info-form form-area p-0">
                                        <form method="POST" action="{{route('changePasswordSave')}}">
                                            @csrf
                                            <div class="form-group">
                                                <label for="currentpassword">{{__('Current Password')}}</label>
                                                <input name="password" type="password" class="form-control" id="currentpassword" placeholder="">
                                                <span class="flaticon-look"></span>
                                            </div>
                                            <div class="form-group">
                                                <label for="newpassword">{{__('New Password')}}</label>
                                                <input name="new_password" type="password" class="form-control" id="newpassword" placeholder="">
                                                <span class="flaticon-look"></span>
                                            </div>
                                            <div class="form-group">
                                                <label for="confirmpassword">{{__('Confirm Password')}}</label>
                                                <input name="confirm_new_password" type="password" class="form-control" id="confirmpassword" placeholder="">
                                                <span class="flaticon-look"></span>
                                            </div>
                                            <button type="submit" class="btn theme-btn">{{__('Change Password')}}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade @if(isset($tab) && $tab=='two_factor')show active @endif" id="two_factor" role="tabpanel" aria-labelledby="braintree-tab">
                        <div class="form-area">

                            <div class="row">
                                <div class="col-lg-6">
                                    @if($two_factor && in_array(GOOGLE_AUTH,$two_factor))
                                        <h4 class="mb-4">{{__('Two Factor Auth')}}</h4>
                                    <div class="profile-info-form form-area p-0">
                                        <div class="cp-user-auth-icon">
                                            <img style="border-radius: 10px; border: 1px solid #2B3C70;" src="{{asset('assets/user/images/gauth.svg')}}" class="img-fluid" alt="">
                                        </div>
                                        <br>
                                        @if(empty(Auth::user()->google2fa_secret))
                                        <a href="javascript:" data-toggle="modal" data-target="#exampleModal" style="width: 10%" class="btn btn-success">{{__('Set up')}}</a>
                                        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <form method="post" action="{{ route("SaveTwoFactorAdmin") }}">
                                                    @csrf
                                                    <input type="hidden" name="google2fa_secret" value="{{ $google2fa_secret }}">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">{{__('Google Authentication')}}</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-12 text-center">
                                                                    {{-- <img src="{{ $qrcode }}" class="img-fluid" alt=""> --}}
                                                                    <div class="d-inline-block" style="border: 10px solid white">
                                                                        @if(!empty($qrcode))  {!! QrCode::size(200)->generate($qrcode); !!} @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 mt-4 text-center">
                                                                    <p class="mb-3" style="font-size: 16px; line-height: 24px;">{{__('Open your Google Authenticator app, and scan Your secret code and enter the 6-digit code from the app into the input field')}}</p>
                                                                    <input placeholder="{{__('Code')}}" type="text" class="form-control" name="code">
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                                                            <button type="submit" class="btn btn-primary">{{__('Verify')}}</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @else
                                        <a href="javascript:" data-toggle="modal" data-target="#exampleModalRemove" style="width: 10%" class="btn btn-danger">{{__('Remove google secret key')}}</a>
                                        <div class="modal fade" id="exampleModalRemove" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <form method="post" action="{{ route("SaveTwoFactorAdmin") }}">
                                                    @csrf
                                                    <input type="hidden" name="remove" value="1">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">{{__('Google Authentication')}}</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">

                                                                <div class="col-12">
                                                                    <p>{{__('Open your Google Authenticator app and enter the 6-digit code from the app into the input field to remove the google secret key')}}</p>
                                                                    <input placeholder="{{__('Code')}}" type="text" class="form-control" name="code">
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                                                            <button type="submit" class="btn btn-primary">{{__('Verify')}}</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                    @if($two_factor)
                                    <hr>
                                    <h4 class="mb-4">{{__('Two Factor Security')}}</h4>
                                    <p class="">{{__('Please on this option to enable two factor authentication at log In.')}}</p>
                                    <form method="post" action="{{ route("UpdateTwoFactor") }}" id="userFaSave">
                                        @csrf
                                        @if(in_array(GOOGLE_AUTH,$two_factor))
                                        <div class="row">
                                            <div class="col-lg-6 col-12  mt-20">
                                                <div class="form-group">
                                                    <label for="#">{{__('Google auth Two Factor At login')}}</label>
                                                    <select name="google" class="form-control" >
                                                        <option @if(Auth::user()->g2f_enabled == DISABLE) selected @endif value="{{DISABLE}}">{{__("No")}}</option>
                                                        <option @if(Auth::user()->g2f_enabled == ENABLE) selected @endif value="{{ENABLE}}">{{__("Yes")}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @if(in_array(EMAIL_AUTH,$two_factor))
                                        <div class="row">
                                            <div class="col-lg-6 col-12  mt-20">
                                                <div class="form-group">
                                                    <label for="#">{{__('Email Two Factor At login')}}</label>
                                                    <select name="email" class="form-control" >
                                                        <option @if(Auth::user()->email_enabled == DISABLE) selected @endif value="{{DISABLE}}">{{__("No")}}</option>
                                                        <option @if(Auth::user()->email_enabled == ENABLE) selected @endif value="{{ENABLE}}">{{__("Yes")}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @if(in_array(PHONE_AUTH,$two_factor))
                                        <div class="row">
                                            <div class="col-lg-6 col-12  mt-20">
                                                <div class="form-group">
                                                    <label for="#">{{__('Phone number Two Factor At login')}}</label>
                                                    <select name="phone" class="form-control" >
                                                        <option @if(Auth::user()->phone_enabled == DISABLE) selected @endif value="{{DISABLE}}">{{__("No")}}</option>
                                                        <option @if(Auth::user()->phone_enabled == ENABLE) selected @endif value="{{ENABLE}}">{{__("Yes")}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-lg-2 col-12 mt-20">
                                                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
                                            </div>
                                        </div>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
    <script>
        (function($) {
            "use strict";

            $('.nav-link').on('click', function () {
                var query = $(this).data('id');
                window.history.pushState('page2', 'Title', '{{route('adminProfile')}}?tab=' + query);
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                var str = '#' + $(this).data('controls');
                $('.tab-pane').removeClass('show active');
                $(str).addClass('show active');
            });

            jQuery("#file").on('change', function () {
                this.form.submit();

            });

            $(function () {
                $(document.body).on('submit', '.Upload', function (e) {
                    e.preventDefault();
                    $('.error_msg').addClass('d-none');
                    $('.succ_msg').addClass('d-none');
                    var form = $(this);
                    $.ajax({
                        type: "POST",
                        enctype: 'multipart/form-data',
                        url: form.attr('action'),
                        data: new FormData($(this)[0]),
                        async: false,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            if (data.success == true) {
                                $('.succ_msg').removeClass('d-none');
                                $('.succ_msg').html(data.message);
                            } else {

                                $('.error_msg').removeClass('d-none');
                                $('.error_msg').html(data.message);

                            }
                        }
                    });
                    return false;
                });
            });
        })(jQuery);
    </script>
@endsection
