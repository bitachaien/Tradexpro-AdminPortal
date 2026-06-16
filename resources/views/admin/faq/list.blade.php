@extends('admin.master', ['menu' => 'faq', 'sub_menu' => 'faq'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{ __('Settings') }}</li>
                    <li class="active-item">{{ __('FAQs') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management padding-30">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <h3>{{ __('FAQs') }}</h3>
                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn">
                            <a href="{{ route('adminFaqAdd') }}">{{ __('+ Add') }}</a>
                        </div>
                        {{-- <div class="add-btn ml-1">
                            <a href="{{route('adminFaqTypeAdd')}}">{{__('+ Add FAQ Type')}}</a>
                        </div> --}}
                    </div>
                </div>
                <div class="table-area">
                    <div>
                        <table id="table" class="table table-borderless custom-table display" width="100%">
                            <thead>
                                <tr>
                                    <th class="all">{{ __('Question') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Updated At') }}</th>
                                    <th class="text-lg-center all">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
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
            $('#table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                retrieve: true,
                bLengthChange: true,
                responsive: false,
                ajax: '{{ route('adminFaqList') }}',
                order: [2, 'desc'],
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [{
                        "data": "question",
                        "orderable": false
                    },
                    {
                        "data": "type",
                        "orderable": false
                    },
                    {
                        "data": "status",
                        "orderable": false
                    },
                    {
                        "data": "updated_at",
                        "orderable": false
                    },
                    {
                        "data": "actions",
                        "orderable": false
                    }
                ],
            });
        })(jQuery);
    </script>
@endsection
