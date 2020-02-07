<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: index1.php
# Description: 
# Version: 2.8.1
###


$context="index1.php";

require("./../config.php");
require("iptlib.php");
require("ubaselib.php");
require("statlib.php");

$flAdminsOnly=FALSE;
require("auth.php");

print("<html>\n");
require("include/head1.php");

if( !isset($_SESSION["procinfo"]["name"])) $_SESSION["procinfo"]["name"]="Fantomas Iptconf";


print("<body>\n");
print("<table class=table2 cellpadding=\"0px\" width=\"75%\"><tr><td> \n");

print("<font class=top3 style=\"FONT: bold 18pt Tahoma;color:000080\"> ".$_SESSION["procinfo"]["name"]."</font><font style=\"FONT: italic 16pt Tahoma;color:000080;\"> web manager</font><br>\n");
print("<font style=\"FONT: 11pt Tahoma;color:000080\"> Добро пожаловать в открытую систему управления маршрутизатором на базе Iptables/Netfilter! </font>\n");

if( isset($_SESSION["updinfo"])) {
    print($_SESSION["updinfo"]); 
}

print("<br><hr width=\"400px\" align=left size=1>\n");

print("<iframe src=\"sysstat.php?p=system\" frameborder=\"0\" name=\"sysinfo\" scrolling=\"no\" width=\"700px\" height=\"650px\" vspace=\"0\" hspace=\"0\">\n");
print("Ваш браузер не поддерживает фреймы.\n");
print("</iframe>\n");

print("</td></tr></table>\n</body>\n</html>");

?>