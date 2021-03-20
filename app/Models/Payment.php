<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamps = false;
}
