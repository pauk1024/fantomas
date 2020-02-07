<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: iptlib2.php
# Description: 
# Version: 2.8.2.9
###

#------------------------------------------------------------------------
function get_iflist($pmode=1)
{
    global $iptconf_dir;
    $alist=array();
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM providers WHERE 1")) return(FALSE);
    if( mysql_num_rows($res)==0) return(FALSE);
    
    while( $row=mysql_fetch_array($res)) {
	$bufppname=$row["name"];
	$bufeth=$row["ifname"];
	if( !trim($bufeth)) continue;
	$alist[]=( $pmode==1) ? $bufeth:array(
		"ifname" => $bufeth,
		"local" => $row["local"]
	    );
    }
    return($alist);
}
#------------------------------------------------------------------------

function f_getcounts($p_ip)
{
    global $_iptables;
    global $_sudo,$_grep;
    if( trim($p_ip)=="") return("");
    $_tbytes=0; $_tpkts=0;
    $_result="";
    list($rr,$t_in)=_exec2("$_iptables -t mangle -nL COUNT_IN -v -x | awk '\$0 ~ /".$p_ip."[ \t]/ { print $0 }'");

    if( (count($t_in)==0) or ($rr!=0)) { return(""); }
    foreach($t_in as $vin) {
	if( !$t_str=_trimline($vin," \t")) continue;
	$_tpkts=$_tpkts+gettok($t_str,1," \t");
	$_tbytes=$_tbytes+gettok($t_str,2," \t");
    }
    $_result="$_tpkts;$_tbytes";
    $_tpkts=0; $_tbytes=0;
    list($rr,$t_out)=_exec2("$_iptables -t mangle -nL COUNT_OUT -v -x | awk '\$0 ~ /".$p_ip."[ \t]/ { print $0 }'");
    if( (count($t_out)==0) or ($rr!=0)) { return(""); }
    foreach($t_out as $vout) {
	if( !$t_str=_trimline($vout," \t")) continue;
	$_tpkts=$_tpkts+gettok($t_str,1," \t");
	$_tbytes=$_tbytes+gettok($t_str,2," \t");
    }
    $_result="$_result|$_tpkts;$_tbytes";

    return($_result);

}


#------------------------------------------------------------------

function f_list_users($grp,$users=FALSE)
{
    global $iptconf_dir;
    global $users_dir;
    global $usr_cname_spaces;
    
    if( !trim($grp)) return(FALSE);
    
    $link=mysql_getlink();
    $res=mysql_query("SELECT id, name, title, default_policy FROM groups WHERE name=\"$grp\"");
    if( mysql_num_rows($res)==0) {
	print("Не удалось найти группу $grp!<br>");
	return(FALSE);
    }
    $row=mysql_fetch_array($res);
    $_id=$row["id"];
    $grptitle=$row["title"];
    $defpolicy=$row["default_policy"];

    $scriptname=( !$users) ? "stat.php" : "users.php";
    $reloadline="<span style=\"margin:0;padding-left:6px; border:0;\"><a href=\"users.php?grp=$grp\" title=\"Обновить страницу и данные\"><img src=\"icons/reload.png\" title=\"Обновить страницу и данные\" align=middle></a></span>\n";
    
    $aalines=array();	
    $aalines[]="<font class=top1>Пользователи<br><br></font>\n";
    $aalines[]="<font class=text1>По группе:</font><font class=text2><b> $grp</b></font>\n<br>\n";
    if( $grptitle!="") $aalines[]="<font class=text1>Описание: </font><font class=text1><i>$grptitle\n</i></font>\n";
    $aalines[]="<br><br>\n";

    $aalines[]="<form name=\"userop1\" action=\"users.php\">\n";
    $aalines[]="<input type=\"hidden\" name=\"grp\" value=\"$grp\">\n";
    $aalines[]="<input type=\"hidden\" name=\"ref\" value=\"grp\">\n";

    $aalines[]="<table class=\"table1\" cellpadding=\"3px\" width=\"92%\">\n";
    $aalines[]="<tr><th rowspan=2 colspan=1><b>&nbsp;</b></th><th rowspan=2 colspan=1><b>Клиент</b></th><th rowspan=2 colspan=1><b>Политики</b></th><th rowspan=1 colspan=2><b>Входящий</b><br><font class=text1>( скачанный ) </th><th rowspan=1 colspan=2><b>Исходящий</b><br><font class=text1>( закачанный )</th><th rowspan=2 colspan=1> $reloadline </th></tr>";
    $aalines[]="<tr><th rowspan=1 colspan=1><font style=\"FONT: italic bold 9pt Arial;\">Пакеты</font></th><th rowspan=1 colspan=1><font style=\"FONT: bold 9pt Arial;\">Трафик</font></th><th rowspan=1 colspan=1><font style=\"FONT: italic bold 9pt Arial;\">Пакеты</font></th><th rowspan=1 colspan=1><font style=\"FONT: bold 9pt Arial;\">Трафик</font></th></tr>";

    mysql_free_result($res);
    $res=mysql_query("SELECT * FROM clients WHERE group_id=$_id");
    if( mysql_num_rows($res)==0) {
	print("Группа $grp пуста!<br>");
	return(FALSE);
    }
    while($row=mysql_fetch_array($res)) {
	$bufip=$row["ip"];
	$bufhost=gethostbyaddr($bufip);
	$bufpols=( trim($row["policies"])=="") ? $defpolicy:$row["policies"];
	$bufcname=$row["cname"];

	$buf_inbytes="";
	$buf_inpkts="";
	$buf_outbytes="";
	$buf_outpkts="";
	
	$bufcounts=f_getcounts($bufip);
	if( $bufcounts!="") {
	    $t=gettok($bufcounts,1,"|");
	    $buf_inpkts=gettok($t,1,";");
	    $buf_inbytes=bytes2mega(gettok($t,2,";"));
	    $t=gettok($bufcounts,2,"|");
	    $buf_outpkts=gettok($t,1,";");
	    $buf_outbytes=bytes2mega(gettok($t,2,";"));
	}
	$aalines[]="<tr>\n";
	$aalines[]="<td width=\"10%\"> <input type=\"radio\" name=\"client\" id=\"client_$bufip\" value=\"$bufip\"> </td>\n";
	$aalines[]="<td> <label for=\"client_$bufip\"> ".((trim($bufcname)!="") ? "<i><b>".$bufcname."</b></i><br>" : "")."$bufip</label>".((trim($bufhost)!=trim($bufip)) ? "<br><font style=\"font-size:8pt;\"><i>".$bufhost : "")."</font> ".(( $row["islocked"]==1) ? "<br>\n<font style=\"FONT: normal 8pt tahoma,sans-serif;color:#B22222;\">Блокирован</font>":"")."</td>\n";
	$aalines[]="<td> <font style=\"font-size:9pt\"><br>\n";

	$ic=coltoks($bufpols,",;");
	$floq=FALSE;
	if( $ic==1) { 
	    $ttraf=mega2bytes($buf_inbytes);
	    if( !isset($tquota)) $tquota="";
	    $floq=( trim($tquota)!="") ? (( $tquota <= $ttraf) ? TRUE : FALSE) : FALSE;
	    $_bufline=get_policy_param($bufpols,"title")." <i>[<b>".$bufpols."</b>]</i> "; 
	    if( $floq) $_bufline = "<font color=red> $_bufline </font>\n";
	    $aalines[]=$_bufline;
	    if( $floq) $aalines[]="<br><font color=B22222<b>!:&nbsp&nbsp</b><i>квота исчерпана</i></font><br>\n"; 
	} else {
	    $floq=FALSE;
	    for( $im=1; $im<=$ic; $im++) {
		$currpol=gettok($bufpols,$im,",:");
		$tquota=mega2bytes(get_policy_param($currpol,"accept","quota"));
		$ttraf=mega2bytes($buf_inbytes);
		$floq=( trim($tquota)!="") ? (( $tquota <= $ttraf) ? TRUE : FALSE) : FALSE;
		$_bufline="<font style=\"font-size:4px\"><li><font style=\"font-size:9pt\"> ".get_policy_param($currpol,"title")."<i> [<b>".$currpol."</b>] </i><br>\n";
		$aalines[]=$_bufline; 
		if( $floq) $aalines[]="<font color=red><b>!:&nbsp&nbsp</b><i>квота исчерпана</i></font><br><br>\n"; 
	    }
	}
	$aalines[]="</font><br></td><td><font class=packets1><i>$buf_inpkts </td><td> $buf_inbytes </td><td> <font class=packets1><i>$buf_outpkts </td><td> $buf_outbytes </td>  <td class=td3 align=center>\n ";
	if( $row["islocked"]==0) $aalines[]="<a href=\"".$scriptname."?grp=$grp&client=$bufip\" title=\"Открыть страницу клиента\"><img src=\"icons/gtk-open.gif\" title=\"Открыть страницу клиента\"></a>&nbsp\n";

	$aalines[]=" </td>\n";
	$aalines[]=" </tr>\n";
    }
    $aalines[]="<tr class=tr1>\n";

    $aalines[]="<td class=td1bottom colspan=3 align=right>Действия: &nbsp;&nbsp;&nbsp; </td>\n";
    $aalines[]="<td class=td1bottom colspan=3 align=left valign=middle> \n";
    $aalines[]="<span class=seldiv><select name=\"mode\">\n";
    $aalines[]="<option value=\"\"> Открыть карточку клиента</option>\n";
    $aalines[]="<option value=\"reportusr\"> Отчет по трафику</option>\n";
    $aalines[]="<option value=\"delusr\"> Удалить клиента</option>\n";
    $aalines[]="<option value=\"moveusr\"> Переместить в другую группу</option>\n";
    $aalines[]="<option value=\"renameusr\"> Переименовать клиента</option>\n";
    $aalines[]="<option value=\"lockusr\"> Блокировать клиента</option>\n";
    $aalines[]="<option value=\"unlockusr\"> Разблокировать клиента</option>\n";
    $aalines[]="</select></span>";
    
    $aalines[]="</td><td class=td1bottom colspan=2>\n";
    $aalines[]="<input type=\"submit\" value=\"Ok\">\n";

    $aalines[]="</td></tr></table>\n";
    $aalines[]="</form>\n";
    foreach($aalines as $aakey => $line) print($line);
    wlog("Просмотр списка клиентов в группе $grp",0,FALSE,1,FALSE);

}


