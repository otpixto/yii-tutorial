<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';
    public static $_table = 'settings';
    public $timestamps = false;
    public static $name = 'Настройки';
}
