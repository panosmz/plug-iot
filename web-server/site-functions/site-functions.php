<?php

function user_access() {

	if(isset($_SESSION['username']) && isset($_SESSION['token'])) {

		return true;
	} else {
		?>
		<div><h5>Please <a href="/login.php">login</a> to continue.</h5></div>
		<?php
	}
}

function echo_head($title) {
	?>
	<head>
		<meta charset="utf-8">
		<meta name="description" content="Open-source internet-of-things controller.">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<meta name="theme-color" content="#00a2cc">
      <link rel="shortcut icon" href="img/favicon-2.png" />
    	<title><?php echo $title; ?></title>
    	<link href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en" rel="stylesheet">
		<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
		<link rel="stylesheet" href="https://code.getmdl.io/1.1.3/material.blue-red.min.css">
		<link rel="stylesheet" href="override.css">
		<script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	</head>
	<?php
}

function echo_navbar_nologin() {
	?>
	<header class="mdl-layout__header plug-bg-blue mdl-color-text--white">
	<div class="mdl-layout__header-row">
		<span class="mdl-layout-title"><a href="index.php"><img src="img/logo.svg" alt="Logo" height="30" id="logo-plug"></a></span>
		<div class="mdl-layout-spacer"></div>
		<nav class="mdl-navigation mdl-layout--large-screen-only">
			<a class="mdl-navigation__link mdl-color-text--white" href="login.php">Login</a>
			<a class="mdl-navigation__link mdl-color-text--white" href="register.php">Register</a>
		</nav>
	</div>
</header>
<div class="mdl-layout__drawer mdl-color--blue-grey-50">
	<span class="mdl-layout-title"><a href="index.php"><img src="img/logo-b.svg" alt="Logo" height="30" id="logo-plug"></a></span>
	<nav class="mdl-navigation">
		<a class="mdl-navigation__link" href="login.php">Login</a>
		<a class="mdl-navigation__link" href="register.php">Register</a>
	</nav>
</div>
	<?php
}

function echo_navbar($selected) {
	$email = $_SESSION['email'];
	if(strlen($email) > 23) {
		$email = mb_substr($email, 0, 20) . "...";
	}
  session_start();
	?>
	<header class="mdl-layout__header plug-bg-blue">
    <div class="mdl-layout__header-row">
    	<span class="mdl-layout-title"><img src="img/logo.svg" alt="Logo" height="30" id="logo-plug" style="margin-right: 20px"><?php echo $selected; ?></span>
      
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable
                  mdl-textfield--floating-label mdl-textfield--align-right">
        <label class="mdl-button mdl-js-button mdl-button--icon"
               for="fixed-header-drawer-exp">
          
        </label>
        <div class="mdl-textfield__expandable-holder">
          <input class="mdl-textfield__input" type="text" name="sample"
                 id="fixed-header-drawer-exp">
        </div>
      </div>
    </div>
  </header>
  <div class="mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">
  	<header class="demo-drawer-header">
          <div class="demo-avatar-dropdown">
            <span><?php echo $email; ?></span>
            <div class="mdl-layout-spacer"></div>
            <button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
              <i class="material-icons" role="presentation">arrow_drop_down</i>
              <span class="visuallyhidden">Accounts</span>
            </button>
            <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="accbtn">
              <a class="menu-link-a" href="account-settings.php"><li class="mdl-menu__item">Account Settings</li></a>
              <a class="menu-link-a" href="logout.php"><li class="mdl-menu__item">Logout</li></a>
            </ul>
          </div>
        </header>
    <nav class="demo-navigation mdl-navigation mdl-color--blue-grey-50">
          <a class="mdl-navigation__link <?php if($selected==="Overview"){echo "nav-active";} ?>" href="overview.php"><i class="mdl-color-text--blue-grey-800 material-icons" role="presentation">home</i>Overview</a>
          <a class="mdl-navigation__link <?php if($selected==="Devices"){echo "nav-active";} ?>" href="devices.php"><i class="mdl-color-text--blue-grey-800 material-icons" role="presentation">power</i>Devices</a>
          <a class="mdl-navigation__link <?php if($selected==="Shared"){echo "nav-active";} ?>" href="#"><i class="mdl-color-text--blue-grey-800 material-icons" role="presentation">share</i>Shared</a>
          <a class="mdl-navigation__link <?php if($selected==="Guides"){echo "nav-active";} ?>" href="#"><i class="mdl-color-text--blue-grey-800 material-icons" role="presentation">insert_drive_file</i>Guides</a>
          <div class="mdl-layout-spacer"></div>
          <a class="mdl-navigation__link <?php if($selected==="About"){echo "nav-active";} ?>" href="https://github.com/panosmz/plug-iot"><i class="mdl-color-text--blue-grey-800 material-icons" role="presentation">help_outline</i>About</a>
        </nav>
  </div>
  <script type="text/javascript">
     var userToken = "<?php echo $_SESSION['token']; ?>";
  </script>
  <?php
}

function echo_footer() {
	?>
	<footer class="mdl-mini-footer">
  		<div class="mdl-mini-footer__left-section">
   		 <div class="mdl-logo">Plug</div>
    	 <ul class="mdl-mini-footer__link-list">
      		<li><a href="https://github.com/panosmz/plug-iot">About</a></li>
      		<li><a href="#">Privacy & Terms</a></li>
      		<li><a href="https://github.com/panosmz/plug-iot">Github</a></li>
          <li><a href="https://panosmazarakis.com/">© 2016 Panos Mazarakis</a></li>
    	</ul>
  		</div>
	</footer>
	<?php
}

function echo_card_noDevices() {
  ?>
  <div class="mdl-card mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col mdl-grid">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text">Welcome</h2>
      </div>
      <div class="mdl-card__supporting-text">
      You have no devices assigned to your account.
      </div>
      <div class="mdl-card__actions mdl-card--border">
          <a href="/add-device.php" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
            Add a device
          </a>
      </div>
    </div>
  <?php
}
?>