<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DimensionConfig extends Model {

    protected $guarded = [];
    protected $table = 'dimension_config';

    static public function getCost($weight) {
        $value = null;
        $amount = Self::where('from_weight', '<=', $weight)->where('to_weight', '>=', $weight)->first();
        if ($amount != null) {
            $value = $amount;
        }
        return $value;
    }

}
