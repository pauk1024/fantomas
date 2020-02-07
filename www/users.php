<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8.2
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: users.php
# Description: 
# Version: 2.8.2.6
###




require("./../config.php");

if( !is_readable("iptlib.php")) { wlog("error opening iptlib",2,TRUE,5,TRUE); exit; }

require("iptlib.php");
require("iptlib2.php");
require("userlib.php");
require("shapelib.php");

$flAdminsOnly=FALSE;
require("auth.php");




#-------------------------------------------------------------------------------

function check_user_exists($addr="",$pgetall=FALSE,$pexactly=TRUE)
{
    global $iptconf_dir,$users_dir;
    $rez=FALSE;
    $link=mysql_getlink();
    $line="SELECT clients.ip, (SELECT groups.name FROM groups WHERE clients.group_id=groups.id) AS grpname, (SELECT groups.title FROM groups WHERE clients.group_id=groups.id) AS grptitle FROM clients WHERE clients.ip=\"$addr\"";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса при проверке существования клиента в процедуре check_user_exists()! line .$line. ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    if( mysql_num_rows($res)>0) {
	if( !$pgetall) {
	    $row=mysql_fetch_array($res);
#	    $rez=$row["ip"];
	    $rez=$row["grpname"];
	} else {
	    $rez=array();
	    while($row=mysql_fetch_array($res)) {
		$rez[$row["grpname"]]=array("client" => $row["ip"], "title" => $row["grptitle"]);
	    }
	}
    } else { $rez=FALSE; }


    return($rez);

}

#-------------------------------------------------------------------------------

function usr_search($pstring,$pexactly,$pgetall)
{
    if( trim($pstring)=="") {
	print("<font class=text33>Ничего не найдено.<br><br>\n<a href=\"users.php?mode=front\">Назад</a></font>\n");
    } else {
	$bufch=check_user_exists($pstring,$pgetall,$pexactly);
	if( !$bufch) {
	    print("<font class=text33>Ничего не найдено.<br><br>\n<a href=\"users.php?mode=front\">Назад</a></font>\n");
	} else {
	    if( $pgetall) {
		print("<br><br><br><div style=\"padding-left:80px\">\n");
		print("<font class=top2>Результаты поиска \"$pstring\":</font><br><br><br>\n");
		print("<table class=table1 width=\"500px\" cellpadding=\"4px\">\n");
		print("<tr><th>Группа</th><th>Описание</th></tr>\n");
		foreach($bufch as $bchkk => $bchvv) {
		    $bufgrp=$bchkk;
		    $grpdesc=$bchvv["title"];
		    $grpdesc=($grpdesc=="") ? "" : "<font style=\"FONT: italic 10pt Arial\">".$grpdesc."</font>";
		    print("<tr><td> <a href=\"users.php?grp=$bufgrp\" style=\"FONT: 13pt Tahoma;\">$bufgrp</a></td><td> $grpdesc</td></tr>\n");
		}
		print("</table><br><br><br><font class=text32>\n");
		print("<a href=\"users.php?mode=front\"><img src=\"icons/gtk-undo.gif\" alt=\"Назад\">Назад</a><br>\n");
		print("</div>\n");
	    } else {
		f_list_users($bufch,TRUE); 
		show_useradd_form($bufch);
	    }
	}
    }

}

#-------------------------------------------------------------------------------

function f_list_groups()
{
    global $iptconf_dir;
    global $users_dir;
    $link=mysql_getlink();
    $line="SELECT groups.name, groups.title, groups.id, (SELECT COUNT(*) FROM clients WHERE groups.id=clients.group_id) AS userc FROM groups";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса при поиске списка групп клиентов! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    print("<font class=top2>Группы клиентов </font><br><br>\n\n");
    print("<font class=top1>Всего: <i>".mysql_num_rows($res)."</i></font><br><br>\n\n");
    if( mysql_num_rows($res)>0) {
	print("<hr size=1 width=\"60%\" align=left>");
	print("<table class=table4 cellpadding=\"6px\" width=\"60%\">\n");
	print("<tr><th class=wbrd>Группа</th><th class=wbrd colspan=2>Описание</th></tr>");
	while($row=mysql_fetch_array($res)) {
	    $grpname=$row["name"];
	    $grpid=$row["id"];
	    $grpdesc=$row["title"];
	    $grpdesc=($grpdesc=="") ? "n/a" : $grpdesc;
	    $grpusersc=$row["userc"];
	    print("<tr><td class=wbrd><a href=\"users.php?grp=$grpname\" title=\"Открыть группу\"><b> $grpname </b></a></td><td class=wbrd1> $grpdesc <br><font class=text3><i> Пользователей: $grpusersc </td>");
	    print("<td><a href=\"users.php?grp=$grpname\" title=\"Открыть группу\"><img src=\"icons/gtk-open.gif\" title=\"Открыть группу\"></a>&nbsp");
	    if($grpusersc==0) {
		if( isadmin()) print("<a href=\"users.php?grp=$grpname&grpid=$grpid&mode=delgrp\" title=\"Удалить\"><img src=\"icons/edittrash.gif\" title=\"Удалить\"></a> ");
		print("</td> </tr>\n");
	    }
	}
	print("</table>\n");
    }
    wlog("Просмотр списка групп пользователей",0,FALSE,1,FALSE);
}

