<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.6
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: networks.php
# Description: 
# Version: 0.2.4.6
###


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : "";
$_net=( isset($_GET["net"])) ? $_GET["net"] : "";
$_local=( isset($_GET["local"])) ? $_GET["local"] : "";
$_count=( isset($_GET["count"])) ? $_GET["count"] : "";
$_notallfwd=( isset($_GET["notallfwd"])) ? $_GET["notallfwd"] : "";
$_show=( isset($_GET["s"])) ? TRUE:FALSE;
$_reload=( isset($_GET["reload"])) ? TRUE:FALSE;
$_nopkts=( isset($_GET["nopkts"])) ? TRUE:FALSE;
$_timeout=( isset($_GET["timeout"])) ? $_GET["timeout"] : "$monitor_delay";



#-------------------------------------------------------------------------

function load_lost_nets()
{
    global $iptconf_dir,$_ifconfig,$_ip,$_grep;
    $aa1=array();
    $ac=0;
    list($rr,$aout1)=_exec2("$_ifconfig | $_grep eth");
    if($rr!=0) { wlog("Error executing command1 in load_lost_nets()..<br> rr .$rr. count .".var_dump($aout1).".",2,TRUE,5,FALSE); exit; }
    foreach($aout1 as $akk1 => $avv1) {
	$buf1=gettok(trim($avv1),1," \t");
	if( isset($aout2)) unset($aout2);
	list($rr2,$aout2)=_exec2("$_ifconfig $buf1 | $_grep 'inet addr'");
	if($rr2!=0) { wlog("Error executing command2 in load_lost_nets()..",2,TRUE,5,FALSE); exit; }
	foreach($aout2 as $akk2 => $avv2) {
	    if( substr(trim($avv2),0,9)!="inet addr") { continue 1; } else {
		$buf2=gettok(substr(trim($avv2),10),1," \t");
		$buf2=substr($buf2,0,strrpos($buf2,"."));
		if( isset($aout3)) unset($aout3);
		list($rr3,$aout3)=_exec2("$_ip route list | $_grep $buf2");
		if($rr3!=0) { wlog("Error executing command3 in load_lost_nets()..",2,TRUE,5,FALSE); exit; }
		foreach($aout3 as $akk3 => $avv3) {
		    $buf3=gettok(trim($avv3),1," \t");
		    if( substr_count($buf3,".")>0) {
			$aa1[$ac]=$buf3; $ac++;
		    }
		}
	    }
	}
    }
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM networks WHERE 1")) {
	wlog("Error loading networks list...",2,TRUE,5,FALSE); exit; 
    }
    while( $row=mysql_fetch_array($res)) {
	$buf11=$row["addr"];
	foreach($aa1 as $aa1kk => $aa1vv) if( trim($buf11)==trim($aa1vv)) unset($aa1[$aa1kk]);
    }
    return($aa1);

}

#------------------------------------------------------------------------

function show_lost_nets()
{
    $aaa=load_lost_nets();
    if( count($aaa)==0) return("");
    
    print("<br><br><br>   \n");
    print("<font class=top1>Доступные для добавления подсети</font><br>   \n");
    print("<form name=\"form331\" action=\"networks.php\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"addnet\">   \n");
    print("<table class=table5e width=\"400px\">   \n");
    print("<tr><td>Адрес: </td><td> <select name=\"net\" multiple> \n");
    foreach($aaa as $akk => $avv) print("<option value=\"$avv\">$avv\n");
    print("</select>\n</td> \n");
    print("<td> <input type=\"SUBMIT\" name=\"sbmt1\" value=\"Добавить\"> </td></tr>   \n");
    print("</table> \n  </form>\n");


}

#-----------------------------------------------------------------------