#----------------------------------------------------------------------

function c2koi($str55="")
{
    if( trim($str55)=="") return("");
    $rezz=htmlspecialchars_decode(htmlentities($str55,ENT_COMPAT,"koi8r"));
    return($rezz);
}

#---------------------------------------------------------------------

function u_show_user($ip="",$grp="",$short=FALSE)
{
    global $_iptables;
    global $_console;
    global $iptconf_dir;
    global $backup_dir;
    global $quota_chk_arg,$usr_cname_spaces;

    $atraf=process_counters($ip,$grp);
    $cname=get_usr_param($grp,$ip,"cname",TRUE);
    if( $usr_cname_spaces) $cname=str_replace("_"," ",$cname);
    if( !$atraf) { unset($atraf); $atraf=array(); }


    $renline="<a href=\"users.php?grp=$grp&client=$ip&mode=renameusr&ref=user\" title=\"Переименовать пользователя\"><img src=\"icons/pyrenamer22.gif\" title=\"Переименовать пользователя\" align=middle></a>";
    $reloadline="<a href=\"users.php?grp=$grp&client=$ip\" title=\"Обновить страницу и данные\"><img src=\"icons/reload.png\" title=\"Обновить страницу и данные\" align=middle></a>";
    $shaper_addline=( !shaper_check_client($ip)) ? "<a href=\"shaper.php?mode=add&client=$ip&grp=$grp\" title=\"Добавить ограничение скорости трафика для клиента\"><img src=\"icons/gnome-util22.gif\" title=\"Добавить ограничение скорости трафика для клиента\" align=middle></a>":"";

    print("<br><br>\n");
    print("<table class=notable>\n");
    print("<tr><td width=\"350px\">\n");
    if(!$short) {
	if( trim($cname)=="") {
	    $line="<font class=top1>Клиент".(($ip!="") ? " <font color=blue> $ip</font>" : "")."</font><br>\n";
	} else {
	    $line="<font class=top1>Клиент".(($cname!="") ? " <font color=blue> $cname</font>" : "")."</font><br>\n";
	    $line=$line."<font class=top1>Адрес".(($ip!="") ? " <font color=blue> $ip</font>" : "")."</font><br>\n";
	}
	print($line);
    }
    print("</td><td width=\"90px\" align=right>\n");
    print("<div valign=top style=\"padding:6px;border:1px;border-style:dashed;border-color:A6CAF0;\">\n");
    print("$reloadline $renline $shaper_addline\n");
    print("</div>\n");
    print("</td></tr><tr><td colspan=2 align=right style=\"padding-top:5px; padding-bottom:9px; border-bottom:1px; border-bottom-style:solid; border-bottom-color:696969;\">\n");
    
    $_id=get_usr_param($grp,"group_id");
    $link=mysql_getlink();
    if( $res=mysql_query("SELECT * FROM clients WHERE group_id=$_id GROUP BY ip")) {
	print("<form name=\"qjmp\" action=\"users.php\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"$grp\">");
	print("<table class=gogo align=right><tr>\n");
	print("<td><font style=\"FONT: Normal 8pt Tahoma;color:330066;\"> Перейти к: </font></td><td> <span=selspan><SELECT name=\"client\" id=\"client\">\n");
	while($row=mysql_fetch_array($res)) {
	    if( trim($row["ip"])==trim($ip)) continue;
	    print("<option value=\"".$row["ip"]."\">".$row["ip"].(( !trim($row["cname"])) ? "":", ".$row["cname"])." </option>\n");
	}
	print("</SELECT></span>\n</td><td>\n");
	print("<input type=\"SUBMIT\" name=\"sbmt\" value=\"Ok\">\n");
	print("</td></tr>\n</table>\n</form>\n");
	
    }
    print("</td></tr></table>\n");
    print("<br><br>\n");

    if( shaper_check_client($ip)) {
	$bufratein=shaper_get_conf("client=$ip","ratein");
	$bufrateout=shaper_get_conf("client=$ip","rateout");
	$bufifname=shaper_get_conf("client=$ip","ifname");
	$bufifbifname=shaper_get_conf("client=$ip","ifbifname");
	print("<font class=text42s>Ограничения скорости:</font>\n<br>\n");
	print("<table class=table5 cellpadding=\"2px\" style=\"margin-left:20px;\">\n");
	$shaper_delline="<a href=\"shaper.php?mode=del&client=$ip&ifname=$bufifname\" title=\"Удалить правила клиента (из памяти и из конфига)\"><img src=\"icons/gtk-cancel_16.gif\" title=\"Удалить правила клиента (из памяти и из конфига)\"></a>\n";
	$shaper_stopline="<a href=\"shaper.php?mode=stop&client=$ip&grp=$grp&ifname=$bufifname&ifbifname=$bufifbifname&rin=$bufratein&rout=$bufrout\" title=\"Отменить правила клиента (удалить из памяти, сохранить в конфиге)\"><img src=\"icons/gtk-delete16.gif\" title=\"Отменить правила клиента (удалить из памяти, сохранить в конфиге)\"></a>\n";
	print("<tr><td class=td41ye>Входящая:</td><td class=td42> $bufratein </td><td rowspan=2  valign=middle style=\"padding-left:5px;padding-right:5px;\"> $shaper_delline $shaper_stopline  </td></tr>");
	print("<tr><td class=td41ye>Исходящая:</td><td class=td42> $bufrateout </td></tr>\n");
	print("</table>\n<br>\n");
    }
    
    print("<table class=\"table1\" cellpadding=\"6px\">\n");
    print("<tr><th rowspan=2 colspan=1><b> ".c2koi("Политика")."</b></th><th rowspan=1 colspan=2><b>".c2koi("Входящий")."</b><br><font class=text1>".c2koi("( скачанный )")."</th><th rowspan=1 colspan=2><b>".c2koi("Исходящий")."</b><br><font class=text1> ".c2koi("( закачанный )")."</th></tr>");
    print("<tr><th rowspan=1 colspan=1><font style=\"font-family:arial; font-size:11px\"><b><i>".c2koi("Пакеты")."</b></th><th rowspan=1 colspan=1><b>".c2koi("Трафик")."</b></th><th rowspan=1 colspan=1><font style=\"font-family:arial; font-size:11px\"><b><i>".c2koi("Пакеты")."</i></b></th><th rowspan=1 colspan=1><b>".c2koi("Трафик")."</b></th></tr>");
    $pollist=get_usr_param($grp,$ip,"policies",TRUE);
    $defpolicy=get_usr_param($grp,"_default_policy","",TRUE," =\t");
    $cp=coltoks($pollist,",");
    for($icp=1; $icp<=$cp; $icp++) {
        $currpol=($cp==1) ? $pollist : gettok($pollist,$icp,",");
        $ptitle=get_policy_param($currpol,"title");
        $defptitle=get_policy_param($defpolicy,"title");
        $dis_details=get_policy_param($currpol,"count","nolog",0,TRUE);

	if($currpol=="") {
	    if($defpolicy!="") {

		print("<tr><td colspan=5 class=td1> <font class=text3>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp ".c2koi("$defptitle")." <i>[ <b> ".c2koi("$defpolicy")." </b> ] <font color=magenta>*default policy \n");
		if( !is_policy_loaded($defpolicy,$ip)) {
		    print("&nbsp<a href=\"users.php?client=$ip&pol=$defpolicy&mode=applypol&grp=$grp\" title=\"Применить политику\"><img src=\"icons/apply16.gif\" title=\"Применить политику\"></a> \n");
		} else {
		    print("&nbsp<a href=\"users.php?client=$ip&pol=$defpolicy&mode=cancelpol&grp=$grp\" title=\"Отменить действие политики\"><img src=\"icons/cancel16.gif\" title=\"Отменить действие политики\"></a> \n");
		}
		print("</font> ".(($dis_details!="") ? ("<font class=text4>".c2koi("(детализация недоступна)")."</font>") : "")."</i></td>");
		if( isadmin()) print("<td class=td1> <a href=\"pol.php?height=300width=300&p=$defpolicy&shconf=1\" class=\"thickbox\" title=\"Конфиг политики $currpol\"><img src=\"icons/eog.gif\" title=\"Показать конфиг политики\"> </a></td></tr>\n");
		$flok=FALSE;
		foreach($atraf as $keyu => $vvu) {
		    if( $atraf[$keyu]["policy"]["policyname"]==$defpolicy) {
			$flok=TRUE;
			$currpol=$defpolicy;
			break 1;
		    }
		}
		if( !$flok) {
		    $apol=policy2array($defpolicy,TRUE);
		    print("<tr><td valign=middle>\n");
		    foreach($apol as $kk0 => $vv0) {
		        if( $apol[$kk0][0]=="#") continue;
		        if( trim($apol[$kk0])=="") continue;
		        $buf1=gettok($apol[$kk0],1," \t");
		        if( ($buf1=="accept") or ($buf1=="reject")) {
			    $tquota=mega2bytes(get_policy_param($defpolicy,"accept","quota"));
			    $ttraf=mega2bytes("0");
			    $accpt_line=$vv0;
			    print("<font style=\"font-size:11px\"><br> ".c2koi("$accpt_line")."<br>\n");
			}
		    }
		    print("</font><br></td><td><font class=packets1><i>0 </td><td> 0 </td><td> <font class=packets1><i>0 </td><td> 0</td><tr> \n");
		}
	    }
	} else {
	    print("<tr><td colspan=5 class=td1> <font class=text3>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp ".c2koi("$ptitle")." <i>[ <b> ".c2koi("$currpol")." </b> ] ".(($dis_details!="") ? ("<font class=text4>".c2koi("(детализация недоступна)")."</font>") : "")."</i></td>");
	    if( isadmin()) print("<td class=td1> <a href=\"pol.php?height=300width=300&p=$currpol&shconf=1\" class=\"thickbox\" title=\"Конфиг политики $currpol\"><img src=\"icons/eog.gif\" title=\"Показать конфиг политики\"> </a>\n");
	    if( isadmin()) print("&nbsp <a href=\"users.php?grp=$grp&client=$ip&mode=delpol&pol=$currpol\" title=\"Удалить политику\"><img src=\"icons/edittrash.gif\" title=\"Удалить политику\"></a> ");
	    print("</td>  </tr>\n");
	    $flok=FALSE;
	    foreach($atraf as $keyu => $vvu) {
	        if( $atraf[$keyu]["policy"]["policyname"]==$currpol) {
		    $flok=TRUE;
		    break 1;
		}
	    }
	    if( !$flok) {
		$apol=policy2array($currpol,TRUE);
		print("<tr><td>\n");
		foreach($apol as $kk0 => $vv0) {
		    if( $apol[$kk0][0]=="#") continue;
		    if( trim($apol[$kk0])=="") continue;
		    $buf1=gettok($apol[$kk0],1," \t");
		    if( ($buf1=="accept") or ($buf1=="reject")) {
			$tquota=mega2bytes(get_policy_param($currpol,"accept","quota"));
			$ttraf=mega2bytes("0");
			$accpt_line=$apol[$kk0];
			print("<font style=\"font-size:11px\"><br> ".c2koi("$accpt_line")."<br>\n");
		    }
		}
		print("</font><br></td><td><font class=packets1><i>0 </td><td> 0 </td><td> <font class=packets1><i>0 </td><td> 0</td><tr> \n");
	    }
	}
	for($i1=1; $i1<=count($atraf); $i1++) {
	    $i=$i1-1; $pname=$atraf[$i]["policy"]["policyname"];
	    if( $pname !=$currpol) continue;
	    $floq=FALSE; 
	    $tquota=mega2bytes(get_policy_param($pname,"accept","quota"));
	    $ttraf=mega2bytes($atraf[$i]["in_bytes"]);
	    $tmode=get_policy_param($pname,"accept","",0,TRUE);
	    if( trim($tmode)=="") $tmode=get_policy_param($pname,"reject","",0,TRUE);
	    $accpt_line="$tmode ".$atraf[$i]["policy"]["proto"]." ".$atraf[$i]["policy"]["ports"];
	    $floq=( trim($tquota)!="") ? (( ($tquota-mega2bytes($quota_chk_arg)) <= $ttraf) ? TRUE : FALSE) : FALSE;
	    print("<tr><td> <font style=\"font-size:11px\"><br> ".c2koi("$accpt_line")."<br>\n");
	    if( trim($tquota)!="") print("<br>".c2koi("Квота: ").c2koi(bytes2mega($tquota))."");
	    if( $floq) print("<font color=red><b>&nbsp&nbsp</b><i>".c2koi("(исчерпана)")."</i></font><br>\n"); 

	    print("</font><br></td><td><font class=packets1><i>".$atraf[$i]["in_pkts"]." </td><td>".bytes2mega($atraf[$i]["in_bytes"])." </td><td> <font class=packets1><i>".$atraf[$i]["out_pkts"]." </td><td> ".bytes2mega($atraf[$i]["out_bytes"])."</td> \n");
	    $bufdir1="";
	    $bufdir1=get_policy_param($atraf[$i]["policy"]["policyname"],"in","",0,TRUE);
	    if( $bufdir1=="") $bufdir1=get_policy_param($atraf[$i]["policy"]["policyname"],"out","",0,TRUE);
	    print("<td class=td1> <a href=\"ustat.php?client=$ip&grp=$grp&full=1&proto=".$atraf[$i]["policy"]["proto"]."&ports=".str_replace(" ","",$atraf[$i]["policy"]["ports"])."&dir=$bufdir1&policy=".$atraf[$i]["policy"]["policyname"]."\" title=\"Отчет по этому виду трафика\"><img src=\"icons/gnumeric.gif\" title=\"Отчет по этому виду трафика\"> </a></td></tr>");
	}
    }
    print("<tr><td colspan=5 class=td2><hr></td></tr></table><br><font class=text41>\n");
    
    wlog("Просмотр параметров клиента $ip группы $grp",0,FALSE,1,FALSE);

}