#-------------------------------------------------------------

function show_add_form()
{
    global $_group,$_client;
    
    if( !isadmin()) return("");
    
    print(" \n");
    print("<table class=table4 width=\"450px\" cellpadding=\"2px\"> \n ");
    print("<form name=\"form223\" action=\"users.php\"> \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"addpol\">\n");
    print("<input type=\"hidden\" name=\"client\" value=\"$_client\">\n");
    print("<input type=\"hidden\" name=\"grp\" value=\"$_group\">\n");
    print("<tr><td><font class=td410>\n");
    print("<i>Добавить политику:</i></font> </td><td class=td410><span class=seldiv><SELECT name=\"pol\">\n<option value=\"empty\" selected>---</option>\n");
    $aa0=load_policies_list();
    if( !$aa0) {
	wlog("error loading policies list in show_add_form()",2,TRUE,5,TRUE); exit; 
    } else {
	foreach($aa0 as $kk0 => $vv0) {
	    print("<option value=\"$kk0\">$kk0</option>\n");
	}
    }
    print("</SELECT></span>\n</td><td class=td410>\n");
    print("<input type=\"submit\" value=\"Ok\">\n");
    print("</td></tr>\n");

    print("<tr><td> &nbsp; </font></td><td>  <input type=\"checkbox\" name=\"pnop\" id=\"pnop\" value=\"1\"><label for=\"pnop\"> Не применять</label> </td></tr>\n");

    print("</form></table>\n");
    print("<br><br><br><font class=text41>\n");

    print("<a href=\"users.php?grp=$_group\"><img src=\"icons/gtk-undo.gif\" alt=\"Назад\"> Назад </a> </font>\n");


}