function edit_net($pnet,$pnew=FALSE)
{
    global $iptconf_dir;
    if( trim($pnet)=="") exit;
    $link=mysql_getlink();
    print("   \n");
    print("<br><br><br><font class=top1>Network $pnet <br> <br><br>\n ");
    print("<table class=table5e width=\"400px\" cellpadding=\"6px\">\n<tr><td colspan=2><br> </td></tr>\n");
    print("<form name=\"form243\" action=\"networks.php\">   \n");
    print("<input type=\"hidden\" name=\"net\" value=\"$pnet\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"".(( $pnew) ? "savenew":"save")."\">   \n");
    
  if( !$pnew) {

    if( !$res=mysql_query("SELECT * FROM networks WHERE addr=\"$pnet\"")) {
	wlog("Error searching network $pnet!",2,TRUE,5,FALSE); exit; 
    }
    $row=mysql_fetch_array($res);
    $bufnet=$row["addr"];
    $buflocal=( $row["local"]=="1") ? "1":"";
    $bufnotallfwd=( $row["notallfwd"]=="1") ? "1":"";
    
  } else {
    
    $buflocal=""; $bufnotallfwd="";
  }
    
    print("<tr><td> Адрес подсети </td><td> <p><i>$pnet <input type=\"hidden\" name=\"net\" value=\"$pnet\"></i></p> </td></tr>   \n");
    print("<tr><td colspan=2> <input type=\"checkbox\" name=\"local\" id=\"local\" ".((trim($buflocal)!="") ? "checked" : "")." value=\"1\"> <label for=\"local\"><b>Local</b> <i> <font style=\"font-size:9pt\">  (признак локального интерфейса)</label> </td></tr>   \n");
    print("<tr><td colspan=2> <input type=\"checkbox\" name=\"notallfwd\" id=\"notallfwd\" ".((trim($bufnotallfwd)!="") ? "checked" : "")."> <label for=\"notallfwd\"><b>Not All Forward</b> <i> <font style=\"font-size:9pt\"> (Не создавать правило ACCEPT в цепочке FORWARD для всех членов подсети)</label> </td></tr>   \n");

    print("<tr><td colspan=2><input type=\"submit\" value=\"Сохранить\"><br><br><br>  </td></tr> ");
    print("</table>  \n</form>\n");
    print("<br><font class=td2> \n");
    print("<table class=notable><tr><td> <a href=\"networks.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"networks.php\" title=\"Назад\"><b>Назад</b> </a><font style=\"FONT: italic 9pt Arial;\">( не сохранять изменения )</font> </td></tr></table>\n");
    print("  </td></tr></table>\n");


}

#-------------------------------------------------------------------------


function show_addnet_form()
{
    print("<br><br><br>   \n");
    print("<font class=top1>Новая подсеть вручную</font><br>   \n");
    print("<form name=\"form33\" action=\"networks.php\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"addnet\">   \n");
    print("<table class=table5e width=\"400px\">   \n");
    print("<tr><td>Адрес: </td><td> <input type=\"text\" name=\"net\" size=25> </td> \n");
    print("<td> <input type=\"SUBMIT\" name=\"sbmt1\" value=\"Создать\"> </td></tr>   \n");
    print("</table> \n  </form>\n");


}

#-----------------------------------------------------------------------


function show_netlist()
{
    global $iptconf_dir;

    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM networks WHERE 1")) {
	wlog("Error loading networks list...",2,TRUE,5,FALSE); exit; 
    }

    print("   \n");
    print("<br><br><br><font class=top1>Networks list<br> \n <font class=text41><br><br>\n ");
    print("<table class=table1 width=\"650px\" cellpadding=\"6px\">\n");

    while( $row=mysql_fetch_array($res)) {
	$bufnet=$row["addr"];
	$buflocal=( $row["local"]=="1") ? "1":"";
	$bufnotallfwd=( $row["notallfwd"]=="1") ? "1":"";

        print("<tr><td width=\"30%\" style=\"font-size:12pt\"><b> $bufnet </b><td width=\"55%\"><font style=\"color:4682B4; font-style:italic\"> \n");

	if( $buflocal!="") print(" <li> Local <br> \n");
	if( $bufnotallfwd!="") print(" <li> Not All FORWARD <br> \n");

	print("</td><td class=td3 width=\"15%\"><a href=\"networks.php?net=$bufnet&mode=edit\" title=\"Редактировать\"><img src=\"icons/gtk-edit.gif\" title=\"Редактировать\"></a> &nbsp  \n");
	print("<a href=\"networks.php?net=$bufnet&mode=del\" title=\"Удалить\"><img src=\"icons/edittrash.gif\" title=\"Удалить\"></a> </td></tr>  \n");

    }
    print("</table>\n");


}

#-----------------------------------------------------------------------


