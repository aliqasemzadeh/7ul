<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemActionLog extends Model
{
    protected $fillable = [
        'command',
        'output',
        'status',
    ];
}
