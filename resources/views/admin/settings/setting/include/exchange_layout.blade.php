<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Exchange Layout Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminExchangeLayoutSettings')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-4 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Choose Layout')}}</label>
                    <div class="cp-select-area">
                        <select id="exchange_view" name="exchange_layout_view" class="form-control">
                            @foreach(exchangeLayout() as $key => $val)
                                <option value="{{$key}}" @if(isset($settings['exchange_layout_view']) && $settings['exchange_layout_view'] == $key) selected @endif>{{$val}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-1"></div>
            <div class="col-lg-7 col-12  mt-20">
                <div id="layout1" class="form-group @if(isset($settings['exchange_layout_view']) && $settings['exchange_layout_view'] == EXCHANGE_LAYOUT_ONE) d-block @else d-none @endif">
                    <img class="exchange-layout-img" width="90%" src="{{asset('assets/admin/images/exchange_layout/layout1.png')}}" alt="">
                </div>
                <div id="layout2" class="form-group @if(isset($settings['exchange_layout_view']) && $settings['exchange_layout_view'] == EXCHANGE_LAYOUT_TWO) d-block @else d-none @endif">
                    <img class="exchange-layout-img" width="90%" src="{{asset('assets/admin/images/exchange_layout/layout2.png')}}" alt="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>

