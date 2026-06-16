@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'config'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li>{{__('Setting')}}</li>
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
                <div class="dashboard-status config-section">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Clear Application Cache')}}</p>
                                            <small>{{__('From here you can clear your application cache . or also from the command line you can run the command "php artisan cache:clear"')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_CACHE)}}" class="theme-btn btn-success">{{__('Cache Clear')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Clear Application Config')}}</p>
                                            <small>{{__('From here you can clear your application all configuration . or also from the command line you can run the command "php artisan config:clear"')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_CONFIG)}}" class="theme-btn  btn-success">{{__('Config Clear')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Clear Application View / Route')}}</p>
                                            <small>{{__('From here you can clear your application view and route . or also from the command line you can run the command "php artisan view:clear", "php artisan route:clear"')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_VIEW)}}" class="theme-btn  btn-success">{{__('View Clear')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Run Migration')}}</p>
                                            <small>{{__('For the new migration you can click the button to migrate or run the command "php artisan migrate"')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_MIGRATE)}}" class="theme-btn  btn-success">{{__('Migrate')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Adjust Trade Fees Settings')}}</p>
                                            <small>{{__('No need to click this button, but if missed initial fees setting , you can adjust trade fess setting by clicking this button')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_TRADE_FEES)}}" class="theme-btn  btn-success">{{__('Trade Fees')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('TRC20 & Polygon Token Deposit')}}</p>
                                            <small>{{__('This command should run in your system every five minutes. It helps to deposit custom TRC20 & Polygon token. So try to run it every five minutes through scheduler. Otherwise you will miss user deposit')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_ERC20_TOKEN_DEPOSIT)}}" class="theme-btn  btn-success">{{__('Run Command')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('BEP20 & ERC20 Token Deposit')}}</p>
                                            <small>{{__('This command should run in your system every five minutes. It helps to deposit custom erc20 and dep20 token. So try to run it every five minutes through scheduler. Otherwise you will miss user deposit')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_TOKEN_DEPOSIT)}}" class="theme-btn  btn-success">{{__('Run Command')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Run Schedule')}}</p>
                                            <small>{{__('In this command we use some command, that should always run in the background')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_SCHEDULE_START)}}" class="theme-btn  btn-success">{{__('Run Command')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Adjust Token Deposit to Admin')}}</p>
                                            <small>{{__('In this command we try to adjust the user deposit, we send the user deposited amount to admin address, that should always run in the background')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_ADJUST_TOKEN_DEPOSIT)}}" class="theme-btn  btn-success">{{__('Run Command')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body py-0">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Adjust Token For Coin Pairs')}}</p>
                                            <small>{{__('In this command we will check if the coin pair is for native coin or token, if token then make coin pair is_token=1')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_TOKEN_COIN_PAIR)}}" class="theme-btn  btn-success">{{__('Run Command')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body py-0">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Get Product Update')}}</p>
                                            <small>{{__('From here you can update your project')}}</small>
                                            <a href="{{route('LaravelUpdater::welcome')}}" class="theme-btn  btn-success">{{__('Get Update')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(env('APP_MODE') == 'myDemo')
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Database clear command')}}</p>
                                            <small class="text-warning">{{__('Before click these button you must enable the maintenance mode ans stop the bot. and after execute these command if you get historical data , go to coin pair and add the historical data, then start the data.')}}</small>
                                            <small class="text-danger">""{{__('Note:')}} {{__('Please never do this. Only for our demo purpose, from here we clear the all buy, sell, transaction and chart data')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_DELETE_BUY_ORDER)}}" class="btn btn-small btn-danger mt-1"><i class="fa fa-trash"></i>{{__('Buy Order')}}</a>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_DELETE_SELL_ORDER)}}" class="btn btn-small btn-danger mt-1"><i class="fa fa-trash"></i>{{__('Sell Order')}}</a>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_DELETE_TRANSACTION)}}" class="btn btn-small btn-danger mt-1"><i class="fa fa-trash"></i>{{__('Transactions')}}</a>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_DELETE_CHART)}}" class="btn btn-small btn-danger mt-1"><i class="fa fa-trash"></i>{{__('Chart Data')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-xl-4 col-md-6 col-12 mb-4">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="status-card-inner">
                                        <div class="content">
                                            <p>{{__('Clear Failed Jobs')}}</p>
                                            <small>{{__('In this command we removed the failed jobs,')}}</small>
                                            <a href="{{route('adminRunCommand',COMMAND_TYPE_DELETE_FAILED_JOBS)}}" class="theme-btn  btn-success">{{__('Run Command')}}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
@endsection
