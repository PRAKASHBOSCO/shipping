<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model {

    protected $guarded = [];
    protected $table = 'client_payment';

    public function client() {
        return $this->hasOne('App\Client', 'id', 'client_id');
    }

}
