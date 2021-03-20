<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'drivers';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamps = false;
}

