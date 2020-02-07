<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.6
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: shaper.php
# Description: 
# Version: 0.2.4.6
###


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");
require("shapelib.php");

$flAdminsOnly=TRUE;
require("auth.php");

print("<html>\n");
$NoHeadEnd=FALSE;
require("include/head1.php");

print("<body><br><br>\n");

#-------------------------------------------------------------
function conf_load_clients($filterifs=FALSE)
{
    global $iptconf_dir;
    global $_ifname,$_ifbifname;
    if( !$fshaperconf=fopen("$iptconf_dir/shaperconf","r")) {
	$ret=FALSE;
    } else {
	$ret=array();
	while( !feof($fshaperconf)) {
	    $str=trim(fgets($fshaperconf));
	    if( strlen($str)==0) continue;
	    if( $str[0]=="#") continue;
	    if( substr_count(gettok($str,1," \t"),"device")>0) continue;
	    $bufclient=""; $bufgrp=""; $bufratein=""; $bufrateout=""; $bufifname=""; $bufifbifname=""; $bufstatus="";
	    $ic=coltoks($str," \t");
	    for($i=1;$i<=$ic;$i++) {
		$bufi=gettok($str,$i," \t");
		$buf1=gettok($bufi,1,"=:");
		if( $buf1=="grp") {
		    $bufgrp=gettok($bufi,2,":=");
		} elseif( $buf1=="client") {
		    $bufclient=gettok($bufi,2,":=");
		} elseif( $buf1=="ratein") {
		    $bufratein=gettok($bufi,2,":=");
		} elseif( $buf1=="rateout") {
		    $bufrateout=gettok($bufi,2,":=");
		} elseif( $buf1=="ifname") {
		    $bufifname=gettok($bufi,2,":=");
		} elseif( $buf1=="ifbifname") {
		    $bufifbifname=gettok($bufi,2,":=");
		} elseif( $buf1=="status") {
		    $bufstatus=gettok($bufi,2,":=");
		}
	    }
	    if( $filterifs) {
		if(( $bufifname!=$_ifname) or ( $bufifbifname!=$_ifbifname)) continue;
	    }
	    $ret[count($ret)]=array( "client" => $bufclient, 
					"grp" => $bufgrp, 
					"ratein" => $bufratein, 
					"rateout" => $bufrateout,
					"ifname" => $bufifname,
					"ifbifname" => $bufifbifname,
					"status" => $bufstatus
				    );
	}
    }
    return($ret);

}
#-----------------------------------------------------------------

$_client=( isset($_GET["client"])) ? $_GET["client"] : "";
$_loadconf=( isset($_GET["loadconf"])) ? TRUE : FALSE;
$_grp=( isset($_GET["grp"])) ? $_GET["grp"] : "";
$_ratein=( isset($_GET["rin"])) ? $_GET["rin"] : "";
$_rateout=( isset($_GET["rout"])) ? $_GET["rout"] : "";
$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : "";
$_nolist=( isset($_GET["nolst"])) ? $_GET["nolst"] : "";
$_run=( isset($_GET["run"])) ? $_GET["run"] : "";
$_ifname=( isset($_GET["ifname"])) ? $_GET["ifname"] : "";
$_ifbifname=( isset($_GET["ifbifname"])) ? $_GET["ifbifname"] : "";

if( trim($_ratein)!="") {
    if( trim($_ratein)=="0") { $_ratein=""; } else {
	if( substr_count("kbitmbitkbpsmbps",substr($_ratein,strlen($_ratein)-4))==0) $_ratein=trim($_ratein)."kbit";
    }
}
if( trim($_rateout)!="") {
    if( trim($_rateout)=="0") { $_rateout=""; } else {
	if( substr_count("kbitmbitkbpsmbps",substr($_rateout,strlen($_rateout)-4))==0) $_rateout=trim($_rateout)."kbit";
    }
}


