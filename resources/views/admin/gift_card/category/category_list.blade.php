@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('admin.gift_card.sidebar.sidebar',['menu'=>'category'])
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
    <div class="user-management user-chart card">
        <div class="row">
            <div class="card-body">
            <div class="ml-1 card-top">
                <h4>{{__('Gift Card Category')}}</h4>
                <a href="{{ route("giftCardCategory") }}" class="float-right btn" style="background: #ffc107;color: white">{{ __("Add Category") }}</a>
            </div>
                <div class="">
                    <table id="table-coin" class="table table-borderless custom-table display text-center" width="100%">
                        <thead>
                            <tr>
                                <th scope="col" class="all">{{__('Name')}}</th>
                                <th scope="col">{{__('Status')}}</th>
                                <th scope="col" class="all">{{__('Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->


@endsection
@section('script')

<script>
     (function($) {
            "use strict";

            $('#table-coin').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                scrollX: true,
                scrollCollapse: true,
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
                ajax: "{{ route('giftCardCategoryListPage') }}",
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [
                   {"data": "name"},
                   {"data": "status"},
                   {"data": "actions"},
                ]
            });
    })(jQuery);
</script>


@endsection