#-------------------------------------------------------------
function show_nmap_usrlist()
{
    global $_nmap,$_mode,$_awk,$_subnet,$_group;
    if( !trim($_subnet)) return(FALSE);
    list($rr,$alist)=_exec2("$_nmap -sP $_subnet -n | $_awk '\$0~/^Host/ {print($2);}'");
    if(( $rr>0) || ( count($alist)==0)) return(FALSE);
    $link=mysql_getlink();
    if( $res=mysql_query("SELECT * FROM clients WHERE 1 GROUP BY ip")) {
	$aip=array();
	while($row=mysql_fetch_array($res)) $aip[]=$row["ip"];
	foreach($alist as $akey => $ipaddr) if(( in_array(trim($ipaddr),$aip)) || ( gettok($ipaddr,4,".")=="0")) unset($alist[$akey]);
	mysql_free_result($res);
    }
    print("<font class=text42s><b>Компьютеры, найденные в подсети $_subnet:</b></font><br><br>\n");
    print("<form name=\"form234\" action=\"users.php\">   \n");
    print("<input type=\"hidden\" name=\"grp\" value=\"$_group\">  \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"nmap_useaddr\">   \n");
    print("<table class=table4 width=\"430px\" cellpadding=\"5px\">\n");
    print("<tr><td>\n");
    print("<span class=seldiv><SELECT name=\"client\" id=\"client\" multiple size=12>\n");
    foreach($alist as $akey => $ipaddr) {
	$bufhost=gethostbyaddr($ipaddr);
	$bufhost=( trim($bufhost)==trim($ipaddr)) ? "":" - ".$bufhost;
	print("<option value=\"$ipaddr\"> $ipaddr$bufhost </option>\n");
    }
    print("</SELECT></span>\n");
    print("</td></tr><tr><td>\n");

    print("<input type=\"submit\" value=\"Выбрать\">\n");
    print("</td></tr>\n</table>\n</form><br>  \n ");
    print("<script type=\"text/javascript\">\n document.getElementById('client').style.borderColor = \"f1ba8d\";\n </script>\n");
    print("<br><br><br>\n");
    print("<a href=\"users.php?grp=$_group\"><img src=\"icons/gtk-undo.gif\" alt=\"Назад\"> Назад </a>\n");    
}
#-------------------------------------------------------------
function show_useradd_form($pgroup="")
{
    global $_group,$_client,$_policy,$_nmap,$_mode;
    $pgroup=( !trim($pgroup)) ? $_group:$pgroup;
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM groups WHERE name=\"$pgroup\"")) {
	wlog("Ошибка выполнения запроса при поиске группы для добавления клиента! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    if( mysql_num_rows($res)==0) {
	print("Группа $pgroup не найдена!"); return(FALSE);
    }
    $row=mysql_fetch_array($res);
    $defpolicy=$row["default_policy"];
    $_id=$row["id"];
    mysql_free_result($res);
    unset($row);

    if( !isadmin()) return("");
    
    print("   \n");
    print("<br><br> \n");


    print("<font class=text42s><b>Новый клиент (поиск):</b></font><br><br>\n");
    if(( file_exists($_nmap)) && ( $_mode!="nmap_useaddr")) {
	$link2=mysql_getlink();
	if( $res=mysql_query("SELECT addr FROM networks WHERE local=1 ORDER BY addr")) {
	    print("<form name=\"form235\" action=\"users.php\">   \n");
	    print("<input type=\"hidden\" name=\"grp\" value=\"$pgroup\">  \n");
	    print("<input type=\"hidden\" name=\"mode\" value=\"nmap_getusrls\">   \n");
	    print("<table class=table4 width=\"530px\" cellpadding=\"5px\"><tr>\n");
	    print("<td style=\"FONT: normal 8pt Tahoma;\">Искать хост в подсети:</td>\n");
	    print("<td> <span class=seldiv><SELECT name=\"subnet\" id=\"subnet\">\n");
	    while($row=mysql_fetch_array($res)) print("<option value=\"".$row["addr"]."\">".$row["addr"]."</option>\n");
	    print("</SELECT></span></td>\n");
	    print("<td> <input type=\"SUBMIT\" value=\"Поиск\"></td>\n</tr></table>\n</form><br>\n");
	}
    }
    print("<font class=text42s><b>Ручное создание:</b></font><br><br>\n");
    print("<table class=table4 width=\"530px\" cellpadding=\"5px\">\n");
    print("<form name=\"form234\" action=\"users.php\">   \n");
    print("<input type=\"hidden\" name=\"grp\" value=\"$pgroup\">  \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"addusr\">   \n");

    $vclient=($_mode=="nmap_useaddr") ? $_client:"";
    $vname="";
    if( trim($vclient)!="") $vname=( trim($vname=gethostbyaddr($_client))==trim($_client)) ? "":$vname;

    print("<tr><td><b> Имя:  </b></font></td><td>  <input type=\"text\" name=\"p1value\" size=45 value=\"$vname\"> </td></tr>\n");
    print("<tr><td><b> Адрес:  </b></font></td><td>  <input type=\"text\" name=\"client\" size=30 value=\"$vclient\"> </td></tr>\n");
    print("<tr><td><b> Политика: </b></td><td><div class=seldiv><SELECT name=\"pol\" id=\"pol\">\n <option value=\"$defpolicy\" selected>Default policy ($defpolicy)\n");
    $aa0=load_policies_list();
    if( !$aa0) {
	wlog("error loading policies list in show_add_form()",2,TRUE,5,TRUE); exit; 
    } else {
	foreach($aa0 as $kk0 => $vv0) {
	    print("<option value=\"$kk0\">$kk0 \n");
	}
    }
    print("</SELECT></div></td></tr>\n");

    print("<tr><td> &nbsp; </font></td><td>  <input type=\"checkbox\" name=\"pnop\" id=\"pnop\" value=\"1\"><label for=\"pnop\"> Не применять</label> </td></tr>\n");

    print("<tr><td colspan=2><input type=\"submit\" value=\"Создать\"></td></tr></form>\n</table>\n<br>  \n ");
    print("<script type=\"text/javascript\">\n document.getElementById('pol').style.borderColor = \"f1ba8d\";\n </script>\n");
    print("<br><br><br>\n");


}

#-------------------------------------------------------------

function show_finduser_form()
{
    global $_group,$_client,$_policy;
    
    print("   \n");
    print("<br><br> \n<br><br>\n");

    print("<table class=table4 width=\"400px\" cellpadding=\"5px\">\n");
    print("<form name=\"form234\" action=\"users.php\">   \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"searchusr\">   \n");
    print("<tr><td colspan=2><font class=top1>Поиск по группам </font></td></tr>\n");
    print("<tr><td> Адрес: </td><td>  <input type=\"text\" name=\"p1value\" size=35> </td></tr>\n");
    print("<tr><td> &nbsp </td><td> <input type=\"checkbox\" name=\"exactly\"  value=\"1\" checked>только точные совпадения</td></tr>\n");
    print("<tr><td>Тип поиска: </td><td><span class=seldiv> <SELECT name=\"getall\">\n<option value=\"first\">Сразу открыть найденную группу\n<option value=\"all\">Показать все результаты\n</SELECT></span>\n</td></tr>\n");
    print("<tr><td colspan=2 align=right><input type=\"submit\" value=\"Найти\"></td></tr>\n</form>\n</table>\n ");
    print("<br><br><br>\n");


}