function show_netlist_traf()
{
    global $iptconf_dir,$_iptables,$_awk;
    global $_nopkts,$_timeout,$_reload;


    print("<br>   \n");
    print("<table class=notable width=\"90%\"><tr>\n");
    print("<td align=left> <font class=top2>Трафик по подсетям</font></td>\n");
    print("<td align=right> <a href=\"".$_SERVER["REQUEST_URI"]."\" title=\"Обновить\"><img src=\"icons/reload.png\" title=\"Обновить\"></a> </td>\n");
    print("</tr>\n</table>\n");
    print("<br>\n <font class=text41>\n ");
    print("<hr size=1 align=left width=\"90%\">\n\n");

    print("<table class=notable width=\"90%\" style=\"padding:2px;\">\n<tr><td> &nbsp </td><td width=\"400px\">\n");
    print("Настройки:<br>");
    print("<table class=table4 cellpadding=\"2px\" width=\"400px\">\n");
    print("<form name=\"opt1\" action=\"networks.php\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" value=\"shtraf\">\n");
    print("<tr>\n<td>\n");
    print("<input type=\"CHECKBOX\" name=\"nopkts\" id=\"nopkts\" value=\"1\" ".(( $_nopkts) ? "checked":"")."><label for=\"nopkts\">Не показывать количество пакетов</label>\n");
    print("</td><td rowspan=3>\n");
    print("<input type=\"SUBMIT\" name=\"sbmt\" value=\"OK\">\n\n");
    print("</td>\n</tr>\n<tr>\n<td>\n");
    print("<input type=\"CHECKBOX\" name=\"reload\" id=\"reload\" value=\"1\" ".(( $_reload) ? "checked":"")."><label for=\"reload\">Обновлять по таймауту:</label>\n");
    print("</td>\n</tr>\n<tr>\n<td>\n");
    print("&nbsp <input type=\"TEXT\" name=\"timeout\" value=\"".$_timeout."\">\n");
    print("</td>\n</tr>\n</form>\n</table>\n");
    print("</td></tr></table>\n");
    
    print("<br><br><br>\n");
    

    $time1=time();
    
    print("<span style=\"width:90%; height:1em; display:inline-block;text-align:right;margin:0; padding:0;\">");
    print("<table class=table1 cellpadding=\"5px\" width=\"100%\">\n");
    
    if( !$_nopkts) {
	print("<tr>\n");
	print("<th colspan=1 rowspan=3> <b>Подсеть</b> </th>\n");
	print("<th colspan=4> <b>Транзитный трафик</b> </th>\n");
	print("<th colspan=4> <b>Локальный трафик</b> </th>\n");
	print("<th colspan=4> <b>Общий трафик</b> </th>\n");
	print("</tr>\n");
	print("<tr>\n");
	print("<th colspan=2 style=\"height:15px; FONT: normal 10pt Tahoma;\"> Входящий </th> <th colspan=2 style=\"height:15px; FONT: normal 10pt Tahoma;\"> Исходящий </th>\n");
	print("<th colspan=2 style=\"height:15px; FONT: normal 10pt Tahoma;\"> Входящий </th> <th colspan=2 style=\"height:15px; FONT: normal 10pt Tahoma;\"> Исходящий </th>\n");
	print("<th colspan=2 style=\"height:15px; FONT: normal 10pt Tahoma;\"> Входящий </th> <th colspan=2 style=\"height:15px; FONT: normal 10pt Tahoma;\"> Исходящий </th>\n");
	print("</tr>\n");
	print("<tr>\n");
	print("<th style=\"height:10px; FONT: normal 8pt Arial;color:330066;\">Пакеты</th><th style=\"height:10px; FONT: italic 8pt Arial;color:330066;\">Трафик </th> <th style=\"height:10px; FONT: normal 8pt Arial;color:330066;\">Пакеты</th><th style=\"height:10px; FONT: italic 8pt Arial;color:330066;\">Трафик </th>\n");
	print("<th style=\"height:10px; FONT: normal 8pt Arial;color:330066;\">Пакеты</th><th style=\"height:10px; FONT: italic 8pt Arial;color:330066;\">Трафик </th> <th style=\"height:10px; FONT: normal 8pt Arial;color:330066;\">Пакеты</th><th style=\"height:10px; FONT: italic 8pt Arial;color:330066;\">Трафик </th>\n");
	print("<th style=\"height:10px; FONT: normal 8pt Arial;color:330066;\">Пакеты</th><th style=\"height:10px; FONT: italic 8pt Arial;color:330066;\">Трафик </th> <th style=\"height:10px; FONT: normal 8pt Arial;color:330066;\">Пакеты</th><th style=\"height:10px; FONT: italic 8pt Arial;color:330066;\">Трафик </th>\n");
	print("</tr>\n");
    } else {
	print("<tr>\n");
	print("<th colspan=1 rowspan=2> <b>Подсеть</b> </th>\n");
	print("<th colspan=2> <b>Транзитный трафик</b> </th>\n");
	print("<th colspan=2> <b>Локальный трафик</b> </th>\n");
	print("<th colspan=2> <b>Общий трафик</b> </th>\n");
	print("</tr>\n");
	print("<tr>\n");
	print("<th style=\"height:15px; FONT: normal 10pt Tahoma;\"> Входящий </th> <th style=\"height:15px; FONT: normal 10pt Tahoma;\"> Исходящий </th>\n");
	print("<th style=\"height:15px; FONT: normal 10pt Tahoma;\"> Входящий </th> <th style=\"height:15px; FONT: normal 10pt Tahoma;\"> Исходящий </th>\n");
	print("<th style=\"height:15px; FONT: normal 10pt Tahoma;\"> Входящий </th> <th style=\"height:15px; FONT: normal 10pt Tahoma;\"> Исходящий </th>\n");
	print("</tr>\n");
    }
    
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM networks WHERE 1")) {
	wlog("Error loading networks list...",2,TRUE,5,FALSE); exit; 
    }
    $anets=array();

    while( $row=mysql_fetch_array($res)) {
	$bufnet=$row["addr"];
	if( isset($aa)) unset($aa);
	$cmd="$_iptables -t mangle -nL -v -x | $_awk '$0~/_".gettok($bufnet,1,"/")."_/&&/RETURN/ {print($1\",\"$2\",\"$11);}'";
	list($rr,$aa)=_exec2($cmd,FALSE,FALSE);
	if(( $rr>0) || ( count($aa)==0)) {
	    print("Ошибка получения информации по подсети $bufnet.<br>rr .$rr.<br>cmd .$cmd."); continue;
	}
	$all_pkts_in=""; $all_bytes_in=""; $all_pkts_out=""; $all_bytes_out="";
	$loc_pkts_in=""; $loc_bytes_in=""; $loc_pkts_out=""; $loc_bytes_out="";
	$fwd_pkts_in=""; $fwd_bytes_in=""; $fwd_pkts_out=""; $fwd_bytes_out="";
	foreach($aa as $line) {
	    $buf1=gettok($line,3,",");
	    $target=gettok($buf1,1,"_");
	    $dir=gettok($buf1,3,"_");
	    if( $target=="CNTNET") {
		if($dir=="DST") {
		    $all_pkts_in=gettok($line,1,",");
		    $all_bytes_in=bytes2mega(gettok($line,2,","));
		} elseif($dir=="SRC") {
		    $all_pkts_out=gettok($line,1,",");
		    $all_bytes_out=bytes2mega(gettok($line,2,","));
		}
	    } elseif( $target=="CNTNETF") {
		if($dir=="DST") {
		    $fwd_pkts_in=gettok($line,1,",");
		    $fwd_bytes_in=bytes2mega(gettok($line,2,","));
		} elseif($dir=="SRC") {
		    $fwd_pkts_out=gettok($line,1,",");
		    $fwd_bytes_out=bytes2mega(gettok($line,2,","));
		}
	    } elseif( $target=="CNTNETL") {
		if($dir=="DST") {
		    $loc_pkts_in=gettok($line,1,",");
		    $loc_bytes_in=bytes2mega(gettok($line,2,","));
		} elseif($dir=="SRC") {
		    $loc_pkts_out=gettok($line,1,",");
		    $loc_bytes_out=bytes2mega(gettok($line,2,","));
		}
	    }
	}
	$anets[$bufnet]=array(
		"all" => array(
			    "pkts_in" => $all_pkts_in,
			    "bytes_in" => $all_bytes_in,
			    "pkts_out" => $all_pkts_out,
			    "bytes_out" => $all_bytes_out
			    ),
		"fwd" => array(
			    "pkts_in" => $fwd_pkts_in,
			    "bytes_in" => $fwd_bytes_in,
			    "pkts_out" => $fwd_pkts_out,
			    "bytes_out" => $fwd_bytes_out
			    ),
		"local" => array(
			    "pkts_in" => $loc_pkts_in,
			    "bytes_in" => $loc_bytes_in,
			    "pkts_out" => $loc_pkts_out,
			    "bytes_out" => $loc_bytes_out
			    )
	    );
	

	print("<tr>\n");
	if( !$_nopkts) {
	    print("<td> $bufnet </td>\n");
	    print("<td> $fwd_pkts_in </td><td> $fwd_bytes_in </td><td> $fwd_pkts_out </td><td> $fwd_bytes_out </td>\n");
	    print("<td> $loc_pkts_in </td><td> $loc_bytes_in </td><td> $loc_pkts_out </td><td> $loc_bytes_out </td>\n");
	    print("<td> $all_pkts_in </td><td> $all_bytes_in </td><td> $all_pkts_out </td><td> $all_bytes_out </td>\n");
	} else {
	    print("<td> $bufnet </td>\n");
	    print("<td> $fwd_bytes_in </td><td> $fwd_bytes_out </td>\n");
	    print("<td> $loc_bytes_in </td><td> $loc_bytes_out </td>\n");
	    print("<td> $all_bytes_in </td><td> $all_bytes_out </td>\n");
	}
	print("</tr>\n");

    }

    print("</table>\n</span>\n");
    $wtime=time()-$time1;
    if( $_reload) {
	print("<br><br>Сформировано в ".strftime("%T")." за $wtime сек. ( ".round(($wtime/60),2)." мин.)\n<br><br>\n");
	print("<script type=\"text/javascript\">\n");
	print("setTimeout(\"document.location.replace('".$_SERVER["REQUEST_URI"]."')\",$_timeout*1000);\n");
	print("</script>");
    }


}


