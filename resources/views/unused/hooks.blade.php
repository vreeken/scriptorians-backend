@extends('templates.main_layout')

@section('title')
{{ env('APP_TITLE') }} Plot Hooks
@endsection

@section('extra_header')
	
@endsection

@section('extra_css')
	
@endsection

@section('content')
	<div class="vue-loader" style="display: none">
		<svg id="d20_anim_icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="720px" height="720px" viewBox="0 0 720 720" enable-background="new 0 0 720 720" xml:space="preserve">
			<polygon id="d20_outside" fill="none" stroke="#000000" stroke-width="20" stroke-miterlimit="10" points="355.933,124.834 153.167,241.167 153.167,481.834 360,598.5 569.833,481.834 569.833,241.167 360,122.5" style="stroke-dasharray: 1438.407470703125; stroke-dashoffset: 1438.407470703125;"/>
			<polygon id="d20_inside" fill="none" stroke="#000000" stroke-width="20" stroke-linejoin="round" stroke-miterlimit="10" points="360,122.5 245.167,293.5 153.167,481.834 361.873,494.5 569.833,481.834 475.833,293.5" style="stroke-dasharray: 1250.046630859375; stroke-dashoffset: 1250.046630859375;"/>
			<polyline id="d20_line1" fill="none" stroke="#000000" stroke-width="20" stroke-miterlimit="10" points="153.167,241.167 245.167,293.5 475.833,293.5" style="stroke-dasharray: 336.5090026855469; stroke-dashoffset: 336.5090026855469;" />
			<polyline id="d20_line2" fill="none" stroke="#000000" stroke-width="20" stroke-miterlimit="10" points="245.167,293.5 360,494.5 360,598.5" style="stroke-dasharray: 335.489990234375; stroke-dashoffset: 335.489990234375;" />
			<polyline id="d20_line3" fill="none" stroke="#000000" stroke-width="20" stroke-miterlimit="10" points="361.873,494.5 475.833,293.5 569.833,241.167" style="stroke-dasharray: 338.6441650390625; stroke-dashoffset: 338.6441650390625;" />			
		</svg>
	</div>
	<div class="section-content" style="display: none;" v-bind:class="{'overlay-open': showingPost}">

		<div class="section-header">
			<div class="field is-horizontal">
				<div class="lbl is-normal">
					Sort By:
				</div>
				<div class="field-body">
					<div class="field">
						<div class="control is-expanded">
							<div class="select is-fullwidth">
								<select :value="sortByMethod" id="sortby" @change="changeSortMethod(sortByMethod, $event)">
									<option value="0">Random</option>
									<option value="1">Upvotes</option>
									<option value="2">Downvotes</option>
									<option value="3">Newest</option>
									<option value="4">Oldest</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div>
				<div id="new-post-btn" class="button" @click="showNewPost()">New Hook</div>
			</div>
		</div>

		<div v-if="posts">
			<div class="post" v-for="(h, index) in posts" :key="h.id" v-bind:class="{minimized: h.minimized}">
				<div class="post-rank">
					<br/>
					<span v-html="index+1"></span>
				</div>
				<div class="vote-btns">
					<div class="vote-arrow up" @click="upvote(h)" v-bind:class="{'voted': h.voted==1}"><i class="fas fa-arrow-alt-circle-up"></i></div>
					<div class="score" v-bind:title="'+'+h.upvotes+' -'+h.downvotes">@{{ h.upvotes-h.downvotes }}</div>
					<div class="vote-arrow down" @click="downvote(h)" v-bind:class="{'voted': h.voted==0}"><i class="fas fa-arrow-alt-circle-down"></i></div>
				</div>
				<div class="post-content">
					<div class="">
						<div>
							<div class="post-title" @click="expandPost(h)" v-bind:class="{minimized: h.minimized}">
								<div class="" v-html="h.title"></div>
							</div>
							<div class="post-description" v-if="!h.minimized">
								<div class="description" v-html="h.description"></div>
							</div>
						</div>
						<div class="post-date" v-show="!h.minimized">
							Submitted 
							<time v-bind:title="h.created_at" class="">@{{ h.created_at | fromNow }}</time> 
							by <span v-html="h.username"></span>
						</div>
						<ul style="" class="post-buttons" v-show="!h.minimized">
							<li>
								<div class="link" @click="quickViewPost(h)">Quick View</div>
							</li>
							<li>
								<a v-bind:href="'/hooks/'+h.title">Full View</a>
							</li>
							<li class="post-num-comments">
								<div>0 comments</div>
							</li>
							<li class="post-share">
								<a href="#" class="">share</a>
							</li>
							<li class="post-save">
								<a href="#">save</a>
							</li>
							<li class="post-report-button">
								<a href="#">report</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="post-minimize-btn" @click="toggleMinimized(h)">
					<i v-show="!h.minimized" class="far fa-minus-square"></i>
					<i v-show="h.minimized" class="far fa-plus-square"></i>
				</div>
			</div>
		</div>

		<div class="load-more button" @click="loadMore()">
			Load More
		</div>
	</div>



	<div id="new-post-modal" class="modal" v-bind:class="{'is-active': showingNewPost}">
		<div class="modal-background" @click="hideNewPost()"></div>
		<div class="modal-content">
			<div class="has-text-centered">
				<div class="box">
					<div class="modal__title">Submit a new @{{postType}}</div>
					<div class="error-field" v-if="newPost.titleError.length>0" v-html="newPost.titleError"></div>
					<div class="field">
						<div class="control">
							<input v-model="newPost.title" id="post-title" class="input is-large" type="text" placeholder="Title" autofocus="">
						</div>
					</div>

					<div class="error-field" v-if="newPost.bodyError.length>0" v-html="newPost.bodyError"></div>
					<div class="field">
						<div class="control">
							<textarea v-model="newPost.body" id="post-body" class="textarea is-large" type="text" :placeholder="postType" rows="5"></textarea>
						</div>
					</div>
					<div class="button is-block is-info is-large is-fullwidth" @click="submitPost()">Submit</div>
					<div class="error-field" v-if="newPost.ajaxError.length>0" v-html="newPost.ajaxError"></div>
				</div>
			</div>
		</div>
		<button class="modal-close is-large" aria-label="close" @click="hideNewPost()"></button>
	</div>


	<div class="overlay overlay-animated" v-bind:class="{'open': showingPost, 'close': !showingPost}">
		<div class="overlay-close" @click="toggleShowingPost()"><i class="fas fa-times"></i></div>
		


		<div class="quick-view">
			<div class="post-header">
				<div class="vote-btns">
					<div class="vote-arrow up" @click="upvote(currPost)" v-bind:class="{'voted': currPost.voted==1}"><i class="fas fa-arrow-alt-circle-up"></i></div>
					<div class="score" v-bind:title="'+'+currPost.upvotes+' -'+currPost.downvotes">@{{ currPost.upvotes-currPost.downvotes }}</div>
					<div class="vote-arrow down" @click="downvote(currPost)" v-bind:class="{'voted': currPost.voted==0}"><i class="fas fa-arrow-alt-circle-down"></i></div>
				</div>
				<div class="post-content">
					<div class="">
						<div>
							<div class="post-title">
								<div class="" v-html="currPost.title"></div>
							</div>
							<div class="post-description">
								<div class="description" v-html="currPost.description"></div>
							</div>
						</div>
						<div class="post-date">
							Submitted 
							<time v-bind:title="currPost.created_at" class="">@{{ currPost.created_at | fromNow }}</time> 
							by <span v-html="currPost.username"></span>
						</div>
						<ul style="" class="post-buttons">
							<li>
								<a v-bind:href="'/hooks/'+currPost.title">Full View</a>
							</li>
							<li class="post-share">
								<a href="#" class="">share</a>
							</li>
							<li class="post-save">
								<a href="#">save</a>
							</li>
							<li class="post-report-button">
								<a href="#">report</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="post-comments">
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

				<!--
				<div class="post-comment" v-for="(c, index) in currPostComments" :key="c.id">
					<div class="comment-vote-btns">
						<div class="vote-arrow up" @click="upvote(currPost)" v-bind:class="{'voted': c.voted==1}"><i class="fas fa-arrow-alt-circle-up"></i></div>
						<div class="vote-arrow down" @click="downvote(currPost)" v-bind:class="{'voted': c.voted==0}"><i class="fas fa-arrow-alt-circle-down"></i></div>
					</div>
					<div>
						<div class="comment-body" v-html="c.comment"></div>
						<div class="comment-header">
							<span class="comment-score" v-bind:title="'+'+c.upvotes+' -'+c.downvotes">@{{ c.upvotes-c.downvotes }} points</span>
							- Posted by <span class="comment-author" v-html="c.username"></span>
							<span class="comment-date">@{{ c.created_at | fromNow }}</span>
						</div>
					</div>
				</div>
			-->

				<comment-list :comments="currPostComments"></comment-list>
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

		var GET_URL = '{{ url('api/hooks') }}';
		var POST_TYPE = "hook";
		var VOTE_URL = '{{ url('api/vote') }}';
		var SORT_BY_METHODS = ["r", "uv", "dv", "dd", "da"];
		var POST_TYPE_PRETTY = 'Plot Hook';
		var LOGIN_TITLE='Login';
		var REGISTER_TITLE='Create an Account';
		var SUBMIT_POST_URL = '{{ url('api/hooks/new') }}';
		var SUBMIT_COMMENT_URL = '{{ url('api/comments/new') }}';
		var UPDATE_COMMENT_URL = '{{ url('api/comments/update') }}';
		var GET_COMMENTS_URL = '{{ url('api/comments/get') }}';
		var VOTE_ON_COMMENT_URL = '{{ url('api/comments/vote') }}';
		var D20_ANIM_DONE = false;
		var VUE_LOADED = false;
		var USERNAME = '{{ Auth::user()->username }}';

		var d20Interval = setInterval(checkD20Anim, 300);

		function checkD20Anim() {
			var e = document.getElementById('d20_outside');
			var s = window.getComputedStyle(e);
			var sdo = s.getPropertyValue('stroke-dashoffset');
			if (sdo=='0px') {
				clearInterval(d20Interval);
				if (VUE_LOADED) {
					document.querySelector('.vue-loader').setAttribute("style", "display:none");
					document.querySelector('.section-content').setAttribute("style", "display:block");
				}
				D20_ANIM_DONE=true;
			}
		}

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
			mounted(){
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
					<comment-list v-if="comment.children.length" v-bind:comments="comment.children"></comment-list>
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
				postType: POST_TYPE_PRETTY,
				sortByMethod: 0,
				sortByRandomSeed: Math.floor(Math.random() * 1000000),
				pageNum: 0,
				posts: null,
				cachedPosts: [[],[],[],[],[]],
				cachedPageNums: [0,0,0,0,0],

				showingNewPost: false,
				newPostAjaxError: null,
				newPost: {
					title: "",
					body: "",
					titleError: "",
					bodyError: "",
					ajaxError: "",
					comments: []
				},

				currPost: {
					title: "",
					body: ""
				},
				currPostComments: [],

				newComment: {
					body: "",
					bodyError: "",
					ajaxError: "",
				},

				showingPost: false,

			},
			created: function () {
				document.querySelector('.vue-loader').setAttribute("style", "display:block");
			},
			mounted: function() {
				this.getPosts(0);
				if (D20_ANIM_DONE) {
					document.querySelector('.vue-loader').setAttribute("style", "display:none");
					document.querySelector('.section-content').setAttribute("style", "display:block");
				}
				VUE_LOADED=true;
			},
			methods: {
				getPosts: function() {
					axios.post(GET_URL, {
						page: this.pageNum,
						method: SORT_BY_METHODS[this.sortByMethod],
						seed: this.sortByRandomSeed
					}, config)
					.then(function (response) {
						if (response.data && response.data.length>0) {
							if (app.posts == null) { app.posts = []; }
							while(response.data.length) {
								response.data[0].minimized=false;
								app.posts.push(response.data.shift());
							}
							app.pageNum+=1;
						}
						else {
							//??
						}
					})
					.catch(function (error) {
						console.log(error);
					});
				},
				loadMore: function() {
					//TODO get more posts before user requests, show when user requests, then begin the next load, so that it is instant.
					this.getPosts();
				},
				toggleMinimized: function(p) {
					p.minimized = !p.minimized;
				},
				expandPost: function(p) {
					if (p.minimized) p.minimized=false;
				},
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
				changeSortMethod: function(sbm, event) {
					//Cache the current posts and page num for faster reloading if they come back to this sort method
					this.cachedPosts[this.sortByMethod] = clone(this.posts);
					this.cachedPageNums[this.sortByMethod] = this.pageNum;

					sbm = event.target.value;

					if (this.sortByMethod == sbm) {
						//No Change in sort method
						return;
					}

					this.sortByMethod = sbm;
					
					//Check if a cache of posts already exists for this new sort method
					if (this.cachedPosts[sbm].length>0) {
						this.pageNum = this.cachedPageNums[sbm];
						this.posts=this.cachedPosts[sbm];
						this.$set(this.posts, 0, this.cachedPosts[sbm][0]);
					}
					else {
						this.posts = [];
						this.pageNum=0;
						this.loadMore();
					}
				},
				showNewPost: function() {
					if (!this.checkLoggedIn("Please login to submit a post")) { return; }

					this.showingNewPost=true;
				},
				hideNewPost: function() {
					this.showingNewPost=false;
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
				clearNewPost: function() {
					this.newPost = {
						title: "",
						body: "",
						titleError: "",
						bodyError: "",
						ajaxError: ""
					};
					/*
					//This should also work if all properties can be initialized to ""
					for (var key in this.newPost) {
						if (this.newPost.hasOwnProperty(key)) {
							this.newPost[key] = "";
						}
					}
					*/
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

				quickViewPost: function(p) {
					this.currPost = p;
					this.showingPost=true;

					//Hide scrollbar on body content. Set on delay to sync with css transition of 0.4s
					setTimeout(hideBodyScrolling, 400);

					this.loadComments();
				},
				toggleShowingPost: function() {
					this.showingPost = !this.showingPost;
					if (this.showingPost) {
						//Hide scrollbar on body content. Set on delay to sync with css transition of 0.4s
						setTimeout(hideBodyScrolling, 400);						
					}
					else {
						//Restore scrollbar on body content
						showBodyScrolling();
					}
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
				processComments: function(c) {
					this.currPostComments = [];
					
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
						this.currPostComments.push(roots[i]);
					}

					//console.log(this.currPostComments);
					
					/*
					for (var i=0; i<c.length; i++) {
						this.currPostComments.push(c[i]);
					}
					*/
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
				fromNow: function(v) {
					if (moment(v).isValid()) {
						return moment(v + 'Z', 'YYYY-MM-DD HH:mm:ssZ').fromNow(); //'Z' converts to local time zone
					}
					console.log(v);
					return v;
				},
			},
		});

		function hideBodyScrolling() {
			document.documentElement.style.overflow = 'hidden';
			document.body.scroll = "no";
		}
		function showBodyScrolling() {
			document.documentElement.style.overflow = 'auto';
			document.body.scroll = "yes";
		}

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