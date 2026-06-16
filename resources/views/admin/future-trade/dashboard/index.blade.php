@extends('admin.future-trade.layouts.master',['menu'=>'future_trade', 'sub_menu'=>'dashboard'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Dashboard')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">

            
            
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    
@endsection