if( $_mode=="add") {
    if( trim($_run)=="") {
	print("<br><br>\n<div style=\"padding-left:80px\">\n");
	shaper_show_add_form();
	print("</div>\n");
	exit;
    } else {
	shaper_save_client();
    }
} elseif( $_mode=="enable") {
    if( shape_getstatus()) { 
	wlog("Шейпер уже загружен!",2,TRUE,4,TRUE); exit;
    } else {
	shaper_up($_ifbifname,$_ifname);
	if( $_loadconf) {
	    $aclients=conf_load_clients(TRUE);
	    foreach($aclients as $aclient) {
		if( trim($aclient["status"])=="1") continue;
		$_ratein=( trim($aclient["ratein"])!="") ? $aclient["ratein"] : "";
		$_rateout=( trim($aclient["rateout"])!="") ? $aclient["rateout"] : "";
		if( !$_client=trim($aclient["client"])) continue;
		$_grp=trim($aclient["grp"]);
		shaper_save_client();
	    }
	}
    }

} elseif( $_mode=="disable") {
    if( !shape_getstatus()) { 
	wlog("Шейпер еще не уже загружен!",2,TRUE,4,TRUE); exit;
    } else {
	shaper_down($_ifbifname);
    }

} elseif( $_mode=="del") {
    
    if( !shape_getstatus()) { 
	wlog("Шейпер еще не уже загружен!",2,TRUE,4,TRUE); exit;
    } else {
	shaper_del_client(1);
    }

} elseif( $_mode=="stop") {
    
    if( !shape_getstatus()) { 
	wlog("Шейпер еще не уже загружен!",2,TRUE,4,TRUE); exit;
    } else {
	shaper_del_client(0);
    }

} elseif( $_mode=="start") {
    
    if( !shape_getstatus()) { 
	wlog("Шейпер еще не уже загружен!",2,TRUE,4,TRUE); exit;
    } else {
	shaper_save_client();
    }

}

