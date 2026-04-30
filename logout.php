<?php
require_once "db/config.php";

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>