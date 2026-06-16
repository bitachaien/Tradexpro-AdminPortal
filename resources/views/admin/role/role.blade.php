@extends('admin.master',['menu'=>'role', 'sub_menu'=>'admin_role_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Role management')}}</li>
                    <li class="active-item">{{__('Role')}}</li>
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
                        <a data-id="admin_list" class="nav-link @if(isset($tab)  && $tab == 'admin_list') active @endif" id="pills-admin-list" data-toggle="pill" href="#pills-admin-user" role="tab" aria-controls="pills-user" aria-selected="true">
                            <img src="{{asset('assets/admin/images/user-management-icons/user.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Role List')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a  class="nav-link @if(isset($tab)  && $tab == 'role_list') active @endif add_user" @if(isset($id)) href="{{ route('adminRoleList').'?tab=role_list' }}" @else data-id="profile_tab" id="pills-add-user-tab" data-toggle="pill"  href="#pills-add-user" role="tab" aria-controls="pills-add-user" aria-selected="true" @endif  >
                            <img src="{{asset('assets/admin/images/user-management-icons/add-user.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Add Role')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a  class="nav-link @if(isset($tab)  && $tab == 'permission_routes') active @endif add_user" data-id="profile_tab" id="pills-permission_routes" data-toggle="pill"  href="#permission_routes" role="tab" aria-controls="pills-add-user" aria-selected="true">
                            <img src="{{asset('assets/admin/images/user-management-icons/add-user.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Permission Routes')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab)  && $tab == 'admin_list') active @endif" id="pills-admin-user" role="tabpanel" aria-labelledby="pills-user-tab">
                    @include('admin.role.include.role_list')
                    </div>
                    <div class="tab-pane add_user @if(isset($tab) && $tab == 'role_list') active @endif" id="pills-add-user" role="tabpanel" aria-labelledby="pills-add-user-tab">
                    @include('admin.role.include.add_role')
                    </div>
                    <div class="tab-pane add_user @if(isset($tab) && $tab == 'permission_routes') active @endif" id="permission_routes" role="tabpanel" aria-labelledby="pills-permission_routes">
                    @include('admin.role.include.permission_routes')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection

@section('script')
    <script>
        @include('admin.role.role_js')
    </script>
@endsection
