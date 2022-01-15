<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $guarded = [];

    public function shipmentReasons(){
		return $this->hasMany('App\ShipmentReason', 'reason_id' , 'id');
	}
}