#-------------------------------------------------------------------------


function del_net($net1)
{
    global $iptconf_dir,$_local,$_count,$_notallfwd;
    $link=mysql_getlink();
    if( !mysql_query("DELETE QUICK FROM networks WHERE addr=\"$net1\"")) {
	wlog("Ошибка удаления данных из списка подсетей в del_net()!",2);
    }
    show_netlist();
    show_lost_nets();
    show_addnet_form();
    wlog("Удаление подсети $net1",0,FALSE,1,FALSE);
    
}

#-------------------------------------------------------------------------


function save_net($net1)
{
    global $iptconf_dir,$_local,$_count,$_notallfwd,$_mode;

    $link=mysql_getlink();
    if( $_mode=="save") {
	$line="UPDATE networks SET addr=\"$net1\",local=".(( !trim($_local)) ? "0":"1").",notallfwd=".(( !trim($_notallfwd)) ? "0":"1")." WHERE addr=\"$net1\"";
    } elseif( $_mode=="savenew") {
	$line="INSERT INTO networks SET addr=\"$net1\",local=".(( !trim($_local)) ? "0":"1").",notallfwd=".(( !trim($_notallfwd)) ? "0":"1");
    } else {
	return(FALSE);
    }
    if( !$res=mysql_query($line)) {
	wlog("Error updating network $net1 options!",2,TRUE,5,FALSE); exit; 
    }
    show_netlist();
    show_lost_nets();
    show_addnet_form();
    wlog("Сохранение подсети $net1",0,FALSE,1,FALSE);
    
}

