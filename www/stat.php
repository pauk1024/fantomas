<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: stat.php
# Description: 
# Version: 2.8
###

require("include/head.php");

require("./../config.php");

if( !is_readable("iptlib.php")) { wlog("error opening iptlib<br>",2,TRUE,5,TRUE); exit(); }
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=FALSE;
require("auth.php");


print("<br>\n");
require("include/head.php");

#------------------------------------------------------------

function f_list_userfiles()
{
    global $iptconf_dir;
    $link=mysql_getlink();
    $line="SELECT groups.name, groups.title, groups.id, (SELECT COUNT(*) FROM clients WHERE groups.id=clients.group_id) AS userc FROM groups";
    if( !$res=mysql_query($line)) {
        wlog("Ошибка выполнения запроса при поиске списка групп! ".mysql_error($link),2,TRUE,5,TRUE); exit;
    }
    print("<font class=top1>Групп клиентов обнаружено: <i>".mysql_num_rows($res)."</i></font><br><br>\n\n");
    if( mysql_num_rows($res)>0) {
	print("<hr>");
	print("<table class=table2 cellpadding=\"6px\" width=\"80%\">\n");
	print("<tr><th>Группа</th><th>Описание</th></tr>\n");
	while($row=mysql_fetch_array($res)) {
	    $grpname=$row["name"];
	    $grpuserc=$row["userc"];
	    $grpdesc=$row["title"];get_usr_param($grpname,"title");
	    $grpdesc=($grpdesc=="") ? "n/a" : $grpdesc;
	    print("<tr><td><a href=\"stat.php?grp=$grpname\" title=\"Открыть статистику по группе\"><b> $grpname </b> <i>(Клиентов: $userc)</i></a></td><td> $grpdesc</td><td><a href=\"stat.php?grp=$grpname\" title=\"Открыть статистику по группе\"><img src=\"icons/adir.gif\"></a></td> </tr>\n");
	}
	print("<tr><td colspan=5 class=td2><hr></td></tr></table>\n");
    }
}



$_group="";
$_client="";
$_short=FALSE;

if( isset($_GET["grp"])) $_group=$_GET["grp"];
if( isset($_GET["client"])) $_client=$_GET["client"];
if( isset($_GET["short"])) $_short=TRUE;

print("<body>\n");
if(!$short) print("<br><br><br>");

if( $_group=="") { f_list_userfiles(); } else {

    
    if( $_client=="") { f_list_users($_group); } else { 
	u_show_user($_client,$_group,$_short); 
     }
    exit;


}






?>
</body>
</html>