<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8.2
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: services.php
# Description: 
# Version: 2.8.2.5
###




require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

print("<html>\n");
$NoHeadEnd=FALSE;
require("include/head1.php");

print("<body><br><br>\n");

#-------------------------------------------------------------
function getprocesslist($pservice)
{
    global $iptconf_dir,$initd_dir,$_iptables,$_sudo,$_grep,$_chkconfig,$_top,$_ps;
    global $chkconfig_mode,$sysv_rc_conf,$default_rctool;
    
    if( $default_rctool=="chkconfig") {
	$line="$_chkconfig --list";
    } elseif( $default_rctool=="sysv_rc_conf") {
	$line="$_sysv_rc_conf --list | $_grep \":o\"";
    } else {
	wlog("Ошибка: недоступен default_rctool, путь переменной: .$default_rctool.",2,TRUE,4,TRUE); exit;
    }
    
    if( trim($initd_dir)=="") {
	wlog("Укажите путь к директории с инит-скриптами сервисов в переменной initd_dir!",2,TRUE,4,TRUE); exit;
    }
    
    if( (!is_file($lfile=$initd_dir."/".$pservice)) or (!file_exists($lfile))) return(FALSE);
    if( !is_readable($lfile)) return(FALSE);
    if( isset($aafile)) unset($aafile);
    $aafile=file($lfile);
    $processname=""; $prog=""; $pidfile=""; $lockfile="";
    foreach($aafile as $aafkk => &$aafvv) {
	if( trim($aafvv)=="") continue;
	$aafvv=_trimline(strtolower($aafvv));
	if( $aafvv[0]=="#") $aafvv=substr($aafvv,1);
	if( ($buf1=gettok($aafvv,1,"=:"))=="processname") {
	    $processname=gettok($aafvv,2,"=:");
	}
	if(( $buf1=="prog") or ( $buf1=="exe")) {
	    $prog=gettok($aafvv,2,"=: \t");
	}
	if(( $buf1=="lockfile") or ( $buf1=="pidfile")) {
	    $$buf1=gettok($aafvv,2,"=:\t");
	}
	if( substr_count($aafvv,"start()")>0) break;

    }

    if( isset($aa2)) unset($aa2);

    if( $default_rctool=="chkconfig") {
	$line="$_chkconfig --list $pservice";
    } elseif( $default_rctool=="sysv_rc_conf") {
	$line="$_sysv_rc_conf --list $pservice | $_grep \":o\"";
    } else {
	wlog("Ошибка: недоступен default_rctool, путь переменной: .$default_rctool.",2,TRUE,4,TRUE); exit;
    }

    list($rr2,$aa2)=_exec2($line);
    $flag=FALSE;
    foreach($aa2 as $aakey => $aaval) $flag=( substr_count($aaval,":o")>0) ? TRUE:$flag;
    if( !$flag) return(FALSE);

    if( isset($aa2)) unset($aa2);
    list($rr,$aa2)=_exec2("$_ps axo comm,pid,pcpu,rss,size,vsize | $_grep $pservice | $_grep -v grep");
    if( count($aa2)==0) {
	$bufname=( trim($processname)) ? $processname:(( trim($prog)) ? $prog:$pservice);
	if( isset($aa2)) unset($aa2);
	list($rr,$aa2)=_exec2("$_ps axo comm,pid,pcpu,rss,size,vsize | $_grep $bufname | $_grep -v grep");
    } 
    $arez=array();
    
    if( count($aa2)>0) {
	foreach($aa2 as $aa1key => &$aa1value) {
	    $aa1value=_trimline($aa1value);
	    if( !$pproc=gettok($aa1value,1," \t")) continue;
	    $arez[count($arez)]=array( 
		"process" => $pproc,
		"pid" => gettok($aa1value,2," \t"),
		"cpu" => gettok($aa1value,3," \t"),
		"rss" => gettok($aa1value,4," \t"),
		"size" => gettok($aa1value,5," \t"),
		"vsize" => gettok($aa1value,6," \t")
		);
	}
    }
    return(( count($arez)>0) ? $arez:FALSE);
}
#-----------------------------------------------------------------

