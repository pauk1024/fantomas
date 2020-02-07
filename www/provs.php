<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: provs.php
# Description: 
# Version: 0.2.4.6
###




require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : "";
$_link=( isset($_GET["link"])) ? $_GET["link"] : "";
$_flink=( isset($_GET["flink"])) ? $_GET["flink"] : "";
$_local=( isset($_GET["local"])) ? $_GET["local"] : "";
$_ip=( isset($_GET["ip"])) ? $_GET["ip"] : "";
$_ifname=( isset($_GET["ifname"])) ? $_GET["ifname"] : "";
$_show=( isset($_GET["s"])) ? TRUE:FALSE;


#-------------------------------------------------------------------------

function load_provlist()
{
    global $iptconf_dir;
    $alist=array();
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM providers WHERE 1")) return("");
    while($row=mysql_fetch_array($res)) {
	$_ppname=$row["name"];
	$alist[$_ppname]=array(
			    "ip" => $row["ip"],
			    "eth" => $row["ifname"],
			    "local" => ( $row["local"]=="1") ? "local":""
			    );
    }
    return($alist);
}


#-------------------------------------------------------------------------

function load_ethlist($pfull=FALSE)
{
    global $iptconf_dir,$_exec_errlevel,$_sudo,$_ifconfig,$_grep;
    $_exec_errlevel=(!isset($_exec_errlevel)) ? 1:$_exec_errlevel;
    $aa1=array();
    $aprovs=load_provlist();
    $ac=0;
    list($rr,$aout1)=_exec2("$_ifconfig | $_grep 'Link encap:'");
    if($rr>$_exec_errlevel) { wlog("Error executing command1 in load_ethlist()..",2,TRUE,4,TRUE); }
    foreach($aout1 as $akk1 => $avv1) {
	$buf1=gettok(trim($avv1),1," \t");
	$buf3="";
	$flok=FALSE;
	if( !$pfull) foreach($aprovs as $apkk => $apvv) if( trim($apvv["eth"])==$buf1) $flok=TRUE;
	if( !$flok) {
	    if( isset($aout2)) unset($aout2);
	    list($rr2,$aout2)=_exec2("$_ifconfig $buf1 | $_grep 'inet addr'");
	    if($rr2>$_exec_errlevel) { print("Error executing command2 in load_ethlist().."); }
	    if( count($aout2)>0) $buf3=gettok(str_replace("inet addr:","",trim($aout2[0])),1," \t");
	    $aa1[$buf1]=$buf3;
	}
    }
    
    return($aa1);

}

#------------------------------------------------------------------------

function show_lost_links()
{
    $aaa=load_ethlist();
    if( count($aaa)==0) return("");
    
    print("<br><br><br>   \n");
    print("<font class=top1> Доступные для добавления подключения</font><br>   \n");
    print("<form name=\"form331\" action=\"provs.php\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"addprov\">   \n");
    print("<table class=table5e width=\"400px\">   \n");
    print("<tr><td> Название </td><td> <input type=\"text\" name=\"link\" size=25> </td><td> &nbsp </td></tr> \n");
    print("<tr><td>:: </td><td> <span class=seldiv><SELECT name=\"flink\" multiple> \n");
    foreach($aaa as $akk => $avv) print("<option value=\"$akk|$avv\">$akk -- $avv\n");
    print("</SELECT></span>\n</td> \n");
    print("<td> <input type=\"SUBMIT\" name=\"sbmt1\" value=\"Добавить\"> </td></tr>   \n");
    print("</table> \n  </form>\n");


}

#-----------------------------------------------------------------------

