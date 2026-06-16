<?php

namespace App\Http\Controllers\admin;

use App\User;
use Carbon\Carbon;
use App\Model\Role;
use App\Model\Permission;
use Illuminate\Http\Request;
use App\Model\PermissionFromData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Services\RoleManagmentService;
use App\Http\Requests\Admin\RoleSaveRequest;
use App\Http\Requests\Admin\AddPermissionRoute;
use App\Http\Requests\Admin\CreateAdminRequest;

class RoleManagmentController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new RoleManagmentService();
    }
    public function adminList(Request $request)
    {
        if($request->ajax()){
            $users = User::where('id', '<>', Auth::id())->whereRole(USER_ROLE_ADMIN)->whereSuperAdmin('0')->whereStatus(STATUS_ACTIVE);
            return datatables($users)
                ->addColumn('type', function ($item) {
                    $role = Role::find($item->role_id);
                    if ($role)
                        return $role->title;
                return userRole($item->role);
            })
            ->addColumn('online_status', function($item) {
                $response = lastSeenStatus($item->id);
                if($response['success']== true)
                {
                    return onlineStatus($response['data']['online_status']) ;
                }
                return ;
            })
            ->editColumn('created_at', function ($item) {
                return $item->created_at ? with(new Carbon($item->created_at))->format('d M Y') : '';
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(created_at,'%d %M %Y') like ?", ["%$keyword%"]);
            })
            ->addColumn('activity', function ($item) use ($request) {
                return getActionHtmlForAdmin($item->id);
            })
            ->rawColumns(['activity', 'status','online_status'])
            ->make(true);
        }
        $data['roles'] = Role::get();
        return view('admin.role.admin.admin',$data);
    }

    public function adminRoleList(Request $request)
    {
        if($request->ajax()){
            $roles = Role::get();
            return datatables($roles)
            ->addColumn('title', function ($item) {
                return $item->title;
            })
            ->addColumn('activity', function ($item) use ($request) {
                return getActionHtmlForRole($item->id);
            })
            ->rawColumns(['activity'])
            ->make(true);
        }
        $data['tab'] = 'admin_list';
        if(isset($request->tab)){
            $data['tab'] = $request->tab;
        }
        $data['permission_routes'] = PermissionFromData::get();
        if(isset($request->id)){
            $data['id'] = decrypt($request->id);
            $data['role'] = Role::find($data['id']);
            $data['actions'] = PermissionFromData::groupBy('group')->orderBy('id')->get();
            $data['allSelect'] = $this->service->checkAllPermissionActive($data['id'],'all');
        }
        return view('admin.role.role',$data);
    }

    public function adminRolePermissionGroupList(Request $request)
    {
        try {
            if (!isset($request->id))
                return response()->json(responseData(false,__("Something went wrong")));

            $id = decrypt($request->id);
            $list = PermissionFromData::whereStatus((string)STATUS_ACTIVE)->get();
            return datatables($list)
                ->addColumn('checkbox', function ($item) use ($id) {
                    $checked = Permission::whereRoleId($id)->whereActionId($item->id)->get()->count() > 0;
                return getCheckboxForRole($item->id,$checked);
            })
            ->addColumn('action', function ($item){
                return $item->action;
            })
            ->addColumn('for', function ($item){
                return $item->for;
            })
            ->addColumn('route', function ($item){
                return $item->route;
            })
            ->addColumn('group', function ($item){
                return $item->group;
            })
            ->rawColumns(['checkbox'])
            ->make(true);
        } catch (\Exception $e) {
            storeException('adminRolePermissionGroupList', $e->getMessage());
            return responseData(false, __('Something went wrong'), []);
        }
    }
    public function viewAdminProfile($id)
    {
        try{

            $id = decrypt($id);
            $data['title'] = __('Admin Profile');
            $data['user'] = User::with('roles')->find($id);
            return view('admin.role.admin.admin_profile',$data);

        } catch (\Exception $e) {
            storeException('viewAdminProfile', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }

    public function editAdminProfile($id)
    {
        try{

            $id = decrypt($id);
            $data['title'] = __('Edit Admin');
            $data['user'] = User::find($id);
            $data['roles'] = Role::get();
            return view('admin.role.admin.admin_edit',$data);

        } catch (\Exception $e) {
            storeException('editAdminProfile', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }
    public function addEditAdmin(CreateAdminRequest $request)
    {
        try{
            $response = $this->service->addEditAdmin($request);
            if (isset($request->id)) {
                if ($response['success'])
                    return redirect()->route('adminList')->with('success', $response['message']);
                return redirect()->back()->with('dismiss', $response['message']);
            }
            if ($response['success'])
                    return redirect()->route('adminList')->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('addEditAdmin co', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }
    
    public function deleteAdminProfile($id)
    {
        try{
            $id = decrypt($id);
            $response = $this->service->deleteAdminProfile($id);
            if ($response['success'])
                return redirect()->back()->with('success',$response['message']);
            return redirect()->back()->with('dismiss',$response['message']);
        } catch (\Exception $e) {
            storeException('addEditAdmin co', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }

    public function adminRoleSave(RoleSaveRequest $request)
    {
        try{
            $response = $this->service->adminRoleSave($request);
            if ($response['success'])
                return redirect()->route('adminRoleList')->with('success',$response['message']);
            return redirect()->back()->with('dismiss',$response['message']);
        } catch (\Exception $e) {
            storeException('adminRoleSave co', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }

    public function adminRoleDelete($id)
    {
        try{
            $id = decrypt($id);
            $response = $this->service->adminRoleDelete($id);
            if ($response['success'])
                return redirect()->back()->with('success',$response['message']);
            return redirect()->back()->with('dismiss',$response['message']);
        } catch (\Exception $e) {
            storeException('adminRoleDelete co', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }
    public function adminRolePermissionSave(Request $request)
    {
        try{
            $response = $this->service->adminRolePermissionSave($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('adminRolePermissionSave co', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }

    public function addPermissionRoute(AddPermissionRoute $request)
    {
        try{
            $response = $this->service->addPermissionRoute($request);
            if ($response['success'])
                    return redirect()->route('adminRoleList')->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('addPermissionRoute co', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }

    public function addPermissionRouteDelete($id)
    {
        try{
            $id = decrypt($id);
            $response = $this->service->addPermissionRouteDelete($id);
            if ($response['success'])
                return redirect()->back()->with('success',$response['message']);
            return redirect()->back()->with('dismiss',$response['message']);
        } catch (\Exception $e) {
            storeException('addPermissionRouteDelete co', $e->getMessage());
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }

    public function addPermissionRouteEdit($id)
    {
        try{
            $id = decrypt($id);
            $route = PermissionFromData::find($id);
            if($route){
                $route->route_id = encrypt($route->id);
                return response()->json(responseData(true,__("Success"),$route));
            }
            return response()->json(responseData(false,__("Failed")));
        } catch (\Exception $e) {
            storeException('addPermissionRouteEdit co', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function addPermissionRouteReset()
    {
        try{
            Artisan::call('db:seed',['--class' => 'PermissionFromDataSeeder']);
            return response()->json(responseData(true,__("Success")));
        } catch (\Exception $e) {
            storeException('addPermissionRouteReset co', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }
}
