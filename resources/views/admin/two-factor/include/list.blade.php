<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Two Factor List')}}</h3>
    </div>
</div>

<div class="card-body">
    <div class="table-area payment-table-area">
        <div class="">
            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                <thead>
                <tr>
                    <th scope="col">{{__('Name')}}</th>
                    <th scope="col">{{__('Status')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach(getTwoFactorArray() as $key => $value)
                    @php
                        $select = in_array($key,$twofa_list??[]) ? "checked" : "";
                    @endphp
                        <tr>
                            <td> {{ $value }} </td>
                            <td>
                                <div>
                                    <label class="switch">
                                        <input type="checkbox" {{ $select }} onclick="statusChange('{{ $key }}')"
                                               id="notification" name="security">
                                        <span class="slider" for="status"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
