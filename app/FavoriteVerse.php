<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model {
    public function user() {
    	$this->belongsTo('App\User');
    }
}
