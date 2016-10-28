<?php
session_start();

require_once 'api/plug_api_functions.php';
token_remove($_SESSION['token']);
session_unset();
session_destroy();

header("location:index.php");
exit();
?>