function service_action($pservice,$pmode)
{
    global $iptconf_dir,$initd_dir,$_iptables,$_grep,$_chkconfig,$_top,$_service,$_kill;
    
    if( trim($pservice)=="") return("");
    if( trim($pmode)=="") return("");
    $line="";
    if( $pmode=="start") {
	$line="$_service $pservice start";
    } elseif( $pmode=="stop") {
	$line="$_service $pservice stop";
    } elseif( $pmode=="restart") {
	$line="$_service $pservice restart";
    } elseif( $pmode=="killproc") {
	$line="$_kill $pservice";
    }
    $result="";
    if( trim($line)!="") {
	list($rr,$aa1)=_exec2($line);
	$result=( $rr>0) ? "Команда $pmode вернула статус $rr...<br>" : "Команда $pmode успешно выполнена.<br>";
    }
    wlog("Системные сервисы: выполнение $line",0,FALSE,1,FALSE);
    print($result);
    unset($aa1);

}
#-----------------------------------------------------------------
function show_processlist($pservice)
{
    global $iptconf_dir,$initd_dir,$_iptables,$_sudo,$_grep,$_chkconfig,$_top,$_ps;
    if( !trim($pservice)) {
	wlog("Не указано имя сервиса в show_processlist().",2,TRUE,5,TRUE); exit; 
    }
    if(( count($aprocs=getprocesslist($pservice))==0) || ( !$aprocs)) {
	print("Процессов для сервиса $pservice не найдено...<br><br>\n"); 
	print("<table class=notable><tr><td>\n <a href=\"services.php\" title=\"Назад к списку сервисов\"><img src=\"icons/gtk-undo.gif\" title=\"Назад к списку сервисов\"></a></td>\n <td> <a href=\"services.php\" title=\"Назад к списку сервисов\">Назад</a></td></tr></table>\n");
	exit; 
    }
    print("<table class=notable><tr><td>\n <a href=\"services.php\" title=\"Назад к списку сервисов\"><img src=\"icons/gtk-undo.gif\" title=\"Назад к списку сервисов\"></a></td>\n <td> <a href=\"services.php\" title=\"Назад к списку сервисов\">Назад</a></td></tr></table>\n");

    print("<blockquote>\n<font class=top1>Процессы сервиса $pservice</font><br><br>\n");
    print("<table class=table1 cellpadding=\"5px\" width=\"auto\">\n");
    print("<tr><th align=middle colspan=2 rowspan=1> Процесс</th><th align=middle colspan=1 rowspan=2> cpu<br>usage </th><th colspan=3 rowspan=1> Память </th><th colspan=1 rowspan=2> Summary<br>of memory<br>usage </th></tr><tr><th> name </th><th> pid </th><th> resident </th><th> swap </th><th> virtual </th></tr>\n");
    $memsumm=0;
    foreach($aprocs as &$aproc) {
	if( !trim($aproc["process"]=trim($aproc["process"]))) continue;

	print("<tr><td class=td3> ".$aproc["process"]." </td><td class=td3> ".$aproc["pid"]." </td><td class=td3> ".$aproc["cpu"]." </td><td class=td3> ".bytes2mega(mega2bytes($aproc["rss"]."kb"))." </td><td class=td3> ".bytes2mega(mega2bytes($aproc["size"]."kb"))."</td><td class=td3> ".bytes2mega(mega2bytes($aproc["vsize"]."kb"))."</td>\n");
    	print("<td class=td3> ".bytes2mega(mega2bytes(($memsumm+=($aproc["rss"]+$aproc["size"]+$aproc["vsize"]))."kb"))."</td>\n");
    	print("<td class=td3>\n");
	print("&nbsp <a href=\"services.php?s=".$aproc["pid"]."&mode=killproc\" title=\"Убить процесс ".$aproc["process"]."\"><img src=\"icons/cancel16.gif\" title=\"Убить процесс ".$aproc["process"]."\"></a> &nbsp");
	print("</td></tr>\n");
    }
    print("<tr><td class=td3> &nbsp </td><td class=td3> &nbsp </td><td class=td3> &nbsp </td><td class=td3> &nbsp </td><td class=td3> &nbsp </td><td class=td3> &nbsp </td><td class=td3> <b>".bytes2mega(mega2bytes($memsumm."kb"))." </b></td></tr>\n");
    print("</table>\n</blockquote>\n<br><br>\n");
    print("<table class=notable><tr><td>\n <a href=\"services.php\" title=\"Назад к списку сервисов\"><img src=\"icons/gtk-undo.gif\" title=\"Назад к списку сервисов\"></a></td>\n <td> <a href=\"services.php\" title=\"Назад к списку сервисов\">Назад</a></td></tr></table>\n");
    

}
#-----------------------------------------------------------------
function services_list()
{
    global $iptconf_dir,$initd_dir,$_iptables,$_sudo,$_grep,$_chkconfig,$_top,$_awk;
    global $chkconfig_mode,$_sysv_rc_conf,$default_rctool,$shall;
    
    if( $default_rctool=="chkconfig") {
	$line="$_chkconfig --list".(( $shall) ? "":" | $_awk '\$0~/:on/'");
    } elseif( $default_rctool=="sysv_rc_conf") {
	$line="$_sysv_rc_conf --list | $_grep \":o\"".(( $shall) ? "":" | $_awk '\$0~/:on/'");
    } else {
	wlog("Ошибка: недоступен default_rctool, путь переменной: .$default_rctool.",2,TRUE,4,TRUE); exit;
    }

    list($rr,$aalist)=_exec2($line);
    if(( count($aalist)==0) or ( $rr!=0)) {
	wlog("Ошибка получения результатов $default_rctool..",2,TRUE,4,TRUE); exit;
    }
    
    
    print("<table class=notable>\n");
    print("<tr>\n<td> <a href=\"sysstat.php?p=system\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td>\n");
    print("<td> <a href=\"sysstat.php?p=system\" title=\"Назад\">Назад</a> </td>\n");
    print("<td style=\"padding-left:235px;\"> ");
    print(( !$shall) ? "<a href=\"services.php?all\" title=\"Показать все сервисы (и удаленные тоже)\">Все сервисы</a>":"<a href=\"services.php\" title=\"Показать только активные сервисы\">Только активные</a>");
    print("</td>\n");
    print("</tr>\n</table>\n<br>\n");

    
    print("<table class=table1 cellpadding=\"5px\" width=\"450px\">\n");
    print("<form name=\"service1\" action=\"services.php\">\n");
    print("<tr><th align=middle> Сервис </th><th align=middle> Уровни </th></tr>\n");
    foreach($aalist as $aakey => &$aaline) {
	if( trim($aaline=_trimline($aaline))=="") continue;
	$line="<tr>\n";
	$service=gettok($aaline,1," \t");
	$line.="<td> <input type=\"radio\" name=\"s\" id=\"s_$service\" value=\"$service\"> \n";
	$line.="&nbsp; <label for=\"s_$service\"> $service </label> </td>\n";
	$aaline1=explode(" ",_trimline(str_replace($service,"",$aaline)));
	$levels1="";
	for($l=0;$l<=6;$l++) 
	    if( substr_count($aaline1[$l],"on")>0) $levels1.=" $l";
	$line=$line."<td align=center class=td3 colspan=2 style=\"padding-left:15px;padding-right:15px;\"> ".trim($levels1)."</td>";

	$line=$line."</tr>\n";
	
	print($line);
	    
    }
    print("<tr class=tr1>\n");
    print("<td class=td1bottom align=right> Действия:&nbsp;  \n");
    print("<span class=seldiv><select name=\"mode\">\n");
    print("<option value=\"start\">Запустить </option>\n");
    print("<option value=\"stop\">Остановить </option>\n");
    print("<option value=\"restart\">Перезапустить </option>\n");
    print("<option value=\"proclist\">Список процессов </option>\n");
    print("<option value=\"options\">Параметры </option>\n");
    print("<option value=\"editconf\">Править конфиг </option>\n");
    print("</select>\n</span>&nbsp;\n");
    print("</td><td class=td1bottom align=left>&nbsp; \n");
    print("<input type=\"submit\" value=\"Ok\">\n");
    print("</td></tr>\n");
    
    print("</form>\n");
    print("</table>\n");
    print("<br><br>\n");

    print("<table class=notable><tr><td> <a href=\"sysstat.php?p=system\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td>\n");
    print("<td> <a href=\"sysstat.php?p=system\" title=\"Назад\">Назад</a> </td></tr></table>\n<br>\n");

    print("</blockquote>\n<br><br><br>\n");
    unset($aalist);
    
    ob_flush();
    ob_end_flush();
    
    wlog("Просмотра списка системных сервисов",0,FALSE,1,FALSE);

}
#-----------------------------------------------------------------

