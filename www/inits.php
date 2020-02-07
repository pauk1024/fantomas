<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: inits.php
# Description: 
# Version: 0.2.1
###


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"] : "");
$_inits=( isset($_GET["inits"])) ? $_GET["inits"] : (( isset($_POST["inits"])) ? $_POST["inits"] : "");

#-------------------------------------------------------------------------

function show_inits()
{
    global $iptconf_dir;
    $ifile=$iptconf_dir."/inits";
    if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	wlog("Ошибка записи в файл $ifile...",2,TRUE,4,TRUE); exit; 
    }
    print("   \n");
    print("<br><br><br><font class=top1>Inits<br> \n <font class=text41><i>( Произвольные команды iptables )<br><br>\n ");
    print("<table class=table5 width=\"auto\" cellpadding=\"6px\"><tr><td class=td3>\n");
    print("<form name=\"form243\" action=\"inits.php\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"edit\">   \n");
    print("<input type=\"submit\" value=\"Редактировать\"><br><hr>\n");
    
    if( file_exists($ifile)) {
	$iff=fopen($ifile,"r");
	while( !feof($iff)) {
	    $str1=fgets($iff);
	    print("$str1<br>");
	}
	fclose($iff);
    }
    print("</form>\n</td></tr></table>\n");
    wlog("Просмотр inits",0,FALSE,0,FALSE);

}



#-------------------------------------------------------------------------

function edit_inits()
{
    global $iptconf_dir;
    $ifile=$iptconf_dir."/inits";
    if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	wlog("Ошибка записи в файл $ifile...",2,TRUE,4,TRUE); exit; 
    }
    print("   \n");
    print("<br><br><br><font class=top1>Inits<br> \n <font class=text41><i>( Произвольные команды iptables )<br><br>\n ");
    print("<table class=table5 width=\"auto\" cellpadding=\"6px\"><tr><td><br>\n");
    print("<form name=\"form243\" action=\"inits.php\" method=\"POST\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"save\">   \n");
    print("<textarea name=\"inits\" rows=30 cols=90 wrap=\"off\" style=\"background-color:FFFBF0\">   \n");
    
    if( file_exists($ifile)) {
	$iff=fopen($ifile,"r");
	while( !feof($iff)) {
	    $str1=fgets($iff);
	    print("$str1");
	}
	fclose($iff);
    }
    print("</textarea><br><br>\n");
    print("<input type=\"submit\" value=\"Сохранить\">   \n</form>\n");
    print("<font class=td2> <a href=\"inits.php?mode=show\"> >> <b>Назад</b> >> </a><i>( не сохранять изменения )</i>  </td></tr></table>\n");
    wlog("Редатирование inits",0,FALSE,0,FALSE);

}

#-------------------------------------------------------------------------

function save_inits()
{
    global $iptconf_dir,$_mode,$_inits;
    $ifile=$iptconf_dir."/inits";
    if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	wlog("Error write access to $ifile...",2,TRUE,5,TRUE); exit; 
    }
    $inewfile=tempnam($iptconf_dir,$ifile);
    $inewff=fopen($inewfile,"a");
    foreach( explode("\n",$_inits) as $ikey => $ivv) fwrite($inewff,_trimline($ivv)."\n");
    fclose($inewff);
    if( file_exists($ifile.".bak")) unlink($ifile.".bak");
    rename($ifile,$ifile.".bak");
    rename($inewfile,$ifile);
    wlog("Сохранение редактирования inits",0,FALSE,1,FALSE);
    show_inits();

}

#-------------------------------------------------------------------------

print("<html>\n");
require("include/head.php");
print("<body\n>");

if( ($_mode=="") or ($_mode=="show")) {

    show_inits();

} elseif( $_mode=="edit") {

    edit_inits();

} elseif( $_mode=="save") {
    
    save_inits();
    
}





?>
</body>
</html>