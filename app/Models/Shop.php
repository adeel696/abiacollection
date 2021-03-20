<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $table = 'shops';
	// //primary key
	public $primarykey = 'id';
	// //timestamps
	public $timestamps = false;
}
