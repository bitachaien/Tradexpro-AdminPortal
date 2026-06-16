@extends('admin.master',['menu'=>'landing_setting','sub_menu'=>'landing'])
@section('title', 'Landing Setting')
@section('style')
@endsection
@section('content')
    <!-- coin-area start -->
    <div class="landing-page-area user-management">
        <div class="page-wraper section-padding">
            <div class="row no-gutters">
                <div class="col-12 col-lg-3 col-xl-2">
                    <ul class="nav nav-pills nav-pill-three landing-tab user-management-nav" id="tab" role="tablist">
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='hero') active @endif" data-toggle="tab"
                            href="#hero">{{__('Header Setting')}}</a>
                        </li>
                        {{-- <li>
                            <a class="nav-link @if(isset($tab) && $tab=='advertisement') active @endif" data-toggle="tab"
                            href="#advertisement">{{__('Advertisement')}}</a>
                        </li> --}}
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='advertisement') active @endif" data-toggle="tab"
                            href="#advertisement">{{__('Advertisement')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='features') active @endif" data-toggle="tab"
                            href="#features">{{__('Landing Trade')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='contact') active @endif" data-toggle="tab"
                                href="#contact">{{__('Customization')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='pair_assets') active @endif" data-toggle="tab"
                                href="#pair_assets">{{__('Pair List')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='links') active @endif" data-toggle="tab"
                                href="#links">{{__('Download Link')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='section_settings') active @endif" data-toggle="tab"
                                href="#section_settings">{{__('Section settings')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='blog_section') active @endif" data-toggle="tab"
                                href="#blog_section">{{__('Blog Section')}}</a>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-lg-9 col-xl-10">
                    <div class="single-tab section-height">
                        <div class="section-body ">
                            <div class="tab-content">
                                <!-- genarel-setting start-->
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='hero')show active @endif " id="hero" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.header')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='contact')show active @endif "
                                        id="contact" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.customization')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='advertisement')show active @endif "
                                        id="advertisement" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.advertisement')
                                </div>
                                {{-- <div class="tab-pane fade  @if(isset($tab) && $tab=='advertisement')show active @endif "
                                        id="advertisement" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.new_advertisement')
                                </div> --}}
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='features')show active @endif "
                                        id="features" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.trade')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='pair_assets')show active @endif "
                                        id="pair_assets" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.pair_assets')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='links')show active @endif "
                                        id="links" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.links')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='section_settings')show active @endif "
                                        id="section_settings" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.section-settings')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='blog_section')show active @endif "
                                        id="blog_section" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.blog_section')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection
@section('script')
<script>
    $(document).ready(function(){
        var type = "{{isset($adm_setting['download_link_display_type'])?$adm_setting['download_link_display_type']:1}}";
        if(type ==='{{SHOW_LINK}}'){
            $('#display_text').hide();
            $('#display_link').dhow();
        }else
        {
            $('#display_text').show();
            $('#display_link').hide();
        }
    });
    $('#download_link_display_type').change(function(){
        var type = $('#download_link_display_type').val();
        if(type ==='{{SHOW_LINK}}'){
            $('#display_text').hide();
            $('#display_link').show();
        }else if(type ==='{{SHOW_TEXT}}')
        {
            $('#display_text').show();
            $('#display_link').hide();
        }
    });

    var drEvent = $('.dropify').dropify();

    drEvent.on('dropify.beforeClear', function(event, element){
        return confirm("{{ __('Do you really want to delete this image?') }}");
    });
    drEvent.on('dropify.afterClear', function(event, element){
        let name = element.element.name;
        if(name){
            $.get(
                '{{ route("removeAdminImageSettings") }}?slug='+name,
                (response) => {
                    if(response.success || false)
                    {
                        VanillaToasts.create({
                            text: response.message,
                            backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                            type: 'success',
                            timeout: 40000
                        });
                    }else{
                        VanillaToasts.create({
                            text: response.message,
                            type: 'warning',
                            timeout: 40000
                        });
                    }
                }
            )
        }
    });
</script>
@endsection
