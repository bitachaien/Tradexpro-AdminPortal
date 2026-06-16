<?php

namespace App\Http\Repositories;


class CommonRepository
{
    public $model = null;

    function __construct($model)
    {
        $this->model = $model;
    }

    public function getById($id)
    {
        return $this->model::where('id', '=', $id)->first();
    }

    public function getAll()
    {
        return $this->model::get();
    }

    public function deleteById($id)
    {
        return $this->model::where('id', '=', $id)->delete();
    }

    public function create($data)
    {
        return $this->model::create($data);
    }

    public function insert($data)
    {
        return $this->model::insert($data);
    }

    public function getDocs($params=[],$select=null,$orderBy=[],$with=[]){
        if($select == null){
            $select = ['*'];
        }
        $query = $this->model::select($select);
        foreach($with as $wt) {
            $query = $query->with($wt);
        }
        foreach($params as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }
        foreach($orderBy as $key => $value) {
            $query->orderBy($key,$value);
        }

        return $query->get();
    }

    public function updateWhere($where=[], $update=[])
    {
        $query = $this->model::query();
        foreach($where as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }
        return $query->update($update);
    }

    public function deleteWhere($where=[], $isForce = false)
    {
        $query = $this->model::query();
        foreach($where as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }

        if ($isForce) {
            return $query->forceDelete();
        }
        return $query->delete();
    }

    public function exists($where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->exists();
    }
    public function countWhere($where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->count();
    }

    public function randomWhere($quantity, $where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->inRandomOrder($quantity)->get();
    }

    public function whereFirst($where = [], $relation = [])
    {
        return app($this->model)->where($where)->with($relation)->first();
    }

    public function selectWhere($select, $where, $relation = [], $paginate = 0)
    {
        if ($paginate === 0) {
            return $this->model->select($select)->where($where)->with($relation)->get();
        }

        return $this->model->select($select)->where($where)->with($relation)->paginate($paginate);
    }

    public function limitWhere($quantity, $where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->limit($quantity)->get();
    }
}
