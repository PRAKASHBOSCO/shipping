<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PickupWeightConfig extends Model {

    protected $guarded = [];
    protected $table = 'pickup_weight_config';

    static public function getCost($weight) {
        $value = 0;
        $distance = Self::where('from_weight', '<=', $weight)->where('to_weight', '>=', $weight)->first();
        if ($distance != null) {
            $value = $distance->percentage;
        }
        return $value;
    }

}
