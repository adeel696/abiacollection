<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $table = 'user_logs';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamps = false;
}