function services_form()
{
    global $iptconf_dir,$initd_dir,$_iptables,$_sudo,$_grep,$_chkconfig,$_top,$_awk;
    global $chkconfig_mode,$_sysv_rc_conf,$default_rctool,$shall;
    
    if( $default_rctool=="chkconfig") {
	$line="$_chkconfig --list".(( $shall) ? "":" | $_awk '\$0~/:on/'");
    } elseif( $default_rctool=="sysv_rc_conf") {
	$line="$_sysv_rc_conf --list | $_grep \":o\"".(( $shall) ? "":" | $_awk '\$0~/:on/'");
    } else {
	wlog("Ошибка: недоступен default_rctool, путь переменной: .$default_rctool.",2,TRUE,4,TRUE); exit;
    }

    list($rr,$aalist)=_exec2($line);
    if(( count($aalist)==0) or ( $rr!=0)) {
	wlog("Ошибка получения результатов $default_rctool..",2,TRUE,4,TRUE); exit;
    }
    
    
    print("<table class=notable>\n");
    print("<tr>\n<td> <a href=\"sysstat.php?p=system\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td>\n");
    print("<td> <a href=\"sysstat.php?p=system\" title=\"Назад\">Назад</a> </td>\n");
    print("<td style=\"padding-left:235px;\"> ");
    print(( !$shall) ? "<a href=\"services.php?all\" title=\"Показать все сервисы (и удаленные тоже)\">Все сервисы</a>":"<a href=\"services.php\" title=\"Показать только активные сервисы\">Только активные</a>");
    print("</td>\n");
    print("</tr>\n</table>\n<br>\n");


?>
<script type="text/javascript">
function submtForm(vmode)
{
    var s = document.getElementById('s').value;
    if( s == false ) {
	return false;
    }
    if ( vmode.length ) {
	var levels = document.getElementById('div_'+s).innerHTML;
	document.getElementById('mode').value=vmode;
	document.getElementById('service1').submit();
    }
}

function renewInfo(pservice)
{
    if( document.getElementById('s').value == false ) {
	return false;
    }
    if ( pservice.length ) {
	var levels = document.getElementById('div_'+pservice).innerHTML;
	document.getElementById('sinfo').innerHTML = "svc: "+pservice+", levels: "+levels;
    } else {
	document.getElementById('sinfo').innerHTML = "";
    }
}
</script>
<?php


    
    print("<form name=\"service1\" id=\"service1\" action=\"services.php\">\n");
    print("<input type=\"hidden\" name=\"mode\" id=\"mode\" value=\"\">\n");
    print("<table class=notable cellpadding=\"5px\">\n");
    print("<tr>\n");
    print("<td colspan=2> <div style=\"color:#000;FONT: normal 10pt Tahoma,sans-serif;\">Сервисы: </div> </td>\n");
    print("</tr>\n");
    print("<tr>\n<td>\n");
    print("<span class=seldiv>\n<select name=\"s\" id=\"s\" multiple size=18 style=\"width:300px;\" onChange=\"javascript: renewInfo(this.value);\">\n");
    $line1="";
    foreach($aalist as $aakey => &$aaline) {
	if( trim($aaline=_trimline($aaline))=="") continue;
	$service=gettok($aaline,1," \t");
	$aaline1=explode(" ",_trimline(str_replace($service,"",$aaline)));
	$levels1="";
	for($l=0;$l<=6;$l++) 
	    if( substr_count($aaline1[$l],"on")>0) $levels1.=" $l";
	if(( !trim($levels1)) && ( !$shall)) continue;
	print("<option value=\"$service\">&nbsp;$service ".(( !trim($levels1)) ? "&nbsp;(off)":"")."</option>\n");
	$line1.="<div id=\"div_$service\" style=\"display:none;\">$levels1</div>\n";
    }
    print("</select>\n</span>\n");
    print("</td>\n<td valign=top style=\"padding-left:3px;\">\n");
    print("<input type=\"button\" value=\"Старт\" onClick=\"javascript: submtForm('start');\" style=\"width:105px;\"><br>\n");
    print("<input type=\"button\" value=\"Стоп\" onClick=\"javascript: submtForm('stop');\" style=\"width:105px;\"><br>\n");
    print("<input type=\"button\" value=\"Рестарт\" onClick=\"javascript: submtForm('restart');\" style=\"width:105px;\"><br>\n");
    print("<br>\n");
    print("<input type=\"button\" value=\"Процессы\" onClick=\"javascript: submtForm('proclist');\" style=\"width:105px;\"><br>\n");
    print("<br>\n");
    print("<input type=\"button\" value=\"Параметры\" onClick=\"javascript: submtForm('options');\" style=\"width:105px;\"><br>\n");
    print("<input type=\"button\" value=\"Конфиг\" onClick=\"javascript: submtForm('editconf');\" style=\"width:105px;\">\n");

    print("</td>\n</tr>\n");
    
    print("<tr>\n");
    print("<td colspan=2> <div id=\"sinfo\" style=\"color:#000;FONT: normal 10pt Tahoma,sans-serif;\"> </div> </td>\n");
    print("</tr>\n");
    
    print("</table>\n");
    print($line1);

    print("<input type=\"submit\" value=\"Ok\" style=\"display:none;\">\n");
    print("</form>\n");

    print("<br><br>\n");

    print("</blockquote>\n<br><br><br>\n");
    unset($aalist);
    
    ob_flush();
    ob_end_flush();
    
    wlog("Просмотра списка системных сервисов",0,FALSE,1,FALSE);

}
#-----------------------------------------------------------------
function svc_getlevels($pservice)
{
    global $iptconf_dir,$initd_dir,$_iptables,$_sudo,$_grep,$_chkconfig,$_top,$_awk;
    global $chkconfig_mode,$_sysv_rc_conf,$default_rctool,$shall;
    
    if( !trim($pservice)) {
	wlog("Не указано имя сервиса в service_options_form().",2,TRUE,5,TRUE); exit; 
    }
    
    if( $default_rctool=="chkconfig") {
	$line="$_chkconfig --list $pservice";
    } elseif( $default_rctool=="sysv_rc_conf") {
	$line="$_sysv_rc_conf --list  $pservice";
    } else {
	wlog("Ошибка: недоступен default_rctool, путь переменной: .$default_rctool.",2,TRUE,4,TRUE); exit;
    }

    list($rr,$svc)=_exec2($line);
    if(( count($svc)==0) or ( $rr!=0)) {
	wlog("Ошибка получения результатов $default_rctool..",2,TRUE,4,TRUE); exit;
    }
    foreach($svc as $skey => $sval) if(( substr_count($sval,":o")==0) || ( !trim($sval))) unset($svc[$skey]);
    $svc=( count($svc)==0) ? "":_trimline($svc[0]);
    return($svc);
}
#-----------------------------------------------------------------

