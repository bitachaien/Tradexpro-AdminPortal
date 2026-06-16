@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
    @include('admin.gift_card.sidebar.sidebar', ['menu' => 'dashboard'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{ __('Dashboard') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">

            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body">
                        <div class="status-card-inner">
                            <div class="d-flex align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"
                                    style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #add4981d;">
                                    <img src="{{ asset('assets/admin/images/status-icons/funds.svg') }}" class="img-fluid"
                                        alt="">
                                </div>
                                <div class="content ml-3">
                                    <p>{{ __('Total Gift Card') }}</p>
                                    <h3>{{ $total_card ?? 0 }}</h3>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body">
                        <div class="status-card-inner">
                            <div class="d-flex align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"
                                    style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #add4981d;">
                                    <img src="{{ asset('assets/admin/images/status-icons/funds.svg') }}" class="img-fluid"
                                        alt="">
                                </div>
                                <div class="content ml-3">
                                    <p>{{ __('Total Redeemed Gift Card') }}</p>
                                    <h3>{{ $total_card_redeem ?? 0 }}</h3>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body">
                        <div class="status-card-inner">
                            <div class="d-flex align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"
                                    style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #add4981d;">
                                    <img src="{{ asset('assets/admin/images/status-icons/funds.svg') }}" class="img-fluid"
                                        alt="">
                                </div>
                                <div class="content ml-3">
                                    <p>{{ __('Total Active Gift Card') }}</p>
                                    <h3>{{ $total_card_active ?? 0 }}</h3>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body">
                        <div class="status-card-inner">
                            <div class="d-flex align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"
                                    style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #add4981d;">
                                    <img src="{{ asset('assets/admin/images/status-icons/funds.svg') }}" class="img-fluid"
                                        alt="">
                                </div>
                                <div class="content ml-3">
                                    <p>{{ __('Total Locked Gift Card') }}</p>
                                    <h3>{{ $total_card_lock ?? 0 }}</h3>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body">
                        <div class="status-card-inner">
                            <div class="d-flex align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"
                                    style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #add4981d;">
                                    <img src="{{ asset('assets/admin/images/status-icons/funds.svg') }}" class="img-fluid"
                                        alt="">
                                </div>
                                <div class="content ml-3">
                                    <p>{{ __('Total Transferred Gift Card') }}</p>
                                    <h3>{{ $total_card_transfer ?? 0 }}</h3>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-average">
                    <div class="card-body">
                        <div class="status-card-inner">
                            <div class="d-flex align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"
                                    style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #add4981d;">
                                    <img src="{{ asset('assets/admin/images/status-icons/funds.svg') }}" class="img-fluid"
                                        alt="">
                                </div>
                                <div class="content ml-3">
                                    <p>{{ __('Active Trading Gift Card') }}</p>
                                    <h3>{{ $total_card_trading ?? 0 }}</h3>
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
