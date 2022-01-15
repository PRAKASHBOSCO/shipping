<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CodConfig extends Model {

    protected $guarded = [];
    protected $table = 'cod_config';

    static public function getCost($cod) {
        $value = 0;
        $amount = Self::where('from_amount', '<=', $cod)->where('to_amount', '>=', $cod)->first();
        if ($amount != null) {
            $value = $amount->price;
        }
        return $value;
    }

}
