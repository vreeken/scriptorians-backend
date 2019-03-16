<div id="login__modal" class="modal">
	<div class="modal-background"></div>
	<div class="modal-content">
		<div class="has-text-centered login-container">

			<!--Login tab-->
			<div id="login__modal__login" class="column login-tab active">
				<div class="box">
					<div class="modal__title" v-html="loginTitle">Login</div>
					<figure class="avatar">
						<img src="{{ url('/img/logo.svg') }}" onerror="this.onerror=null; this.src='{{ url('/img/logo.png') }}'">
					</figure>
					<form>
						<div class="error-field" id="login-email-error" style="display: none;"></div>
						<div class="field">
							<div class="control">
								<input id="login-email" class="input is-large" type="email" placeholder="Email" autofocus="">
							</div>
						</div>

						<div class="error-field" id="login-password-error" style="display: none;"></div>
						<div class="field">
							<div class="control">
								<input id="login-password" class="input is-large" type="password" placeholder="Password">
							</div>
						</div>
						<div class="field">
							<label class="checkbox">
								<input id="login-remember" type="checkbox">
								Remember me
							</label>
						</div>
						<div class="error-field" id="login-error" style="display: none;"></div>
						<div id="login-btn" class="button is-block is-info is-large is-fullwidth">Login</div>						
					</form>
				</div>
				<p class="login__modal__footer has-text-grey column is-8 is-offset-2">
					<a onclick="loginModalChangeTab('r')">Sign Up</a> &nbsp;·&nbsp;
					<a href="../">Forgot Password</a> &nbsp;·&nbsp;
					<a href="../">Need Help?</a>
				</p>
			</div>

			<!--Register tab-->
			<div id="login__modal__register" class="column login-tab">
				<div class="box">
					<div class="modal__title" v-html="registerTitle">Create an Account</div>
					<figure class="avatar">
						<img src="{{ url('/img/logo.svg') }}" onerror="this.onerror=null; this.src='{{ url('/img/logo.png') }}'">
					</figure>
					<form>
						<div class="error-field" id="register-username-error" style="display: none;"></div>
						<div class="field">
							<div class="control">
								<input id="register-username" class="input is-large" type="text" placeholder="Username" autofocus="">
							</div>
						</div>

						<div class="error-field" id="register-email-error" style="display: none;"></div>
						<div class="field">
							<div class="control">
								<input id="register-email" class="input is-large" type="email" placeholder="Email" autofocus="">
							</div>
						</div>

						<div class="error-field" id="register-password-error" style="display: none;"></div>
						<div class="field">
							<div class="control">
								<input id="register-password" class="input is-large" type="password" placeholder="Password">
							</div>
						</div>
						<div class="field">
							<div class="control">
								<input id="register-password-confirm" class="input is-large" type="password" placeholder="Confirm Password">
							</div>
						</div>
						<div class="field">
							<label class="checkbox">
								<input id="register-remember" type="checkbox">
								Remember me
							</label>
						</div>
						<div class="error-field" id="register-error" style="display: none;"></div>
						<div id="register-btn" class="button is-block is-info is-large is-fullwidth">Create Account</div>						
					</form>
				</div>
				<p class="login__modal__footer has-text-grey column is-8 is-offset-2">
					<a onclick="loginModalChangeTab('l')">Already have an account?</a> &nbsp;·&nbsp;
					<a href="../">Need Help?</a>
				</p>
			</div>
		</div>
	</div>
	<button class="modal-close is-large" aria-label="close"></button>
	<form id="frm-logout" action="{{ url('auth/logout') }}" method="POST" style="display: none;">{{ csrf_field() }}<form>
</div>