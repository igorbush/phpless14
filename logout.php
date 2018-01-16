<?php 
require_once("getdb.php");
session_destroy();
header("location: index.php");
?>