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
                    <li>{{ __('User management') }}</li>
                    <li class="active-item">{{ __('User') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row no-gutters">
            <div class="col-12 col-lg-3">
                <ul class="nav user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a data-id="active_users" class="nav-link active" id="pills-user-tab" data-toggle="pill"
                            href="#pills-user" role="tab" aria-controls="pills-user" aria-selected="true">
                            <img src="{{ asset('assets/admin/images/user-management-icons/user.svg') }}" class="img-fluid"
                                alt="">
                            <span>{{ __('User List') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-id="profile_tab" class="nav-link add_user" id="pills-add-user-tab" data-toggle="pill"
                            href="#pills-add-user" role="tab" aria-controls="pills-add-user" aria-selected="true">
                            <img src="{{ asset('assets/admin/images/user-management-icons/add-user.svg') }}"
                                class="img-fluid" alt="">
                            <span>{{ __('Add User') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-id="suspend_user" class="nav-link" id="pills-suspended-user-tab" data-toggle="pill"
                            href="#pills-user" role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                            <img src="{{ asset('assets/admin/images/user-management-icons/block-user.svg') }}"
                                class="img-fluid" alt="">
                            <span>{{ __('Suspended User') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-id="deleted_user" class="nav-link" id="pills-deleted-user-tab" data-toggle="pill"
                            href="#pills-user" role="tab" aria-controls="pills-deleted-user" aria-selected="true">
                            <img src="{{ asset('assets/admin/images/user-management-icons/delete-user.svg') }}"
                                class="img-fluid" alt="">
                            <span>{{ __('Deleted User') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-id="delete_request" class="nav-link" id="pills-deleted-user-tab" data-toggle="pill"
                            href="#pills-user" role="tab" aria-controls="pills-deleted-user" aria-selected="true">
                            <img src="{{ asset('assets/admin/images/user-management-icons/delete-user.svg') }}"
                                class="img-fluid" alt="">
                            <span>{{ __('Delete Request') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-id="email_pending" class="nav-link" id="pills-email-tab" data-toggle="pill"
                            href="#pills-user" role="tab" aria-controls="pills-email" aria-selected="true">
                            <img src="{{ asset('assets/admin/images/user-management-icons/email.svg') }}" class="img-fluid"
                                alt="">
                            {{ __('Email Pending') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-id="phone_pending" class="nav-link" id="pills-email-tab" data-toggle="pill"
                            href="#pills-user" role="tab" aria-controls="pills-email" aria-selected="true">
                            <img src="{{ asset('assets/admin/images/user-management-icons/email.svg') }}" class="img-fluid"
                                alt="">
                            {{ __('Pending Phone Verify') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show active" id="pills-user" role="tabpanel" aria-labelledby="pills-user-tab">
                        <div class="table-area">

                            <div class="custom-box-shadow p-3 mb-3 rounded">
                                <form id="withdrawal_form" class="row" action="{{ route('userExport') }}"
                                    method="get">
                                    @csrf
                                    <div class="col-6 col-md-3 form-group">
                                        <label for="#">{{ __('From Date') }}</label>
                                        <input type="hidden" name="type" value="withdrawal" />
                                        <input type="date" name="from_date" class="form-control" />
                                    </div>
                                    <div class="col-6 col-md-3 form-group">
                                        <label for="#">{{ __('To Date') }}</label>
                                        <input type="date" name="to_date" class="form-control" />
                                    </div>
                                    <div class="col-6 col-md-3 form-group">
                                        <label for="#">{{ __('Export') }}</label>
                                        <select name="export_to" class="selectpicker" data-style="form-control"
                                            data-width="100%" title="{{ __('Select a file type') }}">
                                            <option value=".csv">CSV</option>
                                            <option value=".xlsx">XLSX</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 form-group">
                                        <label for="#">&nbsp;</label>
                                        <input class="form-control btn btn-primary"
                                            style="background-color:#ffba00; border: none; color: white" type="submit"
                                            value="{{ __('Export') }}" />
                                    </div>
                                </form>
                            </div>
                            <div class="custom-box-shadow p-3 rounded">
                                <table id="table" class="table table-borderless custom-table display ">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="all">{{ __('User Name') }}</th>
                                            <th scope="col">{{ __('Email ID') }}</th>
                                            <th scope="col">{{ __('Status') }}</th>
                                            <th scope="col">{{ __('Created At') }}</th>
                                            <th scope="col" class="all">{{ __('Activity') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>


                        </div>
                    </div>
                    <div class="tab-pane add_user" id="pills-add-user" role="tabpanel"
                        aria-labelledby="pills-add-user-tab">
                        <div class="header-bar">
                            <div class="table-title">
                                <h3>{{ __('Add User') }}</h3>
                            </div>
                        </div>
                        <div class="add-user-form">
                            <form action="{{ route('admin.UserAddEdit') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="firstname">{{ __('First Name') }}</label>
                                            <input type="text" name="first_name" class="form-control" id="firstname"
                                                value="{{ old('first_name') }}" placeholder="First Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lastname">{{ __('Last Name') }}</label>
                                            <input name="last_name" type="text" class="form-control" id="lastname"
                                                value="{{ old('last_name') }}" placeholder="Last Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">{{ __('Email') }}</label>
                                            <input type="email" name="email" class="form-control" id="email"
                                                value="{{ old('email') }}" placeholder="Email address">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lastname">{{ __('Phone Number') }}</label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                value="{{ old('phone') }}" placeholder="phone">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button class="button-primary theme-btn">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </form>
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
        (function($) {
            "use strict";

            @if (isset($errors->all()[0]))

                $('.tab-pane').removeClass('active show');
                $('.nav-link').removeClass('active show');
                $('.add_user').addClass('active show');
                $('#profile-tab').addClass('active show');
            @endif

            function getTable(type) {

                var table = $('#table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 10,

                    responsive: false,
                    ajax: '{{ route('adminUsers') }}?type=' + type,
                    order: [4, 'desc'],
                    autoWidth: false,
                    scrollX: true,
                    scrollY: true,
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
                            "data": "first_name",
                            "orderable": false
                        },
                        {
                            "data": "email",
                            "orderable": true
                        },
                        {
                            "data": "status",
                            "orderable": false
                        },
                        {
                            "data": "created_at",
                            "orderable": true
                        },
                        {
                            "data": "activity",
                            "orderable": false
                        }
                    ],
                });

            }

            $(document.body).on('click', '.nav-link', function() {
                var id = $(this).data('id');
                if (id != 'undefined') {
                    $('#table').DataTable().destroy();
                    getTable(id)
                    
                }

            });
            getTable('active_users');
        })(jQuery)
    </script>
@endsection
