<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB;

//use App\Bookmark;
//use App\Comment;
//use App\CommentVote;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;
use Log;

require_once(app_path().'/constants.php');

/*
ROUTES
Route::get('', 										'MainController@getHome');
Route::get('scriptures', 							'MainController@getDisplayVolumes');
Route::get('scriptures/{volume}', 					'MainController@getDisplayBooks');
Route::get('scriptures/{volume}/{book}', 			'MainController@getDisplayChapters');
Route::get('scriptures/{volume}/{book}/{chapter}',	'MainController@getDisplayChapter');
 */

use App\Traits\UserAndOptionsUtils;

class MainController extends Controller {

	use UserAndOptionsUtils;

	public function getSpaPage(Request $request) {
		$user=self::getUser();

		$data = $user ? ['user_id'=>$user->id, 'username'=>$user->username, 'email'=>$user->email, 'account_status'=>$user->account_status] : null;
		
		return view('spa_main', ['userdata'=>$data]);
	}

	public function getScriptureNavigation($v='', $b='', $c='', Request $request) {
		//$d = DB::table('books')->select('id', 'book_title', 'book_title_long', 'lds_org', 'num_chapters', 'num_verses')->get();

		return view('scriptures.navigation', ['volume'=>$v, 'book'=>$b, 'chapter'=>$c]);
	}

	public function getDisplayChapter($v, $b, $c, Request $request) {
		$vid=array_search($v, VOLUMES);
		if ($vid===false) { return 'invalid volume: '.$v; }

		$bid=array_search($b, BOOKS[$vid]);
		if ($bid===false) { return 'invalid book: '.$b; }
		$bid += BOOK_ID_OFFSETS[$vid];

		$user=null;
		if ($user) {
			$RAW_VOTED_QUERY = '(CASE '.
				'WHEN (SELECT count(*) FROM comment_votes WHERE comment_votes.comment_id=comments.id AND comment_votes.vote=0 AND comment_votes.user_id='.intval($user->id).')>0 THEN 0 '.
				'WHEN (SELECT count(*) FROM comment_votes WHERE comment_votes.comment_id=comments.id AND comment_votes.vote=1 AND comment_votes.user_id='.intval($user->id).')>0 THEN 1 '.
				'ELSE -1 END) AS voted';
		}
		else {
			$RAW_VOTED_QUERY = '-1 AS voted';
		}

		//Get Scripture Verses
		$verses = DB::table('verses')
			->select('id', 'verse', 'pilcrow', 'verse_scripture')
			->where('volume_id', $vid)
			->where('book_id', $bid)
			->where('chapter', $c)
			->get();

		//Map verse ids to an array for use in getting comments
		$ids = $verses->map(function ($v, $k) {
			return $v->id;
		});

		//Get Comments
		$comments =DB::table('comments')
			->select('comments.id', 'comments.verse_id', 'comments.comment', 'comments.parent_id', 'comments.lineage', 'comments.created_at', 'users.username',
				DB::raw('CAST(sum(if(comment_votes.vote=1, 1, 0)) as UNSIGNED) as upvotes'),
				DB::raw('CAST(sum(if(comment_votes.vote=0, 1, 0)) AS UNSIGNED) as downvotes'),
				DB::raw($RAW_VOTED_QUERY)
			)
			->leftJoin('users', 'comments.user_id', '=', 'users.id')
			->leftJoin('comment_votes', 'comments.id', '=', 'comment_votes.comment_id')
			->whereIn('comments.verse_id', $ids->toArray())
			->whereNull('comments.deleted_at')
			->groupBy('comments.verse_id', 'comments.id')
			->get();

		return view('scriptures.display_chapter', ['verses'=>$verses, 'comments'=>$comments, 'volume_id'=>$vid, 'book_id'=>$bid, 'chapter_id'=>$c]);
	}

