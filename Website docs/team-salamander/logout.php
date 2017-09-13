<?php
/* Start Session */
if(!isset($_SESSION))
{
session_start();
}

/* Inluded files */
require_once('template.php');
require_once('../private/config.php');
require_once('./private/database.php');


$_SESSION["loggedin"] = false;
$_SESSION["userid"] = "";
$_SESSION['errormessage'] = "";

// Unset all Sessions
$_SESSION = array();

$_SESSION['backpage'] = 'index.php';
$_SESSION['responsemessage'] = "You are now logged out, thank you for using Salamander Book Exchange";
header("Location: responsepage.php");
exit;
?>
