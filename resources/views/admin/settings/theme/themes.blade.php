@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'theme_setting'])
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
                    <li class="active-item">{{ 'Themes' }}</li>
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
                        <a class="@if(isset($tab) && $tab=='theme_color') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="theme_color" href="#theme_color" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{__('Theme Color')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='theme_navbar') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="theme_navbar" href="#theme_navbar" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{__('User Navbar')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='footer_custom_pages') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="footer_custom_pages" href="#footer_custom_pages" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{__('Footer Custom Page')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab) && $tab=='theme_color')  active @endif" id="theme_color"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.settings.theme.includes.theme_colors')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='theme_navbar')  active @endif" id="theme_navbar"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.settings.theme.includes.navbar')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='footer_custom_pages')  active @endif" id="footer_custom_pages"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.settings.theme.includes.footer_custom_pages')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
<script>

    let selected_theme = '1';
    let themes_data    = {!! json_encode($themeColors) !!};

    function savetoNavbar(t){
        var a = $(t);
        var data = { _token: '{{ csrf_token() }}',id : a.data('id'), value : '', type: a.data('type') };
        if(a.data('type'))
           data.value = a.val();

        $.post('{{ route("themeNavebarSettingsSave") }}',data,function(data){
            
        });
    }

    function chengeColorPlate(theme){
        let inputColors = document.querySelectorAll('input[type="color"]');
        if(inputColors){
            inputColors.forEach((c)=>{
                let slug = c.dataset.slug;
                let name = `${slug}_${theme}`;

                if(themes_data.hasOwnProperty(name) && themes_data[name]){
                   c.value = themes_data[name];
                }

                c.setAttribute("name", name);
            });
        }
    }

    chengeColorPlate("{{ $settings['default_theme_color'] ?? 'green' }}");

</script>
@endsection
