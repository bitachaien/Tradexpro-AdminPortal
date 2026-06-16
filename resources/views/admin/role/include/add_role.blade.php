<div class="header-bar">
    <div class="table-title">
        @if(isset($id))
            <h3>{{__('Update Role')}}</h3>
        @else
            <h3>{{__('Add Role')}}</h3>
        @endif
    </div>
</div>
<div class="add-user-form">
    <form action="{{route('adminRoleSave')}}" method="post">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="firstname">{{__('Title')}}</label>
                    <input type="text" name="title" class="form-control" value="@if(isset($role)){{$role->title}}@else{{old('title')}}@endif"  placeholder="Enter role title">
                </div>
            </div>
            @if(isset($id))
            <input type="hidden" name="id" id="role_id"  value="{{encrypt($id)}}">
            <div class="col-md-6">
                <label>{{__('Filter By Groups')}}</label>
                <div class="cp-select-area">
                <select id="role_filter" class="wide form-control">
                    <option value="all">{{__('All')}}</option>
                    @foreach ($actions as $row)
                        <option value="{{ $row->group }}">{{ ucwords(preg_replace("/[_-]/",' ',$row->group)) }}</option>
                    @endforeach
                </select>
                </div>
            </div>
            <div class="table-area col-md-12">
                <div class=" table-responsive">
                    <table id="role_permission" class="table table-borderless custom-table display">
                        <thead>
                        <tr>
                            <th scope="col" class="all">
                                <input type="checkbox" @if(isset($allSelect) && $allSelect) checked @endif class="role_checkbox" data-id="NaN" style="width:30px;height:30px;" id="allSelect" />
                            </th>
                            <th scope="col">{{__('Action')}}</th>
                            <th scope="col">{{__('Details')}}</th>
                            <th scope="col">{{__('Route')}}</th>
                            <th scope="col">{{__('Group')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            <div class="col-md-12">
                <button class="button-primary theme-btn">{{__('Save')}}</button>
            </div>
        </div>
    </form>
</div>