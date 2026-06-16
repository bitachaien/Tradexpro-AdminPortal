<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Personal Node Details')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminNodeSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Username')}}</label>
                    <input class="form-control" type="text" name="coin_api_user"
                           autocomplete="off" placeholder=""
                           value="">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Password')}}</label>
                    <input class="form-control" type="password" name="coin_api_pass"
                           autocomplete="off" placeholder=""
                           value="">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Host')}}</label>
                    <input class="form-control" type="text" name="coin_api_host"
                           autocomplete="off" placeholder=""
                           value="{{$settings['coin_api_host']}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Port')}}</label>
                    <input class="form-control" type="text" name="coin_api_port"
                           autocomplete="off" placeholder=""
                           value="{{$settings['coin_api_port']}}">
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
