<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Wallet Address Delete')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('deleteWalletAddress')}}" method="get" >
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Coins')}}</label>

                    <div class="cp-select-area">
                        <select name="coin_type" class="form-control" data-width="100%">
                            <option value="">{{ __("Select a coin") }}</option>
                            @if (isset($coins[0]))
                                @foreach ($coins as $coin)
                                        <option value="{{ $coin->coin_type }}">{{ $coin->coin_type }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <h5 class="text-danger">
                        {{ __("Note :") }}
                        {{ __("If you Delete all addresses, deposits and withdrawals associated to a Coin, none of your users will be able to see the existing Deposit address for this Coin.") }}
                        <br>
                        {{ __("All the deposit and withdrawal histories for this Coin will also be gone.") }}
                        <br>
                        <br>
                        {{ __("But Deleting all these will allow you to change the Coin API for this Coin.") }}
                    </h5>
                </div>
                <div class="form-group">
                    <label for="#">{{__('Admin Password')}}</label>
                    <input class="form-control" id="password" type="password" name="password" />
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="btn btn-danger ">{{__('Delete Address')}}</button>
            </div>
        </div>
    </form>
</div>
