<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryWeightConfig extends Model {

    protected $guarded = [];
    protected $table = 'delivery_weight_config';

    static public function getCost($weight) {
        $value = null;
        $distance = Self::where('from_weight', '<=', $weight)->where('to_weight', '>=', $weight)->first();
        if ($distance != null) {
            $value = $distance;
        }
        return $value;
    }

}
