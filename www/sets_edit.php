<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: sets_edit.php
# Description: 
# Version: 0.2.1
###


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"] : "");
$_set=( isset($_GET["set"])) ? $_GET["set"] : (( isset($_POST["set"])) ? $_POST["set"] : "");
$_pset=( isset($_GET["pset"])) ? $_GET["pset"] : (( isset($_POST["pset"])) ? $_POST["pset"] : "");

#-------------------------------------------------------------------------



function show_set($pset)
{
    global $iptconf_dir,$sets_dir;
    if( trim($pset)=="") exit;
    $ifile=$sets_dir."/".$pset;
    if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	wlog("Error write access to $ifile...",2,TRUE,5,TRUE); exit; 
    }
    print("   \n");
    print("<br><br><br><font class=top1>IPSet $pset<br><br>\n ");
    print("<table class=table5 width=\"auto\" cellpadding=\"6px\"><tr><td class=td3>\n");
    print("<form name=\"form243\" action=\"sets_edit.php\">   \n");
    print("<input type=\"hidden\" name=\"set\" value=\"$pset\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"edit\">   \n");
    if( trim($pset)!="locals") {
	print("<input type=\"submit\" value=\"Редактировать\"><br><hr>\n");
    } else {
	print("<font class=td3 style=\"color:blue\">Сет со списком локальных подсетей,<br> генерируется и используется программой. <br>Не изменять и не удалять!</font><br><br><hr>");
    }
    
    if( file_exists($ifile)) {
	$iff=fopen($ifile,"r");
	while( !feof($iff)) {
	    $str1=trim(fgets($iff));
	    print("$str1<br>");
	}
	fclose($iff);
    }
    print("</form>\n</td></tr></table><br><br><hr width=\"400px\" align=left><a href=\"sets_edit.php\">Назад</a> \n");

    wlog("Просмотр сетлиста $pset",0,FALSE,1,FALSE);
}



#-------------------------------------------------------------------------

function edit_set($pset,$pnew=FALSE)
{
    global $iptconf_dir,$sets_dir;
    if( trim($pset)=="") exit;
    $ifile=$sets_dir."/".$pset;
    if( !$pnew) {
	if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	    wlog("Error write access to $ifile...",2,TRUE,5,TRUE); exit; 
	}
    } else {
	$lstfile=$iptconf_dir."/ipsetlist";
	if( ( file_exists($lstfile)) and ( !is_writable($lstfile))) {
	    wlog("Error write access to $lstfile...",2,TRUE,5,TRUE); exit; 
	}
	$lstff=fopen($lstfile,"a");
	fwrite($lstff,$pset."\n");
	fclose($lstff);
    }
    print("   \n");
    print("<br><br><br><font class=top1>IPSet $pset <br> <br><br>\n ");
    print("<table class=table5 width=\"auto\" cellpadding=\"6px\"><tr><td><br>\n");
    print("<form name=\"form243\" action=\"sets_edit.php\" method=\"POST\">   \n");
    print("<input type=\"hidden\" name=\"set\" value=\"$pset\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"save\">   \n");
    print("<textarea name=\"pset\" rows=30 cols=90 wrap=\"off\" style=\"background-color:FFFBF0\">\n");
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
    print("<font class=td2> <a href=\"sets_edit.php\"> >> <b>Назад</b> >> </a><i>( не сохранять изменения )</i>  </td></tr></table>\n");
    wlog("Редактирование сетлиста $pset",0,FALSE,1,FALSE);

}

#-------------------------------------------------------------------------

function save_set($set1,$pset1)
{
    global $iptconf_dir,$sets_dir;
    $ifile=$sets_dir."/".$set1;
    if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	wlog("Error write access to $ifile...",2,TRUE,5,TRUE); exit; 
    } 
    if( !file_exists($ifile)) {
	$iff1=fopen($ifile,"a");
	fwrite($iff1,"\n");
	fclose($iff1);
    }
    $inewfile=tempnam($sets_dir,$ifile);
    $inewff=fopen($inewfile,"a");
    foreach( explode("\n",$pset1) as $ikey => $ivv) if( trim($ivv)!="") fwrite($inewff,_trimline($ivv)."\n");
    fclose($inewff);
    if( file_exists($ifile.".bak")) unlink($ifile.".bak");
    rename($ifile,$ifile.".bak");
    rename($inewfile,$ifile);
    show_set($set1);
    wlog("Сохранение сетлиста $pset",0,FALSE,1,FALSE);
    
}

