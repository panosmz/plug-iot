<?php

require 'site-functions/site-functions.php';
session_start();
?>
<!doctype html>
<html lang="en">

<?php echo_head("Register | Plug"); ?>

<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">

<?php echo_navbar_nologin(); ?>

<main class="mdl-layout__content login-page mdl-color--grey-800">
<style type="text/css">
	
	.login-box {
		height: 380px;
		margin-top: 0;
		padding: 10px 20px;
	}
</style>

<div class="login-box mdl-typography--text-left mdl-color--blue-grey-700 mdl-color-text--blue-grey-100">
	<div class="mdl-grid">
		<div class="mdl-layout-spacer"></div>
		<div class="mdl-cell mdl-cell--4-col">
			<h5>Register</h5>
			<p class="login-error-message">Login errors</p>
			<form id="registerForm" action="#">
				<div class="mdl-textfield mdl-js-textfield">
					<input class="mdl-textfield__input " type="text" id="username">
					<label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="username">Username</label>
				</div><br>
				<div class="mdl-textfield mdl-js-textfield">
					<input class="mdl-textfield__input " type="password" id="password">
					<label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="password">Password</label>
				</div><br>
				<div class="mdl-textfield mdl-js-textfield">
					<input class="mdl-textfield__input " type="text" id="email">
					<label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="email">E-mail</label>
				</div><br>
			</form>
			<div class="mdl-grid mdl-typography--text-center">
				<div class="mdl-layout-spacer"></div><button id="registerButton" class="mdl-button mdl-js-button mdl-button--primary">Register</button>
			</div>
		</div>
		<div class="mdl-layout-spacer"></div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$('#registerButton').click(function() {
			postRegisterForm();
		});
	})
	function postRegisterForm() {
		var fUsername = $('#username').val();
		var fPassword = $('#password').val();
		var fEmail = $('#email').val();

		if(fUsername === '' || fPassword === '' || fEmail === '') {
			showError('Please fill in all fields');
		} else {
		$.ajax({
			method: "POST",
			url: "api/plug_api_ajax.php",
			data: { action: "register", username: fUsername, password: fPassword, email: fEmail }
		})
		.done(function(msg){

			var parsedMsg = jQuery.parseJSON(msg);

			if(parsedMsg.success == "SUCCESS") {
				//\
				$('#registerForm').hide();
				$('#registerButton').hide();
				$('.login-error-message').show();
				$('.login-error-message').html('Registered successfully. Click <a href="/login.php">here</a> to login.');
				$('.login-error-message').addClass('login-success-message');
			} else {
				var errorMessage = parsedMsg.message;
				if(errorMessage == 'Register Error') {
					errorMessage = parsedMsg.registerMessage;
				}
				showError(errorMessage);
			}

		})};
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