<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionFromData extends Model
{
    use HasFactory;
    protected $fillable = ['group','action','for','route','status'];
}
