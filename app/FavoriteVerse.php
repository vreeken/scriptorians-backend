<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FavoriteVerse extends Model {
    public function user() {
    	$this->belongsTo('App\User');
    }
}
