<?php

require_once("../../private/config.php");
require_once("database.php");

$db = connect_to_db();
regenerate_database($db);

?>