function service_options_form($pservice)
{
    global $iptconf_dir,$initd_dir,$_iptables,$_sudo,$_grep,$_chkconfig,$_top,$_awk;
    global $chkconfig_mode,$_sysv_rc_conf,$default_rctool,$shall;
    
    if( !trim($pservice)) {
	wlog("Не указано имя сервиса в service_options_form().",2,TRUE,5,TRUE); exit; 
    }
    $svc=svc_getlevels($pservice);
    $svc_enabled=( substr_count($svc,":on")>0) ? TRUE:FALSE;
    $svc_levels=array();
    foreach(explode(" ",$svc) as $skey => $sval) {
	if(( $skey==0) || ( substr_count($sval,":o")==0)) continue;
	$svc_levels[gettok($sval,1,":")]=(( gettok($sval,2,":")=="on") ? TRUE:FALSE);
    }
    $svcconf="";
    $svclog="";
    $link=mysql_getlink();
    if( $res=mysql_query("SELECT * FROM services WHERE servicename=\"".$pservice."\"")) {
	if( !$svcconf=@mysql_result($res,0,"config_path")) $svcconf="";
	if( !$svclog=@mysql_result($res,0,"log_path")) $svclog="";
	mysql_free_result($res);
    }
    mysql_close($link);
    print("<br>\n");

    print("<div class=top1> Сервис: <b>$pservice</b> </div>\n<br>\n");
    print("<table class=table4 cellpadding=\"6px\" style=\"padding:6px;\">\n");
    print("<form name=\"svcedit1\" id=\"svcedit1\" action=\"services.php\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"svcsave\">\n");
    print("<input type=\"hidden\" name=\"s\" value=\"$pservice\">\n");
    print("<tr><td align=right> Состояние: </td><td> <b>".(( $svc_enabled) ? "Enabled":"Disabled")."</b> </td></tr>\n");
    print("<tr><td align=right> Уровни запуска: </td><td> \n");
    foreach($svc_levels as $level => $lstate) print("<label for=\"lv".$level."\">".$level."</label><input type=\"checkbox\" id=\"lv".$level."\" name=\"lv".$level."\" value=\"on\" ".(( $lstate) ? "checked":"")."> \n");
    print(" </td></tr>\n");
    print("<tr><td align=right> Файл конфигурации: </td><td> <input type=\"text\" size=65 name=\"svcconf\" value=\"$svcconf\"> </td></tr>\n");
    print("<input type=\"hidden\" name=\"svclog\" value=\"\">\n");
#    print("<tr><td align=right> Лог файл: </td><td> <input type=\"text\" size=65 name=\"svclog\" value=\"$svclog\"> </td></tr>\n");
    print("<tr>\n");
    print("<td align=left style=\"padding-left:20px;\"> <input type=\"button\" id=\"btnCncl\" value=\"Назад\" onClick=\"javascript: document.location.replace('services.php'); \"> </td>\n");
    print("<td align=right style=\"padding-right:20px;\"> <input type=\"submit\" value=\"Сохранить\"> </td>\n");
    print("</tr>\n");
    print("</form>\n");
    print("</table>\n");

}
#-----------------------------------------------------------------
function service_confedit_form($pservice)
{
    global $_mode,$_GET;
    if( !trim($pservice)) return(FALSE);
    if( $_mode!="editconf") return(FALSE);

    $svcconf="";
    $svclog="";
    $link=mysql_getlink();
    if(( !$res=mysql_query("SELECT * FROM services WHERE servicename=\"".$pservice."\"")) || ( !mysql_num_rows($res))) {
	print("Для сервиса $pservice не указан параметр конфига."); 
	return(FALSE);
    } else {
	if( !$svcconf=mysql_result($res,0,"config_path")) $svcconf="";
	if( !$svclog=mysql_result($res,0,"log_path")) $svclog="";
	mysql_free_result($res);
    } 
    mysql_close($link);
    if( !trim($svcconf)) {
	print("Для сервиса $pservice не указан параметр конфига."); 
	return(FALSE);
    }
    if( !file_exists($svcconf)) {
	print("Конфиг файл $svcconf не существует по указанному пути."); return(FALSE);
    }
    $aconf=array();
    if( !is_readable($svcconf)) {
	list($rr,$aconf)=_exec2("cat $svcconf");
	if( $rr!=0) {
	    print("Конфиг файл $svcconf не удалось прочитать даже через sudo cat .."); return(FALSE);
	}
    } else {
	if( !$aconf=file($svcconf)) {
	    print("Ошибка чтения конфиг файла $svcconf."); return(FALSE);
	}
    }

    print("<br>\n");

    print("<div class=top1> Сервис: <b>$pservice</b> </div>\n\n");
    print("<div class=text1> Конфиг: <b>$svcconf</b> </div>\n <br>\n");
    print("<table class=table4 cellpadding=\"6px\" style=\"padding:6px;\">\n");
    print("<form name=\"svcconfedit1\" id=\"svcconfedit1\" action=\"services.php\" method=\"POST\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"svcconfsave\">\n");
    print("<input type=\"hidden\" name=\"s\" value=\"$pservice\">\n");
    print("<tr><td colspan=2> <TEXTAREA name=\"svcconfbody\" wrap=\"off\" style=\"width:98%; height:550px;\">\n");
    foreach($aconf as $astr) print(iconv(mb_detect_encoding($astr,"KOI8-R,CP1251,UTF-8"),"koi8r",$astr));
    print("</TEXTAREA>\n</td></tr>\n");
    print("<tr>\n");
    print("<td align=left style=\"padding-left:20px;\"> <input type=\"button\" id=\"btnCncl\" value=\"Назад\" onClick=\"javascript: document.location.replace('services.php'); \"> </td>\n");
    print("<td align=right style=\"padding-right:20px;\"> <input type=\"submit\" value=\"Сохранить\"> </td>\n");
    print("</tr>\n");
    print("</form>\n");
    print("</table>\n");
    	        
    
    
}
#-----------------------------------------------------------------
function service_confedit_save($pservice)
{
    global $_grep,$_sudo,$_awk,$_mode,$_s,$_POST,$_echo;
    if( $_mode!="svcconfsave") return(FALSE);
    if( !trim($pservice)) {
	wlog("Не указано имя сервиса в service_options_save().",2,TRUE,5,TRUE); exit; 
    }
    
    if( !isset($_POST["svcconfbody"])) {
	print("Нет данных тела конфиг файла!"); return(FALSE);
    }

    $svcconf="";
    $svclog="";
    $link=mysql_getlink();
    if(( !$res=mysql_query("SELECT * FROM services WHERE servicename=\"".$pservice."\"")) || ( !mysql_num_rows($res))) {
	print("Для сервиса $pservice не указан параметр конфига."); 
	return(FALSE);
    } else {
	if( !$svcconf=mysql_result($res,0,"config_path")) $svcconf="";
	if( !$svclog=mysql_result($res,0,"log_path")) $svclog="";
	mysql_free_result($res);
    } 
    mysql_close($link);
    if( !trim($svcconf)) {
	print("Для сервиса $pservice не указан параметр конфига."); 
	return(FALSE);
    }
    $chmode=0;
    $chowne=0;
    $chgrpe=0;
    $svcconfcp="";
    $aconf=explode("\n",$_POST["svcconfbody"]);
    if( !file_exists($svcconf)) {
	print("Конфиг файл $svcconf не существует по указанному пути, будет создан новый (440 root:root).");
	foreach($aconf as $akey => $aline) $lines[]="echo \"".rtrim($aline,"\n\r")."\" | sudo tee -a ".$svcconf." >/dev/null";
    } else {
	$buf0=file_get_contents($svcconf,NULL,NULL,0,1024);
	if(( gettype($buf0)!="boolean") && ( strlen($buf0))) $svcconfcp=mb_detect_encoding($buf0,"KOI8-R, CP1251, UTF-8");
	$chmode=fileperms($svcconf);
	$buf1=posix_getpwuid(fileowner($svcconf));
	$chowne=$buf1["name"];
	$buf1=posix_getgrgid(filegroup($svcconf));
	$chgrpe=$buf1["name"];
	$lines[]="sudo mv -f ".$svcconf." ".$svcconf.".bak";
	foreach($aconf as $akey => $aline) {
	    if( $svcconfcp) $aline=iconv("koi8r",$svcconfcp,$aline);
	    $lines[]="echo \"".rtrim($aline,"\n\r")."\" | sudo tee -a ".$svcconf." >/dev/null";
	}
	$lines[]="sudo chown $chowne:$chgrpe $svcconf";
	$lines[]="sudo chmod $chmode $svcconf";
    }
#    print("debug: svcconfcp .$svcconfcp. buf1 .$buf0. <br>\n");
#    print_r($lines); 
#    print_r($_POST["svcconfbody"]); exit;
    if( count($lines)>0) _exec2($lines,TRUE,TRUE);

}

