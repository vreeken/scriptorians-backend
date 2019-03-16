<?php

namespace App\Traits;

use App\User;
use Illuminate\Http\Request;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


trait UserAndOptionsUtils {

    public static function getUser() {
        $user = null;
        try {
            $user = JWTAuth::toUser(JWTAuth::getToken());
        } catch (JWTException $e) {
            $user = Auth::user();
        }
        if ($user === null) {
            $user = User::find(3);
        }
        return $user;
    }

    public static function getUserOptions($user = '') {
        //If we didn't get the $user passed in to us, attempt to get it
        if ($user === '') {
            $user = self::getUser();
        }

        //If we have an authenticated user, store in the db
        if ($user) {
            $options = $user->options;
            if (!$options || $options === null || $options === 'null') {
                $options = User::DEFAULT_OPTIONS;
            }
        } //otherwise use the session to store user options
        else {
            //Check if there are user options in the session, if not, initialize them with default values
            if (!session()->has('user_options')) {
                session(['user_options' => User::DEFAULT_OPTIONS]);
            }
            $options = session('user_options', User::DEFAULT_OPTIONS);
        }

        return json_decode($options);
    }

    public static function setUserOption($key, $val, $user = ''): bool {
        if ($user === '') {
            $user = self::getUser();
        }

        $options = self::getUserOptions();

        //Make sure option key is valid
        if (!in_array($key, USER_OPTION_KEYS, true)) {
            return false;
        }
        $options->$key = $val;

        //if user is logged in then save to db
        if ($user) {
            $user->options = json_encode($options);
            if ($user->save()) {
                return true;
            }
        } else {
            //otherwise store option data in session
            session(['user_options' => json_encode($options)]);
        }
        return true;
    }

    public static function saveOptions(Request $request) {
        if (!$request->has('option') || !$request->has('value')) {
            return response()->json(['error' => 'invalid_parameters'], 400);
        }

        if (self::setUserOption($request->input('option'), $request->input('value'), self::getUser())) {
            return response()->json(['success' => 'option_saved']);
        }

        return response()->json(['error' => 'invalid_option'], 400);
    }
}