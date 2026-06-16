@extends('admin.master',['menu'=>'fiat_withdraw', 'sub_menu'=>'currency_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Currency Management')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
            <div class="col-md-3 text-left text-md-right py-3 py-md-0">
                <a class="add-btn theme-btn" href="" data-toggle="modal" data-target="#pairModal"><i class="fa fa-plus"></i>{{__('Add New')}}</a>
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
                                    <th scope="col">{{__('Name')}}</th>
                                    <th scope="col">{{__('Code')}}</th>
                                    <th scope="col">{{__('Symbol')}}</th>
                                    <th scope="col">{{__('Rate')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($items[0]))
                                    @foreach($items as $value)
                                        <tr>
                                            <td> {{ $value->name}} </td>
                                            <td> {{$value->code}} </td>
                                            <td> {{$value->symbol}} </td>
                                            <td> {{ $value->rate.' '.$value->code.' / USD' }} </td>
                                            <td>
                                                <div>
                                                    <label class="switch">
                                                        <input type="checkbox" onclick="return changeStatusCall('{{$value->id}}')"
                                                               id="notification" name="security" @if($value->status == STATUS_ACTIVE) checked @endif>
                                                        <span class="slider" for="status"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <ul class=" align-items-center text-center">
                                                    <li>
                                                        <a title="{{__('Delete')}}" href="{{ route("adminFiatCurrencyDelete",["id" => $value->id]) }}">
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
    <!-- Modal -->
    <div id="pairModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{__('Add New Currency')}}</h4>
                </div>
                <div class="modal-body">
                    {{Form::open(['route' => 'adminFiatCurrencySaveProcess', 'files' => 'true' ])}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">{{__('Currency')}}</label>
                                <div class="cp-select-area">
                                    <select required class="form-control" name="currency_id"  style="width: 100%;">
                                        <option value="">{{__('Select')}}</option>
                                        @if(isset($currency_list[0]))
                                            @foreach($currency_list as $item)
                                                <option value="{{$item->id}}">{{$item->name.' ('.$item->code.')'}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer mt-4">
                        <button class="btn btn-warning text-white" type="submit">{{__('Save')}}</button>
                        <button type="button" class="btn btn-dark" data-dismiss="modal">{{__('Close')}}</button>
                    </div>
                    {{Form::close()}}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

        function changeStatusCall(active_id) {
            $.ajax({
                type: "POST",
                url: "{{ route('adminCurrencyStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function (data) {
                   if(data?.success || false)
                   {
                        VanillaToasts.create({
                        text: data.message,
                        backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                        type: 'success',
                        timeout: 40000
                        });
                   }else{
                        VanillaToasts.create({
                        text: data.message,
                        backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                        type: 'warning',
                        timeout: 40000
                        });
                   }
                    
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
