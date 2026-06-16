<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycList extends Model
{
    protected $fillable = ['name', 'type', 'status'];
}
