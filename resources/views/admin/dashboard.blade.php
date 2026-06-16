@extends('admin.master', ['menu' => 'dashboard'])
@section('title', isset($title) ? $title : '')
@section('style')
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

    <!-- Status -->
    <div class="dashboard-status">
        @include('admin.dashboard.dashboard_status')
    </div>

    <!-- user chart -->
    <div class="user-chart mt-0">
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{ __('Deposit') }}</h4>
                        </div>
                        <p class="subtitle">{{ __('Current Year') }}</p>
                        <div id="depositChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{ __('Withdrawal') }}</h4>
                        </div>
                        <p class="subtitle">{{ __('Current Year') }}</p>
                        <div id="withdrawalChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /user chart -->
    @if (isset($pending_withdrawals[0]))
        <div class="mt-4">
            <div class="custom-breadcrumb">
                <div class="row">
                    <div class="col-12">
                        <ul>
                            <li class="active-item">{{ __('Recent Pending Withdrawal') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="user-management user-chart card">
                <div class="row">
                    @foreach ($pending_withdrawals as $wdrl)
                        <div class="col-md-6 col-12 mb-4">
                            <div class="card status-card status-card-bg-average">
                                <div class="card-body">
                                    <div class="status-card-inner" style="height: auto">
                                        <div class="d-flex align-items-center">

                                            <div class="content">
                                                <p class="mb-0" style="font-weight: 700">
                                                    {{ addressType($wdrl->address_type) }} {{ __('Withdrawal') }}</p>
                                                <p class="mb-0" style="font-size: 14px">
                                                    {{!empty($wdrl->created_at) ?  date('d M Y H:i:s', strtotime($wdrl->created_at)) : '' }}</p>
                                            </div>

                                        </div>


                                        <div>
                                            <button class="btn" style="background: #718a71; color: white"
                                                data-toggle="modal" data-id="{{ $wdrl->id }}"
                                                data-target="#exampleModal"
                                                onclick="showModal('{{ encrypt($wdrl->id) }}','{{ $wdrl }}', '{{ addressType($wdrl->address_type) }}', '{{ !empty($wdrl->created_at) ? date('d M Y H:i:s', strtotime($wdrl->created_at)) : '' }}', '{{ isset($wdrl->senderWallet->user) ? $wdrl->senderWallet->user : `N/A` }}', {{ isset($wdrl->receiverWallet->user) ? $wdrl->receiverWallet->user : `N/A` }})">{{ __('Details') }}</button>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div>
                                                <p style="font-size: 14px; color: #cbcfd7">{{ __('Sender') }}</p>
                                                <p style="color: #cbcfd7; font-size: 18px">
                                                    {{ isset($wdrl->senderWallet) ? $wdrl->senderWallet->user->nickname : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div>
                                                <p style="font-size: 14px; color: #cbcfd7">{{ __('Receiver') }}</p>
                                                <p style="color: #cbcfd7; font-size: 18px">
                                                    {{ isset($wdrl->receiverWallet->user) ? $wdrl->receiverWallet->user->nickname : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div>
                                                <p style="font-size: 14px; color: #cbcfd7">{{ __('Amount') }}</p>
                                                <p style="color: #cbcfd7; font-size: 18px">{{ $wdrl->amount }}
                                                    {{ $wdrl->coin_type }}</p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <!-- /user chart -->

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" id="modalData">

        </div>
    </div>

@endsection

@section('script')
    <script src="{{ asset('assets/common/chart/chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (function($) {
            "use strict";
        })(jQuery)

        function showModal(withdrawId, withdrawalData, withdrawType, date, sender, receiver) {

            let withdrwData = JSON.parse(withdrawalData)
            

            let senderUser = sender ? JSON.parse(sender) : null;
            let receiverUser = receiver ? JSON.parse(receiver) : null;
            // let html = ``
            let html = `<div class="modal-content">
                <div class="modal-header">
                    <div class="content">
                        <p class="mb-0" style="font-weight: 700; font-size: 18px; color:#cbcfd7">
                            ${withdrawType} Withdrawal
                        </p>
                        <p class="mb-0" style="font-size: 14px; color:#cbcfd7">Update at ${date}</p>
                    </div>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div>
                                <p style="font-size: 14px; color: #cbcfd7">Sender</p>
                                <p style="color: #cbcfd7; font-size: 18px">${senderUser ? senderUser?.nickname : "N/A"}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div>
                                <p style="font-size: 14px; color: #cbcfd7">Receiver</p>
                                <p style="color: #cbcfd7; font-size: 18px">${receiverUser ? receiverUser?.nickname : "N/A"}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div>
                                <p style="font-size: 14px; color: #cbcfd7">Amount</p>
                                <p style="color: #cbcfd7; font-size: 18px">${withdrwData?.amount}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div>
                                <p style="font-size: 14px; color: #cbcfd7">Coin Type</p>
                                <p style="color: #cbcfd7; font-size: 18px">${withdrwData?.coin_type}</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div>
                                <p style="font-size: 14px; color: #cbcfd7">Address</p>
                                <p style="color: #cbcfd7; font-size: 18px">${withdrwData?.address}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div>
                                <p style="font-size: 14px; color: #cbcfd7">Fees</p>
                                <p style="color: #cbcfd7; font-size: 18px">${withdrwData?.fees}</p>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div>
                                <p style="font-size: 14px; color: #cbcfd7">Transaction Id</p>
                                <p style="color: #cbcfd7; font-size: 18px">${withdrwData?.transaction_hash}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" style="background: #718a71; color: white" onclick="acceptBtnFunc('${withdrawId}')">Accept</button>
                    <button class="btn ml-3" style="background: #ea98a0; color: white" onclick="rejectBtnFunc('${withdrawId}')">Reject</button>
                </div>
            </div>`
            $('#modalData').html(html);
        }

        function acceptBtnFunc(id) {
            confirm("Do you want to Accept ?");

            window.location.href = `/admin/accept-pending-withdrawal-${id}`;
        }

        function rejectBtnFunc(id) {
            confirm("Do you want to Reject ?");

            window.location.href = `/admin/reject-pending-withdrawal-${id}`;
        }

        let chartOptionsHandler = (Color, chartData) => ({
            chart: {
                height: 270,
                type: "area",
                fontFamily: 'Nunito, sans-serif',
                zoom: {
                    enabled: false,
                },
                toolbar: {
                    show: false,
                },
            },
            dataLabels: {
                enabled: false
            },
            colors: [Color],
            series: [{
                name: "Deposit",
                data: chartData
            }],
            stroke: {
                show: true,
                curve: 'smooth',
                width: 2,
                lineCap: 'square',
            },
            fill: {
                colors: [Color],
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 90, 100],
                    shade: 'dark'
                }
            },
            markers: {
                discrete: [{
                        seriesIndex: 0,
                        dataPointIndex: 6,
                        fillColor: Color,
                        strokeColor: 'transparent',
                        size: 7,
                    },
                    {
                        seriesIndex: 1,
                        dataPointIndex: 5,
                        fillColor: Color,
                        strokeColor: 'transparent',
                        size: 7,
                    },
                ],
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                axisTicks: {
                    show: true,
                    borderType: 'solid',
                    color: Color,
                    height: 6,
                    offsetX: 0,
                    offsetY: 0
                },
                axisBorder: {
                    show: true,
                    color: Color,
                    height: 1,
                    width: '100%',
                    offsetX: 0,
                    offsetY: 0
                },
                labels: {
                    show: true,
                    style: {
                        colors: '#cbcfd7',
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                        fontWeight: 400,
                        cssClass: 'apexcharts-xaxis-label',
                    }
                },
            },
            yaxis: {
                show: true,
                labels: {
                    show: true,
                    align: 'right',
                    minWidth: 0,
                    tickAmount: 10,
                    style: {
                        colors: '#cbcfd7',
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                        fontWeight: 400,
                        cssClass: 'apexcharts-yaxis-label',
                    },
                    offsetX: 0,
                    offsetY: 0,
                    rotate: 0,
                    formatter: (value) => {
                        return value
                    },
                },
                opposite: false,
            },
            grid: {
                borderColor: '#191e3a',
                strokeDashArray: 5,
                xaxis: {
                    lines: {
                        show: true,
                    },
                },
                yaxis: {
                    lines: {
                        show: false,
                    },
                },

            },

        })
        let depositData = {!! json_encode($monthly_deposit) !!};
        let withdrawalData = {!! json_encode($monthly_withdrawal) !!};
        var depositOptions = chartOptionsHandler("#718a71", depositData);
        var withdrawlOptions = chartOptionsHandler("#ea98a0", withdrawalData);

        var depositChart = new ApexCharts(document.querySelector("#depositChart"), depositOptions);
        var withdrawlChart = new ApexCharts(document.querySelector("#withdrawalChart"), withdrawlOptions);

        depositChart.render();
        withdrawlChart.render();
    </script>
@endsection
