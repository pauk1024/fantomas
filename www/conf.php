<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: index.php
# Description: 
# Version: 2.8.2
###


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");
require("shapelib.php");
require("authlib.php");

if( session_id()=="") session_start();

$aafile=array();
if( file_exists($iptconf_dir."/1stconf")) {
    $aafile=file($iptconf_dir."/1stconf");
}

if( count($aafile)!=0) {
    if( session_id()!=trim($aafile[min(array_keys($aafile))]) ) {
	print("Ошибка авторизации сеанса..."); exit;
    }
} elseif( count($aafile)==0) {

    if( trim(session_id())!="") {
	session_destroy(); session_start();
    }
    session_regenerate_id();
    if( !$file=fopen($iptconf_dir."/1stconf","w")) {
	print("Ошибка открытия файла 1stconf на запись!"); exit;
    }
    fwrite($file,session_id());
    fclose($file);
    foreach($_SESSION as $skey =>$sval) unset($_SESSION[$skey]);
    $_SESSION["ssh_host"]=$ssh_host;
    $_SESSION["ssh_port"]=$ssh_port;
    $_SESSION["ssh_user"]=$ssh_user;
    $_SESSION["ssh_pass"]=$ssh_pass;
    $_SESSION["mysql_host"]=$mysql_host;
    $_SESSION["mysql_user"]=$mysql_user;
    $_SESSION["mysql_password"]=$mysql_password;
    $_SESSION["mysql_logfile"]=$mysql_logfile;
    $_SESSION["allowed_ip"]=$allowed_ip;
    $_SESSION["_passw_crypt_key"]=$_passw_crypt_key;
    $_SESSION["usr"]=array();
    $_SESSION["ifnets"]=array();
    $_SESSION["ifconfig"]=array();
    $_SESSION["way"]=array(
			"1" => array("desc" => "Параметры соединения SSH", "done" => FALSE),
			"2" => array("desc" => "Параметры доступа", "done" => FALSE),
			"3" => array("desc" => "Параметры MySQL", "done" => FALSE),
			"4" => array("desc" => "Сетевые интерфейсы и подсети", "done" => FALSE),
			"5" => array("desc" => "Поиск хостов клиентов", "done" => FALSE)
			);
    $_SESSION["fantomssessid"]=encrypt0(session_id());

}

