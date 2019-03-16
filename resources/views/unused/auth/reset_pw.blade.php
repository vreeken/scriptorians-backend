<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>RPG Companion</title>

		<!-- Fonts -->
		

		<!-- Styles -->

		<!-- JS -->
		
	</head>
	<body>
		<div class="flex-center position-ref full-height">
			<form id="pw_reset_form" action="" method="post">
				Password: <input id="pw" type="password" name="password"/>
				<br />
				Confirm Password: <input id="pw2" type="password" />
				<input type="hidden" value="{{$t}}" name="t"/>
				<br />
				<input type="submit">
			</form>
		</div>

	<script>
		document.getElementById("pw_reset_form").onsubmit = function(event) {
			event.preventDefault();

			if (document.getElementById("pw").value == document.getElementById("pw2").value) {
				document.getElementById("pw_reset_form").submit();
			}
			else {
				alert("Your passwords don't match");
			}
			return false;
		}
	</script>
	</body>
</html>
