<?php

require 'site-functions/site-functions.php';

session_start();

?>
<!doctype html>
<html lang="en">

<?php echo_head("Login | Plug"); ?>

<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">

<?php echo_navbar_nologin(); ?>

<main class="mdl-layout__content login-page mdl-color--grey-800">
<style type="text/css">
	
	.login-box {
		height: 340px;
		margin-top: 0;
		padding: 10px 20px;
	}
</style>

<div class="login-box mdl-typography--text-left mdl-color--blue-grey-800 mdl-color-text--blue-grey-100">
	<div class="mdl-grid">
		<div class="mdl-layout-spacer"></div>
		<div class="mdl-cell mdl-cell--4-col">
			<h5>Login</h5>
			<p class="login-error-message">Login errors</p>
			<form id="loginForm" action="#">
				<div class="mdl-textfield mdl-js-textfield">
					<input class="mdl-textfield__input " type="text" id="username">
					<label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="username">Username</label>
				</div><br>
				<div class="mdl-textfield mdl-js-textfield">
					<input class="mdl-textfield__input " type="password" id="password">
					<label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="password">Password</label>
				</div><br>
			</form>
			<div class="mdl-grid mdl-typography--text-center">
				<div class="mdl-layout-spacer"></div><button id="loginButton" class="mdl-button mdl-js-button mdl-button--primary">Login</button><a class="mdl-button mdl-js-button mdl-color-text--blue-grey-300" href="register.php">Register</a>
			</div>
		</div>
		<div class="mdl-layout-spacer"></div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$('#loginButton').click(function() {
			postLoginForm();
		});
	})

	function postLoginForm() {
		var fUsername = $('#username').val();
		var fPassword = $('#password').val();

		if(fUsername === '' || fPassword === '') {
			showError('Please fill in both fields');
		} else {
			
			$.ajax({
				method: "POST",
				url: "api/plug_api_ajax.php",
				data: { action: "login", username: fUsername, password: fPassword, client: "web" } 
			})
			.done(function(msg){
				var parsedMsg = jQuery.parseJSON(msg);
				if(parsedMsg.success == "SUCCESS") {
					showError('Login sucessfull. Please wait...');
					$('.login-error-message').addClass('login-success-message');
					window.location.replace("/overview.php");
				} else {
					var errorMessage = parsedMsg.message;
					showError(errorMessage);
				}
			})
		};
	}

	function showError(message) {
		$('.login-error-message').show();
		$('.login-error-message').html(message);
	}

</script>

<?php echo_footer(); ?>


</div>
</main>
</div>
</body>