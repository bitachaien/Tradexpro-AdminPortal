<?php

namespace App\Model;

use App\Model\GiftCardBanner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCardCategory extends Model
{
    use HasFactory;
    protected $fillable = [ 'uid', 'name', 'status'];

    public function banner()
    {
        return $this->hasMany(GiftCardBanner::class,'category_id','uid');
    } 
}
