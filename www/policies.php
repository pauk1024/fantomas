<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: policies.php
# Description: 
# Version: 0.2.1
###




require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

print("<html>\n");
require("include/head.php");
print("<br><br><br>\n");

#-------------------------------------------------------------

function print_policy($ppolname="",$pptitle="")
{
    
    $cusr=get_policy_users($ppolname);
    
    print("<br>\n<table class=table4 width=\"680px\" cellpadding=\"3px\">\n");
    print("<tr><td width=\"22%\"><font style=\"font-size:9pt\">Имя:</td><td align=middle><b> $ppolname </b></td>\n");
    print("<td rowspan=2 width=\"105px\" align=center> <a href=\"pol.php?p=$ppolname&edit=1\" title=\"Редактировать\"><img src=\"icons/gtk-edit.gif\" title=\"Редактировать\"></a> &nbsp&nbsp");
    print("<a href=\"pol.php?height=300&width=300&p=$ppolname&shconf=1\" class=\"thickbox\" title=\"Конфиг политики <b>$ppolname</b>\"><img src=\"icons/eog.gif\" title=\"Просмотр конфига\"></a> &nbsp&nbsp");
    if( count($cusr)>0) {
	print("<img src=\"icons/edittrash.gif\" title=\"Нельзя удалить используемую политику\">\n");
    } else {
	print("<a href=\"pol.php?p=$ppolname&edit=1&mode=delpolicy\" title=\"Удалить политику\"><img src=\"icons/edittrash.gif\" title=\"Удалить политику\"></a>\n");
    }
    print("<br><a href=\"users.php?pol=$ppolname&mode=showpolicyusers\" title=\"Показать юзеров по этой политике\"><font style=\"font-size:8pt; font-style:italic; color:696969\">Используют: ".count($cusr)." </font></a></td></tr>\n");
    
    print("<tr><td width=\"22%\"><font style=\"font-size:9pt\">Описание:</td><td align=middle><i>$pptitle</i></td>\n</tr>  \n");
    print("</table>\n");


}

#-------------------------------------------------------------

function show_search_form()
{
    print(" \n");
    print("<font class=top1>Политики</font><br><br><br> \n");
    print("<table class=table5 width=\"60%\" > \n<tr><td colspan=2>\n");
    print("<form name=\"form1\" action=\"policies.php\"> \n");
    print("<input type=\"hidden\" name=\"s\" value=\"1\">  \n");
    print("<input type=\"hidden\" name=\"rs\" value=\"1\">  \n");
    print("<input type=\"hidden\" name=\"nolst\" value=\"1\">  \n");
    print("<br><font class=top1> &nbsp&nbsp&nbsp&nbsp <i>Искать:</font><br><br></td></tr>\n<tr><td>&nbsp&nbspпо имени/описанию:</td>\n");
    print("<td><input type=\"TEXT\" name=\"pstr\" size=\"30\"></td></tr> \n");
    print("<tr><td>&nbsp&nbspпо направленности:</td><td> <select name=\"d\">\n <option value=\"all\" selected>Пофиг <option value=\"in\">Входящие\n<option value=\"out\">Исходящие \n</select>\n</td></tr>\n");
    print("<tr><td colspan=2>&nbsp&nbsp&nbsp<input type=\"submit\" value=\"Вперед\"></form> \n");
    print("</td></tr> \n</table><br><br>\n");

    print("<table class=table5 width=\"60%\" > \n<tr><td>\n");
    print("<form name=\"form2\" action=\"pol.php\"> \n");
    print("<input type=\"hidden\" name=\"edit\" value=\"1\" />\n");
    print("<br><font class=top1> &nbsp&nbsp&nbsp&nbsp <i>Выбрать:</font><br><br></td></tr>\n");
    print("<tr><td>&nbsp&nbsp <select name=\"p\">\n <option value=\"empty\" selected>--- \n");
    $aa0=load_policies_list();
    if( !$aa0) {
	wlog("Error load policies list in show_search_form()\n",2,TRUE,4,FALSE); exit;
    } else {
	foreach( $aa0 as $key => $vv) {
	    print("<option value=\"$key\">$key \n");
	}
    }
    print("</select>\n</td></tr>\n");
    print("<tr><td colspan=2>&nbsp&nbsp&nbsp<input type=\"submit\" value=\"Открыть\"></form> \n");
    print("</td></tr> \n</table><br><br>\n");
    
    print("<a href=\"policies.php\"> <font class=text3 style=\"color:696969; font-weight: bold; \">Все политики списком</font></a><br>\n");
    print("<br>\n<form name=\"form121\" action=\"pol.php\">    \n");
    print("<input type=\"hidden\" name=\"edit\" value=\"1\">  \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"new\">  \n");
    print("<font class=text32b><i>Новая политика:</i></font> <input type=\"text\" size=20 name=\"p\" value=\"\">\n");
    print("<input type=\"submit\" value=\"Добавить\">\n </form>\n");
    print("<br><hr width=\"60%\" align=left>");    




}


