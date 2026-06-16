<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardBanner extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid','title','sub_title','category_id','updated_by','status','banner'
    ];

    public function category()
    {
        return $this->hasOne(GiftCardCategory::class, 'uid', 'category_id');
    }
}
