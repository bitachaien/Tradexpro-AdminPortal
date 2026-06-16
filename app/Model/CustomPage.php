<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CustomPage extends Model
{
    protected $fillable = [
        'title',
        'key',
        'description',
        'status',
        'type',
        'page_type',
        'page_link',
        'data_order'
    ];
}
