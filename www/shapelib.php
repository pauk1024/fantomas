<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: shapelib.php
# Description:
# Version: 0.2.4.8
###


function shape_getstatus()
{
    global $_grep,$_tc;
    $ret=FALSE;
    list($rr,$aa)=_exec2("$_tc qdisc show | $_grep 'ingress ffff:'");
    if( count($aa)==0) {
	$ret=FALSE;
    } else {
	if( count($aa)>0) $ret=TRUE;
    }
    return($ret);
}
#-----------------------------------------------------------------------
function shape_getprefkey($pclient="",$pmode=1)
{
    if( $pmode==0) return(0);
    if( !$netb=gettok($pclient,2,"/")) { $netb="0"; } else {
	$pclient=gettok($pclient,1,"/");
    }
    $w=array();
    $prefkey=0;
    for($ii=1;$ii<=4;$ii++) {
	$w[$ii]=gettok($pclient,$ii,".");
	if( trim($w[$ii])=="") $w[$ii]="0";
	$prefkey+=$w[$ii];
    }
    $prefkey=$prefkey+$netb+10+$pmode;
    return("".$prefkey);
}
#-----------------------------------------------------------------------
function shaper_get_conf($param,$parn="",$sep=" \t")
{
    global $iptconf_dir;
    $rez="";
    if( !file_exists("$iptconf_dir/shaperconf")) {
	return("");
    }
    if( !$shconf=fopen("$iptconf_dir/shaperconf","r")) {
	wlog("Error opening $iptconf_dir/shaperconf in shaper_get_conf().",2,TRUE,5,TRUE); exit;
    }
    $strnum=0;
    while( !feof($shconf)) {
	$str=_trimline(fgets($shconf),$sep); $strnum++;
	$tmp1=gettok($str,1,$sep);
	if( substr_count($tmp1,$param)>0) {
	    $_tmpp1=_trimline(str_replace($tmp1,"",$str),$sep);
	    if( trim($parn)!="") {
		if( substr_count($_tmpp1,$parn)==0) continue;
		$ic=coltoks($_tmpp1,$sep);
		for($u=0; $u<=$ic; $u++) {
		    $tmp0=gettok($_tmpp1,$u,$sep);
		    if( substr_count($tmp0,$parn)>0) {
			$rez=gettok($tmp0,2,":="); break 2;
		    }
		}
	    } else {
		$rez=_trimline(str_replace($tmp1,"",$str),$sep);
	    }
	}
    }
    return(( trim($rez)=="") ? FALSE : $rez);

}
#-----------------------------------------------------------------------
function shaper_check_client($pclient,$retArray=FALSE,$pifname="")
{
    global $_grep,$_tc;
    global $_ifname,$_shaper_default_ifname;
    if( trim($pclient)=="") return(FALSE);
    if( trim($pifname)=="") {
	if( !$pifname=shaper_get_conf("client=$pclient","ifname")) {
	    $pifname=( isset($_ifname)) ? $_ifname : $_shaper_default_ifname;
	}
    }
    $ret=FALSE;
    $retingress=FALSE;
    $retegress=FALSE;
    $prefkeyin=shape_getprefkey($pclient,1);
    $prefkeyout=shape_getprefkey($pclient,2);
    list($rr,$aa)=_exec2("$_tc filter show parent 2: dev $pifname | $_grep 'pref $prefkeyin'");
    if( count($aa)>0) {
	$ret=TRUE; $retingress=TRUE;
    }
    unset($aa);
    list($rr,$aa)=_exec2("$_tc filter show parent ffff: dev $pifname | $_grep 'pref $prefkeyout'");
    if( count($aa)>0) {
	$ret=TRUE; $retegress=TRUE;
    }
    $rez=( !$retArray) ? $ret : array( "all" => $ret, "ingress" => $retingress, "egress" => $retegress);
    return($rez);    
}
#-----------------------------------------------------------------------
function shaper_get_status($pclient)
{
    global $_sudo,$_grep,$_tc;
    global $_ifname;
    if( trim($pclient)=="") return(FALSE);
    $ret="";
    $aa=shaper_check_client($pclient,TRUE);
    $prefkeyin=shape_getprefkey($pclient,1);
    $prefkeyout=shape_getprefkey($pclient,2);
    $confstatus=shaper_get_conf("client=$pclient","status");
    if( $confstatus=="1") {
	$ret=( !$aa["all"]) ? "Запрещен" : "Запрещен <font color=red>!НО ЗАГРУЖЕН!</font>";
	$ret="<font style=\"FONT:normal 10pt Tahoma; color:maroon;\">".$ret."</font>";
    } else {
	$ret=( !$aa["all"]) ? "<font style=\"FONT:normal 10pt Tahoma; color:maroon;\">Не применен" : "<font style=\"FONT:normal 10pt Tahoma; color:teal;\">Применен";
	$ret=$ret."</font>";
    }
    return($ret);
}
#-----------------------------------------------------------------------
function shaper_modify_conf($param,$pline,$pmode=1)
{
    global $iptconf_dir;
    $rez="";
    $aaconf=array();
    if( is_readable("$iptconf_dir/shaperconf")) {
	$aaconf=file("$iptconf_dir/shaperconf");
    }
    if( !$tmpfile=fopen(($tmpname=tempnam($iptconf_dir,"shaperconf")),"a")) {
	wlog("Error creating temporary file in shaper_put_conf().",2,TRUE,4,TRUE); exit;
    }
    $flChange=FALSE;
    foreach($aaconf as $aakey => $aavalue) {
	$tmp1=gettok($aavalue,1," \t");
	if( trim($tmp1)==trim($param)) {
	    if( $pmode==2) continue;
	    $_line=trim($pline);
	    $flChange=TRUE;
	} else {
	    $_line=trim($aavalue);
	}
	fwrite($tmpfile,$_line."\n");
    }
    if(( $pmode==1) and ( !$flChange)) {
	fwrite($tmpfile,trim($pline)."\n");
    }
    fclose($tmpfile);
    if( file_exists("$iptconf_dir/shaperconf.bak")) unlink("$iptconf_dir/shaperconf.bak");
    rename("$iptconf_dir/shaperconf","$iptconf_dir/shaperconf.bak");
    rename($tmpname,"$iptconf_dir/shaperconf");

}
#-----------------------------------------------------------------------
function shaper_down($pifbifname="")
{
    global $_tc,$_ifconfig,$_grep,$_ip;
    global $iptconf_dir,$_shaper_default_ifname;
    if( trim($pifbifname)=="") return(FALSE);
    list($rr,$aa0)=_exec2("$_ifconfig | $_grep $pifbifname | $_grep -v grep");
    if( count($aa0)==0) {
	wlog("Interface $pifbifname is not found",2,TRUE,4,TRUE); exit;
    }
    if( !$pifname=shaper_get_conf("device","ifname")) $pifname=$_shaper_default_ifname;
    
    list($rr,$aar)=_exec2("$_tc qdisc del dev $pifname ingress");
    if( $rr>0) {
	wlog("Error removing shaper on $pifname at line 1 in shaper_down(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_tc qdisc del dev $pifname root");
    if( $rr>0) {
	wlog("Error removing shaper on $pifname at line 2 in shaper_down(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_tc qdisc del dev $pifbifname root");
    if( $rr>0) {
	wlog("Error removing shaper on $pifname at line 3 in shaper_down(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_ip link set $pifbifname down");
    if( $rr>0) {
	wlog("Error removing shaper on $pifname at line 4 in shaper_down(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    wlog("Downing shaper device $pifbifname",0,FALSE,1,FALSE);
}
#-----------------------------------------------------------------------
function shaper_up($pifbifname="",$pifname="")
{
    global $_grep,$_tc,$_lsmod,$_modprobe,$_ifconfig,$_ip;
    global $_shaper_default_ifname,$_shaper_default_ifbifname;
    $pifbifname=( trim($pifbifname)=="") ? $_shaper_default_ifbifname : $pifbifname;
    if( trim($pifbifname)=="") {
	wlog("Could not specified ifb interface name in shaper_up()...",2,TRUE,4,TRUE); exit;
    }
    $pifname=( trim($pifname)=="") ? $_shaper_default_ifname : $pifname;
    if( trim($pifname)=="") {
	wlog("Could not specified hardware interface name in shaper_up()...",2,TRUE,4,TRUE); exit;
    }
    list($rr,$aar)=_exec2("$_lsmod | $_grep ifb | $_grep -v grep");
    if( count($aar)==0) {
	unset($aar); unset($rr);
	_exec2("$_modprobe ifb");
	list($rr,$aar)=_exec2("$_lsmod | $_grep ifb | $_grep -v grep");
	if( count($aar)==0) {
	    wlog("Error loading ifb module in shaper_up().",2,TRUE,5,TRUE); exit;
	}
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_ifconfig | $_grep $pifbifname | $_grep -v grep");
    if( count($aar)>0) {
	wlog("Interface $pifbifname allready exists! SRC: shaper_up()...",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_ip link set $pifbifname up");
    if( $rr>0) {
	wlog("Error enabling $pifbifname interface in shaper_up(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_tc qdisc add dev $pifbifname root handle 1: prio");
    if( $rr>0) {
	wlog("Error configuring shaper at line 1 in shaper_up(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_tc qdisc add dev $pifbifname parent 1:1 handle 10: tbf rate 100mbit buffer 1600 limit 3000");
    if( $rr>0) {
	wlog("Error configuring shaper at line 2 in shaper_up(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_tc qdisc add dev $pifbifname parent 1:2 handle 20: sfq perturb 10");
    if( $rr>0) {
	wlog("Error configuring shaper at line 3 in shaper_up(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_tc qdisc add dev $pifname ingress");
    if( $rr>0) {
	wlog("Error configuring shaper at line 4 in shaper_up(): $ret.",2,TRUE,5,TRUE); exit;
    }
    unset($aar);
    list($rr,$aar)=_exec2("$_tc qdisc add dev $pifname root handle 2: prio");
    if( $rr>0) {
	wlog("Error configuring shaper at line 5 in shaper_up(): $ret.",2,TRUE,5,TRUE); exit;
    }
    shaper_modify_conf("device=$pifbifname","device=$pifbifname ifname=$pifname");
    wlog("Upping shaper device $pifbifname",0,FALSE,1,FALSE);

}
#-----------------------------------------------------------------------
function shaper_show_add_form()
{
    global $_shaper_default_ifbifname,$_shaper_default_ifname;
    global $_ifconfig,$_grep,$users_dir;
    global $_client,$_grp,$usr_cname_spaces;
    print("<br><font class=top1>Ограничение скорости трафика для клиента</font><br><br>\n");
    if( trim($_client)=="") {
     print("<br><font class=text42s>Выбрать клиента:</font><br>\n");
     if( trim($_grp)=="") {
	print("<table class=table4 cellpadding=\"4px\">\n");
	print("<form name=\"clselect\" id=\"clselect\" action=\"shaper.php\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"add\">\n");
	print("<tr><td class=td21> Из группы: </td><td> <span class=seldiv><SELECT name=\"grp\" id=\"grp\">\n");
	
	mysql_getlink();
	if( $res=mysql_query("SELECT * FROM groups WHERE 1")) {
	    while($row=mysql_fetch_array($res)) {
		$grpname=$row["name"];
		print("<option value=\"$grpname\">$grpname</option>\n");
	    }
	    unset($row);
	}
	mysql_free_result($res);
	
	print("</SELECT></span> </td>\n");
	print("<td> <input type=\"SUBMIT\" name=\"sbmt3\" value=\"Ok\" /> </td></tr>\n");
	print("<td class=td21> Клиент: </td><td class=td21> &lt не выбрана группа &gt </td><td> &nbsp </td></tr>\n");
	print("</form></table>\n");
      } else {
	print("<table class=table4 cellpadding=\"4px\">\n");
	print("<form name=\"clselect\" id=\"clselect\" action=\"shaper.php\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"add\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"$_grp\">\n");
	print("<tr><td class=td21> Из группы: </td><td class=td21> $_grp </td><td> &nbsp </td></tr>\n");
	print("<tr><td class=td21> Клиент: </td><td> <span class=seldiv><SELECT name=\"client\" id=\"client\">\n");
	
	if( !$_id=get_usr_param($_grp,"group_id")) {
	    wlog("Группа $_grp не найдена!",2); return(FALSE);
	}
	if( $res=mysql_query("SELECT * FROM clients WHERE group_id=$_id")) {
	    while( $row=mysql_fetch_array($res)) {
		$bufip=$row["ip"];
		$bufcname=$row["cname"];
		print("<option value=\"$bufip\">$bufip - $bufcname</option>\n");
	    }
	}
	
	print("</SELECT></span> </td>\n");
	print("<td> <input type=\"SUBMIT\" name=\"sbmt3\" value=\"Ok\" /> </td></tr>\n");
	print("</form></table>\n");
        
      }
    } else {
	print("<table class=table4 cellpadding=\"4px\">\n");
	print("<tr><td class=td21> Группа: </td><td class=td21> $_grp </td></tr>\n");
	print("<tr><td class=td21> Клиент: </td><td class=td21> $_client </td></tr>\n");
	print("</table>\n");
    }
    print("<br>\n");
    
    print("<table class=table4 cellpadding=\"4px\">\n");
    print("<form name=\"addshape1\" id=\"addshape1\" action=\"shaper.php\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" value=\"add\">\n");
    print("<input type=\"HIDDEN\" name=\"run\" value=\"1\">\n");
    print("<input type=\"HIDDEN\" name=\"grp\" value=\"$_grp\">\n");
    $bufcname=( trim($_client)=="") ? "" : "<br><i>".get_usr_param($_grp,$_client,"cname",TRUE)."</i>";
    if( $usr_cname_spaces) $bufcname=str_replace("_","",$bufcname);
    print("<tr><td class=td21> Клиент: </td><td class=td21> <input type=\"TEXT\" name=\"client\" value=\"$_client\" size=\"30\">$bufcname </td></tr>\n");
    print("<tr><td class=td21> Входящая скорость: </td><td> <input type=\"TEXT\" name=\"rin\" value=\"\" size=\"8\"> * </td></tr>\n");
    print("<tr><td class=td21> Исходящая скорость: </td><td> <input type=\"TEXT\" name=\"rout\" value=\"\" size=\"8\"> * </td></tr>\n");
    print("<tr><td class=td21> HW Интерфейс </td><td> <span class=seldiv><SELECT name=\"ifname\" id=\"ifname\">\n");
    foreach(get_iflist(1) as $alistvalue) print("<option value=\"$alistvalue\"".(( trim($alistvalue)==trim($_shaper_default_ifname)) ? " SELECTED" : "").">$alistvalue</option>\n");
    print("</SELECT></span>\n</td></tr>\n");
    print("<tr><td class=td21> IFB Интерфейс </td><td> <span class=seldiv><SELECT name=\"ifbifname\" id=\"ifbifname\">\n");
    list($rr,$arez)=_exec2("$_ifconfig | $_grep 'Link encap' | awk '{ print $1 }' | $_grep ifb");
    foreach($arez as $arezvalue) print("<option value=\"$arezvalue\"".(( trim($arezvalue)==trim($_shaper_default_ifbifname)) ? " SELECTED" : "").">$arezvalue</option>\n");
    print("</SELECT></span>\n</td></tr>\n");
    print("<tr><td class=td21 colpan=2> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Добавить\"> </td></tr>\n");    
    print("</form>\n</table>\n");
    print("<br><br><br>\n<div class=text40t style=\"width:80%;\">\n");
    print("* - Значения для входящей и исходящей скорости указываются слитно с единицами измерения - kbit, mbit, kbps, mbps.<br>Если указаны только цифры, то по-умолчанию подразумеваются килобиты(kbit).<br> Пример: <i>512kbit</i> - 512 килобит, <i>256</i> - 256 килобит.<br>\n");
    print("* Чтобы не создавать правило для входящего или исходящего трафика оставьте соответствующее поле пустым.");
    print("</div>\n<br>\n");
    print("<br><a href=\"shaper.php\" title=\"Назад (не сохранять изменения)\"><img src=\"icons/gtk-undo.gif\" title=\"Назад (не сохранять изменения)\">Назад</a><br>");
    
}
#-----------------------------------------------------------------------
function shaper_save_client($aclient="",$nomodify=FALSE)
{
    global $_sudo,$_tc;
    
    if( $aclient!="") {
	$_client=( isset($aclient["client"])) ? $aclient["client"]:"";
	$_grp=( isset($aclient["grp"])) ? $aclient["grp"]:"";
	$_ratein=( isset($aclient["ratein"])) ? $aclient["ratein"]:"";
	$_rateout=( isset($aclient["rateout"])) ? $aclient["rateout"]:"";
	$_ifname=( isset($aclient["ifname"])) ? $aclient["ifname"]:"";
	$_ifbifname=( isset($aclient["ifbifname"])) ? $aclient["ifbifname"]:"";
    } else {
	global $_grp,$_client,$_ratein,$_rateout,$_mode,$_ifname,$_ifbifname;
    }
    
    if( trim($_ifname)=="") {
	wlog("Не указан HW интерфейс - shaper_save_client().",2,TRUE,4,TRUE); exit;
    }
    if( trim($_ifbifname)=="") {
	wlog("Не указан IFB интерфейс - shaper_save_client().",2,TRUE,4,TRUE); exit;
    }
    if( trim($_client)=="") {
	wlog("Не указан клиент - shaper_save_client().",2,TRUE,4,TRUE); exit;
    }
    $prefkeyin=shape_getprefkey($_client,1);
    $prefkeyout=shape_getprefkey($_client,2);

    if( shaper_check_client($_client)) {
	wlog("Client $_client allready exists in shaper config!",2,TRUE,5,TRUE); exit;
    }
    
    if( trim($_rateout)!="") {
	$line="$_tc filter add dev $_ifname parent ffff: protocol ip prio $prefkeyout u32 ";
	$line=$line."match ip src $_client flowid 1:2 ";
	$line=$line."action police rate $_rateout burst 90k drop ";
	$line=$line."mirred egress mirror dev $_ifbifname";
	
	$line1="$_tc filter add dev $_ifname parent ffff: protocol ip prio $prefkeyout u32 ";
	$line1=$line1."match ip src $_client flowid 1:2 ";
	$line1=$line1."action police rate $_rateout burst 90k drop ";
	$line1=$line1."action mirred egress mirror dev $_ifbifname";
	
	list($rr,$aout)=_exec2($line);
	if( $rr>0) {
	    unset($aout);
	    list($rr1,$aout)=_exec2($line1);
	    if( $rr1>0) {
		wlog("Error running command tc with prefout in shaper_save_client().",2,TRUE,5,TRUE); exit;
	    }
	}
	unset($aout);
    }
    if( trim($_ratein)!="") {
	$line="$_tc filter add dev $_ifname parent 2: protocol ip prio $prefkeyin u32 ";
	$line=$line."match ip dst $_client flowid 1:2 ";
	$line=$line."action police rate $_ratein burst 90k drop ";
	$line=$line."mirred egress mirror dev $_ifbifname";

	$lin1e="$_tc filter add dev $_ifname parent 2: protocol ip prio $prefkeyin u32 ";
	$line1=$line1."match ip dst $_client flowid 1:2 ";
	$line1=$line1."action police rate $_ratein burst 90k drop ";
	$line1=$line1."action mirred egress mirror dev $_ifbifname";

	list($rr,$aout)=_exec2($line);
	if( $rr>0) {
	    unset($aout);
	    list($rr1,$aout)=_exec2($line1);
	    if( $rr1>0) {
		wlog("Error running command tc with prefin in shaper_save_client().",2,TRUE,5,TRUE); exit;
	    }
	}
	unset($aout);

    }
    if( !$nomodify) {
	shaper_modify_conf("client=$_client","client=$_client grp=$_grp ratein=$_ratein rateout=$_rateout ifname=$_ifname ifbifname=$_ifbifname keyin=$prefkeyin keyout=$prefkeyout");
    }
    wlog("Сохранение правил шейпера для клиента $_client",0,FALSE,1,FALSE);

}
#-----------------------------------------------------------------------

function shaper_del_client($pmode=0)
{
    global $_grep,$_client,$_ratein,$_rateout,$_mode,$_ifname,$_ifbifname;
    global $_sudo,$_tc,$_grp;
    if( trim($_client)=="") {
	wlog("Не указан клиент - shaper_save_client().",2,TRUE,4,TRUE); exit;
    }
    if( trim($_ifname)=="") {
	if( !$_ifname=shaper_get_conf("client=$_client","ifname")) {
	    wlog("Не указан HW интерфейс - shaper_save_client().",2,TRUE,4,TRUE); exit;
	}
    }
    $prefkeyin=shape_getprefkey($_client,1);
    $prefkeyout=shape_getprefkey($_client,2);
    list($rr,$aout)=_exec2("$_tc filter show parent 2: dev $_ifname | $_grep 'pref $prefkeyin'");
    if( $rr>0) {
	if( shaper_get_conf("client=$_client","keyin")!=$prefkeyin) {
	    wlog("Error deleting tc inbound rule: bad pref key in config.",2,TRUE,4,TRUE);
	} else {
	    list($rr1,$aout1)=_exec2("$_tc filter del dev $_ifname parent 2: pref $prefkeyin");
	    if( $rr1>0) wlog("Error deleting rule in tc with prefin for client $_client.",2,TRUE,4,TRUE);
	}
    }
    unset($aout);
    if( isset($aout1)) unset($aout1);
    list($rr,$aout)=_exec2("$_tc filter show parent ffff: dev $_ifname | $_grep 'pref $prefkeyout'");
    if( $rr>0) {
	if( shaper_get_conf("client=$_client","keyout")!=$prefkeyout) {
	    wlog("Error deleting tc outbound rule: bad pref key in config.",2,TRUE,4,TRUE);
	} else {
	    list($rr1,$aout1)=_exec2("$_tc filter del dev $_ifname parent ffff: pref $prefkeyout");
	    if( $rr1>0) wlog("Error deleting rule in tc with prefout for client $_client.",2,TRUE,4,TRUE);
	}
    }
    if( $pmode==1) {
	shaper_modify_conf("client=$_client","",2);
    } else {
	shaper_modify_conf("client=$_client","client=$_client grp=$_grp status=1 ratein=$_ratein rateout=$_rateout ifname=$_ifname ifbifname=$_ifbifname keyin=$prefkeyin keyout=$prefkeyout");
    }
    $logstr="";
    if( $pmode==0) {
	$logstr="с переходом в статус stop.";
    } elseif( $pmode==1) {
	$logstr="с удалением конфигурации правил.";
    }
    wlog("Удаление правил шейпера для клиента $_client $logstr",0,FALSE,1,FALSE);

}
#-----------------------------------------------------------------------


?>