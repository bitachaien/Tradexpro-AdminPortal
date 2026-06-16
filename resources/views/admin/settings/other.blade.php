@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'other_setting'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Settings')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-3 col-xl-2">
                <ul class="nav user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='address_delete') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="address_delete" href="#address_delete" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{__('Wallet Address Delete')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='coin_pairs') active @endif nav-link " id="pills-coin-tab"
                            data-toggle="pill" data-controls="coin_pairs" href="#coin_pairs" role="tab"
                            aria-controls="pills-coin-pairs" aria-selected="true">
                            <span>{{__('Coin Pairs Settings')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab) && $tab=='address_delete')  active @endif" id="address_delete"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.settings.other_settings.address_delete')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='coin_pairs')  active @endif" id="coin_pairs"
                         role="tabpanel" aria-labelledby="pills-coin-tab">
                        @include('admin.settings.other_settings.coin_pairs')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
    <script>
        (function ($) {
            "use strict";

            $('.nav-link').on('click', function () {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                var str = '#' + $(this).data('controls');
                $('.tab-pane').removeClass('show active');
                $(str).addClass('show active');
            });
            $('#exchange_view').on('change', function () {
                let a = $(this).val();
                if (a == 1){
                    document.getElementById('layout1').classList.toggle("d-none");
                    document.getElementById('layout2').classList.toggle("d-block");
                    document.getElementById('layout1').classList.toggle("d-block");
                    document.getElementById('layout2').classList.toggle("d-none");
                } else {
                    document.getElementById('layout1').classList.toggle("d-none");
                    document.getElementById('layout2').classList.toggle("d-block");
                    document.getElementById('layout1').classList.toggle("d-block");
                    document.getElementById('layout2').classList.toggle("d-none");
                }
            });

            $('#choose_email_template').change(function () {
                let template_value = $(this).val();
                if (template_value == 1){
                    $('#template_number_one').removeClass('d-none').addClass('d-block');
                    $('#template_number_two').removeClass('d-block').addClass('d-none');
                    $('#template_number_three').removeClass('d-block').addClass('d-none');
                    $('#template_number_four').removeClass('d-block').addClass('d-none');
                } else if(template_value == 2) {
                    $('#template_number_one').removeClass('d-block').addClass('d-none');
                    $('#template_number_two').removeClass('d-none').addClass('d-block');
                    $('#template_number_three').removeClass('d-block').addClass('d-none');
                    $('#template_number_four').removeClass('d-block').addClass('d-none');
                }else if(template_value == 3)
                {
                    $('#template_number_one').removeClass('d-block').addClass('d-none');
                    $('#template_number_two').removeClass('d-block').addClass('d-none');
                    $('#template_number_three').removeClass('d-none').addClass('d-block');
                    $('#template_number_four').removeClass('d-block').addClass('d-none');
                }else if(template_value == 4)
                {
                    $('#template_number_one').removeClass('d-block').addClass('d-none');
                    $('#template_number_two').removeClass('d-block').addClass('d-none');
                    $('#template_number_three').removeClass('d-block').addClass('d-none');
                    $('#template_number_four').removeClass('d-none').addClass('d-block');
                }
            });


        })(jQuery)
    </script>
@endsection
