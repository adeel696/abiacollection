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
	
}
