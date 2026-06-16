@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'theme_color'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-6">
                <ul>
                    <li>{{__('Settings')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
            <div class="col-md-6 text-right">
                <a class="add-btn theme-btn" href="{{route('resetThemeColorSettings')}}">{{__('Reset Color')}}</a>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('addEditThemeSettingsStore')}}" method="post">
                            @csrf
                            <div class="header-bar">
                                <div class="table-title">
                                    <h3>{{__('Light Theme Color')}}</h3>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Primary Color')}}</label>
                                        <input class="form-control" type="color" name="user_primary_color" value="{{ (!empty($settings['user_primary_color']))?$settings['user_primary_color']: '#fcd535'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Text Primary Color')}}</label>
                                        <input class="form-control" type="color" name="user_text_primary_color" value="{{ (!empty($settings['user_text_primary_color']))?$settings['user_text_primary_color'] : '#ffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Text Primary Color 2')}}</label>
                                        <input class="form-control" type="color" name="user_text_primary_color_2" value="{{ (!empty($settings['user_text_primary_color_2']))?$settings['user_text_primary_color_2'] : '#23262f'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Text Primary Color 3')}}</label>
                                        <input class="form-control" type="color" name="user_text_primary_color_3" value="{{ (!empty($settings['user_text_primary_color_3']))?$settings['user_text_primary_color_3'] : '#777778'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Text Primary Color 4')}}</label>
                                        <input class="form-control" type="color" name="user_text_primary_color_4" value="{{ (!empty($settings['user_text_primary_color_4']))?$settings['user_text_primary_color_4'] : '#cbcfd7'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Border Color')}}</label>
                                        <input class="form-control" type="color" name="user_border_color" value="{{ (!empty($settings['user_border_color']))?$settings['user_border_color'] : '#dedede'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Border Color 1')}}</label>
                                        <input class="form-control" type="color" name="user_border_color_1" value="{{ (!empty($settings['user_border_color_1']))?$settings['user_border_color_1'] : '#e6e8ec'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Border Color 2')}}</label>
                                        <input class="form-control" type="color" name="user_border_color_2" value="{{ (!empty($settings['user_border_color_2']))?$settings['user_border_color_2'] : '#353535'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Hover Color')}}</label>
                                        <input class="form-control" type="color" name="user_hover_color" value="{{ (!empty($settings['user_hover_color']))?$settings['user_hover_color'] : '#f7cf33'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Font Color')}}</label>
                                        <input class="form-control" type="color" name="user_font_color" value="{{ (!empty($settings['user_font_color']))?$settings['user_font_color'] : '#2a2a2d'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('bColor')}}</label>
                                        <input class="form-control" type="color" name="user_bColor" value="{{ (!empty($settings['user_bColor']))?$settings['user_bColor'] : '#424242'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Title Color')}}</label>
                                        <input class="form-control" type="color" name="user_title_color" value="{{ (!empty($settings['user_title_color']))?$settings['user_title_color'] : '#141414'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('white')}}</label>
                                        <input class="form-control" type="color" name="user_white" value="{{ (!empty($settings['user_white']))?$settings['user_white'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('black')}}</label>
                                        <input class="form-control" type="color" name="user_black" value="{{ (!empty($settings['user_black']))?$settings['user_black'] : '#000000'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Color Pallet 1')}}</label>
                                        <input class="form-control" type="color" name="user_color_pallet_1" value="{{ (!empty($settings['user_color_pallet_1']))?$settings['user_color_pallet_1'] : '#b4b8d7'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_background_color" value="{{ (!empty($settings['user_background_color']))?$settings['user_background_color'] : '#151515'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Background Color Trade')}}</label>
                                        <input class="form-control" type="color" name="user_background_color_trade" value="{{ (!empty($settings['user_background_color_trade']))?$settings['user_background_color_trade'] : '#2a2e37'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Main Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_main_background_color" value="{{ (!empty($settings['user_main_background_color']))?$settings['user_main_background_color'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Card Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_card_background_color" value="{{ (!empty($settings['user_card_background_color']))?$settings['user_card_background_color'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Table Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_table_background_color" value="{{ (!empty($settings['user_table_background_color']))?$settings['user_table_background_color'] : '#dad6d6'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Footer Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_footer_background_color" value="{{ (!empty($settings['user_footer_background_color']))?$settings['user_footer_background_color'] : '#f7f7f8'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Background Color Hover')}}</label>
                                        <input class="form-control" type="color" name="user_background_color_hover" value="{{ (!empty($settings['user_background_color_hover']))?$settings['user_background_color_hover'] : '#fafafa'}}"/>
                                    </div>
                                </div>

                            </div><hr>






                            <div class="header-bar">
                                <div class="table-title">
                                    <h3>{{__('Dark Theme Color')}}</h3>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Primary Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_primary_color" value="{{ (!empty($settings['user_dark_primary_color']))?$settings['user_dark_primary_color']: '#fcd535'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Text Primary Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_text_primary_color" value="{{ (!empty($settings['user_dark_text_primary_color']))?$settings['user_dark_text_primary_color'] : '#ffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Text Primary Color 2')}}</label>
                                        <input class="form-control" type="color" name="user_dark_text_primary_color_2" value="{{ (!empty($settings['user_dark_text_primary_color_2']))?$settings['user_dark_text_primary_color_2'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Text Primary Color 3')}}</label>
                                        <input class="form-control" type="color" name="user_dark_text_primary_color_3" value="{{ (!empty($settings['user_dark_text_primary_color_3']))?$settings['user_dark_text_primary_color_3'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Text Primary Color 4')}}</label>
                                        <input class="form-control" type="color" name="user_dark_text_primary_color_4" value="{{ (!empty($settings['user_dark_text_primary_color_4']))?$settings['user_dark_text_primary_color_4'] : '#cbcfd7'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Border Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_border_color" value="{{ (!empty($settings['user_dark_border_color']))?$settings['user_dark_border_color'] : '#535353'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Border Color 1')}}</label>
                                        <input class="form-control" type="color" name="user_dark_border_color_1" value="{{ (!empty($settings['user_dark_border_color_1']))?$settings['user_dark_border_color_1'] : '#535353'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Border Color 2')}}</label>
                                        <input class="form-control" type="color" name="user_dark_border_color_2" value="{{ (!empty($settings['user_dark_border_color_2']))?$settings['user_dark_border_color_2'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Hover Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_hover_color" value="{{ (!empty($settings['user_dark_hover_color']))?$settings['user_dark_hover_color'] : '#f7cf33'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Font Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_font_color" value="{{ (!empty($settings['user_dark_font_color']))?$settings['user_dark_font_color'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('bColor')}}</label>
                                        <input class="form-control" type="color" name="user_dark_bColor" value="{{ (!empty($settings['user_dark_bColor']))?$settings['user_dark_bColor'] : '#424242'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Title Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_title_color" value="{{ (!empty($settings['user_dark_title_color']))?$settings['user_dark_title_color'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('white')}}</label>
                                        <input class="form-control" type="color" name="user_dark_white" value="{{ (!empty($settings['user_dark_white']))?$settings['user_dark_white'] : '#ffffff'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('black')}}</label>
                                        <input class="form-control" type="color" name="user_dark_black" value="{{ (!empty($settings['user_dark_black']))?$settings['user_dark_black'] : '#000000'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Color Pallet 1')}}</label>
                                        <input class="form-control" type="color" name="user_dark_color_pallet_1" value="{{ (!empty($settings['user_dark_color_pallet_1']))?$settings['user_dark_color_pallet_1'] : '#535353'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_background_color" value="{{ (!empty($settings['user_dark_background_color']))?$settings['user_dark_background_color'] : '#151515'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Background Color Trade')}}</label>
                                        <input class="form-control" type="color" name="user_dark_background_color_trade" value="{{ (!empty($settings['user_dark_background_color_trade']))?$settings['user_dark_background_color_trade'] : '#2a2e37'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Main Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_main_background_color" value="{{ (!empty($settings['user_dark_main_background_color']))?$settings['user_dark_main_background_color'] : '#2a2a2d'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Card Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_card_background_color" value="{{ (!empty($settings['user_dark_card_background_color']))?$settings['user_dark_card_background_color'] : '#2a2a2d'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Table Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_table_background_color" value="{{ (!empty($settings['user_dark_table_background_color']))?$settings['user_dark_table_background_color'] : '#353535'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Footer Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_footer_background_color" value="{{ (!empty($settings['user_dark_footer_background_color']))?$settings['user_dark_footer_background_color'] : '#2a2a2d'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Background Color Hover')}}</label>
                                        <input class="form-control" type="color" name="user_dark_background_color_hover" value="{{ (!empty($settings['user_dark_background_color_hover']))?$settings['user_dark_background_color_hover'] : '#3a3a3a'}}"/>
                                    </div>
                                </div>
                                <!-- <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Footer Background Color')}}</label>
                                        <input class="form-control" type="color" name="user_dark_footer_background_color" value="{{ (!empty($settings['user_dark_footer_background_color']))?$settings['user_dark_footer_background_color'] : '#4b4b4b'}}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Background Color Hover')}}</label>
                                        <input class="form-control" type="color" name="user_dark_background_color_hover" value="{{ (!empty($settings['user_dark_background_color_hover']))?$settings['user_dark_background_color_hover'] : 'rgba(247, 247, 247, 0.05)'}}"/>
                                    </div>
                                </div> -->

                                

                                <div class="col-md-12">
                                    <button class="button-primary theme-btn">@if(!empty($settings['user_primary_color'])) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
      alert();  
    </script>
@endsection
