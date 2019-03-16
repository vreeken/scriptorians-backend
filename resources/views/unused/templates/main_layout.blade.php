<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		{{-- CSRF Token --}}
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<title>@if (trim($__env->yieldContent('title'))) @yield('title') @else DM Companion @endif</title>
		<meta name="description" content="DM Companion: A Dungeon Master's Best Friend. Made for D&D 5e, usable by all.">
		<meta name="author" content="Michael Vreeken">

		<link rel="apple-touch-icon" sizes="180x180" href="{{ url('/apple-touch-icon.png') }}">
		<link rel="icon" type="image/png" sizes="32x32" href="{{ url('/favicon-32x32.png') }}">
		<link rel="icon" type="image/png" sizes="16x16" href="{{ url('/favicon-16x16.png') }}">
		<link rel="manifest" href="{{ url('/site.webmanifest') }}">
		<link rel="mask-icon" href="{{ url('/safari-pinned-tab.svg') }}" color="#43aef1">
		<meta name="msapplication-TileColor" content="#43aef1">
		<meta name="theme-color" content="#ffffff">

		{{-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries --}}
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

		
		{{-- Styles --}}
		<link href="https://fonts.googleapis.com/css?family=Abril+Fatface|Montserrat:400,600" rel="stylesheet">

		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.1/css/bulma.min.css">

		<link href="{{ url('/css/app.css') }}" rel="stylesheet">

		@yield('extra_header')

		<style type="text/css">
			

			
			@yield('extra_css')
		</style>
		{{-- Scripts --}}
		<!--<script> window.Laravel = {!! json_encode(['csrfToken' => csrf_token(),]) !!}; </script>-->


	</head>
	<body>
		<div id="app" >
				@include('templates.partials.nav')
				@include('templates.partials.login-modal')

				<div class="body-container">
				
					<div class="container alerts-container">
						@include('templates.partials.form-status')
					</div>

					<div class="content-container-full">
					   @yield('content')
					</div>
				</div>

				@include('templates.partials.footer')
				
		</div>
		{{-- Scripts --}}
		@yield('footer_scripts')

		<script src="{{ url('/js/common.js') }}"></script>
	</body>
</html>