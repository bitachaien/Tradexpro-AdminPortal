<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingFeature extends Model
{
    use HasFactory;
    protected $fillable = [
        'feature_title',
        'feature_url',
        'description',
        'feature_icon',
        'status'
    ];
}
