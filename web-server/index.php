<?php

require 'site-functions/site-functions.php';

session_start();

?>

<!doctype html>
<html lang="en">

<?php echo_head("Plug"); ?>

<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">

<?php 

if(isset($_SESSION['username']) && isset($_SESSION['token'])) {
	echo_navbar("Overview"); 
} else {
	echo_navbar_nologin();
}


?>

<main class="mdl-layout__content">

<div class="first-section mdl-typography--text-center">
	<div class="logo-font slogan mdl-color-text--white">Open-source controller for IoT Smart Devices</div>
	<div class="logo-font subslogan mdl-color-text--white">Based on the ESP8266 WiFi Module</div>
</div>
<div class="second-section mdl-typography--text-center mdl-color--cyan-700 mdl-color-text--white">
	<h5>Make your own devices</h5>
	<p>Use the provided guides to learn how to make you own DIY internet-of-things devices.</p>
</div>
<div class="third-section mdl-typography--text-center mdl-color--cyan-800 mdl-color-text--white">
	<h5>Connect</h5>
	<p>Connect your devices to the internet and control them with this free open-source app.</p>
</div>

<?php echo_footer(); ?>

</div>
</main>
</div>
</body>