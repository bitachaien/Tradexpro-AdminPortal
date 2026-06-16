<div class="table-area">
    <div class=" table-responsive">
        <button class="btn btn-danger float-right mb-2 ml-2" onclick="resetAllRouts()">{{ __('Reset Routes') }}</button>
        @if(env('APP_MODE') == 'myDemo')
        <button class="btn btn-primary float-right mb-2" data-toggle="modal" data-target="#exampleModalRoute">{{ __('Add Route') }}</button>
        @endif
        <table id="table_route" class="table table-borderless custom-table display">
            <thead>
            <tr>
                <th scope="col">{{__('Action')}}</th>
                <th scope="col">{{__('Details')}}</th>
                <th scope="col">{{__('Route')}}</th>
                <th scope="col">{{__('Group')}}</th>
                <th scope="col">
                    @if(env('APP_MODE') == 'myDemo') {{__('Option')}} @endif
                </th>
            </tr>
            </thead>
            <tbody>
                @foreach($permission_routes as $route)
                    <tr>
                        <td>{{ $route->action }}</td>
                        <td>{{ $route->for }}</td>
                        <td>{{ $route->route }}</td>
                        <td>{{ $route->group }}</td>
                        <td>
                            <div class="activity-icon">
                                @if(env('APP_MODE') == 'myDemo')
                                <ul>
                                    <li onclick="editPermissionRoute('{{ encrypt($route->id) }}')">
                                        <a title="{{ __(' Edit') }}" href="#" class="user-two btn btn-primary btn-sm">
                                            <span>
                                                <i class="fa fa-edit"></i>{{ __(' Edit') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li onclick="deletePermissionRoute('{{ encrypt($route->id) }}')">
                                        <a title="{{ __('Delete') }}" href="#" class="user-two btn btn-danger btn-sm">
                                            <span>
                                                <i class="fa fa-trash"></i>{{ __(' Delete') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                                @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="exampleModalRoute" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    <form action="{{ route('addPermissionRoute') }}" method="post">
        @csrf
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{ _("Route") }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

          <div class="form-group">
            <label for="recipient-name" class="col-form-label">{{ __("Action") }}:</label>
            <input type="text" class="form-control" name="action" required>
          </div>
          <div class="form-group">
            <label for="message-text" class="col-form-label">{{ __("Details") }}:</label>
            <input type="text" class="form-control" name="for" required>
          </div>
          <div class="form-group">
            <label for="message-text" class="col-form-label">{{ __("Route") }}:</label>
            <input type="text" class="form-control" name="route" required>
          </div>
          <div class="form-group">
            <label for="message-text" class="col-form-label">{{ __("Group") }}:</label>
            <input type="text" class="form-control" name="group" required>
          </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">{{ __("Save") }}</button>
      </div>
    </form>
    </div>
  </div>
</div>
