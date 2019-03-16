<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Comment;
use DB;

class CommentController extends Controller {

	public function getComments(Request $request) {
		return Comment::getCommentsForChapter($request);
	}

	public function postComment(Request $request) {
		return Comment::postComment($request);
	}

	public function vote(Request $request) {
		return Comment::vote($request);
	}

	/*

	public function postComment(Request $request) {
		//post_type=["hook","riddle", "puzzle", ...]
		//post_id=INT		
		if (!$request->has('post_type')  || !$request->has('post_id') || !is_numeric($request->input('post_id'))) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}
		$post_id = intval($request->input('post_id'));
		$post_type = $request->input('post_type');

		//comment=STRING
		if (!$request->has('comment')) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}
		$comment = $request->input('comment');
		if (strlen($comment)==0) { 
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		//parent_id=NULL OR INT
			//NULL = top level comment
			//INT=child comment
		if ($request->has('parent_id')) {
			if (!is_numeric($request->input('parent_id'))) {
				return response()->json(['error'=>'invalid_parameters'], 400);
			}
			else {
				$parent_id = $request->input('parent_id');
			}
		}
		else {
			$parent_id=null;
		}




		//Try getting user via jwt, if not try via session
		try {
			$user = JWTAuth::toUser(JWTAuth::getToken());
		} catch (JWTException $e) {
			$user = Auth::user();
		}
		if (!$user) {
			return response()->json(['error'=>'not_logged_in'], 401);
		}

		$uid = $user->id;
		
		
		$prop = $post_type."_id";
		//$vote=intval($request->input('vote'));
		
		switch ($post_type) {
			case "hook":
				$insert = new HookComment;
				$vote = new HookCommentVote;
				break;
			case "riddle":
				$insert = new RiddleComment;
				$vote = new RiddleCommentVote;
				break;
			case "puzzle":
				$insert = new PuzzleComment;
				$vote = new PuzzleCommentVote;
				break;
			case "item":
				$insert = new ItemComment;
				$vote = new ItemCommentVote;
				break;
			case "dungeon":
				$insert = new DungeonComment;
				$vote = new DungeonCommentVote;
				break;
			case "map":
				$insert = new MapComment;
				$vote = new MapCommentVote;
				break;
			default:
				return response()->json(['error'=>'invalid_parameters'], 400);
		}
		
		$insert->parent_id=$parent_id;
		$insert->$prop=$post_id;
		$insert->user_id = $uid;
		$insert->comment=$comment;

		if ($insert->save()) { 
			$vote->comment_id=$insert->id;
			$vote->user_id=$user->id;
			$vote->vote=1;
			$vote->save();
			return response()->json(['success'=>'comment_saved', 'new_id'=>$insert->id]);
		}
		else { return response()->json(['error'=>'db_error'], 500); }
	}



	public function updateComment(Request $request) {
		//post_type=["hook","riddle", "puzzle", ...]
		//post_id=INT		
		if (!$request->has('post_type')  || !$request->has('comment_id') || !is_numeric($request->input('comment_id'))) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}
		$comment_id = intval($request->input('comment_id'));
		$post_type = $request->input('post_type');

		//comment=STRING
		if (!$request->has('comment')) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}
		$comment = $request->input('comment');
		if (strlen($comment)==0) { 
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		//Try getting user via jwt, if not try via session
		try {
			$user = JWTAuth::toUser(JWTAuth::getToken());
		} catch (JWTException $e) {
			$user = Auth::user();
		}
		if (!$user) {
			return response()->json(['error'=>'not_logged_in'], 401);
		}
		$uid = $user->id;
		
		$pc = $post_type.'_comments';
		$prop = $post_type."_id";
		//$vote=intval($request->input('vote'));
		
		if (DB::table($pc)->where('id', $comment_id)->where('user_id', $uid)->whereNull($pc.'.deleted_at')->update(['comment' => $comment, 'updated_at' => date('Y-m-d G:i:s')])) {
			return response()->json(['success'=>'comment_updated']);
		}
		else { 
			return response()->json(['error'=>'access_denied'], 403);
		}
	}

	public function deleteComment(Request $request) {
		if (!$request->has('type')) {
			return response()->json(['error'=>'invalid_parameters1'], 400);
		}
		if (!$request->has('id')) {
			return response()->json(['error'=>'invalid_parameters2'], 400);
		}
		if (!is_numeric($request->input('id'))) {
			return response()->json(['error'=>'invalid_parameters3'], 400);
		}

		$user = Auth::user();
		$uid = $user->id;
		$id = intval($request->input('id'));
		$type = $request->input('type');

		switch ($type) {
			case "hook":
				if (Hook::where('id', $id)->where('user_id', $uid)->delete()) {
					return response()->json(['success'=>'success']);
				}
				break;
			case "item":
				return Item::where('id', $id)->toSql();
				if (Item::where('id', $id)->where('user_id', $uid)->delete()) {
					return response()->json(['success'=>'success']);
				}
				break;
			case "riddle":
				if (Riddle::where('id', $id)->where('user_id', $uid)->delete()) {
					return response()->json(['success'=>'success']);
				}
				break;
			case "map":
				if (Map::where('id', $id)->where('user_id', $uid)->delete()) {
					return response()->json(['success'=>'success']);
				}
				break;
			case "puzzle":
				if (Puzzle::where('id', $id)->where('user_id', $uid)->delete()) {
					return response()->json(['success'=>'success']);
				}
				break;
			default:

		}
		return response()->json(['error'=>'invalid_parameters4', 'id'=> $id], 400);
	}








	public function vote(Request $request) {
		//-1=didn't vote, 0=downvoted, 1=upvoted
		if (!$request->has('type') || !$request->has('vote') || !is_numeric($request->input('vote')) || !$request->has('id') || !is_numeric($request->input('id'))) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		$user = Auth::user();
		$uid = $user->id;
		$id = intval($request->input('id'));
		$type = $request->input('type');
		$prop = $type."_id";
		$vote=intval($request->input('vote'));
		

		switch ($type) {
			case "hook":
				$prev = HookVote::where('user_id', $uid)->where('hook_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new HookVote;
				break;
			case "hook_comment":
				$prev = HookCommentVote::where('user_id', $uid)->where('comment_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new HookCommentVote;
				$prop='comment_id';
				break;
			case "riddle":
				$prev = RiddleVote::where('user_id', $uid)->where('riddle_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new RiddleVote;
				break;
			case "riddle_comment":
				$prev = RiddleCommentVote::where('user_id', $uid)->where('comment_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new RiddleCommentVote;
				$prop='comment_id';
				break;
			case "puzzle":
				$prev = PuzzleVote::where('user_id', $uid)->where('puzzle_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new PuzzleVote;
				break;
			case "puzzle_comment":
				$prev = PuzzleCommentVote::where('user_id', $uid)->where('comment_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new PuzzleCommentVote;
				$prop='comment_id';
				break;
			case "item":
				$prev = ItemVote::where('user_id', $uid)->where('item_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new ItemVote;
				break;
			case "item_comment":
				$prev = ItemCommentVote::where('user_id', $uid)->where('comment_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new ItemCommentVote;
				$prop='comment_id';
				break;
			case "dungeon":
				$prev = DungeonVote::where('user_id', $uid)->where('dungeon_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new DungeonVote;
				break;
			case "dungeon_comment":
				$prev = DungeonCommentVote::where('user_id', $uid)->where('comment_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new DungeonCommentVote;
				$prop='comment_id';
				break;
			case "map":
				$prev = MapVote::where('user_id', $uid)->where('map_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new MapVote;
				break;
			case "map_comment":
				$prev = MapCommentVote::where('user_id', $uid)->where('comment_id', $id)->first();
				if ($prev) {
					if ($prev->vote == $vote) { return response()->json(['success'=>'vote_unchanged']); }
					$prev->vote = $vote;
					if ($prev->save()) { return response()->json(['success'=>'vote_updated']); }
					else { return response()->json(['error'=>'invalid_parameters'], 400); }
				}
				$insert = new MapCommentVote;
				$prop='comment_id';
				break;
			default:
				return response()->json(['error'=>'invalid_parameters'], 400);
		}
		
		$insert->$prop = $id;
		$insert->user_id = $uid;
		$insert->vote = $vote;

		if ($insert->save()) { return response()->json(['success'=>'vote_saved']); }
		else { return response()->json(['error'=>'db_error'], 500); }
	}
	*/
}
