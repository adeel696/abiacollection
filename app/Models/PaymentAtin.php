<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAtin extends Model
{
    protected $table = 'payment_atin';
	// //primary key
	public $primaryKey = 'id';
	// //timestamps
	public $timestamps = false;
	
}
