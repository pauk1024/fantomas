<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.3.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: logview.php
# Description: 
# Version: 0.2.3.1
###

require("./../config.php");
require("iptlib.php");
require("iptlib2.php");
require("statlib.php");

$flAdminsOnly=TRUE;
require("auth.php");


$NoHeadEnd=FALSE;

print("<html>\n");
require("include/head.php");



if(( !isset($_logs_dir)) or ( trim($_logs_dir)=="")) {
    wlog("�� ��������������� ���� � �������� ���-������, ������� ���� � �������� ��� ���-������ � ����� config.php � ���������� \$_logs_dir.",2,TRUE,5,TRUE); 
    exit;
}
if(( !isset($syslog)) or ( trim($syslog)=="")) {
    wlog("�� ���������������� ��� ���-�����.",2,TRUE,5,TRUE); 
    exit;
}
if(( !is_dir($_logs_dir)) or ( !is_writable($_logs_dir))) {
    wlog("�� �������� ������� ���-������.",2,TRUE,5,TRUE); 
    exit;
}
if(( !is_file($_logs_dir."/".$syslog)) or ( !is_writable($_logs_dir."/".$syslog))) {
    wlog("�� �������� ���-����.",2,TRUE,5,TRUE); 
    exit;
}

$_logs_dir=( $_logs_dir[strlen($_logs_dir)-1]=="/") ? substr($_logs_dir,0,-1) : $_logs_dir;

$usr=( isset($_GET["usr"])) ? $_GET["usr"] : "";
$mode=( isset($_GET["mode"])) ? $_GET["mode"] : ""; 
$run=( isset($_GET["run"])) ? TRUE : FALSE; 
$logfile=( isset($_GET["logfile"])) ? $_logs_dir."/".str_replace($_logs_dir."/","",trim($_GET["logfile"])) : $_logs_dir."/".$syslog;
$flShowAll=( substr_count((( isset($_GET["logfile"])) ? $_GET["logfile"]:""),"__all_files_")>0) ? TRUE : FALSE;
$date1=( isset($_GET["d1"])) ? $_GET["d1"]." 00:00:01" : "";
$date2=( isset($_GET["d2"])) ? $_GET["d2"]." 23:23:59" : "";
$order=( isset($_GET["order"])) ? $_GET["order"] : "";
$script="logview.php";


if( !$flShowAll) {
    if(( !file_exists($logfile)) or ( !is_readable($logfile))) {
	wlog("���� $logfile �� ���������� ��� ���������� ��� ������.",2,TRUE,5,TRUE); exit;
    }
}


#-------------------------------------------------------------------


function show_logs_form($logfile)
{
    global $mysql_host,$mysql_user,$mysql_password,$script;
    print("<br>\n");
    print("<font class=top1>������� ������� </font><br><br>\n");
    print("<table class=table4 width=\"350px\" cellpadding=\"3px\" style=\"padding-left:2px;\">\n");
    print("<form name=\"form2345\" action=\"$script\">\n");
    print("<input type=\"hidden\" name=\"run\" value=\"1\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"show\">\n");
    print("<input type=\"hidden\" name=\"logfile\" value=\"$logfile\">\n");
    print("<tr><td colspan=2><b><i> �� ����� </i></b></td></tr>\n");
    print("<tr><td> � </td><td> <input type=\"text\" name=\"d1\" id=\"d1\" size=20 value=\"".strftime("%d-%m-%Y")."\" /> </td></tr>\n");
    print("<tr><td> �� </td><td> <input type=\"text\" name=\"d2\" id=\"d2\" size=20 value=\"".strftime("%d-%m-%Y")."\" /> </td></tr>\n");
    if( !$lnk=mysql_connect($mysql_host,$mysql_user,$mysql_password)) {
	wlog("������ ���������� � MySQL. error: ".mysql_error(),2,TRUE,5,TRUE); exit;
    }
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$lnk);
    mysql_select_db("fantomas");
    if( !$rez=mysql_query("SELECT username FROM users ORDER BY username DESC")) {
	wlog("������ ������� � MySQL. error: ".mysql_error(),2,TRUE,5,TRUE); exit;
    }
    print("<tr><td><b><i> �� ������ </i></b></td><td> <span class=seldiv> <SELECT name=\"usr\" id=\"usr\">\n<option value=\"\">�� ����");
    while( $row=mysql_fetch_array($rez)) {
	print("<option value=\"".$row["username"]."\">".$row["username"]."\n");
    }
    print("</SELECT></span>\n </td></tr>\n");
    print("<tr><td> ����������� </td><td> <span class=seldiv> <SELECT name=\"order\" id=\"order\" /> \n");
    print("<option value=\"desc\" SELECTED>�� ��������\n<option value=\"asc\">�� �����������\n </SELECT></span> </td></tr>\n");    
    print("<tr><td colspan=2> <input type=\"SUBMIT\" name=\"sbmt\" value=\"�������\"> </td></tr> \n");
    print("</form>\n</table>\n");


}

