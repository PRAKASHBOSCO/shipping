<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShipmentReason extends Model
{
    protected $table = "shipment_reasons";

    public function shipment()
    {
        return $this->belongsTo('App\Shipment', 'shipment_id');
    }

    public function reason()
    {
        return $this->belongsTo('App\Reason', 'reason_id');
    }
}
