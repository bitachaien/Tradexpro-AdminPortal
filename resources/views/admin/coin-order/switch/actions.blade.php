@if ($coin->ico_id == 0 || $coin->is_listed == 1)
    <ul class="d-flex activity-menu">
        <li class="viewuser">
            <a href="{{route('adminCoinEdit', encrypt($coin->id))}}" title="{{__("Update")}}" class="btn btn-primary btn-sm">
                <i class="fa fa-pencil"></i>
            </a>
        </li>
        @if($coin->currency_type == CURRENCY_TYPE_CRYPTO)
            <li class="viewuser">
                <a href="{{route('adminCoinSettings', encrypt($coin->id))}}" title="{{__("Settings")}}" class="btn btn-warning btn-sm">
                    <i class="fa fa-cog"></i>
                </a>
            </li>
        @endif
        <li class="viewuser">
            <a href="#delete1WV4d6uF6Ytu8v1Pl_{{($coin->id)}}" data-toggle="modal" title="{{__("Delete")}}" class="btn btn-danger btn-sm">
                <i class="fa fa-trash"></i>
            </a>
            <div id="delete1WV4d6uF6Ytu8v1Pl_{{($coin->id)}}" class="modal fade delete" role="dialog">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header"><h6 class="modal-title">{{__('Delete')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                        <div class="modal-body"><p>{{ __('Do you want to delete ?')}}</p></div>
                        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                            <a class="btn btn-danger"href="{{route('adminCoinDelete', encrypt($coin->id))}}">{{__('Confirm')}} </a>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
@else

    <ul class="d-flex activity-menu">
        <li class="viewuser">
            <a href="#make_listed_coin_{{($coin->id)}}" data-toggle="modal" title="{{__("Make Listed")}}" class="btn btn-danger btn-sm">
                {{__('Make Listed')}}
            </a>
            <div id="make_listed_coin_{{($coin->id)}}" class="modal fade delete" role="dialog">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title">{{__('Make Listed')}}</h6>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <ul>
                                <li>{{__('1')}}.{{__('Check your Token Buy history for this token. If any request is pending for this token then make it rejected or accepted. Otherwise this token is not listed.')}}</li>
                                <li>{{__('2')}}.{{__('Check your Phases for this token. If any phase is running make it deactive or it will automatically deactive. ')}}</li>
                                <li>{{__('3')}}.{{__('After make it listed you can not create new phase or run any phase for this token.')}}</li>
                            </ul>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                            <a class="btn btn-danger"href="{{route('coinMakeListed', encrypt($coin->id))}}">{{__('Confirm')}} </a>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    <ul>
@endif
