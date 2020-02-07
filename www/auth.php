<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.5
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: auth.php
# Description: auth
# Version: 0.2.5
###

if( session_id()=="") session_start();

$username=( isset($_SESSION["fantomsuser"])) ? $_SESSION["fantomsuser"] : "";
$userpass=( isset($_SESSION["fantomsuserpass"])) ? $_SESSION["fantomsuserpass"] : "";
$sessid=( isset($_SESSION["fantomssessid"])) ? $_SESSION["fantomssessid"] : "";


if( !isset($flAdminsOnly)) $flAdminsOnly=FALSE;

require("authlib.php");

if( (trim($username)=="") or (trim($userpass)=="") or (trim($sessid)=="")) {
    print("<html>");
    require("include/head1.php");
    print("<body>\n");
    print(gtext("auth","access_denied")."<br><a href=\"index.php?logout=logout\" target=\"_top\">".gtext("auth","enter")."</a><br>\n");
    if( $_logs_enable) wlog(gtext("auth","notauth_enter_try")." ".$_SERVER["PHP_SELF"].": username .$username. userpass .$userpass. sessid .$sessid.",2,FALSE,1,FALSE);
    exit;
} else {
    if( !CheckPassword($username,$userpass,$flAdminsOnly,$sessid,TRUE)) {
        print("<html>");
	require("include/head1.php");
	print("<body>\n");
	print(gtext("auth","error_login")."<br><br>\n");
	print("<a href=\"index.php?logout=logout\" target=\"_top\">".gtext("auth","try_again")."</a>");
	if( $_logs_enable) wlog(gtext("auth","password_error")." ".$_SERVER["PHP_SELF"].": username .$username. userpass .$userpass. sessid .$sessid.",2,FALSE,1,FALSE);
	exit;
    } 
}
?>