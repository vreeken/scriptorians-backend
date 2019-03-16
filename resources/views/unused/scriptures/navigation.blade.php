@extends('templates.main_layout')

@section('title')
{{ env('APP_TITLE') }} Scriptures
@endsection

@section('extra_header')
	
@endsection

@section('extra_css')
	
@endsection

@section('content')
	<div class="section-content">
		<div class="breadcrumbs">
			<span v-if="currVolume" @click="navigateToByIds(-1, -1, -1)">@{{ currVolume.title }} /</span> <span v-if="currBook" @click="navigateToByIds(currVolumeId, -1, -1)">@{{ currBook.title }} /</span> <span v-if="currChapter>=0" @click="navigateToByIds(currVolumeId, currBookId, -1)">@{{ currChapter }}</span>
		</div>
		<div class="scripture-navigation" v-if="!currVolume || !currBook || !currChapter">
			<div v-if="!currVolume" id="volumes">
				<div class="volume" v-for="(v, index) in scriptures" :key="v.id" @click="selectVolume(index)">
					<div v-html="v.title"></div>
				</div>
			</div>
			<div v-if="currVolume && !currBook" id="books">
				<div class="book" v-for="(b, bIndex) in currVolume.books" :key="b.id" @click="selectBook(bIndex)">
					<div v-html="b.title"></div>
				</div>
			</div>
			<div v-if="currVolume && currBook" id="chapters">
				<div class="book" v-for="n in currBook.num_chapters">
					<div v-html="n"></div>
				</div>
			</div>
		</div>
		<div v-else class="display-chapter">
			<div class="chapter-header header ct">
				<div v-html="volume"></div>
				<div>@{{book}} @{{chapter}}</div>
			</div>

			<div class="verses" v-for="(v, index) in verses">
				<div class="verse">
					<div class="show-comments-btn" @click="toggleComments(v)"><span>@{{ v.commentCount | formatCommentCount }}</span></div>
					<div class="verse-text-container">
						<span class="verse-number" v-html="v.verse"></span> <span v-if="v.pilcrow==1">&para;</span> <span v-html="v.verse_scripture"></span>
					</div>
				</div>
				<div class="post-comments" v-show="v.showingComments">
					<div class="new-comment">
						<div class="error-field" v-if="newComment.bodyError.length>0" v-html="newComment.bodyError"></div>
						<div class="field">
							<div class="control">
								<textarea v-model="newComment.body" id="comment-body" class="textarea" v-bind:class="{'error':newComment.bodyError.length>0}"  type="text" placeholder="Comment" rows="2"></textarea>
							</div>
						</div>
						<div class="comment-categories">
							<span>Question</span><span>Cross Reference</span><span>Interpretation/Clarification</span><span>History</span><span>Personal Experience/Thoughts</span><span>Other</span>
						</div>
						<div class="button is-block is-info is-large is-fullwidth" @click="submitComment(null)">Submit</div>
						<div class="error-field" v-if="newComment.ajaxError.length>0" v-html="newComment.ajaxError"></div>
					</div>
					<comment-list :comments="v.comments"></comment-list>
				</div>
			</div>
		</div>
	</div>


@endsection

