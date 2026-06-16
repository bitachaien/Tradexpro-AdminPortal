@extends('admin.master',['menu'=>'currency_deposit', 'sub_menu'=>'bank_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-5">
                <ul>
                    <li>{{__('Fiat Deposit')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
            <div class="col-sm-7 text-right">
                <a class="add-btn theme-btn" href="{{route('bankAdd')}}">{{__('Add New')}}</a>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="header-bar p-4">
                    <div class="table-title">
                        <h3>{{ $title }}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="">
                            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Account Holder Name')}}</th>
                                    <th scope="col">{{__('Bank Name')}}</th>
                                    <th scope="col">{{__('Country')}}</th>
                                    <th scope="col">{{__('Swift Code')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($banks))
                                    @foreach($banks as $value)
                                        <tr>
                                            <td> {{ $value->account_holder_name}} </td>
                                            <td> {{$value->bank_name}} </td>
                                            <td> {{isset($value->getCountry)?$value->getCountry->value:'N/A'}} </td>
                                            <td> {{$value->swift_code}} </td>
                                            <td>
                                                <div>
                                                    <label class="switch">
                                                        <input type="checkbox" onclick="statusChange('{{$value->id}}')"
                                                               id="notification" name="security" @if($value->status == STATUS_ACTIVE) checked @endif>
                                                        <span class="slider" for="status"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <ul class=" d-flex activity-menu justify-content-center">
                                                    <li>
                                                        <a title="{{__('Edit')}}" href="{{ route("bankEdit",["id" => $value->id]) }}">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a title="{{__('Delete')}}" href="{{ route("bankDelete",["id" => $value->id]) }}">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">{{__('No data found')}}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    <script>

        function statusChange(bank_id) {
            $.ajax({
                type: "POST",
                url: "{{ route('bankStatusChange') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'bank_id': bank_id
                },
                success: function (data) {
                    
                }
            });
        }


        $('#table').DataTable({
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            ordering:  true,
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
                "decimal":        "",
                "emptyTable":     "{{__('No data available in table')}}",
                "info":           "{{__('Showing')}} _START_ to _END_ of _TOTAL_ {{__('entries')}}",
                "infoEmpty":      "{{__('Showing')}} 0 to 0 of 0 {{__('entries')}}",
                "infoFiltered":   "({{__('filtered from')}} _MAX_ {{__('total entries')}})",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "{{__('Show')}} _MENU_ {{__('entries')}}",
                "loadingRecords": "{{__('Loading...')}}",
                "processing":     "",
                "search":         "{{__('Search')}}:",
                "zeroRecords":    "{{__('No matching records found')}}",
                "paginate": {
                    "next": '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                    "previous": '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                },
                "aria": {
                    "sortAscending":  ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
        });
    </script>
@endsection