require("include/des.php");
require("include/head1.php");
print("<body>\n");
#----------------------------------------------------------------------------------
function show_welcome()
{
    print("<center>\n<br><br>\n");
#    print("<img src=\"icons/tux50_916.jpg\">\n");
    print("<br><br><br>\n");
    print("<font class=top3 style=\"FONT: bold 18pt Tahoma;color:000080\"> Добро пожаловать в Fantomas Iptconf!</font><br>\n");
    print("<br><br><br><br><br><br>\n");
    print("<table class=table4 cellpadding=\"20px\" width=\"590px\"><tr><td align=center>\n");
    print("<font style=\"FONT: normal 11pt Tahoma;color:000080\"> ");
    print("Вас приветствует мастер начальной настройки Fantomas!<br> Этот мастер поможет Вам собирать информацию об используемой системе и произвести начальную настройку Программы. \n");
    print("<br>\n");
    print("</font><br>\n");
    print("</td></tr></table>\n");
    print("<br><br><br>\n");
    print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
    print("<input type=\"HIDDEN\" name=\"s\" value=\"1\">\n");
    print("<input type=\"BUTTON\" name=\"btn1\" id=\"btn1\" value=\"Начать\" onClick=\"javascript: document.getElementById('st').submit(); \">\n");
    print("</form>\n");
    
}
#----------------------------------------------------------------------------------
function show_usr_scan()
{
    global $submit,$users_dir,$grp,$grptitle,$subnet,$_nmap;
    global $_ifconfig,$_grep,$_awk,$_ip,$_SESSION,$mysql_fantomas_db,$submit_add,$iface,$submit_save;
    global $ifname,$iflocal,$ifnaf,$submit_cancel,$submit_del,$diface,$submit_resc,$_GET,$step,$self;

    $show=(( $submit_cancel) || ( $submit_del) || ( $submit_resc)) ? TRUE:(( !$submit) ? TRUE:FALSE);

    if( !isset($_SESSION["usr"])) $_SESSION["usr"]=array();
    
    if( $submit_resc) {
	if( isset($_SESSION["usr"])) unset($_SESSION["usr"]);
	$_SESSION["usr"]=array();
    }
    
    if( $submit_add) {
	if( !trim($subnet)) {
	    print("Выберите подсеть...<br>\n"); $show=TRUE;
	}
	if( !trim($grp)) {
	    print("Введите название группы...<br>\n"); $show=TRUE;
	}
	if( isset($_SESSION["usr"][$grp])) {
	    print("Группа с названием $grp уже cуществует...<br>\n"); $show=TRUE;
	}
	    list($rr,$aa)=_exec2("$_nmap -sP $subnet -n | $_awk '\$0~/^Host/ {print(\$2);}'");
	    if( $rr>0) {
		print("Ошибка получения списка хостов..."); $show=TRUE;
	    } else {
		foreach($aa as $aakey => $aaip) {
		    $fl=FALSE;
		    foreach($_SESSION["ifconfig"] as $_iface => $aaif) {
			if( trim($aaif["ip"])==trim($aaip)) { 
			    $fl=TRUE; break;
			}
		    }
		    $fl=( gettok($aaip,4,".")=="0") ? TRUE:$fl;
		    if( $fl) unset($aa[$aakey]);
		}
		$_SESSION["usr"][$grp]=array(
					"name" => $grp,
					"desc" => $grptitle,
					"userc" => count($aa),
#					"bysubnet" => $subnet,
					"userlist" => $aa
				    );
	    }
	    unset($aa);
    }
    if( $submit_del) {
	if( trim($grp)!="") {
	    unset($_SESSION["usr"][$grp]);
	}
	
    }

    if( $show) {
	print("<center><br><br>\n");
	print("<font class=top3 style=\"FONT: bold 16pt Tahoma;color:000080\"> Конфигурирование Fantomas Iptconf </font><br><br>\n");
	print("<font style=\"FONT: bold 12pt Tahoma;color:000080\">Шаг $step: Поиск хостов клиентов </font><br><br>\n");
	print("<hr size=1 width=\"600px\">\n<br>\n");
	
	print("<br><br><br>\n");

	print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
	print("<input type=\"HIDDEN\" name=\"s\" value=\"$step\">\n");
	print("<table class=table4 cellpadding=\"3px\" width=\"590px\">\n");
	print("<tr><th class=brd1> </th><th class=brd1> Группа </th><th class=brd1> Описание </th><th class=brd1> Количество<br>клиентов </th></tr>\n");
	print("<tr><td colspan=4>\n");
	print("<font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Группы клиентов:</font>\n");
	print("</td></tr>\n");
	
        if(( count($_SESSION["usr"])>0) && ( isset($_SESSION["usr"]))) foreach($_SESSION["usr"] as $grpname => $aagrp) {
	    print("<tr>\n");
	    print("<td class=tah11c> <input type=\"RADIO\" name=\"usrf\" id=\"usrf\" value=\"$grpname\"> </td>\n");
	    print("<td class=tah11c> $grpname </td><td class=tah11c> ".$_SESSION["usr"][$grpname]["desc"]." </td><td class=tah11c> ".$_SESSION["usr"][$grpname]["userc"]." </td></tr>\n");
        }
	
	print("<tr><td colspan=4 class=tah11c>\n");
	print("<input type=\"SUBMIT\" name=\"sbmtresc\" id=\"sbmtresc\" value=\"Обновить\">\n");
	if( count($_SESSION["usr"])>0) print("<input type=\"SUBMIT\" name=\"sbmtdel\" id=\"sbmtdel\" value=\"Удалить\">\n");
	print("</td></tr>\n");
	print("</table>\n");
	print("</form>\n");
	    
	print("<br>\n");
	print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
	print("<input type=\"HIDDEN\" name=\"s\" value=\"$step\">\n");
	print("<table class=table4 cellpadding=\"3px\" width=\"590px\">\n");
	print("<tr><td> <font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Добавленные подсети:</font>\n </td>\n");
	print("<td> <font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Параметры поиска:</font>\n </td></tr>\n");
	print("<tr><td rowspan=3 valign=top> \n");
	print("<SELECT name=\"subnet1\" id=\"subnet1\" multiple size=5 onClick=\"javascript: document.getElementById('subnet').value=this.value;\"> \n");


	foreach($_SESSION["ifconfig"] as $_iface => $aav) {
	    foreach($_SESSION["ifconfig"][$_iface]["nets"] as $nkey => $aanet) {
		$fl=FALSE;
    		foreach($_SESSION["usr"] as $grpname => $aagrp) {
    		    if( trim($_SESSION["usr"][$grpname]["bysubnet"])==trim($aanet["net"])) {
    			$fl=TRUE; break;
    		    }
    		}
		if( !$fl) print("<option value=\"".$aanet["net"]."\"> $_iface: ".$aanet["net"]."</option>\n");
	    }
	}
	print("</SELECT>\n ");
	print("<tr><td> \n");
	print("<table class=notable1>\n");
	print("<tr><td> Подсеть: </td><td> <input type=\"TEXT\" name=\"subnet\" id=\"subnet\" size=30> </td></tr>\n");
	print("<tr><td> Имя группы: </td><td> <input type=\"TEXT\" name=\"usrf\" id=\"usrf\" size=30 value=\"default\"> </td></tr>\n");
	print("<tr><td> Описание: </td><td> <input type=\"TEXT\" name=\"usrtitl\" id=\"usrtitl\" size=30 value=\"Группа по умолчанию\"> </td></tr>\n");
	print("</table>\n</td></tr>\n");
	print("<tr>\n<td style=\"padding-left:50px;\">\n");
	print("<input type=\"SUBMIT\" name=\"sbmtadd\" id=\"sbmtadd\" value=\"Поиск клиентов\">\n");
	print("</td></tr>\n");
	print("<tr><td colspan=2>\n");
	print("</td></tr>\n");
	print("</table>\n");
	print("<br>\n");
	print("<table class=notable width=\"590px\">\n<tr>\n");
	print("<td align=center valign=top> <input type=\"SUBMIT\" name=\"sbmtback\" id=\"sbmtback\" value=\"Назад\"> </td>\n");
	print("<td align=center valign=top> <input type=\"SUBMIT\" name=\"sbmt\" id=\"sbmt\" value=\"Завершить\">\n<br>\n");
	print("<font style=\"FONT: italic normal 8pt Arial;color:696969;\">Сохранить конфигурацию в config.php <br>и войти в web-интерфейс.</font>\n </td>\n");
	print("</tr>\n</table>");
	print("</form>\n");
	
    } else {
	$_SESSION["way"]["5"]["done"]=TRUE;
	print("<script type=\"text/javascript\">\n top.location.replace('".$self."?s=".($step+1)."');\n</script>\n");
    }
    
}



