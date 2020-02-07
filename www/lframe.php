<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: lframe.php
# Description: 
# Version: 2.8.2.1
###




require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=FALSE;
require("auth.php");


print("<HTML>\n");

print(" <HEAD>\n");

print("<META content=\"text/html; charset=koi8-r\" http-equiv='Content-Type'>\n");
print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"css/tree2.css\">\n");


print("<script src=\"js/ua.js\"></script>\n");
print("<script src=\"js/ftiens4.js\"></script>\n");

print("\n<SCRIPT>\n");

print("  USETEXTLINKS = 1\n");
#print("  STARTALLOPEN = 0\n");
print("  USERFRAMES = 1\n");
print("  ICONPATH = 'icons/'\n");

print("foldersTree = gFld(\"<b>&nbsp&#47 </i>Fantomas</b>\", \"index1.php\",\"stock_internet.gif\")\n");
print("foldersTree.treeID = \"Frameset1\"\n");



print("aux1 = insFld(foldersTree, gFld(\"".gtext("lframe","userstraffic")." \", \"users.php?mode=front\",\"stock_people.gif\"))\n");
print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","monitor")." \", \"tmonitor.php\",\"invest.gif\"))\n");
print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","reports")." \", \"ustat.php\",\"gnumeric16.gif\"))\n");
print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","groups")." \", \"users.php\",\"stock_people.gif\"))\n");

$link=mysql_getlink();
$res=mysql_query("SELECT name FROM groups WHERE 1");
while($row=mysql_fetch_array($res)) {
    print("insDoc(aux2, gLnk(\"R\", \"&nbsp<i>".$row["name"]."</i>\", \"users.php?grp=".$row["name"]."\",\"stock_people.gif\"))\n");
}

    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","subnets")." \", \"networks.php?mode=shtraf&nopkts=1\",\"network16.gif\"))\n");

if( isadmin()) {
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","shaper")." \", \"shaper.php\",\"gnome-util16.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","natmon")." \", \"natmon.php?m=list\",\"system-monitor16.gif\"))\n");

    print("aux1 = insFld(foldersTree, gFld(\"".gtext("lframe","system")."\", \"sysstat.php?p=system\",\"pidgin_16.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","iptables")." \", \"sysstat.php?p=iptables\",\"gnome-system.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","iproute")." \", \"iproute2.php\",\"gnome-system.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","ulog")." \", \"sysstat.php?p=ulog\",\"gnome-system.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","mysql")." \", \"sysstat.php?p=mysql\",\"gnome-system.gif\"))\n");
#    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","crond")." \", \"sysstat.php?p=crond\",\"gnome-system.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","php")." \", \"sysstat.php?p=php\",\"gnome-system.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","fantomaslogs")."\", \"logview.php\",\"gnome-system.gif\"))\n");

    print("aux1 = insFld(foldersTree, gFld(\"".gtext("lframe","options")."\", \"index1.php\",\"bum.gif\"))\n");

    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","policies")."\", \"pb.php\",\"access.gif\"))\n");
    print("aux3 = insFld(aux2, gFld(\"".gtext("lframe","policies_pollist")."\", \"pb.php?mode=pollist\",\"access.gif\"))\n");
    print("aux3 = insFld(aux2, gFld(\"".gtext("lframe","policies_new")."\", \"pb.php?mode=new\",\"access.gif\"))\n");
    
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","inits")."\", \"inits.php\",\"settings.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","ipset")."\", \"sets.php?mode=ipset\",\"settings.gif\"))\n");
    print("aux3 = insFld(aux2, gFld(\"".gtext("lframe","ipset_panel")."\", \"sets.php\",\"settings.gif\"))\n");
    print("aux3 = insFld(aux2, gFld(\"".gtext("lframe","ipset_editor")."\", \"sets_edit.php\",\"settings.gif\"))\n");

    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","networks")."\", \"networks.php\",\"settings.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","links")."\", \"provs.php\",\"settings.gif\"))\n");
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","ports")."\", \"ports.php\",\"settings.gif\"))\n");
    
    print("aux2 = insFld(aux1, gFld(\"".gtext("lframe","settings")."\", \"options.php\",\"settings.gif\"))\n");
    print("aux3 = insFld(aux2, gFld(\"".gtext("lframe","settings_paths")."\", \"options.php?grp=paths\",\"settings.gif\"))\n");
    print("aux4 = insFld(aux3, gFld(\"".gtext("lframe","settings_pathdir")."\", \"options.php?grp=paths&mode=dir\",\"settings.gif\"))\n");
    print("aux4 = insFld(aux3, gFld(\"".gtext("lframe","settings_pathbin")."\", \"options.php?grp=paths&mode=bin\",\"settings.gif\"))\n");
    print("aux4 = insFld(aux3, gFld(\"".gtext("lframe","settings_pathini")."\", \"options.php?grp=paths&mode=ini\",\"settings.gif\"))\n");
    print("aux3 = insFld(aux2, gFld(\"".gtext("lframe","settings_iface")."\", \"options.php?grp=web\",\"settings.gif\"))\n");
    print("aux3 = insFld(aux2, gFld(\"".gtext("lframe","settings_system")."\", \"options.php?grp=system\",\"settings.gif\"))\n");

    print("insDoc(aux1,gLnk(\"R\",\"".gtext("lframe","fusers")."\", \"fusers.php\",\"emesene.gif\"))\n");
    print("insDoc(aux1,gLnk(\"R\",\"".gtext("lframe","help")."\", \"./man/index.html\",\"stock_dialog-question.gif\"))\n");
    
}


