@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'theme_navbar'])
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
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                            <div class="row">
                                <div class="col-6 overflow-hidden">
                                    <div class="form-group">
                                            @foreach($navbar as $nav)
                                                @if($nav->main_id == NULL)
                                                    <div class="input-group mb-3 ml-3" >
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text" >
                                                                <input onchange="savetoNavbar(this)" data-id="{{ $nav->id }}" data-type="0" type="checkbox" @if($nav->status) checked @endif style="width:20px;height:20px;">
                                                            </div>
                                                        </div>
                                                        <input type="text" onkeyup="savetoNavbar(this)" data-type="1" data-id="{{ $nav->id }}"  class="form-control" id="inlineFormInputGroup" placeholder="{{ $nav->slug }}" value="{{ $nav->title }}">
                                                    </div>
                                                    @if($nav->sub)
                                                        @foreach($navbar as $sub)
                                                            @if($nav->id == $sub->main_id)
                                                                <div class="input-group mb-3 ml-5" >
                                                                    <div class="input-group-prepend">
                                                                        <div class="input-group-text" >
                                                                            <input onchange="savetoNavbar(this)" data-id="{{ $sub->id }}" data-type="0" type="checkbox" @if($sub->status) checked @endif style="width:20px;height:20px;">
                                                                        </div>
                                                                    </div>
                                                                    <input type="text" onkeyup="savetoNavbar(this)" data-id="{{ $sub->id }}" data-type="1" class="form-control" id="inlineFormInputGroup" placeholder="{{ $sub->slug }}" value="{{ $sub->title }}">
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                @endif
                                            @endforeach
                                    </div>
                                </div>
                                <div class="col-auto">
                                <label class="sr-only" for="inlineFormInputGroup">Username</label>

                                </div>
                                <div class="col-6 overflow-hidden">
                                    <div class="form-group">

                                    </div>
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

    function savetoNavbar(t){
        var a = $(t);
        var data = { _token: '{{ csrf_token() }}',id : a.data('id'), value : '', type: a.data('type') };
        if(a.data('type'))
           data.value = a.val();

        $.post('{{ route("themeNavebarSettingsSave") }}',data,function(data){
            
        });
    }

</script>
@endsection
