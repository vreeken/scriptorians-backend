<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Comment;
use DB;
use App\Traits\UserAndOptionsUtils;
use App\FavoriteVerse;

class ScriptureController extends Controller {

	use UserAndOptionsUtils;

	/*
	public function getComments(Request $request) {
		return Comment::getCommentsForChapter($request);
	}
	*/

	public function postComment(Request $request) {
		return Comment::postComment($request);
	}

	public function vote(Request $request) {
		return Comment::vote($request);
	}

	public function getChapterData(Request $request) {
		//TODO Wrap all in try catch?
		if (!$request->has('volume') || !$request->has('book') || !$request->has('chapter')) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		$volume = (int) $request->input('volume');
		$book = (int) $request->input('book');
		$chapter = (int) $request->input('chapter');

		$user=self::getUser();

		$comments = Comment::getCommentsForChapter($volume, $book, $chapter, $user);

		// If the user is logged in get their favorited verses
		$favoriteVerses = $user ? self::getFavoriteVerses($volume, $book, $chapter, $user) : [];

		return response()->json(['success'=>true, 'comments'=>$comments, 'favoriteVerses'=>$favoriteVerses]);
	}

	public static function getFavoriteVerses($volume, $book, $chapter, $user) {
		return $user->favoriteVerses()->where('volume_id', $volume)->where('book_id', $book)->where('chapter_id', $chapter)->get();
	}

	public static function addFavoriteVerse(Request $request) {
		if (!$request->has('volume') || !$request->has('book') || !$request->has('chapter') || !$request->has('verse')) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		$user=self::getUser();
		if (!$user) {
			return response()->json(['error'=>'unauthorized'], 401);
		}

		$volume = (int) $request->input('volume');
		$book = (int) $request->input('book');
		$chapter = (int) $request->input('chapter');
		$verse = (int) $request->input('verse');

		if ($user->addFavoriteVerse($volume, $book, $chapter, $verse)) {
			return response()->json(['success'=>true]);
		}
		return response()->json(['error'=>'db_error'], 500);
	}

	public static function removeFavoriteVerse(Request $request) {
		if (!$request->has('volume') || !$request->has('book') || !$request->has('chapter') || !$request->has('verse')) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		$user=self::getUser();
		if (!$user) {
			return response()->json(['error'=>'unauthorized'], 401);
		}

		$volume = (int) $request->input('volume');
		$book = (int) $request->input('book');
		$chapter = (int) $request->input('chapter');
		$verse = (int) $request->input('verse');

		if ($user->removeFavoriteVerse($volume, $book, $chapter, $verse)) {
			return response()->json(['success'=>true]);
		}
		return response()->json(['error'=>'db_error'], 500);
	}

}
