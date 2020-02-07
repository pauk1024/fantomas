<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: pol.php
# Description: 
# Version: 0.2.1
###



require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

$_showconf=( isset($_GET["shconf"])) ? $_GET["shconf"] : "";
$_policy=( isset($_GET["p"])) ? $_GET["p"] : (( isset($_POST["p"])) ? $_POST["p"] : "");
$_ppolicy=( isset($_GET["policy"])) ? $_GET["policy"] : (( isset($_POST["policy"])) ? $_POST["policy"] : "");
$_edit=( isset($_GET["edit"])) ? $_GET["edit"] : (( isset($_POST["edit"])) ? $_POST["edit"] : "");
$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"] : "");

$_pconfig=stripslashes(( isset($_POST["pconfig"])) ? $_POST["pconfig"] : "");


if( isset($aa1)) unset($aa1);

if( ($_policy=="") and ($_ppolicy=="")) { exit; } else {
    $_policy=( trim($_policy)=="") ? $_ppolicy : $_policy;
}

#---------------------------------------------------------------------

function show_policy_form_tt()
{
    global $_policy,$_mode,$aa1;
    print("  \n");
    print("<br><br>\n");
    print("<table class=table4 width=\"60%\">  \n");
    print("<form name=\"formpol1\" action=\"pol.php\" method=\"POST\">  \n");
    print("<input type=\"hidden\" name=\"edit\" value=\"1\" />  \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"save\" />  \n");
    print("<input type=\"hidden\" name=\"policy\" value=\"$_policy\" />  \n");
    
    print("  \n");
    print("<br><br>\n");
    print("<table class=table4 width=\"60%\"><tr><td><br>  \n");
    print("  \n");

}

#---------------------------------------------------------------------

function l_save_policy($ppname,$pmode)
{
    global $iptconf_dir,$_pconfig;
    if( (!file_exists($iptconf_dir."/policies")) or (!is_readable($iptconf_dir."/policies"))) {
	wlog("Error accessing policies file in l_save_policy()..",2,TRUE,5,TRUE);
	exit;
    }
    $ptmpfile=tempnam($iptconf_dir,"policies");
    $pfile=fopen($iptconf_dir."/policies","r");
    $pnewfile=fopen($ptmpfile,"a");
    if( $pmode=="save") {
	$aat=policy2array($ppname,TRUE);
	$open=FALSE;
	$strnum=0;
	while( !feof($pfile)) {
	    $pstr=_trimline(strtolower(fgets($pfile))); $strnum++;
	    if( trim($pstr)=="") continue;
	    $buf1=gettok($pstr,1," \t");
	    if( $buf1=="policy") {
		$buf2=gettok($pstr,2," \t");
		if( $open) print("Statement error in policies file at line $strnum - policy $buf2 defining when previous policy is not closed!");
		if( $buf2==$ppname) {
		    $open=TRUE;
		    continue;
		}
		fwrite($pnewfile,"$pstr\n");
	    } elseif($buf1=="}") {
		if(!$open) fwrite($pnewfile,"$pstr\n\n");
		$open=FALSE;
		continue;
	    } else {
		if( !$open) fwrite($pnewfile,$pstr."\n");
	    }
	}
	fwrite($pnewfile,"policy $ppname {\n");
	foreach( explode("\n",$_pconfig) as $pkey => $pvv) if( $pvv!="") fwrite($pnewfile,$pvv."\n");
	fwrite($pnewfile,"}\n");
	fclose($pnewfile);
	if( file_exists($iptconf_dir."/policies.bak")) unlink($iptconf_dir."/policies.bak");
	rename($iptconf_dir."/policies",$iptconf_dir."/policies.bak");
	rename($ptmpfile,$iptconf_dir."/policies");
	
    } elseif( $pmode=="newsave") {
    
	while( !feof($pfile)) {
	    $pstr=fgets($pfile);
	    if( gettok(_trimline(strtolower($pstr)),1," \t")=="policy") {
		$bufpname=gettok(_trimline(strtolower($pstr)),2," \t");
		if( $bufpname==$ppname) {
		    print("Такая политика уже существует!<br>");
		    exit;
		}
	    }
	    fwrite($pnewfile,"$pstr");
	}
	fclose($pfile);
	fwrite($pnewfile,"\n\n");
	fwrite($pnewfile,"policy $ppname {\n");
	foreach( explode("\n",$_pconfig) as $pkey => $pvv) if( $pvv!="") fwrite($pnewfile,$pvv."\n");
	fwrite($pnewfile,"}\n");
	fclose($pnewfile);
	if( file_exists($iptconf_dir."/policies.bak")) unlink($iptconf_dir."/policies.bak");
	rename($iptconf_dir."/policies",$iptconf_dir."/policies.bak");
	rename($ptmpfile,$iptconf_dir."/policies");

    }
    wlog("Сохранение политики $ppname",0,FALSE,1,FALSE);

}

