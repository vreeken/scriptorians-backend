<?php

namespace App\Http\Middleware;

use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Closure;

class AuthBoth {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		return $next($request);
		$user=null;
		
		try {
			$user = JWTAuth::toUser(JWTAuth::getToken());
		} catch (JWTException $e) {
			$user = Auth::user();
		}

		if ($user === null) {
			if ($request->expectsJson()) {
				return response()->json(['error'=>'unauthenticated'], 401);
			}
			//TODO test this:
			return redirect('auth/login');
		}

		return $next($request);
	}
}