#-------------------------------------------------------------


function strdate2stamp($strdate,$sep="-")
{
    $day=gettok($strdate,1,$sep);
    $mon=gettok($strdate,2,$sep);
    $year=gettok($strdate,3,$sep);
    $hour=gettok($strdate,4,$sep);
    $min=gettok($strdate,5,$sep);
    $sec=gettok($strdate,6,$sep);
    $rez=mktime($hour,$min,$sec,$mon,$day,$year);
    return($rez);
}

#-------------------------------------------------------------

function stampdate2str($tdate)
{
    $rez=date("d\-m\-y\ H\:i\:s", $tdate);
    return($rez);
}

#-------------------------------------------------------------

function get_query1($ip,$date1,$date2,$query_ptr,$proto="",$ports="",$dir="",$policy="",$group="",$fldirect="")
{
    global $report_min_procent,$rep_whoisurl;
    global $mysql_user, $mysql_password, $mysql_host, $ulog_dbname;
    global $mysql_ulogd_user, $mysql_ulogd_password, $mysql_ulogd_host;
    global $_groupby,$_procents,$_shost,$usr_cname_spaces;
    
    $alist=get_iflist(2);
    $fldirect=( trim($fldirect)=="") ? "1" : $fldirect;
        
    $sqllnk=mysql_connect( $mysql_ulogd_host, $mysql_ulogd_user, $mysql_ulogd_password);
    if( !$sqllnk) {
	wlog("Failed connecting to MySQL Server in get_query1()!",2,TRUE,5,FALSE); exit;
    }
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$sqllnk);
    if( ! mysql_select_db($ulog_dbname)) {
	wlog("Failed database selecting in get_query1()!",2,TRUE,5,FALSE); exit;
    }
    
    $_time1=time();
    $date1=(trim($date1)!="") ? $date1."-00-00-00" : $date1;
    $date2=(trim($date2)!="") ? $date2."-23-59-59" : $date2;

    $ipaddr=( trim($ip)!="") ? sprintf("%u",ip2long($ip)) : 0;


    $ipshost=( trim($_shost)!="") ? sprintf("%u",ip2long($_shost)) : 0;

    $oobdate1=strdate2stamp($date1);
    $oobdate2=strdate2stamp($date2);
    
    $pck_date1 = stampdate2str($oobdate1);
    $pck_date2 = stampdate2str($oobdate2);
    

    $_polkey="";
    $protonum=( !trim($proto=(( $proto=="all") ? "":$proto)) ) ? "":getprotobyname($proto);
    $flInv=FALSE;
    if( $ports!="") {
	$ports=( $ports[0]=="(") ? str_replace("(","",$ports) : $ports;
	$ports=( $ports[strlen($ports)-1]==")") ? str_replace(")","",$ports) : $ports;
	$flInv=($ports[0]=="!") ? TRUE : FALSE;
	$ports=($ports[0]=="!") ? str_replace("!","",$ports) : $ports;
    }
    if( ( $proto!="") and ( $ports!="") and ( $dir=="")) {
	wlog("For not empty proto and ports variable dir must be specified...",2,TRUE,4,FALSE); exit; 
    }


    $_line1="SELECT oob_time_sec, DATE(FROM_UNIXTIME(oob_time_sec)) as date1, sum(ip_totlen) as traf_sum, oob_in, oob_out, ip_saddr, ip_daddr, ip_protocol, tcp_sport, tcp_dport, udp_sport, udp_dport, udp_len";
    $_line1=$_line1." FROM ulog WHERE oob_time_sec>=".$oobdate1."&&oob_time_sec<=".$oobdate2;
    if( trim($ip)!="") { $_line1=$_line1."&&ip_daddr=".$ipaddr; }
    if( (trim($_shost)!="") and (trim($_groupby)=="3")) { $_line1=$_line1."&&ip_saddr=".$ipshost; }
    if( $fldirect=="1") {
	if( count($alist)==1) {
	    if( $alist[0]["local"]=="0") $_line1=$_line1."&&(oob_in='".$alist[0]["ifname"]."')";
	} elseif( count($alist)>1) {
	    $n=0; foreach($alist as $alistkey => $alistvalue) if( $alistvalue["local"]=="0") $n++;
	    if( $n>1) {
		$_line1=$_line1."&&(";
		foreach($alist as $alistkey => $alistvalue) {
		    if(( $alistvalue["local"]!="0") || ( !trim($alistvalue["ifname"]))) continue;
		    if( $alistkey>0) $_line1=$_line1." or ";
		    $_line1=$_line1."(oob_in='".$alistvalue["ifname"]."')";
		}
		$_line1=$_line1.")";
	    } elseif( $n==1) {
		foreach($alist as $alistkey => $alistvalue) {
		    if( $alistvalue["local"]=="0") $_line1=$_line1."&&(oob_in='".$alistvalue["ifname"]."')";
		}
	    }
	}
    } elseif( $fldirect=="2") {
	if( count($alist)==1) {
	    if( $alist[0]["local"]=="1") $_line1=$_line1."&&(oob_in='".$alist[0]["ifname"]."')";
	} elseif( count($alist)>1) {
	    $n=0; foreach($alist as $alistkey => $alistvalue) if( $alistvalue["local"]=="1") $n++;
	    if( $n>1) {
		$_line1=$_line1."&&(";
	        foreach($alist as $alistkey => $alistvalue) {
		    if(( $alistvalue["local"]!="1") || ( !trim($alistvalue["ifname"]))) continue;
		    if( $alistkey>0) $_line1=$_line1." or ";
		    $_line1=$_line1."(oob_in='".$alistvalue["ifname"]."')";
		}
		$_line1=$_line1.")";
	    } elseif( $n==1) {
		foreach($alist as $alistkey => $alistvalue) {
		    if( $alistvalue["local"]=="1") $_line1=$_line1."&&(oob_in='".$alistvalue["ifname"]."')";
		}
	    }
	}
    }
    $cp=coltoks($ports,",:");
    if(( $proto!="") && ( $proto!="all")) {
      $_line1=$_line1."&&ip_protocol=$protonum";
      if( $ports!="") {
	if( $dir=="in") {
	    for( $icp=1; $icp<=$cp; $icp++) {
		$_line1=( !$flInv) ? $_line1."&&tcp_dport=".gettok($ports,$icp,",:") : $_line1."&&tcp_dport!=".gettok($ports,$icp,",:");
	    }
	} elseif( $dir=="out") {
	    for( $icp=1; $icp<=$cp; $icp++) {
		$_line1=( !$flInv) ? $_line1."&&tcp_sport=".gettok($ports,$icp,",:") : $_line1."&&tcp_sport!=".gettok($ports,$icp,",:");
	    }
	}
      }
    }

    if( (trim($_groupby)=="") or ( trim($_groupby)=="1")) {
	$_line1=$_line1." GROUP BY ip_saddr ORDER BY traf_sum DESC";
    } elseif( trim($_groupby)=="2") {
	$_line1=$_line1." GROUP BY date1 ORDER BY date1 DESC";
    } elseif( trim($_groupby)=="3") {
	$_line1=$_line1." GROUP BY ip_daddr ORDER BY traf_sum DESC";
    }

    
  if( $_procents) {
    $summ1=0;
    $_csum=mysql_query($_line1);
    while($row2=mysql_fetch_array($_csum)) { $summ1+=$row2["traf_sum"]; }
    $_persent=( $summ1==0) ? 1 : ($summ1/100);
  } else {
    $_persent=1;
  }


    $result=mysql_query($_line1)
    or die("SQL query selecton failed in get_query1() for $_line1");
    

    print("<br><br><font class=top1>Детализация трафика<br><br><font style=\"font-size:11pt\">\n");

    if( trim($ip)!="") {

	$cname=get_usr_param($group,$ip,"cname",TRUE);
	if( $usr_cname_spaces) $cname=str_replace("_"," ",$cname);
	
	$line0=( trim($cname)=="") ? "$ip" : "$cname / $ip";
	
	print("По клиенту <font style=\"color:blue\"> $line0</font><br>\n");
	print("<font style=\"font-style: italic; font-size:9pt\">( ".gethostbyaddr($ip)." )</font><br><br>\n");
    } else {
	print("По всем клиентам <br><br>\n");
    }
    
    print("За период<br><font style=\"font-style: italic; font-size:9pt\"> с $pck_date1 по $pck_date2</font><br><br> \n");
    
    print("<table class=\"table1\" cellpadding=\"6px\">\n");
    if( (trim($_groupby)=="1") or (trim($_groupby)=="")) {
	print("<tr><th><b> Удаленный хост</b></th>".(( $query_ptr) ? "<th><b> IP </b></th>" : "")."<th><b>Трафик</b></th>".(($_procents) ? "<th><b> % </b></th> </tr>" : "")."\n");
    } elseif( trim($_groupby)=="2") {
	print("<tr><th><b> Дата</b></th><th><b>Трафик</b></th></tr>\n");
    } elseif( trim($_groupby)=="3") {
	print("<tr><th><b> Клиент</b></th><th><b> Удаленный хост</b></th>".(( $query_ptr) ? "<th><b> IP </b></th>" : "")."<th><b>Трафик</b></th>".(($_procents) ? "<th><b> % </b></th> </tr>" : "")."\n");
    }
    $_ctraf=0;
    while($_row=mysql_fetch_array($result)) {
	
	$_saddr=long2ip($_row["ip_saddr"]);
	$_daddr=long2ip($_row["ip_daddr"]);
	if( $_procents) $_traf1=round($_row["traf_sum"]/$_persent,2);
	$_ctraf +=$_row["traf_sum"];
	if( $_procents) {
	    if( $_traf1<=$report_min_procent) { unset($_traf1); continue; }
	}
	$chost=( $query_ptr) ? gethostbyaddr($_saddr) : "";
	$cdate=gettok($_row["date1"],3,"-")."-".gettok($_row["date1"],2,"-")."-".gettok($_row["date1"],1,"-");
	$slink="<a href=\"ustat.php?d1=$date1&d2=$date2&shost=$_saddr&groupby=3&fd=$fldirect&run=1".(($query_ptr) ? "&query_ptr=1" : "")."\" target=_blank class=awho title=\"Показать клиентов по этому трафику\">";
	if( (trim($_groupby)=="1") or (trim($_groupby)=="")) {
	    print("<tr><td> ".(( $query_ptr) ? $slink.$chost."</a></td><td><font style=\"font-style: italic\">".$slink.$_saddr."</a></font>" : $slink.$_saddr."</a>")."</td>\n");
	} elseif( trim($_groupby)=="2") {
	    print("<tr><td>$cdate</td>\n");
	} elseif( trim($_groupby)=="3") {
	    $dhost=gethostbyaddr($_daddr);
	    print("<tr><td> $dhost<br><font style=\"FONT: italic 8pt Arial\">$_daddr</font></td><td> ".(( $query_ptr) ? $chost."</td><td><font style=\"font-style: italic\">".$_saddr."</font>" : $_saddr)."</td>\n");
#	    print("<td> $dhost<br>$_daddr</td>\n");
	}
	print("<td> ".bytes2mega($_row["traf_sum"])."</td>  \n");
	if( $_procents) print("<td> ".$_traf1." % </td>  \n");
	if( (trim($_groupby)=="1") or (trim($_groupby)=="")) {
	    print("<td> <a href=\"http://".(( trim($chost)=="") ? $_saddr : $chost)."\" class=a1 target=_blank><img src=\"icons/referencer.gif\" title=\"link to http://$_saddr\"></a> &nbsp <a href=\"ff.php?f=whois&p=$_saddr\" class=a1 target=_blank><img src=\"icons/question.gif\" title=\"Whois $_saddr\"></a> &nbsp <a href=\"sets.php?mode=set_addto&addr=$_saddr\" target=_blank class=a1 title=\"Добавить в сетлист\"><img src=\"icons/evolution-tasklist.gif\" title=\"Добавить в сетлист\"></a></td>\n");
	} elseif( trim($_groupby)=="3") {
	    print("<td> <a href=\"http://".(( trim($chost)=="") ? $_saddr : $chost)."\" class=a1 target=_blank><img src=\"icons/referencer.gif\" title=\"link to http://$_saddr\"></a> &nbsp <a href=\"ff.php?f=whois&p=$_saddr\" class=a1 target=_blank><img src=\"icons/question.gif\" title=\"Whois $_saddr\"></a> &nbsp <a href=\"sets.php?mode=set_addto&addr=$_saddr\" target=_blank class=a1 title=\"Добавить в сетлист\"><img src=\"icons/evolution-tasklist.gif\" title=\"Добавить в сетлист\"></a></td>\n");
	}
	print("</tr>");
    }
    $_polkey=$policy.$proto.(($flInv) ? "!" : "").str_replace(",","",$ports);
    print("</tr></table><br><font class=text1 color=696969><b><i> Итого: </i>&nbsp&nbsp ".bytes2mega($_ctraf)."</b><br>");
    if( trim($_groupby)!="3") {
	if( isadmin()) print("<br><br><a href=\"ipt.php?p=replcounts&ip=$ip&ctraf=$_ctraf&pkey=$_polkey\">Установить результат на счетчик пользователя $ip</a><br><br>  \n");
    }
    $_wtime=time()-$_time1;
    print("<br><br><font style=\"font-size:8pt; font-style: italic;\">Сформировано за ".round(($_wtime/60),2)."мин. &nbsp ".round($_wtime,2)."сек. </font> \n");
    
    wlog("Формирование отчета с параметрами: ip $ip, ports $ports, proto $proto, date1 $date1, date2 $date2",0,FALSE,1,FALSE);    

}