@section('footer_scripts')
	<script src="https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.js"></script>
	<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>

	<script>
		var config = {};
	@if (Auth::check())
		var user = JSON.parse(localStorage.getItem('user'));		
		if (user) {
			config.headers = {'Authorization': "Bearer " + user.jwt}
		}
		var LOGGED_IN=true;
	@else
		var LOGGED_IN=false;
	@endif


	/***************************************
	TODO
		Implement caching?
			is there a reason? Not a lot of data via ajax, not true sync, easier just to reload.
				Maybe make it an option?

		Load more posts before user requests them, show when user requests, then begin the next load, so that it is instant.

		Edit comments
		
		Consider reqorkig the loading anim
			Maybe it's visible until Vue is done then clip-path out (or another anim out)
			Maybe just an anim for ajax loading
	****************************************/

		var V_PARAM='{{ $volume }}';
		var B_PARAM='{{ $book }}';
		var C_PARAM='{{ $chapter }}';

	
	/*
	axios.defaults.baseURL = "{{ url('/api') }}";
	//axios.defaults.withCredentials
	axios.defaults.headers = {
		'X-Requested-With': 'XMLHttpRequest',
		'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
		Authorization: "Bearer " + user.jwt
	};
	*/

		var GET_URL = '{{ url('api/hooks') }}';
		var VOTE_URL = '{{ url('api/vote') }}';
		var SORT_BY_METHODS = ["uv", "dv", "dd", "da"];
		var LOGIN_TITLE='Login';
		var REGISTER_TITLE='Create an Account';
		var SUBMIT_POST_URL = '{{ url('api/hooks/new') }}';
		var SUBMIT_COMMENT_URL = '{{ url('api/comments/new') }}';
		var UPDATE_COMMENT_URL = '{{ url('api/comments/update') }}';
		var GET_COMMENTS_URL = '{{ url('api/comments/get') }}';
		var VOTE_ON_COMMENT_URL = '{{ url('api/comments/vote') }}';
		var USERNAME = '{{ Auth::user() ? Auth::user()->username : '' }}';

		const VOLUMES = ["", "The Old Testament", "The New Testament", "The Book of Mormon", "The Doctrine and Covenants", "The Pearl of Great Price"];
    	const BOOKS =["", "Genesis", "Exodus", "Leviticus", "Numbers", "Deutoronomy", "Joshua", "Judges", "Ruth", "1st Samuel", "2nd Samuel", "1st Kings", "2nd Kings", "1st Chronicles", "2nd Chronicles", "Ezra", "Nehemiah", "Esther", "Job", "Psalms", "Proverbs","Ecclesiastes", "Songs of Solomon", "Isaiah", "Jeremiah", "Lamentation", "Ezekiel", "Daniel", "Hosea", "Joel", "Amos", "Obadiah", "Jonah", "Micah", "Nahum", "Habakkuk", "Zephaniah", "Haggai", "Zechariah", "Malachi","Mattew", "Mark", "Luke", "John", "Acts", "Romans", "1 Corinthians", "2nd Corinthians", "Galation", "Ephesians", "Philipians", "Colossians", "1st Thessalonians", "2nd Thessalonians", "1st Timothy", "2nd Timothy", "Titus", "Philemon", "Hebrews", "James", "1st Peter", "2ns Peter", "1st John", "2nd John", "3rd John", "Jude", "Revelations","1st Nephi", "2nd Nephi", "Jacob", "Enos", "Jarom", "Omni", "Words of Mormon","Mosiah", "Alma", "Helaman", "3rd Nephi", "4th Nephi", "Mormon", "Ether", "Moroni","Section","Moses", "Abraham","Joseph Smith-Matthew", "Joseph Smith-History", "Articles of Faith"];

