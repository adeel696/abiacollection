<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendCsLog extends Model
{
    protected $table = 'send_cs_log';
	// //primary key
	public $primaryKey = 'id';
	// //timestamps
	public $timestamps = false;
	
	public function ShopFee() 
	{
		return $this->belongsTo('App\Models\ShopFee' , 'shop_fees_id');
	}
	
	public function PaymentAtin() 
	{
		return $this->belongsTo('App\Models\PaymentAtin' , 'payment_atin_id');
	}
	
}
