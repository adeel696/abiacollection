<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UssdSession extends Model
{
    protected $table = 'ussd_session';
	// //primary key
	public $primaryKey = 'msisdn';
	// //timestamps
	public $timestamps = false;
	
}