#-------------------------------------------------------------------

print("<body>\n");


#-------------------------------------------------------------------




if(( $mode=="") or ( $mode=="list")) {
    print("<br>\n<div style=\"padding-left:2px;\">\n");
    print("<font class=top2>Fantomas Iptconf Logs</font><br><hr width=\"350px\" size=1 align=left><br>\n");
    print("<font class=text42s><b>���������:</b></font></div>\n");
    print("<div style=\"padding-left:2px;\">\n");
    print("<div style=\"float:left;position:relative;left:1px;\"><font class=text40t>\n");
    print("<div style=\"padding:6px;border:1px;border-style:dotted;border-color:A6CAF0;\">\n");
    $afiles=scandir($_logs_dir);
    if( !$flShowAll) {
	print("����: <b>$logfile</b><br>������ �����: ".bytes2mega(filesize($logfile))."<br>\n");
	print("���� ���������: ".strftime("%d-%m-%Y %H-%M-%S",filemtime($logfile))."<br>\n");
    } else {
	print("<font style=\"FONT: normal 9pt Tahoma;color:708090;\">�����:<br>");
	reset($afiles); $ii=1;
	foreach($afiles as $aflkey => $aflvalue) {
	    if( trim($aflvalue)=="") continue;
	    if( !is_file($_logs_dir."/".$aflvalue)) continue;
	    print("$ii. $aflvalue, ".bytes2mega(filesize($_logs_dir."/".$aflvalue)).", ".strftime("%d-%m-%Y %H-%M-%S",filemtime($_logs_dir."/".$aflvalue))."<br>");
	    $ii++;
	}
	print("</font>\n");
    }
    print("</div>\n");

    show_logs_form($logfile);
    print("</div>\n");
    
    print("<div style=\"position:absolute;left:375px;\">\n");
    print("<form name=\"logfsel\" id=\"logfsel\" action=\"$script\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"list\">\n");
    print("<input type=\"hidden\" name=\"run\" value=\"1\">\n");
    print("<table class=table4 cellpadding=\"5px\"><tr><td class=td21>�������: </td><td class=td21>\n");
    print("<span class=seldiv><SELECT name=\"logfile\" id=\"logfile\" onChange=\"javascript: document.getElementById('logfsel').submit();\" style=\"FONT: normal 8pt Tahoma; color:708090;\">\n");
    print("<option value=\"__all_files_\"".(($logfile==("__all_files_")) ? " SELECTED" : "").">��� �����\n");
    reset($afiles);
    foreach($afiles as $aflkey => $aflvalue) {
	if( trim($aflvalue)=="") continue;
	if( !is_file($_logs_dir."/".$aflvalue)) continue;
	print("<option value=\"$aflvalue\"".(($logfile==($_logs_dir."/".$aflvalue)) ? " SELECTED" : "").">$aflvalue - ".bytes2mega(filesize($_logs_dir."/".$aflvalue)).";".strftime("%d-%m-%Y %H-%M-%S",filemtime($_logs_dir."/".$aflvalue))."\n");
    }
    print("</SELECT></span>\n </td></tr>\n</form>\n");
    
    print("</div>\n");
    
    
    print("</div\n");

} elseif( $mode=="show") {
    
    $alogs=array();
    if( !$flShowAll) {
	if( trim($logfile)=="") {
	    wlog("�� ������ ���-���� ��� ���������",2,TRUE,5,TRUE); exit;
	}
	if( !file_exists($logfile)) {
	    wlog("���-���� $logfile �� ������",2,TRUE,5,TRUE); exit;
	}
	
	$alogs[0]=$logfile;
    } else {
	$afiles=scandir($_logs_dir);
	foreach($afiles as $aflkey => $aflvalue) {
	    if( trim($aflvalue)=="") continue;
	    if( !is_file($_logs_dir."/".$aflvalue)) continue;
	    $alogs[filemtime($_logs_dir."/".$aflvalue)]=$_logs_dir."/".$aflvalue;
	}
	unset($afiles);
	if( $order=="desc") { krsort($alogs); } elseif( $order=="asc") { ksort($alogs); }
    }
    print("<font class=top2>Fantomas Iptconf Logs</font><br><hr width=\"350px\" size=1 align=left><br>\n<font class=text40t>\n");
    $line=(( trim($date1)!="") and ( trim($date2)!="")) ? "�� ����� � $date1 �� $date2<br>" : "";
    print($line);
    $line=( trim($usr)!="") ? "�� ������: $usr<br>" : "";
    print($line."<br>\n");
    print("<table class=table2 cellpadding=\"2px\" width=\"95%\">\n");
    print("<tr><th> ���� </th><th> ����� </th><th> IP ��������� </th><th> �������� �������</th></tr>\n  ");
    print("<tr><th colspan=4> ��������� </th></tr>\n");
    foreach($alogs as $alkey => $alvalue) {
	if( isset($alines)) unset($alines);
	$alines=file($alvalue);
	if( $order=="desc") {
	    krsort($alines);
	} elseif( $order=="asc") {
	    ksort($alines);
	}
	print("<tr><td class=tdl1 colspan=4 style=\"padding-left:50px;\"> ����: $alvalue - ".count($alines)." �����</td></tr>\n");
	foreach($alines as $alinekey => $alinevalue) {
	    if( trim($alinevalue)=="") continue;
	    $fdate=gettok($alinevalue,1," \t")." ".gettok($alinevalue,2," \t");
	    if(( trim($date1)!="") and ( trim($date2)!="")) {
		if(( $fdate<$date1) or ( $fdate>$date2)) continue;
	    }
	    $alinevalue=trim(str_replace($fdate,"",$alinevalue));
	    $fbuf=gettok($alinevalue,1," \t");
	    $fusr=gettok($fbuf,2,":");
	    if( $usr!="") {
		if( trim($fusr)!=$usr) continue;
	    }
	    $alinevalue=trim(str_replace($fbuf,"",$alinevalue));
	    $fbuf=gettok($alinevalue,1," \t");
	    $alinevalue=trim(str_replace($fbuf,"",$alinevalue));
	    $fip=gettok($fbuf,2,":");
	    $fbuf=gettok($alinevalue,1," \t");
	    $alinevalue=trim(str_replace($fbuf,"",$alinevalue));
	    $fsrc=gettok($fbuf,2,":");
	    $fmessage=trim(str_replace($fbuf,"",$alinevalue));
	    print("<tr><td class=tdl3> $fdate </td><td class=tdl2> $fusr </td><td class=tdl32> $fip </td><td class=tdl2> $fsrc </td></tr>\n");
	    print("<tr><td class=tdl5 colspan=4> $fmessage </td></tr>");
	}
    }
    print("</table>\n<br><br><br>\n");

}


?>

</body>
</html>