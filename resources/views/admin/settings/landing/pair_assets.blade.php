<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Landing Page Pair List Settings')}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-area plr-65 profile-info-form">
    <form enctype="multipart/form-data" method="POST"
          action="{{route('adminLandingPairAssetSave')}}">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="#">{{__('Assets List')}}</label>
                            <div class="cp-select-area">
                                <select name="pair_assets_list" class="form-control" data-width="100%">
                                    <option @if(isset($adm_setting['pair_assets_list']) && $adm_setting['pair_assets_list'] == '6') selected @endif value="6">6</option>
                                    <option @if(isset($adm_setting['pair_assets_list']) && $adm_setting['pair_assets_list'] == '10') selected @endif value="10">10</option>
                                    <option @if(isset($adm_setting['pair_assets_list']) && $adm_setting['pair_assets_list'] == '15') selected @endif value="15">15</option>
                                    <option @if(isset($adm_setting['pair_assets_list']) && $adm_setting['pair_assets_list'] == '20') selected @endif value="20">20</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Assets base coin')}}</label>
                            <div class="customSelect">
                                <select name="pair_assets_base_coin" class="selectpicker" data-width="100%" data-live-search="true">
                                    @foreach(getAllCoinList() as $coin)
                                        <option @if(isset($adm_setting['pair_assets_base_coin']) && $adm_setting['pair_assets_base_coin'] == $coin->coin_type) selected @endif value="{{ $coin->coin_type }}">{{ $coin->coin_type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