#-------------------------------------------------------------
function show_grpadd_form()
{
    global $_group,$_client,$_policy;
    
    if( !isadmin()) return("");
    
    print("   \n");
    print("<br><br> \n<br><br>\n");
    print("<table width=\"600px\" cellpadding=\"2px\" class=table4>\n");
    print("<form name=\"form34\" action=\"users.php\">   \n");
    print("<tr><td colspan=2><b>Добавить группу</b><br><br></td></tr>  \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"addgrp\">   \n");
    print("<tr><td><font class=text41> Идентификатор: </font></td><td><input type=\"text\" name=\"grp\" size=16></td></tr> \n");
    print("<tr><td><font class=text41> Описание: </font></td><td><input type=\"text\" name=\"grptitle\" size=60></td></tr> \n");
    $plist=load_policies_list();
    print("<tr><td><font class=text41> Политика по умолчанию:  </font></td><td><span class=seldiv><SELECT name=\"pol\">\n");
    foreach($plist as $pkk => $pvv) {
	print("<option value=\"$pkk\">$pkk\n");
    }
    print("</SELECT></span></td></tr>\n");
    print("<tr><td colspan=2><br><input type=\"submit\" value=\"Добавить\"></td></tr>\n</form>\n</table>  \n ");
    print("<br><br><br>\n");
    print("<script language=\"text/javascript\"> \n top.frames(\"treeframe\").location.reload();\n");
    print("</script><br><br>\n");



}

#------------------------------------------------------------