@include('scriptures.vue_components')

		var app = new Vue({
			el: '#app',
			data: {
				scriptures: [{"id":1,"title":"Old Testament","title_long":"The Old Testament","url":"ot","books":[{"id":1,"title":"Genesis","title_long":"The First Book of Moses called Genesis","url":"gen","num_chapters":50,"num_verses":1533},{"id":2,"title":"Exodus","title_long":"The Second Book of Moses called Exodus","url":"ex","num_chapters":40,"num_verses":1213},{"id":3,"title":"Leviticus","title_long":"The Third Book of Moses called Leviticus","url":"lev","num_chapters":27,"num_verses":859},{"id":4,"title":"Numbers","title_long":"The Fourth Book of Moses called Numbers","url":"num","num_chapters":36,"num_verses":1288},{"id":5,"title":"Deuteronomy","title_long":"The Fifth Book of Moses called Deuteronomy","url":"deut","num_chapters":34,"num_verses":959},{"id":6,"title":"Joshua","title_long":"The Book of Joshua","url":"josh","num_chapters":24,"num_verses":658},{"id":7,"title":"Judges","title_long":"The Book of Judges","url":"judg","num_chapters":21,"num_verses":618},{"id":8,"title":"Ruth","title_long":"The Book of Ruth","url":"ruth","num_chapters":4,"num_verses":85},{"id":9,"title":"1 Samuel","title_long":"The First Book of Samuel","url":"1_sam","num_chapters":31,"num_verses":810},{"id":10,"title":"2 Samuel","title_long":"The Second Book of Samuel","url":"2_sam","num_chapters":24,"num_verses":695},{"id":11,"title":"1 Kings","title_long":"The First Book of the Kings","url":"1_kgs","num_chapters":22,"num_verses":816},{"id":12,"title":"2 Kings","title_long":"The Second Book of the King","url":"2_kgs","num_chapters":25,"num_verses":719},{"id":13,"title":"1 Chronicles","title_long":"The First Book of the Chronicles","url":"1_chr","num_chapters":29,"num_verses":942},{"id":14,"title":"2 Chronicles","title_long":"The Second Book of the Chronicles","url":"2_chr","num_chapters":36,"num_verses":822},{"id":15,"title":"Ezra","title_long":"Ezra","url":"ezra","num_chapters":10,"num_verses":280},{"id":16,"title":"Nehemiah","title_long":"The Book of Nehemiah","url":"neh","num_chapters":13,"num_verses":406},{"id":17,"title":"Esther","title_long":"The Book of Esther","url":"esth","num_chapters":10,"num_verses":167},{"id":18,"title":"Job","title_long":"The Book of Job","url":"job","num_chapters":42,"num_verses":1070},{"id":19,"title":"Psalms","title_long":"The Book of Psalms","url":"ps","num_chapters":150,"num_verses":2461},{"id":20,"title":"Proverbs","title_long":"The Proverbs","url":"prov","num_chapters":31,"num_verses":915},{"id":21,"title":"Ecclesiastes","title_long":"Ecclesiastes or, The Preacher","url":"eccl","num_chapters":12,"num_verses":222},{"id":22,"title":"Solomon's Song","title_long":"The Song of Solomon","url":"song","num_chapters":8,"num_verses":117},{"id":23,"title":"Isaiah","title_long":"The Book of the Prophet Isaiah","url":"isa","num_chapters":66,"num_verses":1292},{"id":24,"title":"Jeremiah","title_long":"The Book of the Prophet Jeremiah","url":"jer","num_chapters":52,"num_verses":1364},{"id":25,"title":"Lamentations","title_long":"The Lamentations of Jeremiah","url":"lam","num_chapters":5,"num_verses":154},{"id":26,"title":"Ezekiel","title_long":"The Book of the Prophet Ezekiel","url":"ezek","num_chapters":48,"num_verses":1273},{"id":27,"title":"Daniel","title_long":"The Book of Daniel","url":"dan","num_chapters":12,"num_verses":357},{"id":28,"title":"Hosea","title_long":"Hosea","url":"hosea","num_chapters":14,"num_verses":197},{"id":29,"title":"Joel","title_long":"Joel","url":"joel","num_chapters":3,"num_verses":73},{"id":30,"title":"Amos","title_long":"Amos","url":"amos","num_chapters":9,"num_verses":146},{"id":31,"title":"Obadiah","title_long":"Obadiah","url":"obad","num_chapters":1,"num_verses":21},{"id":32,"title":"Jonah","title_long":"Jonah","url":"jonah","num_chapters":4,"num_verses":48},{"id":33,"title":"Micah","title_long":"Micah","url":"micah","num_chapters":7,"num_verses":105},{"id":34,"title":"Nahum","title_long":"Nahum","url":"nahum","num_chapters":3,"num_verses":47},{"id":35,"title":"Habakkuk","title_long":"Habakkuk","url":"hab","num_chapters":3,"num_verses":56},{"id":36,"title":"Zephaniah","title_long":"Zephaniah","url":"zeph","num_chapters":3,"num_verses":53},{"id":37,"title":"Haggai","title_long":"Haggai","url":"hag","num_chapters":2,"num_verses":38},{"id":38,"title":"Zechariah","title_long":"Zechariah","url":"zech","num_chapters":14,"num_verses":211},{"id":39,"title":"Malachi","title_long":"Malachi","url":"mal","num_chapters":4,"num_verses":55}]},{"id":2,"title":"New Testament","title_long":"The New Testament of our Lord and Saviour Jesus Christ","url":"nt","books":[{"id":40,"title":"Matthew","title_long":"The Gospel According to St Matthew","url":"matt","num_chapters":28,"num_verses":1071},{"id":41,"title":"Mark","title_long":"The Gospel According to St Mark","url":"mark","num_chapters":16,"num_verses":678},{"id":42,"title":"Luke","title_long":"The Gospel According to St Luke","url":"luke","num_chapters":24,"num_verses":1151},{"id":43,"title":"John","title_long":"The Gospel According to St John","url":"john","num_chapters":21,"num_verses":879},{"id":44,"title":"Acts","title_long":"The Acts of the Apostles","url":"acts","num_chapters":28,"num_verses":1007},{"id":45,"title":"Romans","title_long":"The Epistle of Paul the Apostle to the Romans","url":"rom","num_chapters":16,"num_verses":433},{"id":46,"title":"1 Corinthians","title_long":"The First Epistle of Paul the Apostle to the Corinthians","url":"1_cor","num_chapters":16,"num_verses":437},{"id":47,"title":"2 Corinthians","title_long":"The Second Epistle of Paul the Apostle to the Corinthians","url":"2_cor","num_chapters":13,"num_verses":257},{"id":48,"title":"Galatians","title_long":"The Epistle of Paul the Apostle to the Galatians","url":"gal","num_chapters":6,"num_verses":149},{"id":49,"title":"Ephesians","title_long":"The Epistle of Paul the Apostle to the Ephesians","url":"eph","num_chapters":6,"num_verses":155},{"id":50,"title":"Philippians","title_long":"The Epistle of Paul the Apostle to the Philippians","url":"philip","num_chapters":4,"num_verses":104},{"id":51,"title":"Colossians","title_long":"The Epistle of Paul the Apostle to the Colossians","url":"col","num_chapters":4,"num_verses":95},{"id":52,"title":"1 Thessalonians","title_long":"The First Epistle of Paul the Apostle to the Thessalonians","url":"1_thes","num_chapters":5,"num_verses":89},{"id":53,"title":"2 Thessalonians","title_long":"The Second Epistle of Paul the Apostle to the Thessalonians","url":"2_thes","num_chapters":3,"num_verses":47},{"id":54,"title":"1 Timothy","title_long":"The First Epistle of Paul the Apostle to Timothy","url":"1_tim","num_chapters":6,"num_verses":113},{"id":55,"title":"2 Timothy","title_long":"The Second Epistle of Paul the Apostle to Timothy","url":"2_tim","num_chapters":4,"num_verses":83},{"id":56,"title":"Titus","title_long":"The Epistle of Paul to Titus","url":"titus","num_chapters":3,"num_verses":46},{"id":57,"title":"Philemon","title_long":"The Epistle of Paul to Philemon","url":"philem","num_chapters":1,"num_verses":25},{"id":58,"title":"Hebrews","title_long":"The Epistle of Paul to the Hebrews","url":"heb","num_chapters":13,"num_verses":303},{"id":59,"title":"James","title_long":"The General Epistle of James","url":"james","num_chapters":5,"num_verses":108},{"id":60,"title":"1 Peter","title_long":"The First Epistle General of Peter","url":"1_pet","num_chapters":5,"num_verses":105},{"id":61,"title":"2 Peter","title_long":"The Second Epistle General of Peter","url":"2_pet","num_chapters":3,"num_verses":61},{"id":62,"title":"1 John","title_long":"The First Epistle General of John","url":"1_jn","num_chapters":5,"num_verses":105},{"id":63,"title":"2 John","title_long":"The Second Epistle of John","url":"2_jn","num_chapters":1,"num_verses":13},{"id":64,"title":"3 John","title_long":"The Third Epistle of John","url":"3_jn","num_chapters":1,"num_verses":14},{"id":65,"title":"Jude","title_long":"The General Epistle of Jude","url":"jude","num_chapters":1,"num_verses":25},{"id":66,"title":"Revelation","title_long":"The Revelation of St John the Divine","url":"rev","num_chapters":22,"num_verses":404}]},{"id":3,"title":"Book of Mormon","title_long":"The Book of Mormon Another Testament of Jesus Christ","url":"bm","books":[{"id":67,"title":"1 Nephi","title_long":"The First Book of Nephi","url":"1_ne","num_chapters":22,"num_verses":618},{"id":68,"title":"2 Nephi","title_long":"The Second Book of Nephi","url":"2_ne","num_chapters":33,"num_verses":779},{"id":69,"title":"Jacob","title_long":"The Book of Jacob","url":"jacob","num_chapters":7,"num_verses":203},{"id":70,"title":"Enos","title_long":"The Book of Enos","url":"enos","num_chapters":1,"num_verses":27},{"id":71,"title":"Jarom","title_long":"The Book of Jarom","url":"jarom","num_chapters":1,"num_verses":15},{"id":72,"title":"Omni","title_long":"The Book of Omni","url":"omni","num_chapters":1,"num_verses":30},{"id":73,"title":"Words of Mormon","title_long":"Words of Mormon","url":"w_of_m","num_chapters":1,"num_verses":18},{"id":74,"title":"Mosiah","title_long":"The Book of Mosiah","url":"mosiah","num_chapters":29,"num_verses":785},{"id":75,"title":"Alma","title_long":"The Book of Alma","url":"alma","num_chapters":63,"num_verses":1975},{"id":76,"title":"Helaman","title_long":"The Book of Helaman","url":"hel","num_chapters":16,"num_verses":497},{"id":77,"title":"3 Nephi","title_long":"The Third Book of Nephi","url":"3_ne","num_chapters":30,"num_verses":785},{"id":78,"title":"4 Nephi","title_long":"The Fourth Book of Nephi","url":"4_ne","num_chapters":1,"num_verses":49},{"id":79,"title":"Mormon","title_long":"The Book of Mormon","url":"morm","num_chapters":9,"num_verses":227},{"id":80,"title":"Ether","title_long":"The Book of Ether","url":"ether","num_chapters":15,"num_verses":433},{"id":81,"title":"Moroni","title_long":"The Book of Moroni","url":"moro","num_chapters":10,"num_verses":163}]},{"id":4,"title":"Doctrine &amp; Covenants","title_long":"The Doctrine and Covenants","url":"dc","books":[{"id":82,"title":"Doctrine and Covenants","title_long":"The Doctrine and Covenants","url":"dc","num_chapters":138,"num_verses":3654}]},{"id":5,"title":"Pearl of Great Price","title_long":"The Pearl of Great Price","url":"pgp","books":[{"id":83,"title":"Moses","title_long":"Selections from the Book of Moses","url":"moses","num_chapters":8,"num_verses":356},{"id":84,"title":"Abraham","title_long":"The Book of Abraham","url":"abr","num_chapters":5,"num_verses":136},{"id":85,"title":"Joseph Smith--Matthew","title_long":"Joseph Smith--Matthew","url":"js_m","num_chapters":1,"num_verses":55},{"id":86,"title":"Joseph Smith--History","title_long":"Joseph Smith--History","url":"js_h","num_chapters":1,"num_verses":75},{"id":87,"title":"Articles of Faith","title_long":"The Articles of Faith","url":"a_of_f","num_chapters":1,"num_verses":13}]}],				
				currVolumeId: -1,
				currBookId: -1,
				currChapterId: -1,

				currVolume: null,
				currBook: null,
				currChapter: null,

				loginTitle: 'Login',
				registerTitle: 'Create an Account',

				sortByMethod: 0,
				
				verses: [],
				
				currVerseComments: [],

				newComment: {
					body: "",
					bodyError: "",
					ajaxError: "",
				},

				showingPost: false,
				
			},
			created: function () {
				this.navigateToByShorts(V_PARAM, B_PARAM, C_PARAM);
			},
			beforeMount: function() {

			},
			mounted: function() {
				this.loadAllVerses();
			},
			methods: {
				loadAllVerses: function() {
					//TODO tell browser specifically to cache this file OR somehow put this into localStorage
					//https://stackoverflow.com/questions/311062/caching-javascript-files
					axios.get('{{ url('files/scriptures_verses_only.json.gz') }}', {

					}, config)
					.then(function (response) {
						/*
						console.log(response);
						console.log(response.data);
						console.log(response.data[1][2][1]);
						*/
						this.verses=response.data;
					})
					.catch(function (error) {
						console.log("ERROR Loading All Verses");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
					});
				},
				getComments: function() {
					/*
					axios.post(GET_COMMENTS_URL, {
						post_type: POST_TYPE,
						post_id: this.currPost.id
					}, config)
					.then(function (response) {
						console.log(response.data);
						if (response.data.success) {
							app.processComments(response.data.comments);
						}
						else {
							//Unknown Error
							app.newComment.ajaxError = "An error has occurred. Please try again.";
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
						//invalid_parameters
						//db_error
						app.newComment.ajaxError = "An error has occurred. Please try again.";
					});
					*/
				},
				parseComments: function(c) {
					//var VERSES = json_encode($verses);
					//var COMMENTS = json_encode($comments);

					var commentIdMap = {}; //Keeps track of nodes using id as key, for fast lookup
					var rootComments = []; //Initially set our root node to an array
					var verseIdCountMap = {};

					//loop over data
					COMMENTS.forEach(function(comment) {
						//each node will have children, so let's give it a "children" poperty
						comment.children = [];
						//add an entry for this node to the map so that any future children can lookup the parent
						commentIdMap[comment.id] = comment;

						if (verseIdCountMap[comment.verse_id]) {
							verseIdCountMap[comment.verse_id]+=1;
						}
						else {
							verseIdCountMap[comment.verse_id]=1;
						}

						//Does this node have a parent?
						if (comment.parent_id == null) {
							rootComments.push(comment);
						}
						else {
							//This node has a parent, so let's look it up using the id
							parentNode = commentIdMap[comment.parent_id];
							//Let's add the current node as a child of the parent node.
							parentNode.children.push(comment);
						}
					});

					//We have to process the arrays like this if we want to add any members to the objects, ie .showingComments, or they aren't reactive
					while(VERSES.length) {
						VERSES[0].showingComments=false;
						VERSES[0].comments = [];
						var l = rootComments.length;
						for (var i=0; i<l; i++) {
							if (rootComments[i].verse_id == VERSES[0].id) {
								VERSES[0].comments.push(clone(rootComments[i]));
							}
						}
						VERSES[0].commentCount = verseIdCountMap[VERSES[0].id] ? verseIdCountMap[VERSES[0].id] : 0;
						
						this.verses.push(clone(VERSES.shift()));
					}
				},
				selectVolume: function(i) {
					this.currVolumeId=i;
					this.currVolume=this.scriptures[i];
				},
				selectBook: function(i) {
					this.currBookId=i;
					this.currBook=this.currVolume.books[i];
				},
				navigateToByShorts: function(v,b,c) {
					if (v.length>0) {	
						for (var i=0; i<this.scriptures.length; i++) {
							if (this.scriptures[i].url == v) {
								this.currVolumeId = i;
								this.currVolume=this.scriptures[i];
								break;
							}
						}
						if (this.currVolume && b.length>0) {
							for (var j=0; j<this.currVolume.books.length; j++) {
								if (this.currVolume.books[j].url == b) {
									this.currBookId = j;
									this.currBook = this.currVolume.books[j];
									break;
								}
							}
						}
						if (this.currVolume && this.currBook && c.length>0) {
							this.currChapterId=parseInt(c);
							this.currChapter=parseInt(c);
						}
					}
				},
				navigateToByIds: function(v,b,c) {
					this.currVolumeId = parseInt(v);
					this.currVolume = v>=0 ? this.scriptures[parseInt(v)] : null;

					this.currBookId = parseInt(b);
					this.currBook = this.currVolume ? this.currVolume.books[parseInt(b)] : null;
					
					this.currChapterId = parseInt(c);
					this.currChapter = parseInt(c);
				},
				getCitations: function() {
					//TODO Not working fully yet

					//https://scriptures.byu.edu/citation_index/citation_ajax/Any/1830/2018/all/s/f/101/3?verses=
					var base_url = 'https://scriptures.byu.edu/citation_index/citation_ajax/Any/1830/2018/all/s/f/';
					var byu_volumes = ['','1','1','2','3','4'];
					var BOOK_ID_OFFSETS = [0,1,1,67-4,82-2,83];
					var b = B_PARAM - BOOK_ID_OFFSETS[V_PARAM] + 1;
					if (b<10) { b = "0"+b; }
					var url = byu_volumes[V_PARAM] + b + '/' + C_PARAM + '?verses=';

					console.log(base_url + url);
				},
				toggleComments: function(v) {
					v.showingComments=!v.showingComments;
				},
				submitComment: function(pid) {
					if (!this.checkLoggedIn("Please login to post a comment")) { return; }

					this.newComment.bodyError = this.newComment.ajaxError = "";
					
					if (this.newComment.body.length == 0) {
						this.newComment.bodyError = "Please include content in your comment";	
						return;
					}

					axios.post(SUBMIT_COMMENT_URL, {
						post_type: POST_TYPE,
						post_id: this.currPost.id,
						comment: this.newComment.body,
						parent_id: pid
					}, config)
					.then(function (response) {
						console.log(response);
						if (response.data.success) {							
							var c = {
								children: [],
								comment: app.newComment.body,
								created_at: moment().format('YYYY-MM-DD HH:mm:ssZ'),
								downvotes: 0,
								upvotes: 1,
								id: response.data.new_id,
								parent_id: null,
								updated_at: moment().format('YYYY-MM-DD HH:mm:ssZ'),
								username: USERNAME,
								voted: 1
							}
							app.currVerseComments.unshift(c);
							//Vue.set(app.currVerseComments, 0, c);

							app.clearNewComment();
						}
						else {
							//Unknown Error
							app.newComment.ajaxError = "An error has occurred. Please try again.";
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
						//invalid_parameters
						//db_error
						app.newComment.ajaxError = "An error has occurred. Please try again.";
					});
				},
				clearNewComment: function() {
					this.newComment = {
						body: "",
						bodyError: "",
						ajaxError: ""
					};
				},
				checkLoggedIn: function(t) {
					if (!LOGGED_IN) {
						this.showingNewPost=false;
						this.loginTitle=t;
						showLoginModal();
						return false;
					}
					return true;
				},
				/*
				upvote: function(p) {
					this.vote(p, 1);
				},
				downvote: function(p) {
					this.vote(p, 0);
				},
				vote: function(p, v) {
					if (!this.checkLoggedIn("You must be logged in to vote")) { return; }
					
					axios.post(VOTE_URL, {
						type: POST_TYPE,
						vote: v,
						id: p.id
					}, config)
					.then(function (response) {
						if (response.data.success) {
							if (response.data.success == "vote_saved") {
								if (v==1) { p.upvotes+=1; }
								else if (v==0) { p.downvotes+=1; }
								p.voted=v;
							}
							else if (response.data.success == "vote_unchanged") {

							}
							else if (response.data.success == "vote_updated") {
								if (v==1) {
									p.upvotes+=1;
									p.downvotes-=1;
								}
								else if (v==0) {
									p.downvotes+=1;
									p.upvotes-=1;
								}
								p.voted=v;
							}
						}
						else {
							//Error
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
					});
				},
				submitPost: function() {
					if (!this.checkLoggedIn("Please login to submit a post")) { return; }

					this.newPost.titleError = this.newPost.bodyError = this.newPost.ajaxError = "";

					if (this.newPost.title.length == 0) {
						this.newPost.titleError = "Please include a title";
						return;
					}
					if (this.newPost.body.length == 0) {
						this.newPost.bodyError = "Please include content in your " + POST_TYPE_PRETTY;
						return;
					}
					
					axios.post(SUBMIT_POST_URL, {
						hook_body: this.newPost.body,
						hook_title: this.newPost.title
					}, config)
					.then(function (response) {
						if (response.data.success) {
							app.clearNewPost();
							app.showingNewPost=false;
							//TODO what do we do here? Show a success alert? Navigate to somewhere?
						}
						else {
							//Unknown Error
							this.newPost.ajaxError = "An error has occurred. Please try again.";
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
						//invalid_parameters
						//db_error
						app.newPost.ajaxError = "An error has occurred. Please try again.";
					});
				},
				submitComment: function(pid) {
					if (!this.checkLoggedIn("Please login to post a comment")) { return; }

					this.newComment.bodyError = this.newComment.ajaxError = "";
					
					if (this.newComment.body.length == 0) {
						this.newComment.bodyError = "Please include content in your comment";	
						return;
					}

					axios.post(SUBMIT_COMMENT_URL, {
						post_type: POST_TYPE,
						post_id: this.currPost.id,
						comment: this.newComment.body,
						parent_id: pid
					}, config)
					.then(function (response) {
						console.log(response);
						if (response.data.success) {							
							var c = {
								children: [],
								comment: app.newComment.body,
								created_at: moment().format('YYYY-MM-DD HH:mm:ssZ'),
								downvotes: 0,
								upvotes: 1,
								id: response.data.new_id,
								parent_id: null,
								updated_at: moment().format('YYYY-MM-DD HH:mm:ssZ'),
								username: USERNAME,
								voted: 1
							}
							app.currPostComments.unshift(c);
							//Vue.set(app.currPostComments, 0, c);

							app.clearNewComment();
						}
						else {
							//Unknown Error
							app.newComment.ajaxError = "An error has occurred. Please try again.";
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
						//invalid_parameters
						//db_error
						app.newComment.ajaxError = "An error has occurred. Please try again.";
					});
				},
				loadComments: function() {
					axios.post(GET_COMMENTS_URL, {
						post_type: POST_TYPE,
						post_id: this.currPost.id
					}, config)
					.then(function (response) {
						console.log(response.data);
						if (response.data.success) {
							app.processComments(response.data.comments);
						}
						else {
							//Unknown Error
							app.newComment.ajaxError = "An error has occurred. Please try again.";
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
						//invalid_parameters
						//db_error
						app.newComment.ajaxError = "An error has occurred. Please try again.";
					});
				},
				*/
			},
			computed: {
				/*
				fullName: function () {
					return this.firstName + ' ' + this.lastName
				}
				//////////////////// OR /////////////////////
				fullName: {
					get: function () {
						return this.firstName + ' ' + this.lastName
					},
					set: function (newValue) {
						var names = newValue.split(' ')
						this.firstName = names[0]
						this.lastName = names[names.length - 1]
					}
				}
				*/
			},
			watch: {
				/*
				// whenever question changes, this function will run
				question: function (newQuestion, oldQuestion) {
					this.answer = 'Waiting for you to stop typing...'
					this.debouncedGetAnswer()
				}
				*/
			},
			filters: {
				formatCommentCount: function(n) {
					if (n>=1000) {
						return parseInt(n/1000)+'k';
					}
					return n;
				},
				fromNow: function(v) {
					if (moment(v).isValid()) {
						return moment(v + 'Z', 'YYYY-MM-DD HH:mm:ssZ').fromNow(); //'Z' converts to local time zone
					}
					return v;
				},
			},
		});

		function clone(aObject) {
			if (!aObject) {
				return aObject;
			  }

			  var bObject, v, k;
			  bObject = Array.isArray(aObject) ? [] : {};
			  for (k in aObject) {
				if (k == 'next_date' || k == 'next_date2') {
					bObject[k] = new Date(new Date(aObject[k]).getTime());
				}
				else if (k == 'other_dates') {
					var o = [];
					for (var i=0; i< aObject[k].length; i++) {
						o.push(new Date(aObject[k][i]));
					}
					bObject[k] = o;
				}
				else {
					v = aObject[k];
					bObject[k] = (typeof v === "object") ? this.clone(v) : v;
				}
			  }
			  return bObject;
		}
	</script>
@endsection