function edit_prov($prov,$pnew=FALSE)
{
    global $iptconf_dir,$_ip,$_ifname,$_flink,$_link;
    if( trim($prov)=="") exit;
    
    $aprovs=load_provlist();
    $aeths=load_ethlist(TRUE);
    if( $pnew) {
	$flag=FALSE;
	$buflink=gettok($_flink,1,"|");
	$bufip=gettok($_flink,2,"|");
	foreach($aeths as $eth => $net) if( $eth==$buflink ) { $flag=TRUE; break; }
	if( !$flag) {
	    print("<br><br><br>\nТакого сетевого интерфейса не существует в системе!<br>\n<a href=\"provs.php\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"> Назад</a>\n");
	    return(FALSE);
	}
    }
    
    if( !$pnew) {
	if( !array_key_exists(trim($prov),$aprovs)) {
	    wlog("<font class=error>Specified link is not found",2,TRUE,4,TRUE); exit; 
	}
    } 
    if( trim($_flink)!="") {
	$_ip=gettok($_flink,2,"|");
	$_ifname=gettok($_flink,1,"|");
    }
    print("   \n");
    print("<br><br><br><font class=top1>Подключение $prov <br> <br><br>\n ");
    print("<form name=\"form243\" action=\"provs.php\">   \n");
    print("<input type=\"hidden\" name=\"link\" value=\"$prov\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"".(( $pnew) ? "savenew":"save")."\">   \n");
    print("<table class=table5e width=\"400px\" cellpadding=\"6px\">\n<tr><td colspan=2><br> </td></tr>\n");
    print("<tr><td> ipaddr </td><td> <input type=\"text\" name=\"ip\" id=\"ip\" value=\"".((!$pnew) ? $aprovs[$prov]["ip"] : $_ip)."\" size=20> </td></tr>   \n");
    print("<tr><td colspan=2> <input type=\"checkbox\" name=\"local\" id=\"local\" ".(( !$pnew) ? ((trim($aprovs[$prov]["local"])!="") ? "checked" : ""):"")." value=\"1\"> <label for=\"local\"><b>Local</b> <i> <font style=\"font-size:9pt\">  (признак локального интерфейса)</label> </td></tr>   \n");
    print("<tr><td> ifname </td><td> <input type=\"text\" name=\"ifname\" value=\"".((!$pnew) ? $aprovs[$prov]["eth"] : $_ifname)."\" size=10> </td></tr>   \n");

    print("<tr><td colspan=2><input type=\"submit\" value=\"Сохранить\"><br><br><br>  </td></tr> ");
    print("</table>  \n</form>\n");
    print("<br><font class=td2> ");
    print("<table class=notable><tr><td><a href=\"provs.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"provs.php\" title=\"Назад\"><b>Назад</b> </a><font style=\"FONT:italic 9pt Arial;\">( не сохранять изменения )</font>  </td></tr></table>\n");
    print("</td></tr></table>\n");
    wlog("Редактирование подключения $prov, режим".(($pnew) ? "новый" : "правка"),0,FALSE,1,FALSE);

}

#-------------------------------------------------------------------------


function show_addprov_form()
{
    print("<br><br><br>   \n");
    print("<font class=top1>Новое подключение вручную</font><br>   \n");
    print("<form name=\"form33\" action=\"provs.php\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"addprov\">   \n");
    print("<table class=table5e width=\"400px\">   \n");
    print("<tr><td>Название: </td><td> <input type=\"text\" name=\"link\" size=25> </td> \n");
    print("<td> <input type=\"SUBMIT\" name=\"sbmt1\" value=\"Создать\"> </td></tr>   \n");
    print("</table> \n  </form>\n");


}

#-----------------------------------------------------------------------


function show_provlist()
{
    global $iptconf_dir;

    $aprovs=load_provlist();
    if( count($aprovs)==0) return("");
    
    print("   \n");
    print("<br><br><br><font class=top1>Подключения<br> \n <font class=text41><br><br>\n ");
    print("<table class=table1 width=\"650px\" cellpadding=\"6px\">\n");
    
    foreach($aprovs as $apkk => $apvv) {

	print("<tr><td width=\"30%\" style=\"font-size:12pt\"><b> $apkk </b><td width=\"55%\"><font style=\"color:4682B4; font-style:italic\"> \n");

	if( trim($apvv["ip"]!="")) print(" <li> ipaddr ".$apvv["ip"]." <br> \n");
	if( trim($apvv["local"]!="")) print(" <li> Local <br> \n");
	if( trim($apvv["eth"]!="")) print(" <li> ifname ".$apvv["eth"]." <br> \n");
	    
	print("</td><td class=td3 width=\"15%\"><a href=\"provs.php?link=$apkk&mode=edit\" title=\"Редактировать\"><img src=\"icons/gtk-edit.gif\" title=\"Редактировать\"></a> &nbsp  \n");
	print("<a href=\"provs.php?link=$apkk&mode=del\" title=\"Удалить\"><img src=\"icons/edittrash.gif\" title=\"Удалить\"></a> </td></tr>  \n");
	    
    }
    print("</table>\n");


}


#-------------------------------------------------------------------------


function del_prov($prov)
{
    global $iptconf_dir,$_local,$_ip,$_ifname;

    $link=mysql_getlink();
    if( !mysql_query("DELETE QUICK FROM providers WHERE name=\"$prov\"")) {
	wlog("Error deleting provider link $prov!",2,TRUE,4,TRUE); exit; 
    }
    show_provlist();
    show_lost_links();
    show_addprov_form();
    wlog("Удалание подключения $prov",0,FALSE,1,FALSE);

}

#-------------------------------------------------------------------------


function save_prov($prov)
{
    global $iptconf_dir,$_local,$_ip,$_ifname,$_mode;

    if( trim($prov)!="") {
	$link=mysql_getlink();
    
	if( $_mode=="save") {
	    $line="UPDATE providers SET name=\"$prov\",local=".(( !trim($_local)) ? "0":"1").",ip=\"$_ip\",ifname=\"$_ifname\" WHERE name=\"$prov\"";
	} elseif( $_mode=="savenew") {
	    $line="INSERT INTO providers SET name=\"$prov\",local=".(( !trim($_local)) ? "0":"1").",ip=\"$_ip\",ifname=\"$_ifname\"";
	} else {
	    return(FALSE);
	}
    
	if( !mysql_query($line)) {
	    wlog("Ошибка сохранения данных в save_prov()! mode=.$_mode....",2,TRUE,5,TRUE); exit; 
	}
    } else {
	print("Не указано название подключения!<br>");
    }
    show_provlist();
    show_lost_links();
    show_addprov_form();
    wlog("Сохранение подключения $prov",0,FALSE,1,FALSE);

}

#-------------------------------------------------------------------------



print("<html>\n");
require("include/head.php");
print("<body\n>");

if( $_link=="") {
    if( trim($_mode)!="") {
	print("<br><br><br>Не указано название подключения...<br><br><br>\n");
        print("<table class=notable><tr><td><a href=\"provs.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"provs.php\" title=\"Назад\"><b>Назад</b> </a><font style=\"FONT:italic 9pt Arial;\">( не сохранять изменения )</font>  </td></tr></table>\n");
        exit;
    }
    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."?s=1","Чтение подключений");
    } else {
	show_provlist();
	show_lost_links();
	show_addprov_form();
    }
} else {
    
    if( $_mode=="edit") {

	if( !$_show) {
	    show_load($_SERVER["REQUEST_URI"]."&s=1","Загрузка данных подключения");
	} else {
	    edit_prov($_link);
	}

    } elseif(( $_mode=="save") || ( $_mode=="savenew")) {

	if( !$_show) {
	    show_load($_SERVER["REQUEST_URI"]."&s=1","Сохранение данных");
	} else {
	    save_prov($_link);
	}

    } elseif( $_mode=="del") {

	if( !$_show) {
	    show_load($_SERVER["REQUEST_URI"]."&s=1","Удаление подключения");
	} else {
	    del_prov($_link);
	}

    } elseif( $_mode=="addprov") {

	if(( !trim($_link)) || ( !trim($_flink))) {
	    print("Не указаны параметры создаваемого подключения!<br><br>\n");
	    print("<table class=notable><tr><td><a href=\"provs.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"provs.php\" title=\"Назад\"><b>Назад</b> </a><font style=\"FONT:italic 9pt Arial;\">( не сохранять изменения )</font>  </td></tr></table>\n");
	    exit;
	} else {
	    
	    if( !$_show) {
		show_load($_SERVER["REQUEST_URI"]."&s=1","Подготовка данных");
	    } else {
		edit_prov($_link,TRUE);
	    }
	}

    }
}





?>
</body>
</html>