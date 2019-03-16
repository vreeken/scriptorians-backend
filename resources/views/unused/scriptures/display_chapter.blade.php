@extends('templates.main_layout')

@section('title')
{{ env('APP_TITLE') }}
@endsection

@section('extra_header')
	
@endsection

@section('extra_css')
	
@endsection

@section('content')
	<div class="section-content">
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
					<div class="button is-block is-info is-large is-fullwidth" @click="submitComment(null)">Submit</div>
					<div class="error-field" v-if="newComment.ajaxError.length>0" v-html="newComment.ajaxError"></div>
				</div>
				<comment-list :comments="v.comments"></comment-list>
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
	
	/*
	axios.defaults.baseURL = "{{ url('/api') }}";
	//axios.defaults.withCredentials
	axios.defaults.headers = {
		'X-Requested-With': 'XMLHttpRequest',
		'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
		Authorization: "Bearer " + user.jwt
	};
	*/

		var SORT_BY_METHODS = ["uv", "dv", "dd", "da"];
		var LOGIN_TITLE='Login';
		var REGISTER_TITLE='Create an Account';
		var SUBMIT_COMMENT_URL = '{{ url('api/comments/new') }}';
		var UPDATE_COMMENT_URL = '{{ url('api/comments/update') }}';
		var VOTE_ON_COMMENT_URL = '{{ url('api/comments/vote') }}';
		var USERNAME = '{{ Auth::user() ? Auth::user()->username : '' }}';

		var VID = {{ $volume_id }};
		var BID = {{ $book_id }};
		var CID = {{ $chapter_id }};

		const VOLUMES = ["", "The Old Testament", "The New Testament", "The Book of Mormon", "The Doctrine and Covenants", "The Pearl of Great Price"];
    	const BOOKS =["", "Genesis", "Exodus", "Leviticus", "Numbers", "Deutoronomy", "Joshua", "Judges", "Ruth", "1st Samuel", "2nd Samuel", "1st Kings", "2nd Kings", "1st Chronicles", "2nd Chronicles", "Ezra", "Nehemiah", "Esther", "Job", "Psalms", "Proverbs","Ecclesiastes", "Songs of Solomon", "Isaiah", "Jeremiah", "Lamentation", "Ezekiel", "Daniel", "Hosea", "Joel", "Amos", "Obadiah", "Jonah", "Micah", "Nahum", "Habakkuk", "Zephaniah", "Haggai", "Zechariah", "Malachi","Mattew", "Mark", "Luke", "John", "Acts", "Romans", "1 Corinthians", "2nd Corinthians", "Galation", "Ephesians", "Philipians", "Colossians", "1st Thessalonians", "2nd Thessalonians", "1st Timothy", "2nd Timothy", "Titus", "Philemon", "Hebrews", "James", "1st Peter", "2ns Peter", "1st John", "2nd John", "3rd John", "Jude", "Revelations","1st Nephi", "2nd Nephi", "Jacob", "Enos", "Jarom", "Omni", "Words of Mormon","Mosiah", "Alma", "Helaman", "3rd Nephi", "4th Nephi", "Mormon", "Ether", "Moroni","Section","Moses", "Abraham","Joseph Smith-Matthew", "Joseph Smith-History", "Articles of Faith"];



		//https://scotch.io/@jagadeshanh/build-nested-commenting-system-using-laravel-and-vuejs-part-1
		Vue.component('comment-list', {
			data: function () {
				return {
					count: 0
				}
			},
			props: ['comments'],
			computed:{
			},
			beforeCreate: function () {
				//this.$options.components.Comment = require('./Comment.vue')
			},
			template: `
				<div class="comment-list">
					<comment v-for="(comment, index) in comments" v-bind:key="comment.id" v-bind:comment="comment"></comment>
				</div>
			`,
		});

		Vue.component('comment', {
			props: ['comment'],
			data(){
				return {
					replyToComment: false,
					editComment: false,
					me: USERNAME
				}
			},
			mounted() {

			},
			components: {
			},
			methods:{
				voteOnComment(v, c) {
					if (v!=0 && v!=1) { return; }

					var self = this;
					//ajax post
					axios.post(VOTE_URL, {
						type: POST_TYPE+"_comment",
						id: self.comment.id,
						vote: v
					}, config)
					.then(function (response) {
						console.log(response);
						if (response.data.success) {
							if (response.data.success == "vote_saved") {
								if (v==1) { self.comment.upvotes+=1; }
								else if (v==0) { self.comment.downvotes+=1; }
								self.comment.voted=v;
							}
							else if (response.data.success == "vote_unchanged") {

							}
							else if (response.data.success == "vote_updated") {
								if (v==1) {
									self.comment.upvotes+=1;
									self.comment.downvotes-=1;
								}
								else if (v==0) {
									self.comment.downvotes+=1;
									self.comment.upvotes-=1;
								}
								self.comment.voted=v;
							}
						}
						else {
							//Unknown Error
							self.newComment.ajaxError = "An error has occurred. Please try again.";
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
						//invalid_parameters
						//db_error
						self.newComment.ajaxError = "An error has occurred. Please try again.";
					});
				}
			},
			template: `
				<div class="comment-group">
					<div class="post-comment">
						<div class="comment-vote-btns">
							<div class="vote-arrow up" @click="voteOnComment(1, comment)" v-bind:class="{'voted': comment.voted==1}">
								<i class="fas fa-arrow-alt-circle-up"></i>
							</div>
							<div class="vote-arrow down" @click="voteOnComment(0, comment)" v-bind:class="{'voted': comment.voted==0}">
								<i class="fas fa-arrow-alt-circle-down"></i>
							</div>
						</div>
						<div>
							<div class="comment-body" v-html="comment.comment"></div>
							<div class="comment-header">
								<span class="comment-score" v-bind:title="'+'+comment.upvotes+' -'+comment.downvotes">@{{ comment.upvotes-comment.downvotes }} point<span v-if="comment.upvotes-comment.downvotes != 1 && comment.upvotes-comment.downvotes != -1">s</span></span> - Posted by 
								<span class="comment-author" v-html="comment.username"></span>
								<span class="comment-date">@{{ comment.created_at | fromNow }}</span> 
								<span class="comment-reply-btn fake-link" @click="replyToComment == comment ? replyToComment=false : replyToComment=comment">Reply</span>
								<span v-if="comment.username==me">
									&nbsp;&bull;&nbsp;<span class="comment-edit-btn fake-link" @click="editComment == comment ? editComment=false : editComment=comment">Edit</span>
								</span>
							</div>
						</div>
					</div>
					<comment-reply v-if="replyToComment == comment" :comment="comment" :replyToComment="replyToComment" @onReplySuccess="replyToComment=false"></comment-reply>
					<comment-edit v-if="editComment == comment" :comment="comment" :editComment="editComment" @onEditSuccess="editComment=false"></comment-edit>
					<comment-list v-if="comment.children && comment.children.length" v-bind:comments="comment.children"></comment-list>
				</div>
			`,
			filters: {
				fromNow: function(v) {
					if (moment(v).isValid()) {
						return moment(v + 'Z', 'YYYY-MM-DD HH:mm:ssZ').fromNow(); //'Z' converts to local time zone
					}
					return v;
				},
			},
		});

		Vue.component('comment-reply', {
			props: ['comment', 'replyToComment'],
			data() {
				return {
					newComment: {
						body: '',
						bodyError: false,
						ajaxError: ''
					}
				}
			},
			methods: {
				replyTo(comment) {
					//basic clientside validation
					if (this.newComment.body.length==0) {
						this.newComment.bodyError=true;
						return;
					}
					//Reset any errors
					this.newComment.bodyError=false;
					this.newComment.ajaxError="";

					//For use within Axios scope to gain access to 'this'
					var self = this;

					//ajax post
					axios.post(SUBMIT_COMMENT_URL, {
						post_type: POST_TYPE,
						post_id: app.currPost.id,
						comment: this.newComment.body,
						parent_id: comment.id
					}, config)
					.then(function (response) {
						console.log(response);
						if (response.data.success) {
							//Create a new basic 'comment' with the newly submitted into and add to the parent comments list of children
							var c = {
								children: [],
								comment: self.newComment.body,
								created_at: moment().format('YYYY-MM-DD HH:mm:ssZ'),
								downvotes: 0,
								upvotes: 1,
								id: response.data.new_id,
								parent_id: comment.id,
								updated_at: moment().format('YYYY-MM-DD HH:mm:ssZ'),
								username: USERNAME,
								voted: 1
							}
							//Add to 0 position of parent's children comments so that it show up on top (near the submit form), 
							//if reloaded their comment will be at the end of the list, but this makes things more intuitive
							self.comment.children.unshift(c);

							//unset the new comment data
							self.newComment = {
								body: '',
								bodyError: false,
								ajaxError: ''
							}

							//Emit event to let parent (comment group) know the form is done
							self.$emit('onReplySuccess');
						}
						else {
							//Unknown Error
							self.newComment.ajaxError = "An error has occurred. Please try again.";
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
						//invalid_parameters
						//db_error
						self.newComment.ajaxError = "An error has occurred. Please try again.";
					});
				}
			},
			template: `
				<div>
					<div class="field">
						<div class="control">
							<textarea v-model="newComment.body" class="child-comment-body textarea" v-bind:class="{'error':newComment.bodyError}" type="text" placeholder="Comment" rows="2"></textarea>
						</div>
					</div>
					<div class="button is-block is-info is-large is-fullwidth" @click="replyTo(comment)">Submit</div>
					<div class="error-field" v-if="newComment.ajaxError.length>0" v-html="newComment.ajaxError"></div>
				</div>
			`,
		});

		Vue.component('comment-edit', {
			props: ['comment', 'editComment'],
			data() {
				return {
					bodyError: false,
					ajaxError: false,
					editedComment: this.comment.comment
				}
			},
			methods: {
				update() {
					//basic clientside validation
					if (this.editedComment.length==0) {
						this.bodyError='Please fill out your comment or click delete if you wish to remove your comment';
						return;
					}
					//Reset any errors
					this.bodyError=false;
					this.ajaxError=false;

					//If comment didn't change then return, but still emit success so that parent closes the edit div
					if (this.editedComment == this.comment.comment) {
						this.$emit('onEditSuccess');
						return;
					}

					//For use within Axios scope to gain access to 'this'
					var self = this;

					//ajax post
					axios.post(UPDATE_COMMENT_URL, {
						post_type: POST_TYPE,
						comment: self.editedComment,
						comment_id: self.comment.id
					}, config)
					.then(function (response) {
						console.log(response);
						if (response.data.success) {
							self.comment.comment=self.editedComment;
							self.$emit('onEditSuccess');
						}
						else {
							//Unknown Error
							self.ajaxError="An error has occurred. Please try again.";
						}
					})
					.catch(function (error) {
						console.log("ERROR");
						console.log(error);
						console.log(error.response.headers);
						console.log(error.response.data);
						//invalid_parameters
						//db_error
						self.ajaxError="An error has occurred. Please try again.";
					});
				}
			},
			template: `
				<div>
					<span v-if="bodyError" v-html="bodyError"></span>
					<div class="field">
						<div class="control">
							<textarea v-model="editedComment" class="child-comment-body textarea" v-bind:class="{'error':bodyError}" type="text" placeholder="Comment" rows="2"></textarea>
						</div>
					</div>
					<div class="button is-block is-info is-large is-fullwidth" @click="update()">Update Comment</div>
					<div class="error-field" v-if="ajaxError" v-html="ajaxError"></div>
				</div>
			`,
		});

		var app = new Vue({
			el: '#app',
			data: {
				loginTitle: LOGIN_TITLE,
				registerTitle: REGISTER_TITLE,
				
				sortByMethod: 0,
				
				verses: [],
				
				currVerseComments: [],

				newComment: {
					body: "",
					bodyError: "",
					ajaxError: "",
				},

				showingPost: false,
				volume: VOLUMES[VID],
				book: BOOKS[BID],
				chapter: CID,
			},
			created: function () {
				var VERSES = {!! json_encode($verses) !!};
				var COMMENTS = {!! json_encode($comments) !!};

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
			mounted: function() {

				//TODO tell browser specifically to cache this file
				//https://stackoverflow.com/questions/311062/caching-javascript-files
				axios.get('{{ url('files/scriptures_verses_only.json.gz') }}', {

				}, config)
				.then(function (response) {
					console.log(response);
					console.log(response.data);

					console.log(response.data[1][2][1]);
				})
				.catch(function (error) {
					console.log("ERROR");
					console.log(error);
					console.log(error.response.headers);
					console.log(error.response.data);
					//invalid_parameters
					//db_error
					self.newComment.ajaxError = "An error has occurred. Please try again.";
				});


				
			},
			methods: {
				getCitations: function() {
					//https://scriptures.byu.edu/citation_index/citation_ajax/Any/1830/2018/all/s/f/101/3?verses=
					var base_url = 'https://scriptures.byu.edu/citation_index/citation_ajax/Any/1830/2018/all/s/f/';
					var byu_volumes = ['','1','1','2','3','4'];
					var BOOK_ID_OFFSETS = [0,1,1,67-4,82-2,83];
					var b = BID - BOOK_ID_OFFSETS[VID] + 1;
					if (b<10) { b = "0"+b; }
					var url = byu_volumes[VID] + b + '/' + CID + '?verses=';

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
				loadComments: function() {
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
				processComments: function(c) {
					this.currVerseComments = [];
					
					var commentIdMap = {}; //Keeps track of nodes using id as key, for fast lookup
					var roots = []; //Initially set our root node to an array

					//loop over data
					c.forEach(function(comment) {
						//each node will have children, so let's give it a "children" poperty
						comment.children = [];
						//add an entry for this node to the map so that any future children can lookup the parent
						commentIdMap[comment.id] = comment;
						//Does this node have a parent?
						if (comment.parent_id == null) {
							//No, so add it to the array of root nodes
							roots.push(comment);
						}
						else {
							//This node has a parent, so let's look it up using the id
							parentNode = commentIdMap[comment.parent_id];

							//Let's add the current node as a child of the parent node.
							parentNode.children.push(comment);
						}
					});

					for (var i=0; i<roots.length; i++) {
						this.currVerseComments.push(roots[i]);
					}

					//console.log(this.currVerseComments);
				},
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
			}
		});



		


		
		
		/*
		function hideBodyScrolling() {
			document.documentElement.style.overflow = 'hidden';
			document.body.scroll = "no";
		}
		function showBodyScrolling() {
			document.documentElement.style.overflow = 'auto';
			document.body.scroll = "yes";
		}
		*/

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