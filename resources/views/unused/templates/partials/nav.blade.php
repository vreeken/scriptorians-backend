<nav class="navbar has-shadow">
	<div class="navbar-brand">
		<a class="navbar-item" href="../"><img src="{{ url('img/logo_simple.svg') }}" alt="Scriptorians" onerror="this.onerror=null; this.src='{{ url('img/logo_simple.png') }}'"/></a>

		<div class="navbar-burger burger" data-target="navMenu"><span></span><span></span><span></span></div>
	</div>
	<div class="navbar-menu" id="navMenu">
		<div class="navbar-start is-link">
			<div class="navbar-item has-dropdown is-hoverable">
				<a class="navbar-link">Scriptures</a>
				<div class="navbar-dropdown">
					<a class="navbar-item" href="{{ url('scriptures/ot') }}">The Old Testament</a>
					<a class="navbar-item" href="{{ url('scriptures/nt') }}">The New Testament</a>
					<a class="navbar-item" href="{{ url('scriptures/bm') }}">The Book of Mormon</a>
					<a class="navbar-item" href="{{ url('scriptures/dc') }}">The Doctrine and Covenants</a>
					<a class="navbar-item" href="{{ url('scriptures/pgp') }}">The Pearl of Great Price</a>
				</div>
			</div>
			<a class="navbar-item" href="{{ url('scriptures/ot') }}">OT</a>
			<a class="navbar-item" href="{{ url('scriptures/nt') }}">NT</a>
			<a class="navbar-item" href="{{ url('scriptures/bm') }}">BoM</a>
			<a class="navbar-item" href="{{ url('scriptures/dc') }}">D&amp;C</a>
			<a class="navbar-item" href="{{ url('scriptures/pgp') }}">PoGP</a>
		</div>
		<div class="navbar-end">
		@if (Auth::check())
			<div class="navbar-item has-dropdown is-hoverable">
				<a class="navbar-link">Account</a>
				<div class="navbar-dropdown">
					<a class="navbar-item">Profile</a>
					<a class="navbar-item">Settings</a>
					<hr class="navbar-divider" />
					<a id="nav-logout" class="navbar-item" href="{{ url('auth/logout') }}">Logout</a>
				</div>
			</div>
		@else
			<a id="navbar-login" class="navbar-item" href="auth/login">
				Login
			</a>
			<a id="navbar-register" class="navbar-item" href="auth/logout">
				Register
			</a>
		@endif
		</div>
	</div>
</nav>