<?php

namespace App\Http\Repositories;


use Illuminate\Support\Facades\DB;

/**
 * Created by Masum.
 * User: itech
 * Date: 11/14/18
 * Time: 5:11 PM
 */

class TradingViewChartRepository
{
    public function getChartData($model, $baseCoinId, $tradeCoinId, $start, $end,$trade=null){
        if (!empty($trade) && ($trade == 1)) {
            return $model::select('interval as time','low','high','open','close','volume')
                ->where(['base_coin_id' => $baseCoinId,'trade_coin_id' => $tradeCoinId])
                ->orderBy('id','DESC')->first();
        } else {
            return $model::select('interval as time','low','high','open','close','volume')
                ->whereBetween('interval', [$start, $end])
                ->where(['base_coin_id' => $baseCoinId,'trade_coin_id' => $tradeCoinId])
                ->orderBy('interval','ASC')->get();
        }
    }

    public function getCandle($model, $params=[],$select=null){
        if($select == null){
            $select = ['*'];
        }
        $query = $model::select($select);
        foreach($params as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }
        return $query->get();
    }

    public function createNewCandle($model, $data){
        return $model::create($data);
    }

    public function updateCandle($model, $where=[], $update=[])
    {
        $query = $model::query();
        foreach($where as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }
        return $query->update($update);
    }
}
