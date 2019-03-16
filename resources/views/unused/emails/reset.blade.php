<!DOCTYPE html>
<html><head>
</head><body style="width:100%">
	<h1>RPG Companion</h1>
	<p>Reset your password</p>
	
	<p>Clicking this link should open up the RPG Companion app where you will be asked to input a new password:
		<br>
		<a href="{{url('api/reset-password?t=' . $token)}}">{{url('api/reset-password?t=' . $token)}}</a>
	</p>
	<p>Thank you for using RPG Companion.</p>
</body></html>