if( $_nolist=="") {


    print("<br>\n");
    print("<font class=top1>Ограничение скорости сетевого трафика:</font><br><br>\n");
    print("<font class=text42s>Состояние: <font style=\"color:".(( shape_getstatus()) ? "teal;\">ВКЛЮЧЕН" : "maroon;\">ВЫКЛЮЧЕН")."</font><br></font>\n");
    print("<div style=\"width:450px;float:left;\">");
    if( $_shaper_enable) {
	if( !shape_getstatus()) {
	    print("<table class=table4 cellpadding=\"2px\" style=\"padding:4px;\" width=\"auto\">\n<form name=\"ifbup1\" id=\"ifbup1\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" value=\"enable\">\n");
	    print("<tr><td class=td21> IFB IFace </td><td> <input type=\"TEXT\" name=\"ifbifname\" id=\"ifbifname\" size=12 value=\"$_shaper_default_ifbifname\"> </td></tr>\n");
	    print("<tr><td class=td21> HW IFace </td><td> <span class=seldiv><SELECT name=\"ifname\" id=\"ifname\">\n");
	    $alist=get_iflist(1);
	    foreach($alist as $alistkey => $alistvalue) print("<option value=\"$alistvalue\"".(( trim($alistvalue)==trim($_shaper_default_ifname)) ? " SELECTED" : "").">$alistvalue</option>\n");
	    print("</SELECT></span></td></tr>\n");
	    print("<tr><td class=td21 colspan=2> <input type=\"CHECKBOX\" name=\"loadconf\" id=\"loadconf\" value=\"1\" CHECKED><label for=\"loadconf\"> Загрузить правила клиентов</label>  </td></tr>\n");
	    print("<tr><td colspan=2> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Включить\" /> </td></tr>\n");
	    print("</form>\n</table>\n<br>\n");
	} else {
	    list($rr,$arez)=_exec2("$_ifconfig | $_grep 'Link encap' | awk '{ print $1 }' | $_grep ifb");
	    print("<table class=table4 cellpadding=\"2px\" width=\"auto\">\n \n");
	    foreach($arez as $arezkey => $arezvalue) {
		if( isset($aar)) unset($aar);
		list($rr,$rezz)=_exec2("$_sudo $_tc qdisc show | grep ingress | awk '{ print $5 }'");
		if( count($rezz)==0) continue;
		print("<form name=\"ifbdown$arezkey\" id=\"ifbdown1\">\n");
		print("<input type=\"HIDDEN\" name=\"mode\" value=\"disable\">\n");
		print("<input type=\"HIDDEN\" name=\"ifbifname\" value=\"$arezvalue\">\n");
		print("<tr><td class=td21> HW:$rezz to IFB:$arezvalue </td><td> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Выключить\" /> </td></tr>\n");
		print("</form>\n");
	    }
	    print("</table>\n");
	}
    } else {
	print("Состояние: <br>Отключен в конфигурации</font>\n");
    }
    print("</div>\n<div style=\"float:left;padding-left:2px;\">");
    print("<table class=table5d><tr><td style=\"padding-left:10px;padding-right:10px\"><a href=\"shaper.php?mode=add\" title=\"Добавить клиента\"><img src=\"icons/list-new.gif\" title=\"Добавить клиента\"></a></td></tr></table>\n");
    print("</div>\n<br><br><br>\n");
    
    print("<table id=\"table_t\" class=table1 cellpadding=\"4px\" width=\"740px\"> \n");
    print("<tr><th class=thp> Клиент </th><th class=thp> Входящая<br>скорость </th><th class=thp> Исходящая<br>скорость </th><th class=thp> HW<br>Интерфейс </th><th class=thp> IFB<br>Интерфейс </th><th class=thp> Статус </th></tr>\n");
    
    if(isset($aclients)) unset($aclients);
    $aclients=conf_load_clients();
    foreach($aclients as $aclient) {
	print("<tr><td> ".$aclient["grp"]."<b>".(( trim($aclient["grp"])!="") ? ":" : "")."</b>".$aclient["client"]." </td><td> ".$aclient["ratein"]." </td><td> ".$aclient["rateout"]." </td><td> ".$aclient["ifname"]." </td><td> ".$aclient["ifbifname"]." </td>\n");
	print("<td> ".shaper_get_status($aclient["client"])." </td>\n");
	print("<td style=\"padding-left:5px;padding-right:5px;border-style:solid;\">\n");
	print("<a href=\"shaper.php?mode=del&client=".$aclient["client"]."&ifname=".$aclient["ifname"]."\" title=\"Удалить правила клиента (удаление правил из памяти и из конфига)\"><img src=\"icons/gtk-cancel_16.gif\" title=\"Удалить правила клиента (удаление правил из памяти и из конфига)\"></a>\n");
      if( shape_getstatus()) {
	if( shaper_check_client($aclient["client"])) {
	    print(" <a href=\"shaper.php?mode=stop&client=".$aclient["client"]."&grp=".$aclient["grp"]."&ifname=".$aclient["ifname"]."&ifbifname=".$aclient["ifbifname"]."&rin=".$aclient["ratein"]."&rout=".$aclient["rateout"]."\" title=\"Отменить правила клиента (удалить из памяти, но сохранить их в конфиге)\"><img src=\"icons/gtk-delete16.gif\" title=\"Отменить правила клиента (удалить из памяти, но сохранить их в конфиге)\"></a>\n");
	} else {
	    print(" <a href=\"shaper.php?mode=start&client=".$aclient["client"]."&grp=".$aclient["grp"]."&ifname=".$aclient["ifname"]."&ifbifname=".$aclient["ifbifname"]."&rin=".$aclient["ratein"]."&rout=".$aclient["rateout"]."\" title=\"Применить правила\"><img src=\"icons/apply16.gif\" title=\"Применить правила\"></a>\n");
	}
      }
	print("</td></tr>\n");

    }

    
    print("</table>\n");
    print("<hr width=\"300px\" align=left><br><br>\n");
    wlog("Просмотр списка правил шейпера",0,FALSE,1,FALSE);
    print("<br><br>\n</body></html>  \n");


}

?>