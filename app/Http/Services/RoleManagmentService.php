<?php
namespace App\Http\Services;

use App\User;
use App\Model\Permission;
use App\Jobs\SendMailToAdmin;
use App\Model\PermissionFromData;
use App\Http\Services\MailService;
use App\Model\UserVerificationCode;
use Illuminate\Support\Facades\Hash;
use App\Http\Repositories\RoleManagmentRepository;

class RoleManagmentService extends BaseService
{
    public $model = User::class;
    public $repository = RoleManagmentRepository::class;
    public $emailService;

    public function __construct()
    {
        $this->emailService = new MailService;
        parent::__construct($this->model,$this->repository);
    }

    public function addEditAdmin($request): array
    {
        try{
            $id = isset($request->id) ? decrypt($request->id) : 0;
            $responseData = responseData(true, __('Admin created successfully'));
            $responseDataErr = responseData(false, __('Admin created failed'));
            if(isset($request->id)){
                $responseData = responseData(true, __('Admin updated successfully'));
                $responseDataErr = responseData(false, __('Admin updated failed'));
            }
            $password = uniqid();
            $Admindata = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'role' => USER_ROLE_ADMIN,
                'role_id' => $request->role,
                'is_verified' => 1,
                'password' => Hash::make($password)
            ];
            if(!isset($request->id)) $Admindata['email'] = $request->email;
            $response = $this->object->createOrUpdate($Admindata, $id);
            if($response){
                if(!isset($request->id)){
                    dispatch(new SendMailToAdmin($response,$password))->onQueue('send-mail');
                }
                return $responseData;
            }
            return $responseDataErr;
        } catch (\Exception $e) {
            storeException('addEditAdmin service',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function sendEmailToAdmin($user,$password){
        $key = randomNumber(6);
        $token = UserVerificationCode::create(['user_id' => $user->id, 'code' => $key, 'expired_at' => date('Y-m-d', strtotime('+15 days')), 'status' => STATUS_PENDING]);
        $userName = $user->first_name.' '.$user->last_name;
        $userEmail = $user->email;
        $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('TradexPro');
        $subject = __('Change Password | :companyName', ['companyName' => $companyName]);
        $user_data = [
            'email' => $user->email,
            'user' => $user,
            'token' => $key,
            'companyName' => $companyName,
            'password' => $password
        ];
        $template = emailTemplateName('admin_password_reset');
        $this->emailService->send($template, $user_data, $userEmail, $userName, $subject);
    }

    public function deleteAdminProfile($id)
    {
        try{
            $responseData = responseData(true, __('Admin deleted successfully'));
            $responseDataErr = responseData(false, __('Admin deleted failed'));
            $response = $this->object->deleteAdmin($id);
            if($response){
                return $responseData;
            }
            return $responseDataErr;
        } catch (\Exception $e) {
            storeException('deleteAdminProfile service',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function addPermissionRouteDelete($id)
    {
        try{
            $responseData = responseData(true, __('Route deleted successfully'));
            $responseDataErr = responseData(false, __('Route deleted failed'));
            $response = $this->object->deleteAdmin($id);
            if($response){
                return $responseData;
            }
            return $responseDataErr;
        } catch (\Exception $e) {
            storeException('addPermissionRouteDelete service',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function adminRoleDelete($id)
    {
        try{
            if (User::where(['role' => USER_ROLE_ADMIN, 'role_id' => $id, 'status' => STATUS_ACTIVE])->get()->count())
                return responseData('false', __("This role has assigned to a admin"));
            $responseData = responseData(true, __('Role deleted successfully'));
            $responseDataErr = responseData(false, __('Role deleted failed'));
            $response = $this->object->deleteAdminRole($id);
            if($response){
                return $responseData;
            }
            return $responseDataErr;
        } catch (\Exception $e) {
            storeException('adminRoleDelete service',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function adminRoleSave($request)
    {
        try{
            $id = isset($request->id) ? decrypt($request->id) : 0;
            $responseData = responseData(true, __('Role created successfully'));
            $responseDataErr = responseData(false, __('Role created failed'));
            if(isset($request->id)){
                $responseData = responseData(true, __('Role updated successfully'));
                $responseDataErr = responseData(false, __('Role updated failed'));
            }
            $data = [
                'title' => $request->title
            ];
            $response = $this->object->adminRoleSave($data,$id);
            if($response){
                return $responseData;
            }
            return $responseDataErr;
        } catch (\Exception $e) {
            storeException('adminRoleSave service',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function checkAllPermissionActive($role,$group)
    {
        try {
            $pageOrAll = $group == 'all' ? false : true;
            $permission = Permission::whereRoleId($role);
            $permission = !$pageOrAll ? $permission : $permission->whereGroup($group);
            $permission = $permission->get()->count();

            $permissions = !$pageOrAll ? PermissionFromData::get()->count()
                : PermissionFromData::whereGroup($group)->get()->count();
            return $permission == $permissions;
        } catch (\Exception $e) {
            storeException('checkAllPermissionActive service',$e->getLine());
            return false;
        }
    }

    private function clearPermissions($role,$group)
    {
        try{
            if($group == 'all'){
                Permission::whereRoleId($role)->delete();
            }else{
                Permission::whereRoleId($role)->whereGroup($group)->delete();
            }
        } catch (\Exception $e){
            storeException('clearPermissions service',$e->getMessage());
        }
    }

    private function setAllRolePermission($request)
    {
        try{
            $role = decrypt($request->role_id);
            $pageOrAll = $request->group == 'all' ? false : true;
            if ($all = $this->checkAllPermissionActive($role,$request->group)) {
                $permissions = Permission::whereRoleId($role);
                $permissions = !$pageOrAll ? $permissions : $permissions->whereGroup($request->group);
                $permissions->delete();
                return responseData(true, __('All permission deleted successfully'),['all' => false]);
            }
            $this->clearPermissions($role,$request->group);
            $responseData = responseData(true, __('Permission saved successfully'),['all' => true]);
            $permissions = !$pageOrAll ? PermissionFromData::get() :PermissionFromData::whereGroup($request->group)->get() ;
            foreach ($permissions as $permission) {
                $data = [
                    'role_id' => $role,
                    'action_id' => $permission->id,
                    'group' => $permission->group,
                    'action' => $permission->action,
                    'route' => $permission->route,
                ];
                $response = $this->object->createPermission($data);
            }
            return $responseData;
        } catch (\Exception $e) {
            storeException('setAllRolePermission service',$e->getFile());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function adminRolePermissionSave($request)
    {
        try {
            if(isset($request->id) && $request->id == "NaN"){
               return $this->setAllRolePermission($request);
            }
            $responseData = responseData(true, __('Permission saved successfully'));
            $responseDataErr = responseData(false, __('Permission saved failed'));
            $permission = PermissionFromData::find(decrypt($request->id));
            $exist = Permission::whereRoleId(decrypt($request->role_id))->whereActionId($permission->id)->first();
            if($exist) {
                $exist->delete();
                $allCheck = $this->checkAllPermissionActive(decrypt($request->role_id),$request->group);
                $responseData['data'] = ['all' => $allCheck];
                return $responseData;
            }
            $data = [
                'role_id' => decrypt($request->role_id),
                'action_id' => $permission->id,
                'group' => $permission->group,
                'action' => $permission->action,
                'route' => $permission->route,
            ];
            $response = $this->object->createPermission($data);
            if($response){
                $allCheck = $this->checkAllPermissionActive(decrypt($request->role_id),$request->group);
                $responseData['data'] = ['all' => $allCheck];
                return $responseData;
            }
            return $responseDataErr;
        } catch (\Exception $e) {
            storeException('adminRolePermissionSave service',$e->getFile());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function addPermissionRoute($request)
    {
        try{
            $id = isset($request->id) ? decrypt($request->id) : 0;
            $responseData = responseData(true, __('Route created successfully'));
            $responseDataErr = responseData(false, __('Route created failed'));
            if(isset($request->id)){
                $responseData = responseData(true, __('Route updated successfully'));
                $responseDataErr = responseData(false, __('Route updated failed'));
            }
            $data = [
                'action' => $request->action,
                'group' => $request->group,
                'for' => $request->for,
                'route' => $request->route,
            ];
            $response = $this->object->addPermissionRoute($data,$id);
            if($response){
                return $responseData;
            }
            return $responseDataErr;
        } catch (\Exception $e) {
            storeException('addPermissionRoute service',$e->getLine());
            return responseData(false, __('Something went wrong'));
        }
    }

}