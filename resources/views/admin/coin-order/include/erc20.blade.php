<div class="row">
    <div class="col-lg-6 col-12  mt-20">
        <div class="form-group">
            <label for="#">{{__('Contract coin name')}}</label>
            <input class="form-control" type="text" name="contract_coin_name"
                   placeholder="{{__('Base Coin Name For Token Ex. ETH/BNB')}}"
                   value="{{$item->contract_coin_name ?? 'ETH'}}">
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('Chain link')}}</label>
            <input class="form-control" type="text" name="chain_link" required
                   placeholder="" value="{{$item->chain_link ?? ''}}">
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('Chain ID')}}</label>
            <input class="form-control" type="text" name="chain_id" required
                   placeholder="" value="{{$item->chain_id ?? ''}}">
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('Contract Address')}}</label>
            <input class="form-control" type="text" name="contract_address" required
                   placeholder="" value="{{$item->contract_address ?? ''}}">
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('Wallet address')}}</label>
            @if(env('APP_MODE') == 'demo')
                <input class="form-control" value="{{'disablefordemo'}}">
            @else
                <input class="form-control" type="text" required name="wallet_address"
                       placeholder="" value="{{$item->wallet_address}}">
            @endif
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <div class="row mb-1">
                <div class="col-md-6">
                    <label for="#">{{__('Wallet key')}}</label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <input type="password" class="form-control" value="{{'disablefordemo'}}" id="wallet_key">
                </div>
                <div class="col-md-12">
                    <div class="float-right">
                        <a href="#update_wallet" data-toggle="modal" class="bg-warning col-md-4 p-1 mr-1 text-white font-weight-bold">
                            {{__('Update')}}
                        </a>
                        <a href="#viewWalletKey" data-toggle="modal" class="bg-success col-md-4 p-1 text-white font-weight-bold view-wallet-key">
                            {{__('View')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('Decimal')}}</label>
            <input type="munber" name="contract_decimal" class="form-control"
                   @if(isset($item->contract_decimal)) value="{{$item->contract_decimal}}" @else value="{{ old('contract_decimal') }}" @endif>
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('Gas Limit')}}</label>
            <input type="text" name="gas_limit" class="form-control"
                   @if(isset($item->gas_limit)) value="{{$item->gas_limit}}" @else value="43000" @endif>
        </div>
    </div>

    {{-- <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('Last Block Number')}}</label>
            <input type="text" class="form-control" readonly
                @if(isset($item->last_block_number))
                    value="{{$item->last_block_number}}"
                @else
                    value="0"
                @endif
                >

        </div>
    </div> --}}

    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('From Block Number')}}</label>
            <input name="from_block_number" type="text" class="form-control"
                @if(isset($item->from_block_number))
                    value="{{$item->from_block_number}}"
                @else
                    value="0"
                @endif
                >

        </div>
    </div>

    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="#">{{__('To Block Number')}}</label>
            <input name="to_block_number" type="text" class="form-control"
                @if(isset($item->to_block_number))
                    value="{{$item->to_block_number}}"
                @else
                    value="0"
                @endif
                >

        </div>
    </div>
</div>

