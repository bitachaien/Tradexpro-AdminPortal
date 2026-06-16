@extends('admin.master', ['menu' => 'users', 'sub_menu' => 'user'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{ __('User') }}</li>
                    <li class="active-item">{{ __('Api Access Setting') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management profile">
        <div class="row">
            <div class="col-12">
                <div class="profile-info padding-40">
                    <div class="row">
                        <div class="col-xl-4 mb-xl-0 mb-4">
                            <div class="user-info text-center mb-8">
                                <div class="avater-img">
                                    <img src="{{ show_image($user->id, 'user') }}" alt="">
                                </div>
                                <h4>{{ $user->first_name . ' ' . $user->last_name }}</h4>
                                <p>{{ $user->email }}</p>
                            </div>
                            <div class="mt-5">
                                <form action="{{ route('userApiAccessUpdate') }}" method="post">
                                    @csrf
                                    <div class="row mt-8">
                                        <div class="col-md-6">
                                            <input type="hidden" name="user_id" value="{{ encrypt($user->id) }}">
                                            <div class="form-group">
                                                <label
                                                    for="api_access_allow_user">{{ __('Allow this user to access api') }}</label>
                                                <div class="cp-select-area">
                                                    <select name="api_access_allow_user" id=""
                                                        class="form-control">
                                                        @foreach ($status1 as $key1 => $value1)
                                                            <option value="{{ $key1 }}"
                                                                {{ $key1 == $user->api_access_allow_user ? 'selected' : '' }}>
                                                                {{ $value1 }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="#">{{ __('Trade Api Access Enable') }}</label>
                                                <div class="cp-select-area">
                                                    <select name="api_access_trade_enable" class="form-control"
                                                        data-width="100%">
                                                        @foreach ($status2 as $key2 => $value2)
                                                            <option value="{{ $key2 }}"
                                                                {{ $key2 == $user->api_access_trade_enable ? 'selected' : '' }}>
                                                                {{ $value2 }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">

                                            <div class="form-group">
                                                <label for="#">{{ __('Withdrawal Api Access Enable') }}</label>
                                                <div class="cp-select-area">
                                                    <select name="api_access_withdraw_enable" class="form-control"
                                                        data-width="100%">
                                                        @foreach ($status2 as $key2 => $value2)
                                                            <option value="{{ $key2 }}"
                                                                {{ $key2 == $user->api_access_withdraw_enable ? 'selected' : '' }}>
                                                                {{ $value2 }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <button type="submit"
                                                    class="button-primary theme-btn">{{ __('Update') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                        <div class="col-xl-8">
                            <div class="header-bar">
                                <div class="table-title">
                                    <h3>{{ __('White listed Ip Address') }}</h3>
                                </div>
                                <div class="right d-flex align-items-center">

                                    <div class="add-btn-new mb-2 ml-2">
                                        <a href="#addIp" data-toggle="modal">{{ __('Add New') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-area">
                                <div class="">
                                    <table id="table"
                                        class=" table table-borderless custom-table display text-lg-center" width="100%">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Ip Address') }}</th>
                                                <th scope="col" class="all">{{ __('Trading Access') }}</th>
                                                <th scope="col">{{ __('Withdrawal Access') }}</th>
                                                <th scope="col">{{ __('Is Blocked') }}</th>
                                                <th scope="col">{{ __('Date') }}</th>
                                                <th scope="col" class="all">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($whitelists[0]))
                                                @foreach ($whitelists as $item)
                                                    <tr>
                                                        <td> {{ $item->ip_address }} </td>
                                                        <td>
                                                            <div>
                                                                <label class="switch">
                                                                    <input type="checkbox"
                                                                        onchange="updateUserWhiteListStatus(this)"
                                                                        data-type="trade" data-id="{{ $item->id }}"
                                                                        id="notification" name="security"
                                                                        @if ($item->trade_access == STATUS_ACTIVE) checked @endif>
                                                                    <span class="slider" for="status"></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <label class="switch">
                                                                    <input type="checkbox"
                                                                        onchange="updateUserWhiteListStatus(this)"
                                                                        data-type="withdrawal"
                                                                        data-id="{{ $item->id }}" id="notification"
                                                                        name="security"
                                                                        {{ $item->withdrawal_access == STATUS_ACTIVE ? 'checked' : '' }} />
                                                                    <span class="slider" for="status"></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <label class="switch">
                                                                    <input type="checkbox"
                                                                        onchange="updateUserWhiteListStatus(this)"
                                                                        data-type="status" data-id="{{ $item->id }}"
                                                                        id="notification" name="security"
                                                                        @if ($item->status == STATUS_INACTIVE) checked @endif>
                                                                    <span class="slider" for="status"></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td> {{ $item->created_at ?? 'N/A' }}</td>
                                                        <td>
                                                            <div class="activity-icon">
                                                                <ul>
                                                                    <li><a title="{{ __('Delete') }}"
                                                                            onclick="deleteWhiteList('{{ $item->id }}')"
                                                                            class="user-two btn btn-danger btn-sm"><span><i
                                                                                    class="fa fa-trash pr-1"></i>{{ __('Delete') }}</span></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
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
        </div>
    </div>
    <!-- modal -->
    <div id="addIp" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('Add New Ip Address') }}</h6><button type="button" class="close"
                        data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('addUserWhiteList') }}" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <input type="hidden" name="user_id" value="{{ $user->id }}" />
                    <input type="hidden" name="trade" value="1" />
                    <input type="hidden" name="status" value="1" />
                    <input type="hidden" name="withdrawal" value="1" />
                    <div class="modal-body">
                        <label>{{ __('Ip Address') }}</label>
                        <input type="text" name="ip" class="form-control" required />
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-default"
                            data-dismiss="modal">{{ __('Close') }}</button>
                        <button class="btn btn-warning" type="submit">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')

    <script>
        function updateUserWhiteListStatus(e) {
            let id = e.dataset.id;
            let type = e.dataset.type;
            let value = "0";

            if (e.checked) value = "1";

            $.get(
                '{{ route('updateUserWhiteListStatus') }}', {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    type: type,
                    value: value
                },
                (response) => {

                }
            );
        }

        function deleteWhiteList(id) {
            if (confirm('{{ __('Are you sure you want to delete') }}'))
                window.location.href = "{{ route('deleteUserWhiteList') }}" + id;
        }
        (function($) {
            "use strict";
            $('#table').DataTable({
                responsive: false,
                paging: true,
                searching: true,
                ordering: true,
                select: false,
                bDestroy: true
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
        })(jQuery);
    </script>
@endsection