function delusr()
{
    global $_client,$_group,$users_dir,$_policy;
    
    if( !isadmin()) return("");

    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM groups WHERE name=\"$_group\"")) {
	wlog("Ошибка выполнения запроса при поиске группы для добавления клиента! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    if( mysql_num_rows($res)==0) {
	print("Группа $_group не найдена!"); return(FALSE);
    }
    $row=mysql_fetch_array($res);
    $defpolicy=$row["default_policy"];
    $_id=$row["id"];
    mysql_free_result($res);
    
    $line="SELECT ip, policies FROM clients WHERE (group_id=$_id) && (ip=\"$_client\")";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса при поиске клиента $_client! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    unset($row);
    $row=mysql_fetch_array($res);
    $pollist=$row["policies"];
    
    $pollist=( trim($pollist)=="") ? $defpolicy:$pollist;
    foreach(explode(",",$pollist) as $curr_policy) {
	if( is_policy_loaded($curr_policy,$_client)) _loadpolicy($_client,$curr_policy,TRUE);
    }
    
    mysql_free_result($res);
    unset($row);
    if( !mysql_query("DELETE QUICK FROM clients WHERE (group_id=$_id) && (ip=\"$_client\")")) {
	wlog("Ошибка выполнения запроса при удалении  клиента $_client! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }

    f_list_users($_group,TRUE);
    show_useradd_form();
    wlog("Удаление клиента $_client из группы $_group",0,FALSE,1,FALSE);
    exit;


}


#-------------------------------------------------------------

function lockusr()
{
    global $_client,$_group,$users_dir,$_policy;
    
    if( !isadmin()) return("");

    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM groups WHERE name=\"$_group\"")) {
	wlog("Ошибка выполнения запроса при поиске группы для добавления клиента! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    if( mysql_num_rows($res)==0) {
	print("Группа $_group не найдена!"); return(FALSE);
    }
    $row=mysql_fetch_array($res);
    $defpolicy=$row["default_policy"];
    $_id=$row["id"];
    mysql_free_result($res);
    
    $line="SELECT ip, policies, islocked FROM clients WHERE (group_id=$_id) && (ip=\"$_client\")";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса при поиске клиента $_client! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    unset($row);
    $row=mysql_fetch_array($res);
    if( $row["islocked"]!=0) {
	wlog("Клиент $_client уже имеет статус блокировки! ",2,TRUE,5,TRUE);
	exit;
    }
    $pollist=$row["policies"];
    
    $pollist=( trim($pollist)=="") ? $defpolicy:$pollist;
    foreach(explode(",",$pollist) as $curr_policy) {
	if( is_policy_loaded($curr_policy,$_client)) _loadpolicy($_client,$curr_policy,TRUE);
    }
    
    mysql_free_result($res);
    unset($row);
    if( !mysql_query("UPDATE clients SET islocked=1 WHERE (group_id=$_id) && (ip=\"$_client\")")) {
	wlog("Ошибка выполнения запроса при удалении  клиента $_client! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }

    f_list_users($_group,TRUE);
    show_useradd_form();
    wlog("Блокировка клиента $_client из группы $_group",0,FALSE,1,FALSE);
    exit;


}


#-------------------------------------------------------------

function addusr($q=0)
{
    global $_client,$_group,$users_dir,$iptconf_dir,$_policy,$_cname,$usr_cname_spaces;
    global $_sudo,$_pnop;
    if( $q==0) {
	if(( substr_count($_cname,".")==3) and ( substr_count($_client,".")!=3)) {
	    print("<br><br>\n<font class=text42s><b>Внимание!</b><br>\n");
	    print("Данные в поле \"Имя\" больше похожи на IP-адрес, чем данные в поле \"Адрес\".<br>\n");
	    print("Проверьте, может Вы перепутали поля местами?<br><br> \n");
	    print("<table class=table5 cellpadding=\"4px\">\n");
	    print("<tr><td class=td42ye> Имя: </td><td class=td42ye> $_cname </td></tr>\n");
	    print("<tr><td class=td42ye> Адрес:  </td><td class=td42ye> $_client </td></tr>\n");
	    print("</table>\n<br><br>\n");
	    print("<table class=notable><tr><td>\n");
	    print("<a href=\"users.php?mode=addusr&client=$_client&grp=$_group&pol=$_policy&p1value=$_cname&q=1".(( $_pnop) ? "&pnop=1":"")."\" title=\"Поменять их местами и продолжить\"><img src=\"icons/redo.gif\" title=\"Поменять их местами и продолжить\"></a>\n ");
	    print("</td><td> \n");
	    print("<a href=\"users.php?mode=addusr&client=$_client&grp=$_group&pol=$_policy&p1value=$_cname&q=1".(( $_pnop) ? "&pnop=1":"")."\" title=\"Поменять их местами и продолжить\">Поменять данные местами и продолжить</a>\n ");
	    print("</td></tr><tr><td> \n");
	    print("<a href=\"users.php?mode=addusr&client=$_client&grp=$_group&pol=$_policy&p1value=$_cname&q=2".(( $_pnop) ? "&pnop=1":"")."\" title=\"Данные корректны, продолжить как есть\"><img src=\"icons/redo.gif\" title=\"Поменять их местами и продолжить\"></a>\n ");
	    print("</td><td> \n");
	    print("<a href=\"users.php?mode=addusr&client=$_client&grp=$_group&pol=$_policy&p1value=$_cname&q=2".(( $_pnop) ? "&pnop=1":"")."\" title=\"Данные корректны, продолжить как есть\">Данные корректны, продолжить как есть</a>\n ");
	    print("</td></tr></table> \n");
	    exit;
    	}
    } elseif( $q==1) {
	$buf1=$_client;
	$_client=$_cname;
	$_cname=$buf1;
	unset($buf1);
    }
    if( trim($_client)=="") {
	wlog("Адрес добавляемого клиента не может быть пустым!",2,TRUE,5,TRUE); exit;
    }
    
    if( !isadmin()) return(FALSE);
    $bufch=check_user_exists($_client);

    if( !policy2array($_policy)) {
	wlog("Политика с именем $_policy не существует!",2,TRUE,5,TRUE); exit;
    }
    if( ($_policy=="__nopol__") or( trim($_policy)=="")) {
	wlog("Default policy of the group is not specified.. ",2,TRUE,5,TRUE); exit;
    }
  if( !$bufch) {
    
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM groups WHERE name=\"$_group\"")) {
	wlog("Ошибка выполнения запроса при поиске группы для добавления клиента! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    if( mysql_num_rows($res)==0) {
	print("Группа $_group не найдена!"); return(FALSE);
    }
    $row=mysql_fetch_array($res);
    $defpolicy=$row["default_policy"];
    $_id=$row["id"];

    mysql_free_result($res);
    if(( !isset($link)) || ( !mysql_ping($link))) $link=mysql_getlink();
    $line="INSERT INTO clients SET group_id=$_id,ip=\"$_client\",policies=\"".(($_policy!=$defpolicy) ? $_policy:"")."\",cname=\"$_cname\"";
    if( !$res=mysql_query($line)) {
	wlog("Error inserting client information into group $_group!. ".mysql_error($link),2,TRUE,5,TRUE); exit;
    }

    if( !$_pnop) _loadpolicy($_client,$_policy,FALSE,TRUE);

  } else {
    print("Клиент с адресом $_client уже существует в группе <a href=\"users.php?grp=$bufch\">$bufch</a>..<br><br>\n");
  }
      
    f_list_users($_group,TRUE);
    show_useradd_form();
    wlog("Добавление клиента $_client в группу $_group",0,FALSE,1,FALSE);
    exit;
}


#-------------------------------------------------------------

function unlockusr($q=0)
{
    global $_client,$_group,$users_dir,$_policy;
    
    if( !isadmin()) return("");

    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM groups WHERE name=\"$_group\"")) {
	wlog("Ошибка выполнения запроса при поиске группы для отмены блокировки клиента! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    if( mysql_num_rows($res)==0) {
	print("Группа $_group не найдена!"); return(FALSE);
    }
    $row=mysql_fetch_array($res);
    $defpolicy=$row["default_policy"];
    $_id=$row["id"];
    mysql_free_result($res);
    
    $line="SELECT ip, policies, islocked FROM clients WHERE (group_id=$_id) && (ip=\"$_client\")";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса при поиске клиента $_client! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    unset($row);
    $row=mysql_fetch_array($res);
    if( $row["islocked"]==0) {
	wlog("Клиент $_client не имеет статуса блокировки! ",2,TRUE,5,TRUE);
	exit;
    }
    $pollist=$row["policies"];
    
    $pollist=( trim($pollist)=="") ? $defpolicy:$pollist;
    foreach(explode(",",$pollist) as $curr_policy) {
	_loadpolicy($_client,$curr_policy,FALSE,TRUE);
    }
    
    mysql_free_result($res);
    unset($row);
    if( !mysql_query("UPDATE clients SET islocked=0 WHERE (group_id=$_id) && (ip=\"$_client\")")) {
	wlog("Ошибка выполнения запроса при отмене блокировки клиента $_client! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }

    f_list_users($_group,TRUE);
    show_useradd_form();
    wlog("Отмена блокировки клиента $_client из группы $_group",0,FALSE,1,FALSE);
    exit;

}


#-------------------------------------------------------------

function addgroup()
{
    global $_group,$grptitle,$_policy,$users_dir;

    if( !isadmin()) return(FALSE);

    if( !trim($_group)) {
	print("Не указано наименование группы!<br>\n");
	return(FALSE);
    }
    if( !$_id=makeID()) {
	wlog("Ошибка получения ID при создании группы $_group!",2,TRUE,5,TRUE); exit;
    }
    $link=mysql_getlink();
    $line="INSERT INTO groups SET id=$_id,name=\"$_group\",title=\"$grptitle\",default_policy=\"$_policy\"";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса при добавлении новой группы! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    wlog("Добавление группы $_group",0,FALSE,1,FALSE);
    f_list_groups(); 
    show_grpadd_form();
    print("<script type=\"text/javascript\"> \n parent.frames[\"treeframe\"].document.location.reload();\n </script>");

    exit;


}

#--------------------------------------------------------------

function delgroup()
{
    global $_group,$users_dir,$_group_id;
    
    if( !isadmin()) return("");
    
    if( !trim($_group)) {
	print("Не указано наименование группы!<br>\n");
	return(FALSE);
    }
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT id FROM groups WHERE name=\"$_group\"")) {
	wlog("Ошибка выполнения запроса при поиске группы для удаления! ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    if( mysql_num_rows($res)>0) {
	$row=mysql_fetch_array($res);
	$_id=$row["id"];
    } else {
	return(FALSE);
    }
    mysql_free_result($res);
    mysql_query("DELETE QUICK FROM clients WHERE group_id=$_id");
    mysql_query("DELETE QUICK FROM groups WHERE id=$_id");

    print("<script type=\"text/javascript\"> \n parent.frames[\"treeframe\"].document.location.reload();\n </script>");
#    print("<script type=\"text/javascript\"> \n top.frames(\"treeframe\").location.reload(true);\n");
#    print("</script><br><br><br>\n");
    print("<br><br><br>\n");
    f_list_groups(); 
    show_grpadd_form();

    wlog("Удаление группы $_group",0,FALSE,1,FALSE);
    exit;

}

#--------------------------------------------------------------

function usr_rename($group,$ip,$prun="",$pref="",$p1value="")
{
    global $users_dir,$iptconf_dir,$usr_cname_spaces;
    if( trim($prun)=="") {
	print("<br><br><br>\n");
	print("<div style=\"padding-left:90px\"><br><font class=top1>Переименовать клиента $ip</font><br>\n");
	print("<font class=text1>группа: $group</font><br>\n");
	print("<form name=\"form1\">\n<input type=\"HIDDEN\" name=\"run\" value=\"1\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"$group\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"renameusr\">\n");
	print("<input type=\"HIDDEN\" name=\"client\" value=\"$ip\">\n");
	print("<input type=\"HIDDEN\" name=\"ref\" value=\"$pref\">\n");
	$bufcname=get_usr_param($group,$ip,"cname",TRUE);
	if( $usr_cname_spaces) $bufcname=str_replace("_"," ",$bufcname);
	print("<input type=\"text\" name=\"p1value\" value=\"$bufcname\" size=45><br><br>\n");
	print("<input type=\"submit\" name=\"sbmt\" value=\"Переименовать\"></form><br><br>\n");
	exit;
    } else {
	$bufcname=get_usr_param($group,$ip,"cname",TRUE);
	$link=mysql_getlink();
	if( !$res=mysql_query("SELECT * FROM groups WHERE name=\"$group\"")) {
	    wlog("Ошибка выполнения запроса при поиске группы для переименования клиента! ".mysql_error($link),2,TRUE,5,TRUE);
	    exit;
	}
	if( mysql_num_rows($res)==0) {
	    print("Группа $group не найдена!"); return(FALSE);
	}
	$row=mysql_fetch_array($res);
	$defpolicy=$row["default_policy"];
	$_id=$row["id"];
        mysql_free_result($res);
        unset($row);
	if( !$res=mysql_query("UPDATE clients SET cname=\"$p1value\" WHERE ip=\"$ip\"")) {
	    wlog("Ошибка выполнения запроса при обновлении информации о кленте! ".mysql_error($link),2,TRUE,5,TRUE);
	    exit;
	}

	if( $pref=="user") {
	    u_show_user($ip,$group); 
	    show_add_form();
	} elseif( $pref=="grp") {
	    f_list_users($group,TRUE); 
	    show_useradd_form();
	}
	wlog("Переименование клиента $ip в группе $group",0,FALSE,1,FALSE);
	exit;
	
    }

}

#--------------------------------------------------------------

function moveusr() 
{
    global $iptconf_dir,$users_dir,$_run,$_group,$_client,$_p1value;
    if( trim($_client)=="") return("");    
    if( trim($_group)=="") return("");    

    $link=mysql_getlink();

    
    if( trim($_run)=="") {
	print("<br><br><br>\n");
	print("<div style=\"padding-left:90px\"><br><font class=top1>Перенести клиента $_client в группу</font><br><br>\n");
	print("<form name=\"form1\">\n<input type=\"HIDDEN\" name=\"run\" value=\"1\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"$_group\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"moveusr\">\n");
	print("<input type=\"HIDDEN\" name=\"client\" value=\"$_client\">\n");
	print("<font class=text32> Группа: <select name=\"p1value\">\n ");
	if( !$res=mysql_query("SELECT * FROM groups WHERE name!=\"$_group\"")) {
	    wlog("Ошибка выполнения запроса при поиске групп для переименования клиента! ".mysql_error($link),2,TRUE,5,TRUE);
	    exit;
	}
	if( mysql_num_rows($res)==0) {
	    print("Группы не найдены!"); return(FALSE);
	}
	
	while( $row=mysql_fetch_array($res)) {
	    print("<option value=\"".$row["name"]."\">".$row["name"]." &nbsp(".$row["title"].")\n");
	}
	print("</select><br><br>\n");
	print("<input type=\"SUBMIT\" value=\"Вперед\">\n</form>\n");
	print("</div>");
	exit;
	
	
    } else {
	if( trim($_p1value)=="") {
	    wlog("Группа назначения не может быть пустой!",2,TRUE,5,TRUE); exit;
	}
	if( !$group_id_src=get_usr_param($_group,"group_id")) {
	    wlog("ID исходной группы клиета не найдена!",2,TRUE,5,TRUE); exit;
	}
	if( !$group_id_dst=get_usr_param($_p1value,"group_id")) {
	    wlog("ID группы назначения клиента не найдена!",2,TRUE,5,TRUE); exit;
	}
	$line="UPDATE clients SET group_id=$group_id_dst WHERE (ip=\"$_client\") && (group_id=$group_id_src)";
	if( !mysql_query($line)) {
	    wlog("Ошибка выполнения запроса при обновленнии информации о клиенте! ".mysql_error($link),2,TRUE,5,TRUE);
	    exit;
	}
	
    }
    wlog("Перенос клиента $_client из группы $_group в $_p1value",0,FALSE,1,FALSE);
    $_client="";
    

}

#-------------------------------------------------------------

print("<html>\n");

require("include/head.php");


$_group="";
$_client="";

if( isset($_GET["grp"])) $_group=$_GET["grp"];
$grptitle=( isset($_GET["grptitle"])) ? $_GET["grptitle"] : "";
if( isset($_GET["client"])) $_client=$_GET["client"];
$_mode=( isset( $_GET["mode"])) ? $_GET["mode"] : "";
$_policy=( isset( $_GET["pol"])) ? $_GET["pol"] : "";
$_p1value=( isset( $_GET["p1value"])) ? $_GET["p1value"] : "";
$_exactly=( isset( $_GET["exactly"])) ? TRUE : FALSE;
$_pnop=( isset( $_GET["pnop"])) ? TRUE : FALSE;
$_getall=( isset( $_GET["getall"])) ? (( $_GET["getall"]=="all") ? TRUE : FALSE) : FALSE;
$_run=( isset( $_GET["run"])) ? $_GET["run"] : "";
$_ref=( isset( $_GET["ref"])) ? $_GET["ref"] : "";
$_cname=( isset( $_GET["p1value"])) ? $_GET["p1value"] : "";
$_q=( isset( $_GET["q"])) ? $_GET["q"] : "";
$_show=( isset( $_GET["s"])) ? TRUE : FALSE;
$_subnet=( isset( $_GET["subnet"])) ? $_GET["subnet"] : "";

$_userop=( isset( $_GET["userop"])) ? $_GET["userop"] : "open";


if( $_mode=="delpol") {

	if( !$_show) {
	    show_load("users.php?grp=$_group&client=$_client&mode=$_mode&pol=$_policy&s=1","Отмена действия политики...");
	} else {
	    user_delpol($_group,$_client,$_policy);
	}


} elseif( $_mode=="addpol") {

	if( !$_show) {
	    show_load("users.php?grp=$_group&client=$_client&mode=$_mode&pol=$_policy&s=1","Применение политики...");
	} else {
	    user_addpol($_group,$_client,$_policy);
	}


} elseif( $_mode=="front") {

    print("<br><br><div style=\"padding-left:60px\">\n");
    print("<font class=top2>Клиенты и Трафик</font><br><hr width=\"350px\" align=left><br>\n");
    show_finduser_form();
    print("</div>\n");
    exit;

} elseif( $_mode=="searchusr") {

    usr_search($_p1value,$_exactly,$_getall);
    exit;

} elseif( $_mode=="renameusr") {

    usr_rename($_group,$_client,$_run,$_ref,$_p1value);

} elseif( $_mode=="delusr") {
    
    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Отмена действия политик...");
    } else {
	delusr();
    } 
    
    
} elseif( $_mode=="lockusr") {
    
    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Отмена политик, блокировка клиента...");
    } else {
	lockusr();
    } 
    
} elseif( $_mode=="unlockusr") {
    
    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Отмена блокировки, применение политик...");
    } else {
	unlockusr();
    } 
    
    
} elseif( $_mode=="moveusr") {
    
    moveusr();
    
} elseif( $_mode=="addusr") {

    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Применение политики...");
    } else {
	addusr($_q);
    } 
    
    
} elseif( $_mode=="addgrp") {

    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Создание группы...");
    } else {
	addgroup();
    } 
    
} elseif( $_mode=="delgrp") {

    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Удаление группы...");
    } else {
	delgroup();
    } 

} elseif( $_mode=="applypol") {

    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Применение политики...");
    } else {
	if( (trim($_client)!="") and (trim($_policy)!="")) {
	    _loadpolicy($_client,$_policy,FALSE,TRUE);
	    print("<font style=\"color:green; font-size:8pt; font-style:italic;\">Policy applied</font><br>\n");
	}
    } 


} elseif( $_mode=="cancelpol") {

    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Отмена действия политики...");
    } else {
	if( (trim($_client)!="") and (trim($_policy)!="")) {
	    _loadpolicy($_client,$_policy,TRUE);
	    print("<font style=\"color:green; font-size:8pt; font-style:italic;\">Policy cancelled</font><br>\n");
	}
    } 


} elseif( $_mode=="showpolicyusers") {
    
    if(trim($_policy)=="") die("Need a policy specification for this procedure..");
    $aau=get_policy_users($_policy);
    if( count($aau)>0) {
	print("<br><br><font class=top1>Пользователи  </font><br>\n");
	print("<br><br><font class=text1>использующие политику: </font><font class=text2><b>$_policy</b></font>  <br><br><br>\n");
	print("<table class=\"table1\" cellpadding=\"6px\">\n");
	print("<tr><th rowspan=2 colspan=1><b>Адрес</b></th><th rowspan=2 colspan=1><b>Политики</b></th><th rowspan=1 colspan=2><b>Входящий</b><br><font class=text1>( скачанный )</th><th rowspan1 colspan=2><b>Исходящий</b><br><font class=text1>( закачанный )</th></tr>\n     ");
	print("<tr><th rowspan=1 colspan=1><font style=\"font-family:arial; font-size:11px\"><b><i>Пакеты </th><th rowspan=1 colspan=1><b>Трафик</th><th rowspan=1 colspan=1><font style=\"font-family:arial; font-size:11px\"><b><i>Пакеты</th><th rowspan=1 colspan=1><b>Трафик</th></tr>    \n");
	foreach($aau as $kk2 => $vv2) {
	    s_list_users(gettok($kk2,1,"|"),$aau[$kk2]);
	}
	print("</table>\n");
	exit;
    } else {
	wlog("Users for policy $_policy is not found..",2,TRUE,5,TRUE); exit;
    }

} elseif( $_mode=="nmap_getusrls") {

    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Поиск компьютеров...");
    } else {
	show_nmap_usrlist();
	exit;
    } 

} elseif( $_mode=="nmap_useaddr") {

    if( !$_show) {
	show_load($_SERVER["REQUEST_URI"]."&s=1","Загрузка формы добавления...");
    } else {
	print("<br><br><br>\n");
	show_useradd_form();
	exit;
    } 

} elseif( $_mode=="reportusr") {

    if(( trim($_client)) && ( trim($_group))) {
	print("<script type=\"text/javascript\">\n document.location.replace('ustat.php?client=$_client&grp=$_group&full=1'); \n </script>\n");
    } 

}



print("<body>\n<br>");

if( $_group=="") { 
	f_list_groups(); 
	show_grpadd_form();
} else {

    if( $_client=="") { 
    
	if( !$_show) {
	    show_load("users.php?grp=$_group&s=1","Чтение счетчиков...");
	} else {
	    f_list_users($_group,TRUE); 
	    show_useradd_form();
	}
    
    } else { 
	if( !$_show) {
	    show_load("users.php?grp=$_group&client=$_client&s=1","Чтение счетчиков...");
	} else {
	    if( (get_usr_param($_group,$_client,"islocked"))!=0) {
		f_list_users($_group,TRUE); 
		show_useradd_form();
	    } else {
		u_show_user($_client,$_group); 
	    }
	}
	
	show_add_form();

    }
    print("</body>\n</html>\n");
    exit;


}






?>