#------------------------------------------------------------


function addbutton($btn_name,$btn_icon,$btn_href="stat.php")
{
    print("<a href=\"".$btn_href."\" title=\"".$btn_name."\"><img src=\"".$btn_icon."\"></a>&nbsp   \n");

}

#-----------------------------------------------------------


function get_grp_userscount($pgroup)
{
    global $iptconf_dir,$users_dir;
    if( trim($pgroup)=="") return("");
    $link=mysql_getlink();
    if( !$_id=get_usr_param($pgroup,"group_id")) return(FALSE);
    if( !$res=mysql_query("SELECT COUNT(*) AS userc FROM clients WHERE group_id=$_id")) return(FALSE);
    $row=mysql_fetch_array($res);
    return($row["userc"]);
}

#------------------------------------------------------------

function load_policies_list()
{
    global $iptconf_dir;
    if( ( !file_exists("$iptconf_dir/policies")) or ( ! is_readable("$iptconf_dir/policies"))) {
	wlog("Error opening policies file...  \n",2,TRUE,5,TRUE); exit; 
    }
    $pfile1=fopen("$iptconf_dir/policies","r");
    $aa1=array();
    $strnum1=0;
    while( !feof($pfile1)) {
	$str1=_trimline(strtolower(fgets($pfile1))); $strnum1++;
	if( trim($str1)=="") continue;
	if( $str1[0]=="#") continue;
	$buf1=gettok($str1,1," \t");
	if( $buf1=="policy") {
	    $bufpol=gettok($str1,2," \t");
	    $buftitle=get_policy_param($bufpol,"title");
	    $aa1["$bufpol"]=$buftitle;
	}
    }
    if( count($aa1)!=0) { 
	return($aa1); 
    } else {
	return(FALSE);
    }

}

