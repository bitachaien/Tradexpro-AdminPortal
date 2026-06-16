<div class="header-bar">
    <div class="table-title">
        <h3>{{__('ERC20 / BEP20 / TRC20 Token Details')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveERC20ApiSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('ERC20/BEP20/TRC20 Token App Url')}}</label>
                    <input type="text" name="erc20_app_url" class="form-control" value="{{$settings['erc20_app_url'] ?? ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('ERC20/BEP20/TRC20 Token App Key')}}</label>
                    <input type="text" name="erc20_app_key" class="form-control" value="{{$settings['erc20_app_key'] ?? ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('ERC20/BEP20/TRC20 Token App Port')}}</label>
                    <input type="text" name="erc20_app_port" class="form-control" value="{{$settings['erc20_app_port'] ?? ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Previous Block Count')}}</label>
                    <input type="number" name="previous_block_count" class="form-control" value="{{$settings['previous_block_count'] ?? 100}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('ERC Block Difference')}}</label>
                    <input type="number" name="erc_block_difference" class="form-control" value="{{$settings['erc_block_difference'] ?? 500}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('TRC Block Difference')}}</label>
                    <input type="number" name="trc_block_difference" class="form-control" value="{{$settings['trc_block_difference'] ?? 50}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Polygon Block Difference')}}</label>
                    <input type="number" name="matic_block_difference" class="form-control" value="{{$settings['matic_block_difference'] ?? 500}}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