#----------------------------------------------------------------------------------
function show_nets_config()
{
    global $submit,$ss;
    global $_ifconfig,$_grep,$_awk,$_ip,$_SESSION,$mysql_fantomas_db,$submit_add,$iface,$submit_save;
    global $ifname,$iflocal,$ifnaf,$submit_cancel,$submit_del,$diface,$submit_resc,$_GET,$step,$self;

    $show=(( $submit_cancel) || ( $submit_del) || ( $submit_resc)) ? TRUE:(( !$submit) ? TRUE:FALSE);

    if( !isset($_SESSION["ifnets"])) $_SESSION["ifnets"]=array();
    if( !isset($_SESSION["ifconfig"])) $_SESSION["ifconfig"]=array();

    if(( count($_SESSION["ifnets"])==0) || ( $submit_resc)) {
	if( isset($_SESSION["ifnets"])) unset($_SESSION["ifnets"]);
	if( isset($_SESSION["ifconfig"])) unset($_SESSION["ifconfig"]);
	$_SESSION["ifnets"]=array();
	$_SESSION["ifconfig"]=array();

	list($rr,$aa)=_exec2("$_ifconfig -a | $_grep 'Link encap:' | awk '{print $1}'",FALSE,FALSE,TRUE);
	if(( $rr>0) && ( count($aa)>0)) {
	    print("Ошибка получения списка сетевых интерфейсов.."); exit;
	}
	foreach($aa as $aakey => $_iface) {
	    if(isset($aa1)) unset($aa1);
	    if(isset($bufip)) unset($bufip);
	    list($rr,$aa1)=_exec2("$_ifconfig -a $_iface | $_grep 'inet addr:' | awk '{print(substr($2,index($2,\":\")+1))}'",FALSE,FALSE,TRUE);
	    if(( $rr>0) && ( count($aa1)>0)) {
		print("Ошибка получения ip-адреса сетевого интерфейса $_iface"); exit;
	    }
	    $bufip=( count($aa1)>0) ? $aa1[min(array_keys($aa1))]:"";
	    unset($aa1);
	    list($rr,$aanets)=_exec2("$_ip route list | $_grep $_iface | $_awk '{ if( index($1,\".\")>0) print($1); }' | $_awk '{ if( length==0) exit; cc=split($0,aa,\" \"); for (j=1; j<=cc; j++) print( aa[j]); }'");
	    if(( $rr>0) && ( count($aanets)>0)) {
		print("Ошибка получения списка подсетей сетевого интерфейса $iface. rr .$rr. count .".count($aanets)."."); exit;
	    }
	    $_SESSION["ifnets"][$_iface]=array("ip" => $bufip, "nets" => $aanets);
	    unset($aanets);
	    unset($bufip);
	}
    }

    if( $submit_save) {
	$_SESSION["ifconfig"][$iface]=array(
		"ip" => $_SESSION["ifnets"][$iface]["ip"],
#		"nets" => $_SESSION["ifnets"][$iface]["nets"],
		"nets" => array(),
		"local" => $iflocal,
		"name" => $ifname
	    );
	foreach($_GET as $getkey => $getval) {
	    $bufkey=substr($getkey,0,4);
	    $kkey=substr($getkey,4);
	    if( $bufkey=="knet") {
		$_SESSION["ifconfig"][$iface]["nets"][$kkey]=array(
							    "net" => $_SESSION["ifnets"][$iface]["nets"][$kkey],
							    "naf" => FALSE
							    );
	    } elseif( $bufkey=="knaf") {
		$_SESSION["ifconfig"][$iface]["nets"][$kkey]=array(
							    "net" => $_SESSION["ifnets"][$iface]["nets"][$kkey],
							    "naf" => (( trim($getval)=="") ? FALSE:TRUE),
							    );
	    }
	}
	unset($_SESSION["ifnets"][$iface]);
    }
    if( $submit_del) {
	if( isset($_SESSION["ifconfig"][$diface])) $_SESSION["ifnets"][$diface]=array(
		"ip" => $_SESSION["ifconfig"][$diface]["ip"],
		"nets" => array()
	    );
	foreach($_SESSION["ifconfig"][$diface]["nets"] as $nkey => $aan) $_SESSION["ifnets"][$diface]["nets"][]=$aan["net"];
	unset($_SESSION["ifconfig"][$diface]);
    }
    if( $show) {
	print("<center><br><br>\n");
	print("<font class=top3 style=\"FONT: bold 16pt Tahoma;color:000080\"> Конфигурирование Fantomas Iptconf </font><br><br>\n");
	print("<font style=\"FONT: bold 12pt Tahoma;color:000080\">Шаг $step: Настройка сетевых интерфейсов и подсетей </font><br><br>\n");
	print("<hr size=1 width=\"600px\">\n<br>\n");

	print("<br>\n");
	if( !$submit_add) {

	    print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"s\" value=\"$step\">\n");
	    print("<table class=table4 cellpadding=\"3px\" width=\"650px\">\n");
	    print("<tr><th class=brd1> </th><th class=brd1> Название<br>подключения </th><th class=brd1> Имя<br>интерфейса </th><th class=brd1> IP </th><th class=brd1> Подсети </th></tr>\n");
	    print("<tr><td colspan=4>\n");
	    print("<font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Добавленные сетевые интерфейсы:</font>\n");
	    print("</td></tr>\n");
	    foreach($_SESSION["ifconfig"] as $_iface => $aav) {
		print("<tr>\n");
		print("<td> <input type=\"RADIO\" name=\"diface\" id=\"diface\" value=\"$_iface\"> </td>\n");
		print("<td class=f410> ".$aav["name"]." </td>\n");
		print("<td class=f410> IF name: $_iface ");
		print(( $aav["local"]) ? " <font style=\"FONT:normal 8pt Tahoma;\">Local</font>":"");
		print("</td>\n");
		print("<td class=f410> IP: ".$aav["ip"]."</td>\n<td class=f410>\n");
		foreach($_SESSION["ifconfig"][$_iface]["nets"] as $keynet => $vnet) {
		    print($vnet["net"]);
		    print(( $vnet["naf"]) ? " <font style=\"FONT:normal 8pt Tahoma;\">Not all fwd</font>":"");
		    
		    print("<br>\n");
		}
		print("</td></tr>\n");
	    }
	
	    print("<tr><td colspan=4>\n");
	    if( count($_SESSION["ifconfig"])>0) print("<input type=\"SUBMIT\" name=\"sbmtdel\" id=\"sbmtdel\" value=\"Удалить\">\n");
    	    print("</td></tr>\n");
	    print("</table>\n");
	    print("</form>\n");
	    
	    print("<br>\n");
	    print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"s\" value=\"$step\">\n");
	    print("<table class=table4 cellpadding=\"5px\" width=\"650px\">\n");
	    print("<tr><td colspan=2>\n");
	    print("<font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Найдены следующие сетевые интерфейсы:</font>\n");
	    print("</td></tr>\n");
	    print("<tr><td> <SELECT name=\"iface\" id=\"iface\" multiple size=4> \n");
	    foreach($_SESSION["ifnets"] as $_iface => $aav) print("<option value=\"$_iface\"> IF name: $_iface, IP:".$aav["ip"]."</option>\n");
	    print("</SELECT>\n</td>\n");
	    print("<td> <input type=\"SUBMIT\" name=\"sbmtadd\" id=\"sbmtadd\" value=\"Добавить\"> <br>\n");
	    print("<input type=\"SUBMIT\" name=\"sbmtresc\" id=\"sbmtresc\" value=\"Обновить список\">\n");
	    print("</td></tr>\n");
	    print("<tr><td colspan=2>\n");
	    print("</td></tr>\n");
	    print("</table>\n");
	    print("<br>\n");
	    print("<table class=notable width=\"590px\">\n<tr>\n");
	    print("<td align=center> <input type=\"SUBMIT\" name=\"sbmtback\" id=\"sbmtback\" value=\"Назад\"> </td>\n");
	    print("<td align=center> <input type=\"SUBMIT\" name=\"sbmt\" id=\"sbmt\" value=\"Далее\"> </td>\n");
	    print("</tr>\n</table>");
	    print("</form>\n");
	    
	} else {
	    print("<br>\n");
	    print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"s\" value=\"$step\">\n");
	    print("<input type=\"HIDDEN\" name=\"iface\" value=\"$iface\">\n");
	    print("<table class=table4 cellpadding=\"5px\">\n");
	    print("<tr><td colspan=2>\n");
	    print("<font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Добавление сетевого интерфейса $iface:</font>\n");
	    print("</td></tr>\n");
	    print("<tr><td class=wbrd1> Название подключения: </td><td class=wbrd1> <input type=\"TEXT\" name=\"ifname\" id=\"ifname\" size=35> </td></tr>\n");
	    print("<tr><td colspan=2 class=wbrd1> <input type=\"CHECKBOX\" name=\"iflocal\" id=\"iflocal\" value=\"1\"><label for=\"iflocal\"> Локальный интерфейс</label> </td></tr>\n");
	    print("<tr><td colspan=2 class=wbrd1> <b>Not All Forward</b> (Не создавать правило ACCEPT в цепочке FORWARD для всех членов подсети): <br><br>\n");
	    print("<span style=\"padding-left:35px;FONT: normal 9pt Tahoma;\">\n");
	    foreach($_SESSION["ifnets"][$iface]["nets"] as $knet => $vnet) {
		print("<input type=\"HIDDEN\" name=\"knet$knet\" value=\"knet$knet\">\n");
		print("<input type=\"CHECKBOX\" name=\"knaf$knet\" id=\"knaf$knet\" value=\"1\"><label for=\"knaf$knet\"> <b>$vnet</b> </label> <br>\n");
	    }
	    print("</td></tr>\n");
	    print("<tr><td>\n");
	    print("<input type=\"SUBMIT\" name=\"sbmtsave\" id=\"sbmtsave\" value=\"Добавить\">\n");
	    print("</td><td>\n");
	    print("<input type=\"SUBMIT\" name=\"sbmtcancl\" id=\"sbmtcancl\" value=\"Отмена\">\n");
	    print("</td></tr>\n");
	    print("</table>\n");
	    print("<br>\n");
	    print("</form>\n");
	}
    } else {
	$_SESSION["way"]["4"]["done"]=TRUE;
	print("<script type=\"text/javascript\">\n top.location.replace('".$self."?s=".($step+1)."');\n</script>\n");
    }
    
}



