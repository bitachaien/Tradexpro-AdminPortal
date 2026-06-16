@extends('admin.master',['menu'=>'landing_setting', 'sub_menu'=>'custom_pages'])
@section('title',isset($cp) ? 'Update Custom Page' : 'Add Custom Page')
@section('style')
@endsection
@section('content')
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Custom Pages')}}</li>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="user-management add-custom-page custom-box-shadow">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <h3>{{$title}}</h3>
                    </div>
                </div>
                <div class="profile-info-form">
                    <form id="edit" action="{{route('adminCustomPageSave')}}" method="post">
                        @if(!empty($cp->id))
                            <input type="hidden" name="edit_id" value="{{$cp->id}}">
                        @endif
                        @csrf
                        <div class="row">
                            <div class="col-xl-12 mb-xl-0 mb-4">
                                <div class="form-group">
                                    <label>{{__('Page Title')}}</label>
                                    <input id="page_title" class="form-control" type="text" name="title" placeholder="{{__('Title')}}" @if(isset($cp))value="{{$cp->title}}" @else value="{{old('title')}}" @endif>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{__('Slug')}}</label>
                                            <input  type="text" class="form-control check_slug_validity" name="key" placeholder="{{__('Slug')}}" @if(isset($cp))value="{{$cp->key}}" @else value="{{old('key')}}" @endif>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{__('Type')}}</label>
                                            <div class="cp-select-area">
                                                <select name="type" id="" class="form-control">
                                                    @foreach(custom_page_type() as $key => $val)
                                                        <option value="{{$key}}" @if(isset($cp) && $cp->type == $key) selected @endif>{{$val}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{__('Page Link Type')}}</label>
                                            <div class="cp-select-area">
                                                <select name="page_type" id="custom_page_link_type" class="form-control">
                                                    <option value="">{{__('Select')}}</option>
                                                    @foreach(custom_page_link_type() as $key => $val)
                                                        <option value="{{$key}}" @if(isset($cp) && $cp->page_type == $key) selected @endif>{{$val}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group @if(isset($cp) && ($cp->page_type == CUSTOM_PAGE_LINK_URL)) d-block @else d-none @endif" id="pageLink" >
                                    <label>{{__('Page Link')}}</label>
                                    <input id="page_link" class="form-control-new" type="text" name="page_link" placeholder="{{__('Add here page full url')}}" @if(isset($cp))value="{{$cp->page_link}}" @else value="{{old('page_link')}}" @endif>
                                </div>
                                <div class="form-group @if(isset($cp) && ($cp->page_type == CUSTOM_PAGE_LINK_PAGE)) d-block @else d-none @endif" id="descriptionLink">
                                    <label for="">{{__('Description')}}</label>
                                    <input type="hidden" id="body" name="body" value="" />
                                    <textarea rows="10" name="" id="editor" class="form-control-new textarea note-editable" >@if(isset($cp)){!! ($cp->description) !!} @else {{old('description')}} @endif</textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="button-primary theme-btn">
                                        @if(isset($cp)) {{__('Update')}} @else {{__('Submit')}} @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- .user-area end -->
@endsection
@section('script')
    <script>
        $(document).ready(function()
        {
            $(document).on('input','#page_title',function (e){
                const page_title = $(this).val();
                var url = "{{route('customPageSlugCheck')}}";
                var data = {
                    '_token': '{{ csrf_token() }}',
                    'title': page_title,
                };
                if(typeof $('#edit_id').val()!=='undefined'){
                    data.id = $('#edit_id').val();
                }
                $.ajax({
                    url : url,
                    type : 'GET',
                    data : data,
                    dataType:'json',
                    success : function(data) {
                        $('.check_slug_validity').val(data.slug);
                    },
                    error : function(request,error)
                    {
                    }
                });

            });
        });
        (function($) {
            "use strict";
            var $summernote = $('#editor');
            var isCodeView;

            $(() => {
                $summernote.summernote({
                    height: 500,
                    focus: true,
                    codeviewFilter: false,
                    codeviewFilterRegex: /<\/*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|ilayer|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|t(?:itle|extarea)|xml)[^>]*?>/gi,
                });
            });

            $summernote.on('summernote.codeview.toggled', () => {
                isCodeView = $('.note-editor').hasClass('codeview');
            });

            $("#edit").submit( (event) => {
                var body = $summernote.summernote('code');
                document.getElementById('body').setAttribute('value', body);

            });

            $('#custom_page_link_type').on('change', function () {
                let a = $(this).val();
                if (a == 1){
                    document.getElementById('descriptionLink').classList.remove("d-none");
                    document.getElementById('descriptionLink').classList.add("d-block");
                    document.getElementById('pageLink').classList.remove("d-block");
                    document.getElementById('pageLink').classList.add("d-none");
                } else if(a == 2) {
                    document.getElementById('descriptionLink').classList.remove("d-block");
                    document.getElementById('descriptionLink').classList.add("d-none");
                    document.getElementById('pageLink').classList.remove("d-none");
                    document.getElementById('pageLink').classList.add("d-block");
                } else {
                    document.getElementById('descriptionLink').classList.remove("d-block");
                    document.getElementById('pageLink').classList.add("d-none");
                    document.getElementById('descriptionLink').classList.remove("d-block");
                    document.getElementById('pageLink').classList.add("d-none");
                }
            })
        })(jQuery);
    </script>
@endsection
