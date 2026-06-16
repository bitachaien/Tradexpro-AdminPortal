@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'lang_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Language Management')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('adminLanguageSave')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="firstname">{{__('Language Name')}}</label>
                                        <input type="text" name="name" class="form-control" id="firstname" placeholder="{{__('Language title')}}"
                                               @if(isset($item)) value="{{$item->name}}" @else value="{{old('name')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('name') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="lastname">{{__('Language Key')}}</label>
                                        <select name="key" class="form-control">
                                            @foreach(language() as $key => $val)
                                                <option @if(isset($item) && $item->key == $key) selected @endif value="{{ $key }}"> {{ $val}} </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger"><strong>{{ $errors->first('key') }}</strong></span>
                                    </div>
                                </div>

                                @if(isset($item))
                                    <input type="hidden" name="id" value="{{$item->id}}">
                                @endif
                                <div class="col-md-12">
                                    <button class="button-primary theme-btn">@if(isset($item)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
    <script>
         $('select[name="code"]').selectpicker('val', '@if(isset($item)){{$item->code}}@endif');
    </script>
@endsection