#----------------------------------------------------------------------------------
function show_etc_config()
{
    global $submit,$_SESSION,$allowed_ip;
    global $iptconf_dir,$step,$self,$_passw_crypt_key;
    $show=( !$submit) ? TRUE:FALSE;
    
    if( $show) {
	print("<center><br><br>\n");
	print("<font class=top3 style=\"FONT: bold 16pt Tahoma;color:000080\"> Конфигурирование Fantomas Iptconf </font><br><br>\n");
	print("<font style=\"FONT: bold 12pt Tahoma;color:000080\">Шаг $step: Параметры доступа </font><br><br>\n");
	print("<hr size=1 width=\"600px\">\n<br>\n");

	print("<br>\n");
	print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
	print("<input type=\"HIDDEN\" name=\"s\" value=\"$step\">\n");
	print("<table class=table4 cellpadding=\"5px\">\n");
	print("<tr><td colspan=2>\n");
	print("<font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Укажите следующие параметры:.</font>\n");
	print("</td></tr>\n");
	print("<tr><td valign=top> Allowed IP's </td><td> <input type=\"TEXT\" name=\"allowip\" id=\"allowip\" size=70 value=\"$allowed_ip\"> <br>\n");
	print("<font style=\"FONT: italic normal 8pt Arial;\">Список IP-адресов (через запятую без пробелов), с которых будет разрешен доступ <br>к web-интерфейсу Fantomas Iptconf.</font>");
	print("<tr><td colspan=2> &nbsp </td></tr>\n");
	print("<tr><td valign=top> Passw crypting key </td><td> <input type=\"TEXT\" name=\"pswkey\" id=\"pswkey\" size=35 value=\"$_passw_crypt_key\">\n");
	if( function_exists("md5")) print("<br>\n<font style=\"FONT: italic normal 8pt Arial;\">Заполнять необязательно, т.к. в Вашей системе доступно md5-хэширование.</font>");
	print(" </td></tr>\n");
	print("<tr><td colspan=2>\n");
#	print("<input type=\"SUBMIT\" name=\"sbmt\" id=\"sbmt\" value=\"Далее\">\n");
	print("</td></tr>\n");
	print("</table>\n");
	print("<br>\n");
	print("<table class=notable width=\"590px\">\n<tr>\n");
	print("<td align=center> <input type=\"SUBMIT\" name=\"sbmtback\" id=\"sbmtback\" value=\"Назад\"> </td>\n");
	print("<td align=center> <input type=\"SUBMIT\" name=\"sbmt\" id=\"sbmt\" value=\"Далее\"> </td>\n");
	print("</tr>\n</table>");
	print("</form>\n");
    } else {
	$_SESSION["allowed_ip"]=$allowed_ip;
	$_SESSION["_passw_crypt_key"]=$_passw_crypt_key;
	$_SESSION["way"]["2"]["done"]=TRUE;
	$submit=FALSE;
	print("<script type=\"text/javascript\">\n top.location.replace('".$self."?s=".($step+1)."');\n</script>\n");
    }
    
}