#-------------------------------------------------------------------------

function show_setlist()
{
    global $iptconf_dir;
    $ifile=$iptconf_dir."/ipsetlist";
    if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	wlog("Error write access to $ifile...",2,TRUE,5,TRUE); exit; 
    }
    print("   \n");
    print("<br><br><br><font class=top1>IPSet list<br> \n <font class=text41><i>( Листы, загружаемые программой )<br><br>\n ");
    print("<table class=table1 width=\"500px\" cellpadding=\"6px\">\n");
    
    if( file_exists($ifile)) {
	$iff=fopen($ifile,"r");
	while( !feof($iff)) {
	    $str1=trim(fgets($iff));
	    if( trim($str1)=="") continue;
	    if( $str1[0]=="#") continue;
	    if( trim($str1)!="locals") {
		print("<tr><td width=\"80%\" style=\"font-size:12pt\"><b> $str1 </b></td><td class=td3 width=\"20%\"><a href=\"sets_edit.php?set=$str1&mode=show\" title=\"Открыть лист\"><img src=\"icons/gtk-open.gif\" title=\"Открыть лист\"></a></td></tr> \n");
	    } else {
		print("<tr><td width=\"100%\" style=\"font-size:12pt\"><b> $str1</b><br><font style=\"font-size:9pt\"><i>[ Сет со списком локальных сетей, используется при генерации правил. Не изменять и не удалять! ] </i> <td class=td3><a href=\"sets_edit.php?set=$str1&mode=show\" title=\"Открыть лист\"><img src=\"icons/gtk-open.gif\" title=\"Открыть лист\"></a> </td></tr>  \n");
	    }
	}
	fclose($iff);
    }
    print("</td></tr></table>\n");
    wlog("Просмотр списка сетлистов",0,FALSE,1,FALSE);

}


#-------------------------------------------------------------------------


function del_set($pset)
{
    global $iptconf_dir,$sets_dir;
    $ifile=$sets_dir."/".$pset;
    if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	wlog("Error write access to $ifile...",2,TRUE,5,TRUE); exit; 
    } 
    $lstfile=$iptconf_dir."/ipsetlist";
    $lstnewfile=tempnam($iptconf_dir,$lstfile);
    if( ( file_exists($lstfile)) and ( !is_writable($lstfile))) {
        wlog("Error write access to $lstfile...",2,TRUE,5,TRUE); exit; 
    }
    $lstff=fopen($lstfile,"r");
    $lstnewff=fopen($lstnewfile,"a");
    while( !feof($lstff)) {
	$str1=trim(fgets($lstff));
	if( ($str1!=$pset) and ( trim($str1)!="")) fwrite($lstnewff,$str1."\n");
    }
    fclose($lstff);
    fclose($lstnewff);
    if( file_exists($lstfile.".bak")) unlink($lstfile.".bak");
    rename($lstfile,$lstfile.".bak");
    rename($lstnewfile,$lstfile);
    if( file_exists($ifile.".bak")) unlink($ifile.".bak");
    rename($ifile,$ifile.".bak");

    show_setlist();
    wlog("Удаление сетлиста $pset",0,FALSE,1,FALSE);

}

#------------------------------------------------------------------------


print("<html>\n");
require("include/head.php");
print("<body\n>");

if( $_set=="") {
    show_setlist();
} else {
    
    if( ($_mode=="") or ($_mode=="show")) {

	show_set($_set);

    } elseif( $_mode=="edit") {

	edit_set($_set);

    } elseif( $_mode=="save") {

	save_set($_set,$_pset);

    } elseif( $_mode=="addset") {

	edit_set($_set,TRUE);

    } elseif( $_mode=="delset") {

	del_set($_set);
    }


}





?>
</body>
</html>