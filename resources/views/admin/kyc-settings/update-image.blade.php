@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'kyc_settings'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('KYC settings')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>

    <form action="{{route('kycStoreImage')}}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{$kycDetails->id}}">
        <div class="uplode-img-list">
            <div class="row">
                <div class="col-lg-6 mt-20">
                    <div class="single-uplode">
                        <div class="uplode-catagory">
                            <span>{{__('Update name for')}} {{isset($kycDetails)?$kycDetails->name: ''}}</span>
                        </div>
                        <div class="form-group buy_coin_address_input ">
                            <div>
                                <input class="form-control" type="text" placeholder="{{ __("Enter name") }}" name="name" value="@if(isset($kycDetails['name'])){{ $kycDetails['name'] }}@endif" />
                            </div>
                        </div>
                    </div>
                    <div class="single-uplode">
                        <div class="uplode-catagory">
                            <span>{{__('Upload Image for')}} {{isset($kycDetails)?$kycDetails->name: ''}}</span>
                        </div>
                        <div class="form-group buy_coin_address_input ">
                            <div id="file-upload" class="section-p">
                                <input type="file" placeholder="0.00" name="image" value=""
                                    id="file" ref="file" class="dropify"
                                    @if(isset($kycDetails['image']) && (!empty($kycDetails['image'])))  data-default-file="{{asset(path_image().$kycDetails['image'])}}" @endif />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>

@endsection

@section('script')

@endsection