#----------------------------------------------------------------------------------
function show_mysql_config()
{
    global $submit,$mysql_host,$mysql_user,$mysql_password,$mysql_logfile,$_mysql,$_SESSION,$mysql_fantomas_db;
    global $iptconf_dir,$step,$self,$_passw_crypt_key;
    $show=( !$submit) ? TRUE:FALSE;
    
    if( $submit) {
	if( !$link=mysql_connect($mysql_host,$mysql_user,$mysql_password)) {
	    print("Ошибка подключения к MySQL!"); 
	    $show=TRUE;
	}
    }
    if( $show) {
	print("<center><br><br>\n");
	print("<font class=top3 style=\"FONT: bold 16pt Tahoma;color:000080\"> Конфигурирование Fantomas Iptconf </font><br><br>\n");
	print("<font style=\"FONT: bold 12pt Tahoma;color:000080\">Шаг $step: Параметры соединения MySQL </font><br><br>\n");
	print("<hr size=1 width=\"600px\">\n<br>\n");

	print("<br>\n");
	print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
	print("<input type=\"HIDDEN\" name=\"s\" value=\"$step\">\n");
	print("<table class=table4 cellpadding=\"5px\">\n");
	print("<tr><td colspan=2>\n");
	print("<font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Укажите параметры соединения MySQL:.</font>\n");
	print("</td></tr>\n");
	print("<tr><td> IP-адрес/Хост </td><td> <input type=\"TEXT\" name=\"mysqladdr\" id=\"mysqladdr\" size=35 value=\"$mysql_host\"> </td></tr>\n");
	print("<tr><td> Логин </td><td> <input type=\"TEXT\" name=\"mysqluser\" id=\"mysqluser\" size=35 value=\"$mysql_user\"> </td></tr>\n");
	print("<tr><td> Пароль </td><td> <input type=\"PASSWORD\" name=\"mysqlpass\" id=\"mysqlpass\" size=35 value=\"$mysql_password\"> </td></tr>\n");
	print("<tr><td> MySQL log </td><td> <input type=\"TEXT\" name=\"mysqllog\" id=\"mysqllog\" size=35 value=\"$mysql_logfile\"> </td></tr>\n");
	print("<tr><td colspan=2>\n");
#	print("<input type=\"SUBMIT\" name=\"sbmt\" id=\"sbmt\" value=\"Далее\">\n");
	print("</td></tr>\n");
	print("</table>\n");
	print("<br>\n");
	print("<table class=notable width=\"590px\">\n<tr>\n");
	print("<td align=center> <input type=\"SUBMIT\" name=\"sbmtback\" id=\"sbmtback\" value=\"Назад\"> </td>\n");
	print("<td align=center> <input type=\"SUBMIT\" name=\"sbmt\" id=\"sbmt\" value=\"Далее\"> </td>\n");
	print("</tr>\n</table>");
	print("</form>\n");
    } else {
	$_SESSION["mysql_host"]=$mysql_host;
	$_SESSION["mysql_user"]=$mysql_user;
	$_SESSION["mysql_password"]=$mysql_password;
	$_SESSION["mysql_logfile"]=$mysql_logfile;
	$_SESSION["way"]["3"]["done"]=TRUE;
	$submit=FALSE;
	print("<script type=\"text/javascript\">\n top.location.replace('".$self."?s=".($step+1)."');\n</script>\n");
    }
    
}


