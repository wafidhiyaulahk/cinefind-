<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database = "coda3424_cinefind";

$db = mysqli_connect($hostname, $username, $password, $database);

if ($db ->connect_error) {
    die("Connection failed: " . mysqli_connect_error());
}

?>