#--------------------------------------------------------------

function get_policy_users($ppname)
{
    global $iptconf_dir,$users_dir;
    $arez=array(); $ai=0;
    
    $link=mysql_getlink();
    $line="SELECT ip, policies, cname, (SELECT groups.name FROM groups WHERE groups.id=clients.group_id) AS grpname FROM clients WHERE LOCATE(\"$ppname\",policies)>0";
    if( !$res=mysql_query($line)) return(FALSE);
    while($row=mysql_fetch_array($res)) {
	$arez[$row["grpname"]."|".$ai]=$row["ip"];
	$ai++;
    }
    
    return($arez);

}
#-------------------------------------------------------------------
function getmarkers($pmarker="")
{
    global $iptconf_dir;
    if( ( !file_exists("$iptconf_dir/policies")) or ( ! is_readable("$iptconf_dir/policies"))) {
	wlog("Error opening policies file...  \n",2,TRUE,5,TRUE); exit; 
    }
    $pfile1=fopen("$iptconf_dir/policies","r");
    $rez=array();
    $strnum1=0;
    $open=FALSE; $bufpolicy=""; $bufmarker="";
    while( !feof($pfile1)) {
	$str1=_trimline(strtolower(fgets($pfile1))); $strnum1++;
	if( trim($str1)=="") continue;
	if( $str1[0]=="#") continue;
	$buf1=gettok($str1,1," \t");
	if( !$open) {
	    if( $buf1=="policy") { 
		$bufpolicy=gettok($str1,2," \t"); 
		$bufmarker=""; $buftitle="";
		$open=TRUE; 
		continue; 
	    }
	} else {
	    if( $buf1=="}") { 
		if( $bufmarker!="") { 
		    if( $pmarker) {
			if( $pmarker==$bufmarker) {
			    $rez[$bufmarker][$bufpolicy]=$buftitle;
			}
		    } else { $rez[$bufmarker][$bufpolicy]=$buftitle; }
		}
		$open=FALSE; continue; 
	    } elseif( $buf1=="manglemark") {
		$bufmarker=gettok($str1,2," \t"); 
	    } elseif( $buf1=="title") {
		$buftitle=( substr($buftitle=trim(str_replace("title ","",$str1)),0,1)=="\"") ? substr($buftitle,1):$buftitle;
		$buftitle=( $buftitle[strlen($buftitle)-1]=="\"") ? substr($buftitle,0,-1):$buftitle;
	    }
	    
	}
    }

    fclose($pfile1);
    return($rez);
}
#-------------------------------------------------------------------
function s_list_users($grp,$user)
{
    global $iptconf_dir;
    global $users_dir;
    
    if(( !trim($grp)) || ( !trim($client))) return(FALSE);
    
    $scriptname=( !$users) ? "stat.php" : "users.php";
    
    $link=mysql_getlink();
    if( !$_id=get_usr_param($grp,"group_id")) {
	wlog("Группа $grp не найдена!",2,TRUE,4,TRUE); exit;
    }
    $title1=get_usr_param($grp,"title");
    $defpolicy=get_usr_param($grp,"_default_policy");
    $line="SELECT * FROM clients WHERE (group_id=$_id) && (ip=$client)";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса в функции s_list_users()!",2,TRUE,5,TRUE); exit;
    }
    while( $row=mysql_fetch_array($res)) {
	$bufip=""; $bufhost; $bufpols=""; $bufcounts="";
	$buf_inpkts="0"; $buf_inbytes="0"; $buf_outpkts="0"; $buf_outbytes="0"; 
	$bufip=$row["ip"];
	$bufhost=gethostbyaddr($bufip);
	$bufpols=( !trim($row["policies"])) ? $defpolicy:trim($row["policies"]);
	$bufcounts=f_getcounts($bufip);
	if( $bufcounts!="") {
	    $t=gettok($bufcounts,1,"|");
	    $buf_inpkts=gettok($t,1,";");
	    $buf_inbytes=bytes2mega(gettok($t,2,";"));
	    $t=gettok($bufcounts,2,"|");
	    $buf_outpkts=gettok($t,1,";");
	    $buf_outbytes=bytes2mega(gettok($t,2,";"));
	}
	print("<tr><td> $bufip".((trim($bufhost)!=trim($bufip)) ? "<br><font style\"font-size:8pt;\"><i>".$bufhost : "")."</font> </td><td> <font style=\"font-size:11px\"><br>\n");
	$ic=coltoks($bufpols,",;");
	$floq=FALSE;
	if( $ic==1) { 
	    $ttraf=mega2bytes($buf_inbytes);
	    $floq=( trim($tquota)!="") ? (( $tquota <= $ttraf) ? TRUE : FALSE) : FALSE;
	    $_bufline=get_policy_param($bufpols,"title")." <i>[<b>".$bufpols."</b>]</i> "; 
	    if( $floq) $_bufline = "<font color=red> $_bufline </font>\n";
	    print($_bufline);
	    if( $floq) print("<br><font color=red><b>!:&nbsp&nbsp</b><i>квота исчерпана</i></font><br>\n"); 
	} else {
	    $floq=FALSE;
	    for( $im=1; $im<=$ic; $im++) {
		$currpol=gettok($bufpols,$im,",:");
		$tquota=mega2bytes(get_policy_param($currpol,"accept","quota"));
		$ttraf=mega2bytes($buf_inbytes);
		$floq=( trim($tquota)!="") ? (( $tquota <= $ttraf) ? TRUE : FALSE) : FALSE;
		$_bufline="<font style=\"font-size:4px\"><li></font>".get_policy_param($currpol,"title")."<i> [<b>".$currpol."</b>] </i><br>\n";
		print($_bufline); 
		if( $floq) print("<font color=red><b>!:&nbsp&nbsp</b><i>квота исчерпана</i></font><br><br>\n"); 
	    }
	}
	print("</font><br></td><td><font class=packets1><i>$buf_inpkts </td><td> $buf_inbytes </td><td> <font class=packets1><i>$buf_outpkts </td><td> $buf_outbytes </td>  <td class=td3> <a href=\"".$scriptname."?grp=$grp&client=$bufip\" title=\"Открыть пользователя\"><img src=\"icons/gtk-open.gif\" title=\"Открыть пользователя\"></a>\n");
	if( ($users) and ( isadmin()))  print("<a href=\"users.php?grp=$grp&client=$bufip&mode=delusr\" title=\"Удалить пользователя\"><img src=\"icons/edittrash.gif\" title=\"Удалить пользователя\"></a>\n");
	print("<a href=\"ustat.php?client=$bufip&grp=$grp&full=1\" title=\"Отчет по трафику\"><img src=\"icons/gnumeric.gif\" title=\"Отчет по трафику\"></a>"); 
	print(" </td></tr>\n");
    }

    wlog("Просмотр списка клиентов в группе $grp, параметр user $user",0,FALSE,1,FALSE);

}

