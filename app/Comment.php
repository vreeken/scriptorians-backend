<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


use App\CommentVote;

use DB;
use Log;

class Comment extends Model {

	use UserAndOptionsUtils;

	/**
	 * Get the votes for the Comment.
	 */
	/*
	public function votes() {
		return $this->hasMany('App\CommentVote');
	}

	public function user() {
		return $this->belongsTo('App\User');
	}
	*/

	public static function getCommentsForChapter($volume, $book, $chapter, $user=null) {
		if ($user) {
			$RAW_VOTED_QUERY = '(SELECT count(*) FROM comment_votes WHERE comment_votes.comment_id=comments.id AND comment_votes.user_id=' . (int) $user->id . ' AND comment_votes.vote=1) AS voted';
		}
		else {
			$RAW_VOTED_QUERY = '-1 AS voted';
		}

		$comments = DB::table('comments')
			->join('users', 'comments.user_id', '=', 'users.id')
			->select(
				'comments.id', 'comments.verse_id', 'comments.comment', 'comments.parent_id', 'comments.lineage', 'comments.created_at',
				'users.username',
				DB::raw('(SELECT count(*) FROM comment_votes WHERE comments.id=comment_votes.comment_id AND comment_votes.vote=1) as upvotes'),
				DB::raw($RAW_VOTED_QUERY))
			->where('comments.volume_id', $volume)
			->where('comments.book_id', $book)
			->where('comments.chapter_id', $chapter)
			->whereNull('comments.deleted_at')
			->get();

		return $comments;
	}

	public static function postComment(Request $request) {
		if (!$request->has('volume') || !$request->has('book') || !$request->has('chapter') || !$request->has('verse') || !$request->has('parent') || !$request->has('comment')) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		$volume_id = (int) $request->input('volume');
		$book_id = (int) $request->input('book');
		$chapter_id = (int) $request->input('chapter');
		$verse_id = (int) $request->input('verse');
		$parent_id = $request->input('parent') === null ? null : (int) $request->input('parent');
		$comment = $request->input('comment');
		
		$user=self::getUser();
		if ($user === null) { return response()->json(['error'=>'unauthorized'], 401); }

		if ($parent_id) {
			$parent = Comment::find($parent_id);
			//Make sure parent refers to same VBCV as new comment
			if ($parent->volume_id !== $volume_id || $parent->book_id !== $book_id || $parent->chapter_id !== $chapter_id || $parent->verse_id !== $verse_id) {
				return response()->json(['error'=>'invalid_parent_comment'], 400);
			}
			else {
				$lineage = $parent->lineage . '/' . $parent->id;
			}
		}
		else {
			$lineage = '';
		}
		
		$c = new Comment;
		$c->user_id = $user->id;
        $c->volume_id = $volume_id;
        $c->book_id = $book_id;
        $c->chapter_id = $chapter_id;
        $c->verse_id = $verse_id;
        $c->comment = $comment;
        $c->parent_id = $parent_id;
        $c->lineage = $lineage;
		$c->save();

		return response()->json(['success'=>'comment_posted', 'comment'=>$c]);
	}

	public static function vote(Request $request) {
		if (!$request->has('comment_id')) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		$comment_id = (int) $request->input('comment_id');
		
		$user=self::getUser();
		if ($user === null) { return response()->json(['error'=>'unauthorized'], 401); }

		$cv = CommentVote::firstOrNew(['comment_id' => $comment_id, 'user_id' => $user->id]);

		//If this was a past vote, reverse it (unvoted becomes voted, voted becomes unvoted)
		if (!$cv->wasRecentlyCreated) {
			$cv->vote = $cv->vote === 1 ? 0 : 1;
		}

		if ($cv->save()) {
			return response()->json(['success'=>'voted', 'vote'=>$cv->vote]);
		}
		return response()->json(['error'=>'db_error'], 500);
	}
}