	public function getComments(Request $request) {
		$vid=array_search($v, VOLUMES);
		if ($vid===false) { return 'invalid volume: '.$v; }

		$bid=array_search($b, BOOKS[$vid]);
		if ($bid===false) { return 'invalid book: '.$b; }
		$bid += BOOK_ID_OFFSETS[$vid];

		$user=null;
		if ($user) {
			$RAW_VOTED_QUERY = '(CASE '.
				'WHEN (SELECT count(*) FROM comment_votes WHERE comment_votes.comment_id=comments.id AND comment_votes.vote=0 AND comment_votes.user_id='.intval($user->id).')>0 THEN 0 '.
				'WHEN (SELECT count(*) FROM comment_votes WHERE comment_votes.comment_id=comments.id AND comment_votes.vote=1 AND comment_votes.user_id='.intval($user->id).')>0 THEN 1 '.
				'ELSE -1 END) AS voted';
		}
		else {
			$RAW_VOTED_QUERY = '-1 AS voted';
		}

		//TODO optimize this WHERE IN
		//	Either create a list of verse offsets and lengths
		//	OR make this a subquery within the $comments query (or use "having" somehow)

		//Get Scripture Verses
		$verses = DB::table('verses')
			->select('id', 'verse', 'pilcrow', 'verse_scripture')
			->where('volume_id', $vid)
			->where('book_id', $bid)
			->where('chapter', $c)
			->get();

		//Map verse ids to an array for use in getting comments
		$ids = $verses->map(function ($v, $k) {
			return $v->id;
		});

		//Get Comments
		$comments =DB::table('comments')
			->select('comments.id', 'comments.verse_id', 'comments.comment', 'comments.parent_id', 'comments.lineage', 'comments.created_at', 'users.username',
				DB::raw('CAST(sum(if(comment_votes.vote=1, 1, 0)) as UNSIGNED) as upvotes'),
				DB::raw('CAST(sum(if(comment_votes.vote=0, 1, 0)) AS UNSIGNED) as downvotes'),
				DB::raw($RAW_VOTED_QUERY)
			)
			->leftJoin('users', 'comments.user_id', '=', 'users.id')
			->leftJoin('comment_votes', 'comments.id', '=', 'comment_votes.comment_id')
			->whereIn('comments.verse_id', $ids->toArray())
			->whereNull('comments.deleted_at')
			->groupBy('comments.verse_id', 'comments.id')
			->get();

		return response()->json(['comments'=>$comments]);
	}