#-----------------------------------------------------------------
function service_options_save($pservice)
{
    global $iptconf_dir,$initd_dir,$_iptables,$_sudo,$_grep,$_chkconfig,$_top,$_awk;
    global $chkconfig_mode,$_sysv_rc_conf,$default_rctool,$_mode,$_s,$_GET;
    
    if( $_mode!="svcsave") return(FALSE);
    if( !trim($pservice)) {
	wlog("Не указано имя сервиса в service_options_save().",2,TRUE,5,TRUE); exit; 
    }
    if(( isset($_GET["svcconf"])) || ( isset($_GET["svclog"])) ) {
	$link=mysql_getlink();
	$res0=mysql_query("SELECT * FROM services WHERE servicename=\"".$pservice."\"");
	if( !mysql_num_rows($res0)) {
	    if( !$res=mysql_query("INSERT INTO services SET servicename=\"".$pservice."\",config_path=\"".$_GET["svcconf"]."\",log_path=\"".$_GET["svclog"]."\"")) {
		wlog("Ошибка добавления записи в таблицу services в service_options_save(): ".mysql_error(),2,TRUE,5,TRUE); 
		exit; 
	    }
	} else {
	    if( !$res=mysql_query("UPDATE services SET servicename=\"".$pservice."\",config_path=\"".$_GET["svcconf"]."\",log_path=\"".$_GET["svclog"]."\"")) {
		wlog("Ошибка обновления данных таблицы services в service_options_save(): ".mysql_error(),2,TRUE,5,TRUE); 
		exit; 
	    }
	}
	@mysql_free_result($res0);
	@mysql_free_result($res);
	mysql_close($link);
    }
    $svc_levelsupd=FALSE;
    $svc=svc_getlevels($pservice);
    $svc_levels=array();
    $cmd_levels="";
    $svc_rlevels="";
    foreach(explode(" ",$svc) as $skey => $sval) {
	if(( $skey==0) || ( substr_count($sval,":o")==0)) continue;
	$svc_levels[$lskey=gettok($sval,1,":")]=($lsval=( gettok($sval,2,":")));
	if( $lsval=="on") $svc_rlevels.=$lskey;
	if( ( isset($_GET["lv".$lskey])) && ( $_GET["lv".$lskey]=="on")) $cmd_levels.=$lskey;
    }
    if( $svc_rlevels!=$cmd_levels) $svc_levelsupd=TRUE;
    if(( $svc_levelsupd) && ( trim($cmd_levels))) {
	if( $default_rctool=="chkconfig") {
	    $line="$_chkconfig --level ".$cmd_levels." $pservice on";
	} elseif( $default_rctool=="sysv_rc_conf") {
	    $line="$_sysv_rc_conf --level ".$cmd_levels." $pservice on";
	} else {
	    wlog("Ошибка: недоступен default_rctool, путь переменной: .$default_rctool.",2,TRUE,4,TRUE); exit;
	}
    } elseif(( $svc_levelsupd) && ( !trim($cmd_levels))) {
	if( $default_rctool=="chkconfig") {
	    $line="$_chkconfig --level ".$svc_rlevels." $pservice off";
	} elseif( $default_rctool=="sysv_rc_conf") {
	    $line="$_sysv_rc_conf --level ".$svc_rlevels." $pservice off";
	} else {
	    wlog("Ошибка: недоступен default_rctool, путь переменной: .$default_rctool.",2,TRUE,4,TRUE); exit;
	}
    }
    if( $svc_levelsupd) {
	list($rr,$svc)=_exec2($line);
	if( $rr!=0)
	    wlog("Ошибка получения результатов $default_rctool..",2,TRUE,4,TRUE);
	else
	    wlog("Изменены параметры запуска системного сервиса $pservice",0,FALSE,1,FALSE);
    }

}
#-----------------------------------------------------------------