#-------------------------------------------------------------------------



print("<html>\n");
require("include/head.php");
print("<body\n>");

if( $_net=="") {
    if( $_mode=="") {
	if( !$_show) {
	    show_load($_SERVER["REQUEST_URI"]."?s=1","Чтение подсетей");
	} else {
	    show_netlist();
	    show_lost_nets();
	    show_addnet_form();
	}
    } elseif( $_mode=="shtraf") {
	if( !$_show) {
	    show_load($_SERVER["REQUEST_URI"]."&s=1","Чтение счетчиков подсетей");
	} else {
	    show_netlist_traf();
	    exit;
	}
    }
} else {
    
    if( ($_mode=="") or ($_mode=="show")) {

	show_net($_net);

    } elseif( $_mode=="edit") {

	edit_net($_net);

    } elseif(( $_mode=="save") || ( $_mode=="savenew")) {

	if( !$_show) {
	    show_load($_SERVER["REQUEST_URI"]."&s=1","Сохранение данных");
	} else {
	    save_net($_net);
	}

    } elseif( $_mode=="del") {

	if( !$_show) {
	    show_load($_SERVER["REQUEST_URI"]."&s=1","Удаление подсети");
	} else {
	    del_net($_net);
	}

    } elseif( $_mode=="addnet") {

	edit_net($_net,TRUE);

    }
}





?>
</body>
</html>
