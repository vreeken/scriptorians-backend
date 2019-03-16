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