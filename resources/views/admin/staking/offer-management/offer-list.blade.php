@extends('admin.staking.layouts.master', ['menu' => 'staking_offer_list', 'sub_menu' => 'list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li class="active-item">{{ $title }}</li>
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
                        <table id="table" class=" table table-borderless custom-table display text-lg-center"
                            width="100%">
                            <thead>
                                <tr>
                                    <th scope="col" class="all">{{ __('Coin Type') }}</th>
                                    <th scope="col">{{ __('Period') }}</th>
                                    <th scope="col">{{ __('Percentage') }}</th>
                                    <th scope="col">{{ __('Minimum Investment') }}</th>
                                    <th scope="col">{{ __('Maximum Investment') }}</th>
                                    <th scope="col">{{ __('Terms Type') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col" class="all">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($offer_list))
                                    @foreach ($offer_list as $offer)
                                        <tr>
                                            <td> {{ $offer->coin_type }} </td>
                                            <td> {{ $offer->period }} </td>
                                            <td> {{ $offer->offer_percentage }} </td>
                                            <td> {{ $offer->minimum_investment }} </td>
                                            <td> {{ $offer->maximum_investment }} </td>
                                            <td> {{ getTermsTypeListStaking($offer->terms_type) }} </td>
                                            <td>

                                                <div>
                                                    <label class="switch">
                                                        <input type="checkbox"
                                                            onclick="statusChange('{{ $offer->uid }}')" id="notification"
                                                            name="security"
                                                            @if ($offer->status == STATUS_ACTIVE) checked @endif>
                                                        <span class="slider" for="status"></span>
                                                    </label>
                                                </div>

                                            </td>

                                            <td>
                                                <ul class=" d-flex activity-menu">
                                                    <li class="viewuser">
                                                        <a class="btn btn-primary btn-sm" title="{{ __('Edit') }}"
                                                            href="{{ route('stakingOfferEdit', ['uid' => $offer->uid]) }}">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    </li>
                                                    <li class="viewuser">
                                                        <a class="btn btn-danger btn-sm" title="{{ __('Delete') }}"
                                                            href="{{ route('stakingDeleteOffer', ['uid' => $offer->uid]) }}">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
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
        $('#table').DataTable({
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            ordering: true,
            select: false,
            bDestroy: true,
            order: [0, 'asc'],
            responsive: false,
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
                "decimal": "",
                "emptyTable": "{{ __('No data available in table') }}",
                "info": "{{ __('Showing') }} _START_ to _END_ of _TOTAL_ {{ __('entries') }}",
                "infoEmpty": "{{ __('Showing') }} 0 to 0 of 0 {{ __('entries') }}",
                "infoFiltered": "({{ __('filtered from') }} _MAX_ {{ __('total entries') }})",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "{{ __('Show') }} _MENU_ {{ __('entries') }}",
                "loadingRecords": "{{ __('Loading...') }}",
                "processing": "",
                "search": "{{ __('Search') }}:",
                "zeroRecords": "{{ __('No matching records found') }}",
                "paginate": {
                    "next": '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                    "previous": '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
        });

        function statusChange(uid) {
            $.ajax({
                type: "POST",
                url: "{{ route('stakingOfferStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'uid': uid
                },
                success: function(data) {
                    if (data.success) {
                        VanillaToasts.create({
                            text: data.message,
                            backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                            type: 'success',
                            timeout: 40000
                        });

                    } else {
                        VanillaToasts.create({
                            text: data.message,
                            type: 'warning',
                            timeout: 40000
                        });
                        location.reload();

                    }
                }
            });
        }
    </script>
@endsection