#---------------------------------------------------------------------

function show_policy_form($ppname,$pmode)
{
    print("  \n");
    print("<br><br><br><br><br>\n");
    print("<table class=table4 width=\"60%\" cellpadding=\"4px\"><tr><td width=\"100%\">  \n");
    print("<font class=text4><b>Политика $ppname</b>\n");
    print("<form name=\"formpol1\" action=\"pol.php\" method=\"POST\">  \n");
    print("<input type=\"hidden\" name=\"edit\" value=\"1\" />  \n");
    if( $pmode=="new") {
	print("<input type=\"hidden\" name=\"mode\" value=\"newsave\" />  \n");
	print("Системное имя <i>(краткое)</i>: &nbsp&nbsp&nbsp <input type=\"text\" name=\"policy\" size=\"30\" value=\"$ppname\" /><br><br>\n");
	print("<textarea name=\"pconfig\" rows=\"30\" cols=\"80\" wrap=\"off\">\n");
    } else {
	$aap0=policy2array($ppname,TRUE);
	print("<input type=\"hidden\" name=\"mode\" value=\"save\" />  \n");
	print("<input type=\"hidden\" name=\"p\" value=\"$ppname\" />  \n");
	print("<textarea name=\"pconfig\" rows=\"30\" cols=\"80\" wrap=\"off\">\n");
	foreach($aap0 as $kk1 => $vv1) if( trim($aap0[$kk1])!="") print($aap0[$kk1]."\n");
    }
    print("</textarea><br><br>\n <input type=\"SUBMIT\" value=\"Сохранить\">\n</form>\n</table>\n");
    print("<br><br><font class=text41><a href=\"policies.php\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"> Назад к политикам</a> <i>(не сохранять изменения)</i></font>  \n");
    wlog("Просмотр политики $ppname",0,FALSE,1,FALSE);
}

#--------------------------------------------------------------------

function show_policy($ppname,$paa1)
{
    print("<table class=table4 width=\"60%\" cellpadding=\"6px\">\n");
    print("<tr><th>Политика <b>$ppname</b></th></tr>\n<tr><td>\n");
    foreach($paa1 as $key => $vv) print("&nbsp".$paa1[$key]."<br>\n");
    print("</td></tr></table>\n");

    wlog("Просмотр политики $ppname",0,FALSE,1,FALSE);
}

#---------------------------------------------------------------------

function del_policy($ppname) 
{
    global $iptconf_dir;
    if( trim($ppname)=="") exit;
    $au1=get_policy_users($ppname);
    if( count($au1)>0) { 
	wlog("Нельзя удалить эту политику, т.к. она используется ".count($au1)." клиентами<br>\n",2,TRUE,4,TRUE); 
	exit;
    }

    $ptmpfile=tempnam($iptconf_dir,"policies");
    $pfile=fopen($iptconf_dir."/policies","r");
    $pnewfile=fopen($ptmpfile,"a");
    $aat=policy2array($ppname,TRUE);
    $open=FALSE;
    $strnum=0;
    while( !feof($pfile)) {
        $pstr=_trimline(strtolower(fgets($pfile))); $strnum++;
        if( trim($pstr)=="") continue;
        $buf1=gettok($pstr,1," \t");
        if( $buf1=="policy") {
	    $buf2=gettok($pstr,2," \t");
	    if( $open) print("Statement error in policies file at line $strnum - policy $buf2 defining when previous policy is not closed!");
	    if( $buf2==$ppname) {
		$open=TRUE;
		continue;
	    }
	    fwrite($pnewfile,"$pstr\n");
	} elseif($buf1=="}") {
	    if(!$open) fwrite($pnewfile,"$pstr\n\n");
	    $open=FALSE;
	    continue;
	} else {
	    if( !$open) fwrite($pnewfile,$pstr."\n");
	}
    }
    if( file_exists($iptconf_dir."/policies.bak")) unlink($iptconf_dir."/policies.bak");
    rename($iptconf_dir."/policies",$iptconf_dir."/policies.bak");
    rename($ptmpfile,$iptconf_dir."/policies");
    wlog("Удаление политики $ppname",0,FALSE,1,FALSE);
    
}

#---------------------------------------------------------------------

if( ( !file_exists("$iptconf_dir/policies")) or ( ! is_readable("$iptconf_dir/policies"))) {
    wlog("Error opening policies file...<br>  \n",2,TRUE,5,TRUE); exit; 
}
    $pfile1=fopen("$iptconf_dir/policies","r");
    $open=FALSE;
    $aa1=array();
    $strnum=0;
    $pstrnum=0;
    while( !feof($pfile1)) {
	$string=_trimline(strtolower(fgets($pfile1))," \t"); $strnum++;
	if( trim($string)=="") continue;
	if( $string[0]=="#") continue;
	$buf1=gettok($string,1," \t");
	$buf2=gettok($string,2," \t");
	if( !$open) {
	    if( $buf1=="policy") {
		if( $buf2==$_policy) { $open=TRUE; continue; }
	    }
	} else {
	    if( $buf1=="}") { $open=FALSE; break; }
	    $buf0=htmlentities(((coltoks($string," \t")>1) ? substr($string,strpos($string," ")):""),ENT_NOQUOTES,"koi8r");
	    $buf1=htmlentities($buf1,ENT_NOQUOTES,"koi8r");
	    $bufw=gettok($buf1,1," \t");
	    $aa1[$pstrnum."|".$bufw]="<b>$buf1</b>&nbsp <i>$buf0</i>"; 
	    $pstrnum++;
	}
    }
    fclose($pfile1);


print("<html>\n");
require("include/head.php");

print("<body>\n");

if( $_showconf!="") {
    print("<font class=text5><br>\n");
    foreach($aa1 as $key => $vv) {
	print("&nbsp".$aa1[$key]."<br>\n");
    }
    exit;
}

if( $_edit!="") {
    if( ($_mode=="save") or ($_mode=="newsave")) {
	l_save_policy($_policy,$_mode);
	print("<br><br><br><br><br>\n");
	unset($aa1); $aa1=policy2array($_policy);
	show_policy($_policy,$aa1);
	if( $_mode=="save") {
	    print("<br><br><br><table class=table2 width=\"400px\"><tr><td width=\"22%\" valign=middle>\n");
	    print("<a href=\"ipt.php?p=reload\" title=\"Запустить Reload\"><img src=\"icons/apps_32.gif\" title=\"Запустить Reload\"></a></td><td valign=middle>\n");
	    print("<font class=text3>Теперь можно запустить <a href=\"ipt.php?p=reload\"></font><b>RELOAD</b></a><font class=text3> - <i>перезагрузка конфигурации для применения сделанных изменений.</i></td><tr>\n ");
	    print("<tr><td colspan=2 valign=middle><br><br><a href=\"policies.php\">Все политики списком</a></td></tr></table>\n");
	}
    } elseif( $_mode=="delpolicy") {
	print("debug: _policy $_policy _ppolicy $_ppolicy<br>\n");
	del_policy($_policy);
    } else {
	show_policy_form($_policy,$_mode);
    }
}



print("</body></html>\n");

?>
