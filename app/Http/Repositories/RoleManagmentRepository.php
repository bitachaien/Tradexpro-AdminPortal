<?php

namespace App\Http\Repositories;


use App\Model\Coin;
use App\Model\PermissionFromData;
use App\Model\Role;
use App\Model\Permission;
use Illuminate\Support\Facades\DB;

class RoleManagmentRepository extends CommonRepository
{

    function __construct($model)
    {
        parent::__construct($model);
    }

    public function createOrUpdate($data,$id)
    {
        return $this->model::updateOrCreate(['id' => $id], $data);
    }

    public function deleteAdmin($id)
    {
        $user = $this->model::find($id);
        return $user->update(['status' => STATUS_DELETED]);
    }

    public function deleteRoute($id)
    {
        return PermissionFromData::find($id)->delete();
    }
    public function deleteAdminRole($id)
    {
        DB::beginTransaction();
        try{
            Permission::whereRoleId($id)->delete();
            $result = Role::find($id)->delete();
            DB::commit();
            return $result;
        }catch(\Exception $e){
            DB::rollBack();
            return false;
        }
        
    }

    public function adminRoleSave($data,$id)
    {
        return Role::updateOrCreate(['id' => $id], $data);
    }

    public function createPermission($data)
    {
        return Permission::create($data);
    }

    public function addPermissionRoute($data,$id)
    {
        return PermissionFromData::updateOrCreate(['id' => $id],$data);
    }

}