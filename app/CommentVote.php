<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommentVote extends Model {

    protected $fillable = ['comment_id', 'vote', 'user_id'];

}
