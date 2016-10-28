<?php

	$con = mysqli_connect("localhost","#USER#","#PASS#","#DB#");
	
	if (mysqli_connect_errno())
	  {
	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
	  }
	else {
		mysqli_set_charset($con, "utf8");
	}
?> 

