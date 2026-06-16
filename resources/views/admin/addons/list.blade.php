@extends('admin.master',['menu'=>'addons', 'sub_menu'=>'addons_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Addons Lists')}}</li>
                    <li class="active-item">{{__('Addons')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
@php $update = false; @endphp
    <!-- User Management -->
 <div class="user-management">
    <div class="row">
        <div class="col-md-12">
        <div class="card-body">
            <div class="table-area payment-table-area">
                <div class="">
                    <table class="table table-borderless custom-table display text-center" id="table" >
                        <thead>
                            <tr>
                                <th>{{ __('Addons Name') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(isset($list))
                            @foreach($list as $item)
                                <tr>
                                    <td>{{ $item['title'] }}</td>
                                    <td>
                                        <a href="{{ route($item['url']) }}" class="btn btn-xl btn-primary">
                                        {{ __('Manage') }}
                                        </a>
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
</div>
    <!-- /User Management -->
@endsection

@section('script')
    <script>



       $('#table').DataTable({
            dom:'',
            processing: true,
            serverSide: false,
            order:false,
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
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
            columnDefs: [{
            "orderable": false,
            "targets": '_all'
            }]
        });



    </script>
@endsection
