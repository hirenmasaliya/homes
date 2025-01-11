<?php

$hostname = "localhost";
$username = "root";
$passwrod = "";
$database = "society_management";

$conn = new mysqli($hostname,$username,$passwrod,$database);

if($conn->connect_error){
    die("Connection Error");
}


?>