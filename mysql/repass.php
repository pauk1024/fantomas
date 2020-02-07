#!/bin/php
<?php

require("./../config.php");
require("./../www/authlib.php");

$user="admin";
$pass="admin";

$link=mysql_connect($mysql_host,$mysql_user,$mysql_password)
or die("could not connect to mysql...");
mysql_set_charset("koi8r",$link);
mysql_select_db("fantomas");
#$pass=crypt($pass,$_passw_crypt_key);
$pass=encrypt0($pass);
#mysql_query("INSERT INTO users SET username=\"$user\",isadmin=1,islocked=0,v=3");
mysql_query("UPDATE users SET userpass=\"$pass\",v=3 WHERE username=\"$user\"");

mysql_close($link);


?>