	public function getDisplayChapter1(Request $request) {
		
		/*
		$vs = ["The Old Testament", "The New Testament", "The Book of Mormon", "The Doctrine and Covenants", "The Pearl of Great Price"];
		$bs[0]=["Genesis", "Exodus", "Leviticus", "Numbers", "Deutoronomy", "Joshua", "Judges", "Ruth", "1st Samuel", "2nd Samuel", "1st Kings", "2nd Kings", "1st Chronicles", "2nd Chronicles", "Ezra", "Nehemiah", "Esther", "Job", "Psalms", "Proverbs","Ecclesiastes", "Songs of Solomon", "Isaiah", "Jeremiah", "Lamentation", "Ezekiel", "Daniel", "Hosea", "Joel", "Amos", "Obadiah", "Jonah", "Micah", "Nahum", "Habakkuk", "Zephaniah", "Haggai", "Zechariah", "Malachi"];
		$bs[1]=["Mattew", "Mark", "Luke", "John", "Acts", "Romans", "1 Corinthians", "2nd Corinthians", "Galation", "Ephesians", "Philipians", "Colossians", "1st Thessalonians", "2nd Thessalonians", "1st Timothy", "2nd Timothy", "Titus", "Philemon", "Hebrews", "James", "1st Peter", "2ns Peter", "1st John", "2nd John", "3rd John", "Jude", "Revelations"];
		$bs[2]=["1st Nephi", "2nd Nephi", "Jacob", "Enos", "Jarom", "Omni", "Words of Mormon","Mosiah", "Alma", "Helaman", "3rd Nephi", "4th Nephi", "Mormon", "Ether", "Moroni"];
		$bs[3]=["Section"];
		$bs[4]=["Moses", "Abraham","Joseph Smith-Matthew", "Joseph Smith-History", "Articles of Faith"];

		$o=[];
		for ($i=0; $i<count($vs); $i++) {
			$a['volume']=$vs[$i];
			$a['books']=[];

			for ($j=0; $j<count($bs[$i]); $j++) {
				$b['book']=$bs[$i][$j];
				$b['chapters']=[];
				
				array_push($a['books'], $b);				
			}

			array_push($o, $a);



		}

		$verses = DB::table('verses')->select('id', 'volume_id', 'book_id', 'chapter', 'pilcrow', 'verse_scripture')->get();

		foreach($verses as $v) {
			if (count($o[intval($v->volume_id)-1]['books'][intval($v->book_id)-BOOK_ID_OFFSETS[$v->volume_id]]['chapters'])<$v->chapter) {
				$d['verses']=[];
				array_push($o[intval($v->volume_id)-1]['books'][intval($v->book_id)-BOOK_ID_OFFSETS[$v->volume_id]]['chapters'], $d);
			}

			$vo['id']=$v->id;
			$vo['v']=$v->pilcrow==1 ? '&para'.$v->verse_scripture : $v->verse_scripture;
			
			array_push($o[intval($v->volume_id)-1]['books'][intval($v->book_id)-BOOK_ID_OFFSETS[$v->volume_id]]['chapters'][intval($v->chapter)-1]['verses'], $vo);
			
		}
		*/
		
		$vs = ["The Old Testament", "The New Testament", "The Book of Mormon", "The Doctrine and Covenants", "The Pearl of Great Price"];
		$bs[0]=["Genesis", "Exodus", "Leviticus", "Numbers", "Deutoronomy", "Joshua", "Judges", "Ruth", "1st Samuel", "2nd Samuel", "1st Kings", "2nd Kings", "1st Chronicles", "2nd Chronicles", "Ezra", "Nehemiah", "Esther", "Job", "Psalms", "Proverbs","Ecclesiastes", "Songs of Solomon", "Isaiah", "Jeremiah", "Lamentation", "Ezekiel", "Daniel", "Hosea", "Joel", "Amos", "Obadiah", "Jonah", "Micah", "Nahum", "Habakkuk", "Zephaniah", "Haggai", "Zechariah", "Malachi"];
		$bs[1]=["Mattew", "Mark", "Luke", "John", "Acts", "Romans", "1 Corinthians", "2nd Corinthians", "Galation", "Ephesians", "Philipians", "Colossians", "1st Thessalonians", "2nd Thessalonians", "1st Timothy", "2nd Timothy", "Titus", "Philemon", "Hebrews", "James", "1st Peter", "2ns Peter", "1st John", "2nd John", "3rd John", "Jude", "Revelations"];
		$bs[2]=["1st Nephi", "2nd Nephi", "Jacob", "Enos", "Jarom", "Omni", "Words of Mormon","Mosiah", "Alma", "Helaman", "3rd Nephi", "4th Nephi", "Mormon", "Ether", "Moroni"];
		$bs[3]=["Section"];
		$bs[4]=["Moses", "Abraham","Joseph Smith-Matthew", "Joseph Smith-History", "Articles of Faith"];

		$o=[];
		for ($i=0; $i<count($vs); $i++) {
			//$a['volume']=$vs[$i];
			//$a['books']=[];
			array_push($o, []);

			for ($j=0; $j<count($bs[$i]); $j++) {
				//$b['book']=$bs[$i][$j];
				//$b['chapters']=[];
				
				//array_push($a['books'], $b);				
				array_push($o[$i], []);
			}

			//array_push($o, $a);
		}

		


		$verses = DB::table('verses')->select('id', 'volume_id', 'book_id', 'chapter', 'pilcrow', 'verse_scripture')->get();

		foreach($verses as $v) {
			if (count($o[intval($v->volume_id)-1][intval($v->book_id)-BOOK_ID_OFFSETS[$v->volume_id]])<$v->chapter) {
				//$d['verses']=[];
				array_push($o[intval($v->volume_id)-1][intval($v->book_id)-BOOK_ID_OFFSETS[$v->volume_id]], []);
			}

			$vo['id']=$v->id;
			$vo['v']=$v->pilcrow==1 ? '&para; '.$v->verse_scripture : $v->verse_scripture;
			
			array_push($o[intval($v->volume_id)-1][intval($v->book_id)-BOOK_ID_OFFSETS[$v->volume_id]][intval($v->chapter)-1], $vo);
			
		}

		return response()->json($o);
	}

}
