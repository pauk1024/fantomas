#!/bin/php
<?php


if( file_exists("./../config.php")) require("./../config.php");

if( file_exists("./../www/authlib.php")) require("./../www/authlib.php");

$mysql_host=( ! isset($argv[1])) ? "" : $argv[1];
$mysql_user=( ! isset($argv[2])) ? "" : $argv[2];
$mysql_password=( ! isset($argv[3])) ? "" : $argv[3];
$_passw_crypt_key=( ! isset($argv[4])) ? "" : $argv[4];
$mysql_path=( ! isset($argv[5])) ? "" : $argv[5];

$muscul="";
if(( isset($_mysql)) and ( is_readable($_mysql))) {
    $muscul=$_mysql;
} else {
    $muscul=( isset($mysql_path)) ? trim($mysql_path):"";
}

if( ( trim($mysql_host)=="") or ( trim($mysql_user)=="") or ( trim($mysql_path)=="")) {
    print("Usage: create_db.php mysql-host mysql-user mysql-user-password passw-crypt-key [path-to-bin-mysql]\n");
    exit;
}

exec("$muscul -u ".$mysql_user." -p".$mysql_password." < mysql.table");
$admin_def_pass=encrypt0("admin");
$link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
mysql_set_charset("koi8r",$link);
if( !$link) { echo "Error connecting to mysql!\n"; exit; }

mysql_select_db("fantomas");
$result=mysql_query("INSERT INTO users SET username=\"admin\",userpass=\"$admin_def_pass\",description=\"Administrator\",isadmin=1,islocked=0,v=3");

#$ulogdb=( isset($ulog_dbname)) ? $ulog_dbname : "ulogd";

#mysql_select_db($ulogdb);
#$result=mysql_query("ALTER TABLE `ulog` ADD INDEX ( `oob_time_sec` ), ADD INDEX ( `is_saddr` ), ADD INDEX ( `is_daddr` ) ");



?>