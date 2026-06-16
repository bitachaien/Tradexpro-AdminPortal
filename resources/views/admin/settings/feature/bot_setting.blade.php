<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Trading bot enable disable')}}</h3>
    </div>
    <div>
        <button class="btn theme-btn" id="deleteBotOrders">{{__('Delete Bot Orders')}}</button>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCookieSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Trading bot')}}</label>
                    <div class="cp-select-area">
                        <select name="enable_bot_trade" class="form-control">
                            <option @if(isset($settings['enable_bot_trade']) && $settings['enable_bot_trade'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                            <option @if(isset($settings['enable_bot_trade']) && $settings['enable_bot_trade'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("No")}}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Bot Order Place Interval (Sec)')}} </label>
                    <input class="form-control " type="text"
                           name="trading_bot_buy_interval" placeholder=""
                           value="{{isset($settings['trading_bot_buy_interval']) ? $settings['trading_bot_buy_interval'] : '30'}}">
                </div>
            </div>


            {{-- <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Bot Order Place Process')}}</label>
                    <div class="cp-select-area">
                        <select name="bot_order_place_process" class="form-control">
                            <option @if(isset($settings['bot_order_place_process']) && $settings['bot_order_place_process'] == BOT_ORDER_PROCESS_REDIS) selected @endif value="{{BOT_ORDER_PROCESS_REDIS}}">{{__("Through Redis Server")}}</option>
                            <option @if(isset($settings['bot_order_place_process']) && $settings['bot_order_place_process'] == BOT_ORDER_PROCESS_DIRECT_COMMAND) selected @endif value="{{BOT_ORDER_PROCESS_DIRECT_COMMAND}}">{{__("Through Direct Command")}}</option>
                        </select>
                    </div>
                </div>
            </div> --}}
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
