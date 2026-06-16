<div>
    <ul class="d-flex activity-menu">
        <li class="viewuser">
            <a href="#update-fee-modal-{{($item->id)}}" data-toggle="modal" title="{{__("Update Trade Fees")}}"
                class="btn btn-primary btn-sm">
                <i class="fa fa-pencil"></i>
            </a>
        </li>
    </ul>
    <div id="update-fee-modal-{{($item->id)}}" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                <h4 class="modal-title">{{ __('Update Trade Fees') }}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tradeFees.update', encrypt($item->id)) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group text-left">
                                    <label class="form-label">{{ __('User Name') }}</label>
                                    <input type="text" class="form-control" value="{{ $item->user_email }}" disabled/>
                                    <input type="hidden" name="user_id" value="{{$item->user_id}}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Coin Pair') }}</label>
                                <input type="text" class="form-control" value="{{ $item->child_coin.'/'. $item->parent_coin }}" disabled/>
                                <input type="hidden" name="coin_pair_id" value="{{ $item->coin_pair_id }}" />
                            </div>
                            <div class="col-md-6">
                                <div class="form-group text-left">
                                    <label class="form-label">{{ __('Maker Fee (%)') }}</label>
                                    <input type="number" step="0.001" class="form-control" name="maker_fee" value="{{ $item->maker_fee }}" min="0"
                                        max="100" placeholder="0.1"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group text-left">
                                    <label class="form-label">{{ __('Taker Fee (%)') }}</label>
                                    <input type="number" step="0.001" class="form-control" name="taker_fee" value="{{ $item->taker_fee }}" min="0"
                                        max="100" placeholder="0.1">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer mt-4">
                            <button class="btn btn-warning text-white" type="submit">{{ __('Update') }}</button>
                            <button type="button" class="btn btn-dark" data-dismiss="modal">{{ __('Close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>