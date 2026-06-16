@extends('admin.master',['menu'=>'coin', 'sub_menu' => 'coin_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Coin')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                @if($item->network == BITGO_API)
                <div class="header-bar">
                    <div class="table-title">

                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn-new mb-2">
                            <a href="{{route('adminCoinApiSettings',['tab' => 'bitgo'])}}">{{__('Bitgo Api Setting')}}</a>
                        </div>
                        <div class="add-btn-new mb-2 ml-2">
                            <a href="{{route('adminAdjustBitgoWallet',encrypt($item->coin_id))}}">{{__('Adjust Bitgo Wallet')}}</a>
                        </div>
                    </div>
                </div>
                @endif
                @if($item->network == ERC20_TOKEN || $item->network == BEP20_TOKEN)
                <div class="header-bar">
                    <div class="table-title">

                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn-new mb-2">
                            <a href="{{route('adminCoinApiSettings',['tab' => 'erc20'])}}">{{__('Token Api Setting')}}</a>
                        </div>
                    </div>
                </div>
                @endif
                <div class="profile-info-form">
                    <div>
                        {{Form::open(['route'=>'adminSaveCoinSetting', 'files' => true])}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Type')}}</div>
                                        <p class="form-control">{{$item->coin_type}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin API')}}</div>
                                        <p class="form-control">{{api_settings_new($item->network)}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($item->network == BITCOIN_API)
                            @include('admin.coin-order.include.bitcoin')
                        @elseif($item->network == BITGO_API)
                            @include('admin.coin-order.include.bitgo')
                        @else
                            @include('admin.coin-order.include.erc20')
                        @endif
                        <div class="row">
                            <div class="col-md-2">
                                @if(isset($item))<input type="hidden" name="coin_id" value="{{encrypt($item->coin_id)}}">  @endif
                                <button type="submit" class="btn theme-btn">{{$button_title}}</button>
                            </div>
                        </div>
                        {{Form::close()}}
                        @if($item->network == BITGO_API)
                            <hr>
                            <div class="custom-breadcrumb">
                                <div class="row">
                                    <div class="col-9">
                                        <ul>
                                            <li class="active-item">{{ __("Add Bitgo Webhook") }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            {{Form::open(['route'=>'webhookSave', 'files' => true])}}
                            @include('admin.coin-order.include.webhook')
                            <div class="row">
                                <div class="col-md-2">
                                    @if(isset($item))<input type="hidden" name="coin_id" value="{{encrypt($item->coin_id)}}">  @endif
                                    <button type="submit" class="btn theme-btn">{{__("Update Webhook")}}</button>
                                </div>
                            </div>
                            {{Form::close()}}
                        @endif
                    </div>
                </div>
            </div>
            @if ($item->network == ERC20_TOKEN || $item->network == BEP20_TOKEN)
                <div class="col-md-12 mt-5">
                    <div class="row ">
                        <div class="col-md-4">
                            <input type="password" id="pkkey" class="form-control" placeholder="Insert private key and check with address">
                        </div>
                        <div class="col-md-4">
                            <a href="javascript:" class="btn theme-btn " onclick="check_wallet_address()">{{__('Check Wallet Address')}}</a>
                        </div>
                        <div class="col-md-6">
                            <h3 class="text-danger mt-2 " id="check_wallet_address_message"></h3>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!-- /User Management -->

    <div id="update_wallet" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        {{__('Update Wallet Key')}}
                    </h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{route('updateWalletKey')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="id" value="{{encrypt($item->id)}}">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="#">{{__('Wallet Key')}}</label>
                                    <input type="text" name="wallet_key" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="#">{{__('Enter Your Password')}}</label>
                                    <input type="password" name="password" class="form-control">
                                </div>
                            </div>
                            @if(checkGoogleAuth())
                                <div class="col-md-12" id="google_authenticator_input">
                                    <div class="form-group">
                                        <label for="#">{{__('Google Authenticator Code')}}</label>
                                        <input id="google_authenticator" type="text" name="code" class="form-control">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal">
                            {{__("Cancel")}}
                        </button>
                        <button type="submit" class="btn theme-btn">
                            {{__('Submit')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="viewWalletKey" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        {{__('View Wallet Key')}}
                    </h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="view_wallet_key_submit_form">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="id" value="{{encrypt($item->id)}}">
                            <div class="col-md-12" id="user_password_input_details">
                                <div class="form-group">
                                    <label for="#">{{__('Enter Your Password')}}</label>
                                    <input id="view_wallet_key_user_password" type="password" name="password" class="form-control">
                                </div>
                            </div>
                            @if(checkGoogleAuth())
                                <div class="col-md-12" id="user_password_google_authenticator_input_details">
                                    <div class="form-group">
                                        <label for="#">{{__('Google Authenticator Code')}}</label>
                                        <input id="view_wallet_key_google_authenticator" type="text" name="google_authenticator" class="form-control">
                                    </div>
                                </div>
                            @endif
                            <div id="view_details_wallet_key" class="col-md-12 d-none">

                                    <div class="form-group">
                                        <div class="d-flex justify-content-between">
                                        <label for="#">{{__('Wallet Key')}}</label>
                                        <a class="btn btn-sm btn-success" id="copy_wallet_key">
                                            {{__('Copy')}}
                                        </a>
                                    </div>
                                        <input type="text" id="wallet_key_view" class="form-control" value="">
                                    </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal">
                            {{__("Cancel")}}
                        </button>
                        <button type="submit" class="btn theme-btn">
                            {{__('Confirm')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $('.view-wallet-key').click(function(){
        $('#view_details_wallet_key').addClass('d-none');
        $('#user_password_input_details').removeClass('d-none');
    });

    $('#copy_wallet_key').click(function() {
        var textToCopy = $('#wallet_key_view').val();

        var tempTextarea = $('<textarea>');
        $('body').append(tempTextarea);
        tempTextarea.val(textToCopy).select();
        document.execCommand('copy');
        tempTextarea.remove();
    });

    function check_wallet_address()
    {
        var wallet_key = $('#pkkey').val();

        if(wallet_key !== '')
        {
            $.ajax({
                type: "POST",
                url: "{{ route('check_wallet_address') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'wallet_key': wallet_key,
                    'coin_type': '{{$item->coin_type}}'
                },
                success: function (data) {
                    $('#check_wallet_address_message').empty().text(data.message);
                }
            });


        }else{
            $('#check_wallet_address_message').empty().text('{{__('please, Insert wallet key First')}}');
        }

    }

    $('#view_wallet_key_submit_form').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Serialize form data

        $.ajax({
            url: "{{ route('viewWalletKey') }}", // URL to submit the form
            type: "POST",
            data: formData, // Form data
            dataType: "json", // Response type
            success: function(response) {
                // Handle success response

                if(response.success == true)
                {
                    VanillaToasts.create({
                        text: response.message,
                        backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                        type: 'success',
                        timeout: 40000
                    });

                    $('#view_details_wallet_key').removeClass('d-none');
                    $('#user_password_input_details').addClass('d-none');
                    $('#wallet_key_view').val(response.data);
                    $('#view_wallet_key_user_password').val('');
                    @if(checkGoogleAuth())
                        $('#user_password_google_authenticator_input_details').addClass('d-none');
                        $('#view_wallet_key_google_authenticator').val('');
                    @endif
                }else{
                    VanillaToasts.create({
                        text: response.message,
                        type: 'warning',
                        timeout: 40000
                    });

                }
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.log(error);
            }
        });
    });
</script>
@endsection
