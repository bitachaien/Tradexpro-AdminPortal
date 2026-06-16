<div class="user-management">
    <div class="row">
        <div class="col-12">
            {{-- <div class="header-bar p-4">
                <div class="table-title">
                    <h3>{{ __('KYC List') }}</h3>
                </div>
            </div> --}}
            <div class="card-body">
                <div class="table-area payment-table-area">
                    <div class="">
                        <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('Name')}}</th>
                                    <th scope="col">{{__('Type')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th> {{__('Action')}} </th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(isset($kyc_list))
                                @foreach($kyc_list as $value)
                                    <tr>
                                        <td> {{$value->name}} </td>
                                        <td> {{kycList($value->type)}} </td>
                                        <td>
                                            <div>
                                                <label class="switch">
                                                    <input type="checkbox" onclick="statusChange('{{$value->id}}')"
                                                           id="notification" name="security" @if($value->status == STATUS_ACTIVE) checked @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>

                                            <a href="{{route('kycUpdateImage', encrypt($value->id))}}" title="{{__("Update")}}" class="btn btn-primary btn-sm">
                                                {{__('Update')}}
                                            </a>

                                        </td>

                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6">{{__('No data found')}}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