#--------------------------------------------------------------------------------
function gtext($parent,$pname)
{
    global $lang,$lang_dir,$lang_codepage;
    if( trim($parent)=="") return(FALSE);
    if( trim($pname)=="") return(FALSE);
    $lang=( trim($lang)=="") ? "russian" : $lang;
    $lang_codepage=( trim($lang_codepage)=="") ? "koi8r" : $lang_codepage;
    if(( trim($lang_dir)=="") or ( !is_dir($lang_dir))) return(FALSE);
    $lfilename=$lang_dir."/lang_".$lang;
    if( !$lfile=fopen($lfilename,"r")) {
	wlog("Error opening language file $lfilename in gtext().",2,TRUE,5,TRUE); exit;
    }
    $rez="";
    while( !feof($lfile)) {
	$str=trim(fgets($lfile));
	$buf1=gettok($str,1,"|");
	$bufw1=trim(gettok($buf1,1,"_"));
	$bufw2=str_replace($bufw1."_","",$buf1);
	if(( $bufw1==$parent) and ( $bufw2==$pname)) {
	    $rez=trim(gettok($str,2,"|"));
	    break;
	}
    }
    if( $lang_codepage!="koi8r") {
	if( !$rez=iconv("koi8r",$lang_codepage,$rez)) {
	    wlog("Error codepage converting in gtext().",2,TRUE,5,TRUE); exit;
	}
    }
    return($rez);
    
}
#--------------------------------------------------------------------------------
function show_load($url,$caption="Подготовка данных...")
{
    print("<script type=\"text/javascript\">\n");
    print("a1 = new Image();\n a1.src=\"icons/loader.gif\";\n");
    print("</script>\n");

    print("<br><br><br><center>\n");
    print("<br><br><br>\n<font style=\"FONT:normal 11pt Tahoma;\">$caption</font><br><br>\n");
    print("<img src=\"icons/loader.gif\" title=\"$caption\">\n<br><br>\n");
    print("<script type=\"text/javascript\">\n");
    print("document.location.replace('$url');\n");
    print("</script>\n");
    print("</body>\n</html>\n");

}

