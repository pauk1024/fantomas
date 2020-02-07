<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: trafmon.php
# Description: 
# Version: 2.8.1
###


$_target=( isset($_GET["t"])) ? $_GET["t"] : "";
$_group=( isset($_GET["grp"])) ? $_GET["grp"] : "all";
$_timeout=( isset($_GET["w"])) ? $_GET["w"] : "";
$_r=( isset($_GET["r"])) ? TRUE : FALSE;
$_z=( isset($_GET["z"])) ? (( $_GET["z"]=="1") ? TRUE:FALSE):FALSE;


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");
require("ubaselib.php");

$flAdminsOnly=FALSE;
require("auth.php");

if( trim($_timeout)=="") {
    $_timeout=( $monitor_delay>0) ? "$monitor_delay" : "10";
}



if($_target=="toolbox") {
    print("<html>\n");
    print("<head>\n");
    print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">\n");
    print("<meta http-equiv=\"Expires\" content=\"Mon, 01 Jan 1990 00:00:00 GMT\">\n");
    print("<link rel=\"stylesheet\" type=\"text/css\" href=\"css/text1.css\">\n");
    print("<title>Fantomas page</title>\n<base target=\"chart\">\n</head>\n");

    wlog("Открытие монитора трафика - загрузка тулбокса",0,FALSE,1,FALSE);

    print("<body>\n");
    
    print("<script type=\"text/javascript\">\n");
    print("function send()\n");
    print("{\n");
    print("	var st=document.getElementById('t').value;\n");
    print("	var sr='1';\n");
    print("	var sgrp=document.getElementById('grp').value;\n");
    print("	var sw=document.getElementById('w').value;\n");
    print("	var sz=( document.getElementById('z').checked) ? '1' : '';\n");
    print("	var line='trafmon.php?t=chart&r=1&grp='+sgrp+'&w='+sw+'&z='+sz;\n");
    print("	parent.chart.stop();\n");
    print("	parent.chart.location.replace(line);\n");
    print("}\n</script>\n");
        
    print("<table class=table4 style=\"width:400px;\" cellpadding=\"5px\">\n");
    print("<form id=\"tbox\" name=\"tbox\" action=\"trafmon.php\">\n");
    print("<input type=\"HIDDEN\" id=\"t\" name=\"t\" value=\"chart\">  \n");
    print("<input type=\"HIDDEN\" id=\"r\" name=\"r\" value=\"1\">  \n");
    if( !$_r) $_group=((trim($monitor_def_grp)!="") ? $monitor_def_grp : $_group);
    print("<tr><td><font class=text33>Мониторить: </td><td colspan=2><span class=seldiv><SELECT id=\"grp\" name=\"grp\" style=\"width:200px;\">  \n");
    print("<option value=\"all\" ".((trim($monitor_def_grp)=="all") ? "SELECTED" : "").">Всех \n");
    $link=mysql_getlink();
    if( $res=mysql_query("SELECT * FROM groups WHERE 1")) {
	while( $row=mysql_fetch_array($res)) {
	    $grpname=$row["name"];
	    $grptitle=$row["title"];
	    print("<option VALUE=\"$grpname\" ".(($grpname==trim($monitor_def_grp)) ? "SELECTED" : "").">$grptitle [$grpname]  \n");
	}
	unset($row);
    }
    mysql_free_result($res);
    print("</SELECT></span></td></tr>\n");
    print("<tr><td><font class=text33>Обновлять через: </td><td><input type=\"text\" id=\"w\" name=\"w\" value=\"$_timeout\" size=6><font class=text33>&nbspсек. &nbsp \n ");
    print("</td><td>");
    print("<input type=\"BUTTON\" id=\"apply\" name=\"apply\" value=\"Ok\" OnClick=\"javascript: return send();\">");
    print("</td></tr><tr><td colspan=3> \n");
    print("<input type=\"CHECKBOX\" name=\"z\" id=\"z\" value=\"1\"><label class=text33 for=\"z\">Показывать нулевые полиции </label> \n");
    print("</td></tr> \n</form>  \n</table>\n");
    
    
    print("</body></html>\n");
} elseif( $_target=="chart") {

    $_time1=time();
    print("<html>\n");
    print("<head>\n");
    print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">\n");
    print("<meta http-equiv=\"Expires\" content=\"Mon, 01 Jan 1990 00:00:00 GMT\">\n");
    print("<link rel=\"stylesheet\" type=\"text/css\" href=\"css/text1.css\">\n");
    print("<title>Fantomas page</title>\n<base target=\"basefrm\">\n</head>\n");
    print("<body>\n");
    
    $link=mysql_getlink();
    $t_users=array(); 
    $t_cnames=array();
    if( $_group=="all") {
	$line="SELECT clients.ip, clients.policies, clients.cname, (SELECT groups.name FROM groups WHERE groups.id=clients.group_id) AS grpname FROM clients WHERE 1 ORDER BY grpname";
    } else {
	if( !$_id=get_usr_param($_group,"group_id")) return(FALSE);
	$line="SELECT clients.ip, clients.policies, clients.cname, (SELECT groups.name FROM groups WHERE groups.id=clients.group_id) AS grpname FROM clients WHERE group_id=$_id";
    }
    if( $res=mysql_query($line)) {
	while( $row=mysql_fetch_array($res)) {
	    $bufip=$row["ip"];
	    $bufcname=$row["cname"];
	    $bufgroup=$row["grpname"];
	    $buf2=f_getcounts($bufip);
	    $bufin=gettok(gettok($buf2,1,"|"),2,";");
	    if( trim($bufin)=="") continue 1;
	    $t_users[$bufgroup."|".$bufip]=$bufin; 
	    $t_cnames[$bufip]=$bufcname;
	}
    }

    if( !arsort($t_users)) { wlog("Some error sorting traffic counters...\n",2,TRUE,4,TRUE); exit; }
    
    print("<br><font class=top1>Монитор входящего трафика</font><br><br>\n");
    print("<font class=text32>Группа: <i>".(($_group=="all") ? "Все" : $_group)."</i></font><br><br><br>\n");
    
    print("<table class=table1 width=\"400px\" cellpadding=\"3px\">  \n");
    $i=1;
    foreach( $t_users as $ind => $traf) {
	if( !$_z) if(( trim($traf)=="") or (trim($traf)=="0")) continue;
	$grp=gettok($ind,1,"|");
	$ip=gettok($ind,2,"|");
	$name=( trim($t_cnames[$ip])!="") ? $t_cnames[$ip]."<br>" : "";
	print("<tr><td width=\"10%\" bgcolor=FFFBF0 valign=middle><font style=\"font-size:10pt; color:696969\"> $i. </td>\n");
	print("<td width=\"58%\" bgcolor=FFFBF0 valign=middle><font style=\"font-size:10pt; color:696969\">$name$ip &nbsp&nbsp </font></td>\n");
	print("<td bgcolor=FFFBF0><font style=\"font-size:10pt; color:696969\"><b>".bytes2mega($traf)."</b></td>\n");
	print("<td bgcolor=FFFBF0 align=middle width=\"20px\"> <a href=\"users.php?grp=$grp&client=$ip\" class=a2 style=\"text-decoration:none\" title=\"Перейти на страницу клиента\"><img src=\"icons/stock_people.gif\" title=\"Перейти на страницу клиента\"></a> </td></tr>");
	$i++;
    }
    print("</table>\n");

    print("<script type=\"text/javascript\"> \n");
    print("setTimeout(\"document.location.replace('trafmon.php?t=chart&z=".(( $_z) ? "1" : "")."&grp=$_group&w=$_timeout');\",$_timeout*1000);\n</script>\n");


    $_wtime=time()-$_time1;
    print("<br><br>Сформировано в ".strftime("%T")." за $_wtime сек. (".round(($_wtime/60),2)." мин.)\n</body></html>\n");

}




?>