#----------------------------------------------------------------------------------
function show_ssh_config()
{
    global $submit,$ssh_host,$ssh_port,$ssh_user,$ssh_pass,$step,$self;
    $show=( !$submit) ? TRUE:FALSE;
    
    if( $submit) {
	if( !$link=@ssh2_connect($ssh_host,$ssh_port)) {
	    print("Ошибка подключения к $ssh_host:$ssh_port!"); 
	    $show=TRUE;
	}
	if( !$show) {
	    if( !@ssh2_auth_password($link,$ssh_user,$ssh_pass)) {
		print("Ошибка авторизации под логином $ssh_user на $ssh_host:$ssh_port!"); 
	        $show=TRUE;
	    }
	}
	if( !$show) {
	    list($rr,$aa)=_exec2("ls /root");
	    if( $rr!="0") {
		print("Указанный логин не имеет прав на выполнение команд через sudo.");
		$show=TRUE;
	    }
	}
    }
    if( $show) {
	print("<center><br><br>\n");
	print("<font class=top3 style=\"FONT: bold 16pt Tahoma;color:000080\"> Конфигурирование Fantomas Iptconf </font><br><br>\n");
	print("<font style=\"FONT: bold 12pt Tahoma;color:000080\">Шаг $step: Параметры соединения SSH </font><br><br>\n");
	print("<hr size=1 width=\"600px\">\n");
	print("<br><br><br>\n");

	print("<br><br>\n");
	print("<form name=\"st\" id=\"st\" action=\"conf.php\">\n");
	print("<input type=\"HIDDEN\" name=\"s\" value=\"$step\">\n");
	print("<input type=\"HIDDEN\" name=\"submit\" value=\"1\">\n");
	print("<table class=table4 cellpadding=\"5px\">\n");
	print("<tr><th colspan=2 class=wbrd1>\n");
	print("<font style=\"FONT: normal 11pt Tahoma;color:000080;\"> Укажите параметры соединения ssh:.</font>\n");
	print("</th></tr>\n");
	print("<tr><td class=wbrd1> IP-адрес/Хост </td><td class=wbrd1> <input type=\"TEXT\" name=\"sshaddr\" id=\"sshaddr\" size=35 value=\"$ssh_host\"> </td></tr>\n");
	print("<tr><td class=wbrd1> Порт </td><td class=wbrd1> <input type=\"TEXT\" name=\"sshport\" id=\"sshport\" size=6 value=\"$ssh_port\"> </td></tr>\n");
	print("<tr><td class=wbrd1> Логин </td><td class=wbrd1> <input type=\"TEXT\" name=\"sshuser\" id=\"sshuser\" size=35 value=\"$ssh_user\"> </td></tr>\n");
	print("<tr><td class=wbrd1> Пароль </td><td class=wbrd1> <input type=\"PASSWORD\" name=\"sshpass\" id=\"sshpass\" size=35 value=\"$ssh_pass\"> </td></tr>\n");
	print("<tr><td colspan=2>\n");
#	print("<input type=\"SUBMIT\" name=\"sbmt\" id=\"sbmt\" value=\"Далее\">\n");
	print("</td></tr>\n");
	print("</table>\n");
	print("<br><br>\n");
	print("<table class=notable width=\"590px\">\n<tr>\n");
	print("<td align=center> <input type=\"SUBMIT\" name=\"sbmtback\" id=\"sbmtback\" value=\"Назад\"> </td>\n");
	print("<td align=center> <input type=\"SUBMIT\" name=\"sbmt\" id=\"sbmt\" value=\"Далее\"> </td>\n");
	print("</tr>\n</table>");
	print("</form>\n");
    } else {
	$_SESSION["ssh_host"]=$ssh_host;
	$_SESSION["ssh_port"]=$ssh_port;
	$_SESSION["ssh_user"]=$ssh_user;
	$_SESSION["ssh_pass"]=$ssh_pass;
	$_SESSION["way"]["1"]["done"]=TRUE;
	print("<script type=\"text/javascript\">\n top.location.replace('".$self."?s=".($step+1)."');\n</script>\n");
    }
    
}

