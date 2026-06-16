@extends('admin.master',['menu'=>'addons', 'sub_menu'=>'addons_settings'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Addons Settings')}}</li>
                    <li class="active-item">{{__('Addons')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
@php $update = false; @endphp
    <!-- User Management -->
 <div class="profile-info-form">
 <form action="{{route('saveAddonsSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        @if(isset($IcoLaunchpad) && $IcoLaunchpad)
            @php $update = true; @endphp
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{__('ICO Addons')}}</h3>
                </div>
            </div><hr>
            <div class="row">
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('ICO Addons')}}</label>
                        <div class="cp-select-area">
                            <select name="launchpad_settings" class="form-control">
                                <option @if(isset($settings['launchpad_settings']) && $settings['launchpad_settings'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                                <option @if(isset($settings['launchpad_settings']) && $settings['launchpad_settings'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('ICO token buy request')}}</label>
                        <div class="cp-select-area">
                            <select name="icoTokenBuy_admin_approved" class="form-control">
                                <option @if(isset($settings['icoTokenBuy_admin_approved']) && $settings['icoTokenBuy_admin_approved'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Auto Accept")}}</option>
                                <option @if(isset($settings['icoTokenBuy_admin_approved']) && $settings['icoTokenBuy_admin_approved'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Need Admin Approved")}}</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        @endif
        @if(isset($BlogNews) && $BlogNews)
            @php $update = true; @endphp
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{__('BlogNews Addons')}}</h3>
                </div>
            </div><hr>
            <div class="row">
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('BlogNews Addons')}}</label>
                        <div class="cp-select-area">
                            <select name="blog_news_module" class="form-control">
                                <option @if(isset($settings['blog_news_module']) && $settings['blog_news_module'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                                <option @if(isset($settings['blog_news_module']) && $settings['blog_news_module'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($KnowledgeBase) && $KnowledgeBase)
            @php $update = true; @endphp
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{__('KnowledgeBase and Support Addons')}}</h3>
                </div>
            </div><hr>
            <div class="row">
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('KnowledgeBase and Support Addons')}}</label>
                        <div class="cp-select-area">
                            <select name="knowledgebase_support_module" class="form-control">
                                <option @if(isset($settings['knowledgebase_support_module']) && $settings['knowledgebase_support_module'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                                <option @if(isset($settings['knowledgebase_support_module']) && $settings['knowledgebase_support_module'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(isset($Pagebuilder) && $Pagebuilder)
            @php $update = true; @endphp
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{__('Page Builder Addons')}}</h3>
                </div>
            </div><hr>
            <div class="row">
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('Dynamic Page Builder')}}</label>
                        <div class="cp-select-area">
                            <select name="page_builder_module" class="form-control">
                                <option @if(isset($settings['page_builder_module']) && $settings['page_builder_module'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                                <option @if(isset($settings['page_builder_module']) && $settings['page_builder_module'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(isset($P2P) && $P2P)
            @php $update = true; @endphp
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{__('P2P Trade Addons')}}</h3>
                </div>
            </div><hr>
            <div class="row">
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('P2P Trade')}}</label>
                        <div class="cp-select-area">
                            <select name="p2p_module" class="form-control">
                                <option @if(isset($settings['p2p_module']) && $settings['p2p_module'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                                <option @if(isset($settings['p2p_module']) && $settings['p2p_module'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(isset($demoTrade) && $demoTrade)
        @php $update = true; @endphp
        <div class="header-bar">
            <div class="table-title">
                <h3>{{__('Demo Trade Addons')}}</h3>
            </div>
        </div><hr>
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Demo Trade')}}</label>
                    <div class="cp-select-area">
                        <select name="demo_trade_module" class="form-control">
                            <option @if(isset($settings['demo_trade_module']) && $settings['demo_trade_module'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                            <option @if(isset($settings['demo_trade_module']) && $settings['demo_trade_module'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif
        @if(isset($update) && $update)
            <div class="row">
                <div class="col-lg-2 col-12 mt-20">
                    <button class="button-primary theme-btn">{{__('Update')}}</button>
                </div>
            </div>
        @endif
    </form>
</div>
    <!-- /User Management -->
@endsection

@section('script')
    <script>
    </script>
@endsection
