@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('admin.gift_card.sidebar.sidebar',['menu'=>'header'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{ $title ?? "" }}</li>
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
                        <a class="@if(isset($tab) && $tab=='main_page') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="main_page" href="#main_page" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Gift Card Home Page')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='create_page') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="create_page" href="#create_page" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Gift Card Buy Page')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='themes_page') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="themes_page" href="#themes_page" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Themes Gift Card Page')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='my_card') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="my_card" href="#my_card" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('My Gift Card Page')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane @if(isset($tab) && $tab=='main_page') show active @endif" id="main_page"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.gift_card.header.main')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='create_page') show active @endif" id="create_page"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.gift_card.header.create')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='themes_page') show active @endif" id="themes_page"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.gift_card.header.themes')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='my_card') show active @endif" id="my_card"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.gift_card.header.my_card')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection