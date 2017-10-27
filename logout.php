<?php
session_start();
$_SESSION['valid'] = "Logout";
include '../verify_iron.php';
?>