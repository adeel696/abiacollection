<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $table = 'agents';
	//primary key
	public $primarykey = 'id';
	//timestamps
	public $timestamps = false;
}
