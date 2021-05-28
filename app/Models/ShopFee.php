<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopFee extends Model
{
    protected $table = 'shop_fees';
	// //primary key
	public $primaryKey = 'id';
	// //timestamps
	public $timestamps = false;
	
}
