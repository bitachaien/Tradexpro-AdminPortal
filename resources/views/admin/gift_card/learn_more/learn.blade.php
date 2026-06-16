@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('admin.gift_card.sidebar.sidebar',['menu'=>'page'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Learn More Page')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management user-chart card">
        <div class="row">
            <div class="card-body">
                <div class="">
                    <form action="{{ route('proccessLearnMoreGiftCard') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <textarea type="text" name="page" class="form-control">@if(isset($setting['gift_card_learn_more_page'])) {{$setting['gift_card_learn_more_page']}} @else {{old('name')}} @endif</textarea>
                        </div>
                        <div class="form-group">
                            <input type="submit" class="form-control btn btn-primary" value="{{ __("Save") }}">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->


@endsection
@section('script')

<script>
    $("textarea").summernote({
        height: 600
    });
</script>


@endsection
