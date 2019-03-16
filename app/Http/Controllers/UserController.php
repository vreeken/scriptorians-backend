<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

use App\Traits\UserAndOptionsUtils;

class UserController extends Controller {

	use UserAndOptionsUtils;

	public function getHome(Request $request) {
		$user=self::getUser();

		$data = $user ? ['user_id'=>$user->id, 'username'=>$user->username, 'email'=>$user->email, 'account_status'=>$user->account_status] : null;
		
		return view('spa_main', ['userdata'=>$data]);
	}

	public function getAccountData(Request $request) {
		return response()->json(['success'=>'tokens', 'header_token'=>$request->header('X-CSRF-TOKEN'), 'session_token'=>$request->session()->token()]);
		//Verify a user's token and return basic user data along with a refreshed token
		$user=self::getUser();

		if ($user) {
			return response()->json(['success'=>'valid_login', 'userdata'=>['header_token'=>$request->header('X-CSRF-TOKEN'), 'session_token'=>$request->session()->token(), 'user_id'=>$user->id, 'username'=>$user->username, 'email'=>$user->email, 'account_status'=>$user->account_status]]);
		}
		else {
			return response()->json(['error'=>'invalid_token'], 400);
		}

		$token = JWTAuth::getToken();
		$new_token = JWTAuth::refresh($token);

		if ($user) {
			return response()->json(['success'=>'valid_token', 'userdata'=>['token'=>$new_token, 'user_id'=>$user->id, 'username'=>$user->username, 'email'=>$user->email, 'account_status'=>$user->account_status]]);
		}
		else {
			return response()->json(['error'=>'invalid_token'], 400);
		}
	}

	public function bookmark(Request $request) {
		//int bookmark: 0=delete, 1=save
		if (!$request->has('type') || !$request->has('bookmark') || !is_numeric($request->input('bookmark')) || !$request->has('id') || !is_numeric($request->input('id'))) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		$user = Auth::user();
		$uid = $user->id;
		$id = intval($request->input('id'));
		$type = $request->input('type');
		$prop = $type."_id";
		$table = $type."_bookmarks";
		$bookmark=intval($request->input('bookmark'));
		
		try {
			if ($bookmark==0) {
				DB::table($table)->where('user_id', $uid)->where($prop, $id)->delete();
				return response()->json(['success'=>'vote_saved']);
			}
			else {
				$row = DB::table($table)->where('user_id', $uid)->where($prop, $id)->first();
				if (!$row) {
					$dt = new \DateTime();
					DB::table($table)->insert(['user_id'=>$uid, $prop=>$id, 'created_at'=>$dt, 'updated_at'=>$dt]);
					return response()->json(['success'=>'bookmark_saved']);
				}
				return response()->json(['success'=>'vote_saved']);
			}
		}
		catch (Exception $e) {
			return response()->json(['error'=>'db_error'], 500);
		}
		return response()->json(['error'=>'db_error'], 500);
	}




	public function favorite(Request $request) {
		//int bookmark: 0=delete, 1=save
		if (!$request->has('type') || !$request->has('bookmark') || !is_numeric($request->input('bookmark')) || !$request->has('id') || !is_numeric($request->input('id'))) {
			return response()->json(['error'=>'invalid_parameters'], 400);
		}

		$user = Auth::user();
		$uid = $user->id;
		$id = intval($request->input('id'));
		$type = $request->input('type');
		$prop = $type."_id";
		$table = $type."_bookmarks";
		$bookmark=intval($request->input('bookmark'));
		
		try {
			if ($bookmark==0) {
				DB::table($table)->where('user_id', $uid)->where($prop, $id)->delete();
				return response()->json(['success'=>'vote_saved']);
			}
			else {
				$row = DB::table($table)->where('user_id', $uid)->where($prop, $id)->first();
				if (!$row) {
					$dt = new \DateTime();
					DB::table($table)->insert(['user_id'=>$uid, $prop=>$id, 'created_at'=>$dt, 'updated_at'=>$dt]);
					return response()->json(['success'=>'bookmark_saved']);
				}
				return response()->json(['success'=>'vote_saved']);
			}
		}
		catch (Exception $e) {
			return response()->json(['error'=>'db_error'], 500);
		}
		return response()->json(['error'=>'db_error'], 500);
	}






	public function accountInfo(Request $request) {
		$currentUser = JWTAuth::parseToken()->authenticate();

		return response()->json(['status'=>'success', 'username'=>$currentUser->username]);
	}

	public function updateAccount(Request $request) {
		$currentUser = JWTAuth::parseToken()->authenticate();

		/*
		if ($request->input('password')) {
			$currentUser->password = $request->input('password');
			$currentUser->save();
		}
		*/

	}

	public function jwtCheck(Request $request) {
		try {
			if (! $user = JWTAuth::parseToken()->authenticate()) {
				return response()->json(['error'=>'token_invalid'], 400);
			}
		}
		catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
			return response()->json(['error'=>'token_expired'], $e->getStatusCode());
		}
		catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
			return response()->json(['error'=>'token_invalid'], $e->getStatusCode());
		}
		catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
			return response()->json(['error'=>'token_absent'], $e->getStatusCode());
		}

		//Get Token
		$token = JWTAuth::getToken();

		//Make sure $user isn't null - probably unnecessary
		if (is_null($user)) {
			return response()->json(['fail'=>'invalid_token']);
		}

		//Token is valid, might as well give them a new one
		return response()->json(['success'=>'success', 'jwt'=>JWTAuth::refresh($token), 'id'=>$user->id, 'username'=>$user->username, 'email'=>$user->email, 'account_status'=>$user->account_status]);
	}


}
