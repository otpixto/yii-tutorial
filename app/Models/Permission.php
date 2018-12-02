<?php

namespace App\Models;

class Permission extends \Iphome\Permission\Models\Permission
{
	
	public $guarded = [ 
		'id'
	];
	
    protected $fillable = [ 
		'code', 
		'name', 
		'guard' 
	];
	
}