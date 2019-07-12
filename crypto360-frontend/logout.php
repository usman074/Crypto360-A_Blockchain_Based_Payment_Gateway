<?php
	session_start();
    $_SESSION['firstName']=="";
	session_unset();
	session_destroy();
	header('Location: index.php');
?>
