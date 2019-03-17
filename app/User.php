<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
		use Notifiable;

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array
		 */
		protected $fillable = [
				'username', 'email', 'password', 'email_confirmation_code'
		];

		/**
		 * The attributes that should be hidden for arrays.
		 *
		 * @var array
		 */
		protected $hidden = [
				'password', 'remember_token',
		];

		public static $registrationFields = [
			'username', 'email', 'password'
		];

		public static $registrationValidationRules = [
			'username' => 'required',
			'email' => 'required|email|unique:users',
			'password' => 'required|min:6'
		];

		/**
		* Get the identifier that will be stored in the subject claim of the JWT.
		*
		* @return mixed
		*/
		public function getJWTIdentifier()
		{
				return $this->getKey();
		}

		/**
		* Return a key value array, containing any custom claims to be added to the JWT.
		*
		* @return array
		*/
		public function getJWTCustomClaims()
		{
				return [];
		}

		public function favoriteVerses() {
			return $this->hasMany('App\FavoriteVerse');
		}

		public function addFavoriteVerse($volume, $book, $chapter, $verse) {
			//Do we already have this verse favorited?
			if ($this->favoriteVerses()->where('volume_id', $volume)->where('book_id', $book)->where('chapter_id', $chapter)->where('verse_id', $verse)->first()) {
				return true;
			}

			$fave = new App\FavoriteVerse(['volume_id'=>$volume, 'book_id'=>$book, 'chapter_id'=>$chapter, 'verse_id'=>$verse]);
			if ($this->favoriteVerses()->save($fave)) {
				return true;
			}
			else {
				return false;
			}
		}

		public function removeFavoriteVerse($volume, $book, $chapter, $verse) {
			//Do we already have this verse favorited?
			$fave = $this->favoriteVerses()->where('volume_id', $volume)->where('book_id', $book)->where('chapter_id', $chapter)->where('verse_id', $verse)->first();
			if ($fave) {
				if ($fave->delete()) {
					return true;
				}
				else {
					return false;
				}
			}
			return true;
		}
}