#--------------------------------------------------------------------------------
function show_load1($url,$caption="Подготовка данных...")
{
    print("<script type=\"text/javascript\">\n");
?>
function preload(images) {
    if (typeof document.body == "undefined") return;
    try {
        var div = document.createElement("div");
        var s = div.style;
        s.position = "absolute";
        s.top = s.left = 0;
        s.visibility = "hidden";
        document.body.appendChild(div);
        div.innerHTML = "<img src=\"" + images.join("\" /><img src=\"") + "\" />";
    } catch(e) {
        // Error. Do nothing.
    }
}
preload([
    'icons/loader.gif'
]);
<?php    print("</script>\n");

    print("<br><br><br><center>\n");
    print("<br><br><br>\n<font style=\"FONT:normal 11pt Tahoma;\">$caption</font><br><br>\n");
    print("<img src=\"icons/loader.gif\" title=\"$caption\">\n<br><br>\n");
    print("<script type=\"text/javascript\">\n");
    print("document.location.replace('$url');\n");
    print("</script>\n");
    print("</body>\n</html>\n");

}


#--------------------------------------------------------------------------------

function genframe($p) 
{
    $chkout="";
    if( !trim($p)) return(FALSE);
    $img=( file_exists($p)) ? "icons/apply16.gif" : "icons/cancel16.gif";
    $answ=( file_exists($p)) ? "" : "НЕ ";
    $type=( is_file($p)) ? "Файл" : "Каталог";
    $chkout="<img src=\"$img\" title=\"$type $answ существует\" border=0 hspace=0 vspace=0 style=\"background-color:transparent;\">";
    return($chkout);
}


