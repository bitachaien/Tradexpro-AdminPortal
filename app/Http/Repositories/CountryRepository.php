<?php

namespace App\Http\Repositories;


use App\Model\CountryList;

class CountryRepository extends CommonRepository
{
    function __construct($model) {
        parent::__construct($model);
    }

    public function getCountries()
    {
        return CountryList::get();
    }

    public function getActiveCountries()
    {
        return CountryList::where('status',STATUS_ACTIVE)->get();
    }

    public function statusChange($data)
    {
        $country = CountryList::where('id',$data['country_id'])->first();

        if ($country) {
            if ($country->status == 1) {
               $country->update(['status' => 0]);
            } else {
                $country->update(['status' => 1]);
            }
            return true;
        } else {
            return false;
        }
    }
}