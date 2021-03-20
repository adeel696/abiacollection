<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $table = 'user_session';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamps = false;
	
}