$_policy=( isset($_GET["p"])) ? $_GET["p"] : "";
$_pstr=( isset($_GET["pstr"])) ? $_GET["pstr"] : "";
$_direction=( isset($_GET["d"])) ? $_GET["d"] : "";
$_search=( isset($_GET["s"])) ? $_GET["s"] : "";
$_runsearch=( isset($_GET["rs"])) ? $_GET["rs"] : "";
$_nolist=( isset($_GET["nolst"])) ? $_GET["nolst"] : "";

if( $_search!="") show_search_form();

if( $_runsearch!="") {

    $aa1=array();
    $aa1=load_policies_list();
    if( !$aa1) {
	print("Error load policies list in runsearch\n"); exit;
    } else {
	print("<br><font class=top1><i>Найденные политики:</font></i><br><br>\n");
	foreach( $aa1 as $key1 => $vv1) {
	    $rr=0;
	    if( $_pstr=="") { $rr++; } else {
		if( substr_count($key1,$_pstr)!=0) $rr++;
		if( substr_count($aa1[$key1],$_pstr)!=0) $rr++;
	    }
	    $bufout=get_policy_param($key1,"out","",0,TRUE);
	    $bufin=get_policy_param($key1,"in","",0,TRUE);
	    if( ($_direction=="all") or ($_direction=="")) {
		$rr++;
	    } elseif( ( $_direction==$bufin) or ( $_direction==$bufout)) {
		$rr++;
	    }
	    if( $rr>1) print_policy($key1,$aa1[$key1]);
	}
    }


}

if( $_policy=="") {

    if( $_nolist!="") exit;

    if( ( !file_exists("$iptconf_dir/policies")) or ( ! is_readable("$iptconf_dir/policies"))) {
	wlog("Error opening policies file...<br>  \n",2,TRUE,5,TRUE); exit; 
    }
    $pfile=fopen("$iptconf_dir/policies","r");
    $open=FALSE;
    $aa=array();
    $strnum=0;
    $pstrnum=0;
    print("<br><br><br>\n <font class=top1>Политики:</font><br><br>  \n");


    while( !feof($pfile)) {
	$string=_trimline(strtolower(fgets($pfile))," \t"); $strnum++;
	if( $string[0]=="#") continue;
	if( trim($string)=="") continue;
	if( $string[0]=="}") { 
	    $open=FALSE; 
	    $pstrnum=0;
	    print_policy($polname,$ptitle);
	    foreach( $aa as $kk => $vv) {
	        unset($aa[$kk]);
	    }
	    $pstrnum=0; $polname=""; $ptitle="";
	    continue;
	}
	$buf1=gettok($string,1," \t");
	if( $buf1=="policy") {
	    if( $open) {
		print("Logical error in parsing policies file: policy ".gettok($string,2," \t")." starts when previous policy $polname didn't closed..<br>\n");
		print_policy($polname,$ptitle);

		foreach( $aa as $kk => $vv) {
		    unset($aa[$kk]);
		}
		$open=TRUE;
		$polname=gettok($string,2," \t");
		$pstrnum=0;
		continue;
	    }
	    $open=TRUE;
	    $polname=gettok($string,2," \t");
	    continue;
	}
	if( $buf1=="title") {
	    $ptitle=_trimline(str_replace($buf1,"",$string));
	    $ptitle=( $ptitle[0]=="\"") ? substr($ptitle,1) : $ptitle;
	    $ptitle=( $ptitle[strlen($ptitle)-1]=="\"") ? substr($ptitle,0,-1) : $ptitle;
	} else {
	    if( $open) { $aa[$pstrnum]=$string; $pstrnum++; }
	}
    
    }

print("<br><br><br>\n");
print("\n</body></html>  \n");


} else {



}




?>