$_s=( isset($_GET["s"])) ? $_GET["s"] : (( isset($_POST["s"])) ? $_POST["s"]:"");
$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"]:"");
$run=( isset($_GET["r"])) ? TRUE:FALSE;
$shall=( isset($_GET["all"])) ? TRUE:FALSE;

print("<blockquote>\n<font class=top3>Системные сервисы</font><br>\n");
print("<hr size=1 align=left width=\"95%\">\n");

if( $_s=="") {
    if( ($_mode=="") or ($_mode=="list")) {
	if( !$run) {
	    show_load("services.php?r=1".(( $shall) ? "&all":""),"Загрузка данных $default_rctool...");
	} else {
	    services_form();
	}
    }
} else {

    if( $_mode=="proclist") {
	if( !$run) {
	    show_load("services.php?s=$_s&mode=$_mode&r=1","Поиск процессов...");
	} else {
	    show_processlist($_s);
	}
    } elseif( $_mode=="options") {

	service_options_form($_s);

    } elseif( $_mode=="editconf") {

	service_confedit_form($_s);

    } elseif( $_mode=="svcsave") {

	service_options_save($_s);
	services_form();

    } elseif( $_mode=="svcconfsave") {

	service_confedit_save($_s);
	services_form();

    } else {
	if( !$run) {
	    show_load("services.php?s=$_s&mode=$_mode&r=1","Выполнение команды...");
	} else {
	    service_action($_s,$_mode);
	}
	services_form();
    }

}

?>
</body>
</html>