#----------------------------------------------------------------------------------
function save_config()
{
    global $_SESSION,$users_dir,$iptconf_dir,$_mysql,$_ipset,$sets_dir,$step;
    global $ssh_host,$ssh_port,$ssh_user,$ssh_pass;
    global $mysql_host,$mysql_user,$mysql_password,$mysql_logfile;
    global $allowed_ip,$_passw_crypt_key,$self,$mysql_fantomas_db;
    
    $aaopt=array(
	    "ssh_host" => $_SESSION["ssh_host"],
	    "ssh_port" => $_SESSION["ssh_port"],
	    "ssh_user" => $_SESSION["ssh_user"],
	    "ssh_pass" => $_SESSION["ssh_pass"],
	    "mysql_host" => $_SESSION["mysql_host"],
	    "mysql_user" => $_SESSION["mysql_user"],
	    "mysql_password" => $_SESSION["mysql_password"],
	    "mysql_logfile" => $_SESSION["mysql_logfile"],
	    "allowed_ip" => $_SESSION["allowed_ip"],
	    "_passw_crypt_key" => $_SESSION["_passw_crypt_key"]
	    );
    if( !options_save($aaopt)) {
	print("Ошибка при сохранении конфигурации!<br>\n");
    }

    if( !$link=mysql_connect($mysql_host,$mysql_user,$mysql_password)) die("Ошибка подключения к MySQL!"); 
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    if( !mysql_query("DROP DATABASE IF EXISTS $mysql_fantomas_db")) die("Ошибка удаления старой БД в MySQL");
    if( !mysql_query("CREATE DATABASE $mysql_fantomas_db")) die("Ошибка создания БД в MySQL");
    $line="$_mysql -u $mysql_user -p$mysql_password < $iptconf_dir/mysql/mysql.table";
    list($rr,$aa)=_exec2($line);
    if( $rr!="0") die("Ошибка создания таблиц данных в БД MySQL $mysql_fantomas_db. rr .$rr. count .".count($aa).".<br>".mysql_error($link));
    $admin_def_passwd=encrypt0("admin");
    mysql_select_db($mysql_fantomas_db);
    mysql_query("INSERT INTO users SET username=\"admin\",userpass=\"$admin_def_passwd\",description=\"Default Administrator\",isadmin=1,islocked=0,v=3");


    if( count($_SESSION["usr"])>0) foreach($_SESSION["usr"] as $grpname => $aagrp) {
	if( !$_id=makeID()) {
	    die("Ошибка получения ID для создания группы $grpname...");
	}
	$line="INSERT INTO groups SET id=$_id,name=\"$grpname\",title=\"".trim($aagrp["desc"])."\",default_policy=\"default\"";
	if( !mysql_query($line)) {
	    die("Ошибка создания группы $grpname!.<br>\n".mysql_error($link));
	}
	foreach($aagrp["userlist"] as $akey => $client) {
	    $clname=(trim($clname=gethostbyaddr($client))==trim($client)) ? "":trim($clname);
	    $line="INSERT INTO clients SET group_id=$_id,ip=\"$client\",policies=\"\",cname=\"$clname\"";
	    if( !mysql_query($line)) {
		die("Ошибка добавления клиента $client в группу $grpname!.<br>\n".mysql_error($link));
	    }

	}
    }
    
    if(isset($aa)) unset($aa);
    list($rr,$aa)=_exec2("$_ipset -nL locals");
    if( $rr==0) {
	_exec2(array(
	"$_ipset -F locals",
	"$_ipset -X locals",
	"$_ipset -N locals nethash --hashsize 1024 --probes 4 --resize 50"
	),FALSE,TRUE);
    }
    $aacmd=array();
    if(count($_SESSION["ifconfig"])>0) foreach($_SESSION["ifconfig"] as $_iface => $aav) {
	$line="INSERT INTO providers SET name=\"".$aav["name"]."\",local=\"".(( $aav["local"]) ? "1":"")."\",ip=\"".$aav["ip"]."\",ifname=\"$_iface\"";
	if( !mysql_query($line)) {
	    die("Ошибка добавления сетевого интерфейса $_iface!.<br>\n".mysql_error($link));
	}
	foreach($_SESSION["ifconfig"][$_iface]["nets"] as $nkey => $aanet) {
	    if( !trim($aanet["net"])) continue;
	    $line="INSERT INTO networks SET addr=\"".$aanet["net"]."\",local=".(( $aav["local"]) ? "1":"0").",notallfwd=".(( $aanet["naf"]) ? "1":"0")."";
	    if( !mysql_query($line)) {
		die("Ошибка добавления подсети ".$aanet["net"]."!.<br>line .$line.<br>\n".mysql_error($link));
	    }
	    if( $aav["local"]) $aacmd[]="$_ipset -A locals ".$aanet["net"];
	}
    }
    if( count($aacmd)>0) {
	$aacmd[]="$_ipset -S locals $sets_dir/locals";
	_exec2($aacmd,FALSE,TRUE);
    }
    if( file_exists($iptconf_dir."/ipsetlist")) unlink($iptconf_dir."/ipsetlist");
    $uff=fopen($iptconf_dir."/ipsetlist","a");
    fwrite($uff,"locals\n");
    $aasets=scandir($sets_dir);
    if( count($aasets)>0) foreach($aasets as $setname) {
	if( trim($setname)=="locals") continue;
	fwrite($uff,$setname."\n");
    }
    fclose($uff);




    unlink($iptconf_dir."/1stconf");
    session_destroy();
    print("<script type=\"text/javascript\">\n top.location.replace('index.php');\n</script>\n");
}
#----------------------------------------------------------------------------------



$self=substr($_SERVER["PHP_SELF"],strrpos($_SERVER["PHP_SELF"],"/")+1);

$step=( isset($_GET["s"])) ? $_GET["s"] : "";
$done=( isset($_GET["d"])) ? TRUE : FALSE;
$submit=( isset($_GET["sbmt"])) ? TRUE : FALSE;
$ss=( isset($_GET["ss"])) ? TRUE : FALSE;
$submit_add=( isset($_GET["sbmtadd"])) ? TRUE : FALSE;
$submit_back=( isset($_GET["sbmtback"])) ? TRUE : FALSE;
$submit_save=( isset($_GET["sbmtsave"])) ? TRUE : FALSE;
$submit_cancel=( isset($_GET["sbmtcancl"])) ? TRUE : FALSE;
$submit_del=( isset($_GET["sbmtdel"])) ? TRUE : FALSE;
$submit_resc=( isset($_GET["sbmtresc"])) ? TRUE : FALSE;
$iface=( isset($_GET["iface"])) ? $_GET["iface"] : "";
$diface=( isset($_GET["diface"])) ? $_GET["diface"] : "";


