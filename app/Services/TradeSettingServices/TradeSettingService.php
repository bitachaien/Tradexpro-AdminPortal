<?php

namespace App\Services\TradeSettingServices;

use App\Model\AdminSetting;

class TradeSettingService
{
    public function getFeeSetting(): array
    {
        $limits = AdminSetting::where('slug', 'like', 'trade_limit_%')->get();
        $makers = [];
        $takers = [];
        $trades = [];
        $numbers = [];
        foreach ($limits as $data) {
            $numbers[] = explode('_', $data->slug)[2];
            $makers[] = 'maker_' . explode('_', $data->slug)[2];
            $takers[] = 'taker_' . explode('_', $data->slug)[2];
            $trades[] = 'trade_limit_' . explode('_', $data->slug)[2];
        }
        $allSlugs = array_merge($makers, $takers, $trades);
        $settings = allsetting($allSlugs);
        $formatData = [];

        foreach ($numbers as $number) {
            $formatData[$number] = [
                'trade_limit_' . $number => $settings['trade_limit_' . $number],
                'maker_' . $number => $settings['maker_' . $number],
                'taker_' . $number => $settings['taker_' . $number],
            ];
        }
        
        return $formatData;
    }
}