#--------------------------------------------------------------------------------

function toster($pval)
{
    $rez="";
    if( is_bool($pval)) {
	$rez=( !$pval) ? "FALSE" : "TRUE";
    }
    if( is_int($pval)) {
	$rez="$pval";
    }
    return($rez);
}

#--------------------------------------------------------------------------------

function options_save($aaopt)
{
    global $iptconf_dir;
    $conf=$iptconf_dir."/config.php";
    if( count($aaopt)==0) return(FALSE);
    
    $tmpf=tempnam("$iptconf_dir/","config");
    $tmpfile=fopen($tmpf,"w");
    $conffile=fopen($conf,"r");
    $flconf=FALSE;
    while( !feof($conffile)) {
	$string=trim(fgets($conffile));
	if( trim($string)=="") {
	    fwrite($tmpfile,"\n");
	    continue;
	}
	$buf00=str_replace(" ","",$string);
	if( $buf00=="###Configzone") {
	    $flconf=TRUE;
	}
	if( $buf00=="#########Endofconfigzone") {
	    $flconf=FALSE;
	    $line="";
	}
	if( $flconf) {
	    $bufkey=str_replace("$","",trim(gettok($string,1,"=")));
	    $line="";
	    reset($aaopt);
	    foreach($aaopt as $aafkk => $aafvv) {
		if( substr($aafkk,0,7)=="|nostr|") {
		    $bufkk=substr($aafkk,7);
		    $flnostr=TRUE;
		} else {
		    $bufkk=$aafkk;
		    $flnostr=FALSE;
		}
		if(( trim($bufkk)=="") or ( trim($aafvv)=="")) continue;
		if( $bufkk==$bufkey) {
		    $line="$".$bufkey." = ".(( !$flnostr) ? "\"".$aafvv."\";" : $aafvv.";" );
		}
	    }
	    $line=( trim($line)=="") ? $string : trim($line);
	    fwrite($tmpfile,$line."\n");
	} else {
	    if( strlen(trim($string))>0) fwrite($tmpfile,$string."\n");
	    if( $string=="?>") break;
	}
    }
    fclose($conffile);
    fclose($tmpfile);
    if( file_exists($conf.".bak")) unlink($conf.".bak");
    rename($conf,$conf.".bak");
    return(rename($tmpf,$conf));
}
#--------------------------------------------------------------------------------
function web_show_back($url,$txt="Назад",$flbr=TRUE)
{
    if( trim($url)=="") return(FALSE);
    print("<a href=\"$url\"><img src=\"icons/gtk-undo.gif\" title=\"$txt\"></a><a href=\"$url\" class=text33>$txt</a>");
    if( $flbr) print("<br>\n");
}
#--------------------------------------------------------------------------------
?>