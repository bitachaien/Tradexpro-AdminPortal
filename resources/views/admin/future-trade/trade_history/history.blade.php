@extends('admin.future-trade.layouts.master', ['menu' => 'future_trade_history', 'sub_menu' => isset($sub_menu) ? $sub_menu : ''])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{ __('Future Trade Order History') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management wallet-transaction-area">
        <div class="tab-pane fade show active" id="all_order_tab" role="tabpanel" aria-labelledby="all_order">
            <div class="table-area">
                <div class="">
                    <table id="all_table" class="table table-borderless custom-table display text-left" width="100%">
                        <thead>
                            <tr>
                                <th class="all">{{ __('Side') }}</th>
                                <th class="all">{{ __('User') }}</th>
                                <th class="all">{{ __('Base Coin') }}</th>
                                <th class="all">{{ __('Trade Coin') }}</th>
                                <th class="all">{{ __('Parent') }}</th>
                                <th class="all">{{ __('Entry Price') }}</th>
                                <th class="all">{{ __('Exist Price') }}</th>
                                <th class="all">{{ __('Price') }}</th>
                                <th class="all">{{ __('Created At') }}</th>
                                <th class="all">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->



    <!-- Modal -->
    <div id="tradeDetalsModel" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Trade Details') }}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group text-white">
                                <table>
                                    <tr>
                                        <td>{{ __('Side') }} : </td>
                                        <td id="trade_side"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('user') }} : </td>
                                        <td id="trade_user"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Base Coin') }} : </td>
                                        <td id="trade_base_coin"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Trade Coin') }} : </td>
                                        <td id="trade_trade_coin"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Parent') }} : </td>
                                        <td id="trade_parent"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Entry Price') }} : </td>
                                        <td id="trade_entry_price"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Exist Price') }} : </td>
                                        <td id="trade_exist_price"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Price') }} : </td>
                                        <td id="trade_price"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('PNL') }} : </td>
                                        <td id="trade_pnl"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Closed Time') }} : </td>
                                        <td id="trade_closed_time"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Avarege Close Price') }} : </td>
                                        <td id="trade_avg_close_price"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Order Type') }} : </td>
                                        <td id="trade_order_type"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Base Amount') }} : </td>
                                        <td id="trade_amount_in_base_coin"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Trade Amount') }} : </td>
                                        <td id="trade_amount_in_trade_coin"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Take Profit Price') }} : </td>
                                        <td id="trade_take_profit_price"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Stop Loss Price') }} : </td>
                                        <td id="trade_stop_loss_price"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Liquidation Price') }} : </td>
                                        <td id="trade_liquidation_price"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Margin') }} : </td>
                                        <td id="trade_margin"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Fees') }} : </td>
                                        <td id="trade_fees"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Leverage') }} : </td>
                                        <td id="trade_leverage"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Margin Mode') }} : </td>
                                        <td id="trade_margin_mode"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Trade Type') }} : </td>
                                        <td id="trade_trade_type"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Is Position') }} : </td>
                                        <td id="trade_is_position"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Trade Time') }} : </td>
                                        <td id="trade_future_trade_time"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Status') }} : </td>
                                        <td id="trade_status"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Is Market') }} : </td>
                                        <td id="trade_is_market"></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Executed Amount') }} : </td>
                                        <td id="trade_executed_amount"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-dark" data-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End -->
@endsection

@section('script')
    <script>
        function getTradeDetails(id) {
            $.get(
                '{{ route('futureTradeDetails') }}' + id,
                function(data) {
                    if (data?.success) {
                        $('#trade_side').text(data?.data?.side);
                        $('#trade_user').text(data?.data?.user_id);
                        $('#trade_trade_coin').text(data?.data?.trade_coin_id);
                        $('#trade_parent').text(data?.data?.parent_id);
                        $('#trade_entry_price').text(data?.data?.entry_price);
                        $('#trade_entry_price').text(data?.data?.exist_price);
                        $('#trade_price').text(data?.data?.price);
                        $('#trade_pnl').text(data?.data?.pnl);
                        $('#trade_closed_time').text(data?.data?.closed_time);
                        $('#trade_avg_close_price').text(data?.data?.avg_close_price);
                        $('#trade_order_type').text(data?.data?.order_type);
                        $('#trade_amount_in_base_coin').text(data?.data?.amount_in_base_coin);
                        $('#trade_amount_in_trade_coin').text(data?.data?.amount_in_trade_coin);
                        $('#trade_take_profit_price').text(data?.data?.take_profit_price);
                        $('#trade_stop_loss_price').text(data?.data?.stop_loss_price);
                        $('#trade_liquidation_price').text(data?.data?.liquidation_price);
                        $('#trade_margin').text(data?.data?.margin);
                        $('#trade_fees').text(data?.data?.fees);
                        $('#trade_leverage').text(data?.data?.leverage);
                        $('#trade_margin_mode').text(data?.data?.margin_mode);
                        $('#trade_trade_type').text(data?.data?.trade_type);
                        $('#trade_is_position').text(data?.data?.is_position);
                        $('#trade_future_trade_time').text(data?.data?.future_trade_time);
                        $('#trade_status').text(data?.data?.status);
                        $('#trade_is_market').text(data?.data?.is_market);
                        $('#trade_executed_amount').text(data?.data?.executed_amount);

                        $("#tradeDetalsModel").modal('show');
                    } else {
                        alert(data?.message);
                    }
                    
                }
            );
        }



        (function($) {
            "use strict";

            $("#all_table").DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                //ajax: url,
                // order: [7, 'desc'],
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
                columns: [{
                        'data': 'side'
                    },
                    {
                        'data': 'user_id'
                    },
                    {
                        'data': 'base_coin_id'
                    },
                    {
                        'data': 'trade_coin_id'
                    },
                    {
                        'data': 'parent_id'
                    },
                    {
                        'data': 'entry_price'
                    },
                    {
                        'data': 'exist_price'
                    },
                    {
                        'data': 'price'
                    },
                    {
                        'data': 'created_at'
                    },
                    {
                        'data': 'action'
                    },
                ]
            });
        })(jQuery);
    </script>
@endsection