print("aux1 = insFld(foldersTree, gFld(\"".gtext("lframe","logout")." \", \"index.php?logout=logout\",\"system-log-out.gif\"))\n");


print("</SCRIPT>\n</head>\n<body>\n");
print("<a href=http://www.treeview.net/treemenu/userhelp ></a>\n\n");
print("<font style=\"FONT: bold 13pt Tahoma; color:000080;\">Fantomas Iptconf</font> \n");
print("<br>\n");
#print("<font style=\"FONT: normal 11pt Tahoma; color:000080;\">".trim(str_replace("версия:","",$_SESSION["procinfo"]["version"]))."</font>\n");
print("<font style=\"FONT: normal 11pt Tahoma; color:000080;\">".$_SESSION["procinfo"]["version"]."</font>\n");
print("<br>\n");
print("<hr size=1>\n");
print("<div style=\"padding:5px;\"><img src=\"icons/tux50_916.jpg\" title=\"Fantomas Iptconf - открытая frontend-система управления маршрутизатором/интернет-шлюзом на базе Iptables/Netfilter.\"></div>\n");
print("<hr size=1><br>\n");
print("<script> initializeDocument(); ");
print("top.frames.basefrm.Frameborder=\"1\";\n</script>\n");

print("  <NOSCRIPT>\n");
print("   A tree for site navigation will open here if you enable JavaScript in your browser.\n");
print("  </NOSCRIPT>\n");

print("<br><br>\n");
print("<font style=\"FONT: normal 8pt Tahoma; color:696969;\">".gtext("lframe","loginname")." ".$_SESSION["user"]."\n");
if( $lframe_show_sessid) print("<br>sessid: <font style=\"FONT: normal 7pt Tahoma;\">".session_id()."</font>\n");
print("<hr size=1>\n");
#print("<div style=\"float:left;\"><a href=\"http://coreit.ru/fantomas\" target=_otherframe class=text69A>Coreit! group</a> &#169 2009-2010</div><br><div><a href=\"mailto:admin@coreit.ru\" target=_otherframe class=text69A>admin@coreit.ru</a></div> \n");
print("<div style=\"float:left;\" class=ldiv1>\n");
print("<a href=\"http://coreit.ru/fantomas\" target=_blank>Coreit! group</a> &#169 2009-2010</div>\n<br>\n");
print("<div class=ldiv1>\n");
print("<a href=\"mailto:admin@coreit.ru\" target=_blank>admin@coreit.ru</a>\n");
print("</div> \n");
print(" </BODY>\n\n </HTML>\n");

?>