$ssh_host=( isset($_GET["sshaddr"])) ? $_GET["sshaddr"] : ( isset($_SESSION["ssh_host"]) ? $_SESSION["ssh_host"]:$ssh_host);
$ssh_port=( isset($_GET["sshport"])) ? $_GET["sshport"] : ( isset($_SESSION["ssh_port"]) ? $_SESSION["ssh_port"]:$ssh_port);
$ssh_user=( isset($_GET["sshuser"])) ? $_GET["sshuser"] : ( isset($_SESSION["ssh_user"]) ? $_SESSION["ssh_user"]:$ssh_user);
$ssh_pass=( isset($_GET["sshpass"])) ? $_GET["sshpass"] : ( isset($_SESSION["ssh_pass"]) ? $_SESSION["ssh_pass"]:$ssh_pass);

$mysql_host=( isset($_GET["mysqladdr"])) ? $_GET["mysqladdr"] : ( isset($_SESSION["mysql_host"]) ? $_SESSION["mysql_host"]:$mysql_host);
$mysql_user=( isset($_GET["mysqluser"])) ? $_GET["mysqluser"] : ( isset($_SESSION["mysql_user"]) ? $_SESSION["mysql_user"]:$mysql_user);
$mysql_password=( isset($_GET["mysqlpass"])) ? $_GET["mysqlpass"] : ( isset($_SESSION["mysql_password"]) ? $_SESSION["mysql_password"]:$mysql_password);
$mysql_logfile=( isset($_GET["mysqllog"])) ? $_GET["mysqllog"] : ( isset($_SESSION["mysql_logfile"]) ? $_SESSION["mysql_logfile"]:$mysql_logfile);

$allowed_ip=( isset($_GET["allowip"])) ? $_GET["allowip"] : ( isset($_SESSION["allowed_ip"]) ? $_SESSION["allowed_ip"]:$allowed_ip);
$_passw_crypt_key=( isset($_GET["pswkey"])) ? $_GET["pswkey"] : ( isset($_SESSION["_passw_crypt_key"]) ? $_SESSION["_passw_crypt_key"]:$_passw_crypt_key);

$ifname=( isset($_GET["ifname"])) ? $_GET["ifname"] : "";
$iflocal=( isset($_GET["iflocal"])) ? TRUE : FALSE;
$ifnaf=( isset($_GET["ifnaf"])) ? TRUE : FALSE;

$subnet=( isset($_GET["subnet"])) ? $_GET["subnet"] : "";
$grp=( isset($_GET["usrf"])) ? $_GET["usrf"] : "";
$grptitle=( isset($_GET["usrtitl"])) ? $_GET["usrtitl"] : "";



if( $submit_back) {
    print("<script type=\"text/javascript\">\n top.location.replace('".$self."?s=".($step-1)."');\n</script>\n");
}


print("<table class=netable width=\"95%\">\n");
print("<tr>\n<td width=\"300px\" align=left valign=top style=\"border-right:1px;border-right-style:dotted;border-right-color:A6CAF0;\">\n");
print("<br><br>\n");
print("<center><img src=\"icons/tux50_916.jpg\" title=\"Fantomas logo\"></center>\n");
print("<br><br><br>\n");

print("<span style=\"FONT: bolder 10pt Arial;color:330066;padding-left:10px;\">Шаги:</span><br>\n");
print("<table class=notable width=\"100%\" style=\"border-top:1px;border-top-style:dotted;border-top-color:A6CAF0;\">\n");
#print("<tr><td colspan=2 style=\"height:25px;\"> </td></tr>\n");
foreach($_SESSION["way"] as $_step => $aastep) {
    print("<tr>\n");
    print("<td width=\"35px\" style=\"height:25px;\"> ".(( $step==$_step) ? "<font style=\"FONT:bolder 12pt Tahoma;color:f1ba8d;\">&#9658</font>":"")." </td>\n");
    print("<td width=\"245px\" style=\"height:25px;\"> <font style=\"FONT:bolder 10pt Tahoma;color:".(( $aastep["done"]) ? "5eb131":"d87575").";\">".$_step.".&nbsp ".$aastep["desc"]."</font> </td>\n");
    print("</tr>\n");
}
print("</table>\n");
print("</td><td align=left valign=top>\n");



if(( $step=="") || ( $step=="0")) {

    if( !$done) {
	show_welcome();
    } else {
	print("DONE! <br>\n");
    }

} elseif( $step=="1") {

    if( !$ss) {
	show_load($_SERVER["REQUEST_URI"]."&ss=1","Обработка параметров SSH...");
    } else {
	show_ssh_config();
    }

} elseif( $step=="2") {

    if( !$ss) {
	show_load($_SERVER["REQUEST_URI"]."&ss=1","Обработка параметров доступа...");
    } else {
	show_etc_config();
    }

} elseif( $step=="3") {

    if( !$ss) {
	show_load($_SERVER["REQUEST_URI"]."&ss=1","Обработка параметров MySQL...");
    } else {
        show_mysql_config();
    }

} elseif( $step=="4") {

    if( !$ss) {
	show_load($_SERVER["REQUEST_URI"]."&ss=1","Обработка параметров подключений...");
    } else {
	show_nets_config();
    }

} elseif( $step=="5") {

    if( !$ss) {
	show_load($_SERVER["REQUEST_URI"]."&ss=1","Обработка списков клиентов...");
    } else {
	show_usr_scan();
    }

} elseif( $step=="6") {

    if( !$ss) {
	show_load($_SERVER["REQUEST_URI"]."&ss=1","Сохранение конфигурации...");
    } else {
	save_config();
    }
}

print("</td>\n</tr>\n");
print("</table>\n");







?>