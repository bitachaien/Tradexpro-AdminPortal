@extends('admin.master', ['menu' => 'wallet'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{ __('Wallet Management') }}</li>
                    <li class="active-item">{{ __('User Wallet Address List') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="table-area">
                    <div class="">
                        <table id="table" class="table table-borderless custom-table display text-lg-center"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="all">{{ __('Coin Type') }}</th>
                                    <th class="all">{{ __('User') }}</th>
                                    <th class="all">{{ __('Address') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($address_list[0]))
                                    @foreach ($address_list as $item)
                                        <tr>
                                            <td>{{ $item->coin_type }}</td>
                                            <td>{{ $item?->wallet?->user?->email ?? "N/A" }}</td>
                                            <td>{{ $item->address }}</td>
                                            <td>{{ $item->created_at }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                @if (isset($address_network_list[0]))
                                    @foreach ($address_network_list as $items)
                                        <tr>
                                            <td>{{ $items->network_type }}</td>
                                            <td>{{ $items?->wallet?->user?->email ?? "N/A" }}</td>
                                            <td>{{ $items->address }}</td>
                                            <td>{{ $items->created_at }}</td>
                                        </tr>
                                    @endforeach
                                @endif
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
                responsive: false,
                paging: true,
                searching: true,
                ordering: true,
                select: false,
                bDestroy: true,
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
            });
        })(jQuery)
    </script>
@endsection
