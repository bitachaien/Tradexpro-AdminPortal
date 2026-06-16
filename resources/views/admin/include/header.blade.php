<div class="top-bar">
    <div class="container-fluid">
        <div class="row align-items-center justify-content-between">
            <div class="col-xl-1 col-md-2 col-3 top-bar-logo top-bar-logo-hide">
                <div class="logo">
                    <a href="{{route('adminDashboard')}}"><img src="{{show_image(Auth::user()->id,'logo')}}" class="img-fluid logo-large" alt=""></a>
                    <a href="{{route('adminDashboard')}}"><img src="{{show_image(Auth::user()->id,'logo')}}" class="img-fluid logo-small" alt=""></a>
                </div>
            </div>
            <div class="col-xl-1 col-md-2 col-3">
                <div class="menu-bars">
                    <img src="{{asset('assets/admin/images/sidebar-icons/menu.svg')}}" class="img-fluid" alt="">
                </div>
            </div>

            <div class="col-xl-10 col-md-8 col-6">
                <div class="top-bar-right">
                    <ul class="d-flex align-items-center">
                        @if (function_exists('getNotificationList'))

                            @php
                                $notification_list = getNotificationList();
                            @endphp

                            <li>
                                <a href="" class="position-relative notify-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-bell-o notify_ball" aria-hidden="true"></i>
                                    <small class="notify_count" id="notification_count">{{$notification_list->count()}}</small>
                                </a>

                                <div class="dropdown-menu bg-light notification_list mt-3 mr-4" id="notification_list">
                                    @if (isset($notification_list) && $notification_list->count() > 0)
                                        @foreach ($notification_list as $notification_item)
                                            <div class="p-2 border-bottom">
                                                <a class="text-dark"
                                                    href="{{route('support_agent_notification_details', $notification_item->unique_code)}}"
                                                    target="__blank">
                                                    <small>{{$notification_item->title}}</small>
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-2 border-bottom">
                                            <a class="text-dark"
                                                href="#"
                                                target="__blank">
                                                <small>{{__('No Notification Found')}}</small>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endif

                        <li>
                            <div class="btn-group profile-dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="cp-user-avater">
                                        <span class="cp-user-img">
                                            <img src="{{show_image(Auth::user()->id,'user')}}" class="img-fluid" alt="">
                                        </span>
                                        <span class="name">{{Auth::user()->first_name.' '.Auth::user()->last_name}}</span>
                                    </span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <span class="big-user-thumb">
                                        <img src="{{show_image(Auth::user()->id,'user')}}" class="img-fluid" alt="">
                                    </span>
                                    <div class="user-name mb-2">
                                        <p>{{Auth::user()->first_name.' '.Auth::user()->last_name}}</p>
                                    </div>
                                    <button class="dropdown-item" type="button"><a href="{{route('adminProfile')}}"><i class="fa fa-user-circle-o"></i> {{__('Profile')}}</a></button>
                                    {{-- <button class="dropdown-item" type="button"><a href="{{route('myWalletList')}}"><i class="fa fa-money"></i> {{__('My Wallet List')}}</a></button> --}}
                                    <button class="dropdown-item bg-warning" type="button"><a href="{{route('logOut')}}"><i class="fa fa-sign-out"></i> {{__('Logout')}}</a></button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
