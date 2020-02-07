<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.6
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: ports.php
# Description: 
# Version: 0.2.4.6
###


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

print("<html>\n");
$NoHeadEnd=TRUE;
require("include/head1.php");

print("<body><br><br>\n");

#-------------------------------------------------------------

function pf_getstatus()
{
    global $_iptables,$_sudo,$_grep;
    $rin=0; $rez=FALSE;
    list($rin,$ain)=_exec2("$_iptables -t mangle -nL PORTFILTER | $_grep DROP | $_grep -v grep");
    $rez=($rin!=0) ? FALSE : TRUE;
    return($rez);
}
#-------------------------------------------------------------
function print_policy($ppolname="",$pptitle="")
{

    print("<br>\n<table class=table4 width=\"77%\" cellpadding=\"3px\">\n");
    print("<tr><td width=\"22%\"><font style=\"font-size:9pt\">Имя:</td><td align=middle><b> $ppolname </b></td>\n");
    print("<td rowspan=2 width=\"8%\"><img src=\"icons/edittrash.gif\" title=\"Удаление еще не работает\"> &nbsp&nbsp");
    print("<a href=\"pol.php?height=300&width=300&modal=nomodal&p=$ppolname&shconf=1\" class=\"thickbox\" title=\"Конфиг политики <b>$ppolname</b>\"><img src=\"icons/eog.gif\" title=\"Показать конфиг политики\"></a> </td></tr>\n");
    print("<tr><td width=\"22%\"><font style=\"font-size:9pt\">Описание:</td><td align=middle><i>$pptitle</i></td>\n</tr>  \n");
    print("</table>\n");


}

#-------------------------------------------------------------

function load_ports_list($pproto="")
{
    global $iptconf_dir;
    if( ( !file_exists("$iptconf_dir/ports")) or ( ! is_readable("$iptconf_dir/ports"))) {
	wlog("Error opening ports file in load_ports_list()...\n",2,TRUE,5,TRUE); exit; 
    }
    $pfile1=fopen("$iptconf_dir/ports","r");
    $aa1=array();
    $strnum1=0;
    while( !feof($pfile1)) {
	$str1=_trimline(strtolower(fgets($pfile1))); $strnum1++;
	if( trim($str1)=="") continue;
	if( $str1[0]=="#") continue;
	if( $pproto!="") {
	    $buf1=gettok($str1,1,":");
	    if( $buf1==$pproto) {
		if( coltoks($str1," \t") == 1) {
		    $aa1[$str1]="";
		} else {
		    $buf11=gettok($str1,1," \t");
		    $aa1[$buf11]=trim(str_replace($buf11,"",$str1));
		}
	    }
	} else {
	    $buf1=gettok($str1,1," ");
	    if( coltoks($str1," \t") == 1) {
		$aa1[$buf1]="";
	    } else {
		$aa1[$buf1]=trim(str_replace(gettok($str1,1," \t"),"",$str1));
	    }
	}
    }
    fclose($pfile1);
    if( count($aa1)!=0) { 
	return($aa1); 
    } else {
	return(FALSE);
    }

}

#--------------------------------------------------------------

function show_add_form()
{
    global $_mode,$_pport,$_pproto,$_port;
    global $_src,$_dst,$_local,$srcpath,$dstpath,$direct;
    global $srcinv,$dstinv;
    print(" \n");
    
    print("<font class=top1>".(($_mode!="edit") ? "Добавить порт:" : "Редатирование порта:")."</font><br><br>\n");
    print("<table class=table5e width=\"650px\" cellpadding=\"10px\"> \n<tr><td>\n");
    print("<form name=\"form21\" id=\"form21\" action=\"ports.php\"> \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"$_mode\"> \n ");
    if( $_mode=="edit") print("<input type=\"hidden\" name=\"p\" value=\"$_port\"> \n ");
    print("<input type=\"hidden\" name=\"run\" value=\"1\"> \n ");
    print("<font style=\"FONT: normal 9pt Tahoma;color:696969;\">\n");
    print("<b>Порт:</b><br><table class=table32 cellpadding=\"5px\" width=\"95%\">\n");    
    $aproto=array("tcp", "udp", "icmp", "esp", "ah", "sctp");
    print("<tr><td> <select name=\"pproto\">\n");
    if( $_mode=="edit") {
	$sel=( (isset($_pproto)) and (trim($_pproto)!="")) ? $_pproto : "tcp";
    } else {
	$sel="tcp";
    }
    foreach($aproto as $apkk => $apvv) {
	print("<option value=\"$apvv\"".(($sel==trim($apvv)) ? " selected" : "").">$apvv\n");
    }
    print("</select>\n");
    print("<b>:</b> <input type=\"text\" name=\"pport\" size=\"10\"".(($_mode=="edit") ? " value=\"$_pport\"" : "")."> \n");
    print(" </td><td> \n");
    print("Направление: <SELECT name=\"direct\">\n ");
    print("<option value=\"sport\"".(($direct=="sport") ? " SELECTED" : "").">Исходный порт (src/--sport)\n ");
    print("<option value=\"dport\"".(($direct=="dport") ? " SELECTED" : "").">Порт назначения (dst/--dport)\n ");
    print("<option value=\"both\"".(($direct=="both") ? " SELECTED" : "").">В обе стороны( -m multiport --port)\n </select>\n");
    print(" </td></tr></table> \n");
    print("<br>\n<b>Ограничение по:</b><br>\n");
    print("<table class=table32 cellpadding=\"5px\" width=\"95%\"><tr><td>\n");
    print("<input type=\"checkbox\" id=\"src\" value=\"1\" name=\"src\"".(($_src) ? " CHECKED" : "")." onClick=\"javascript: document.getElementById('srcpath').disabled=( this.checked) ? false : true; document.getElementById('srcinv').disabled=( this.checked) ? false : true;\"><label for=\"src\">Источнику (src, --source) </label>\n");
    print("</td><td>\n");
    print("<input type=\"checkbox\" id=\"dst\" value=\"1\" titname=\"dst\"".(($_dst) ? " CHECKED" : "")." onClick=\"javascript: document.getElementById('dstpath').disabled=( this.checked) ? false : true; document.getElementById('dstinv').disabled=( this.checked) ? false : true;\"><label for=\"dst\">Адресу назначения (dst, --destination) </label> \n");
    print("</td></tr>\n<tr><td>\n");
    print("<input type=\"checkbox\" id=\"srcinv\" value=\"1\" title=\"Инверсия (отрицание)\" name=\"srcinv\"".(($srcinv=="!") ? " CHECKED" : "")."".(( !$_src) ? " disabled" : "")."><label for=\"srcinv\"><b>\"!\"</b></label> <font style=\"FONT: normal 11pt Tahoma;\"> &#8594 </font>\n");
    print("addr: <input type=\"text\" name=\"srcpath\" id=\"srcpath\" value=\"$srcpath\" size=20".(( !$_src) ? " disabled" : "").">\n");
    print("</td><td>\n");
    print("<input type=\"checkbox\" id=\"dstinv\" value=\"1\" title=\"Инверсия (отрицание)\" name=\"dstinv\"".(($dstinv=="!") ? " CHECKED" : "")."".(( !$_src) ? " disabled" : "")."><label for=\"dstinv\"><b>\"!\"</b></label> <font style=\"FONT: normal 11pt Tahoma;\"> &#8594 </font>\n");
    print("addr: <input type=\"text\" name=\"dstpath\" id=\"dstpath\" value=\"$dstpath\" size=20".(( !$_dst) ? " disabled" : "").">\n");
    print("</td></tr></table>\n</td></tr>\n<tr>\n<td align=right>\n");
    print("<input type=\"button\" id=\"sbmt11\" name=\"sbmt\" onClick=\"javascript: document.getElementById('form21').submit(); \" value=\"&nbsp&nbsp OK &nbsp&nbsp\">\n</form> \n");
    print("</td></tr> \n</table>\n");
    print("<br><br><a href=\"ports.php\" title=\"Не сохранять изменения\"><img src=\"icons/gtk-undo.gif\" title=\"Не сохранять изменения\"></a>");

}

#-----------------------------------------------------------------

$_port=( isset($_GET["p"])) ? $_GET["p"] : "";
$_src=( isset($_GET["src"])) ? TRUE : FALSE;
$_dst=( isset($_GET["dst"])) ? TRUE : FALSE;
$_pport=( isset($_GET["pport"])) ? $_GET["pport"] : "";
$_pproto=( isset($_GET["pproto"])) ? $_GET["pproto"] : "";
$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : "";
$_nolist=( isset($_GET["nolst"])) ? $_GET["nolst"] : "";
$_run=( isset($_GET["run"])) ? $_GET["run"] : "";
$srcpath=( isset($_GET["srcpath"])) ? $_GET["srcpath"] : "0.0.0.0/0";
$dstpath=( isset($_GET["dstpath"])) ? $_GET["dstpath"] : "0.0.0.0/0";
$direct=( isset($_GET["direct"])) ? $_GET["direct"] : "both";
$srcinv=( isset($_GET["srcinv"])) ? "!" : "";
$dstinv=( isset($_GET["dstinv"])) ? "!" : "";
$_pf=( isset($_GET["pf"])) ? $_GET["pf"] : "";


if( $_mode=="add") {
    if( trim($_run)=="") {
	print("<br><br>\n<div style=\"padding-left:80px\">\n");
	show_add_form();
	print("</div>\n");
	exit;
    } else {
	if( trim($_pproto)=="") { print("Error adding port - proto is empty"); exit; }
        if( !file_exists("$iptconf_dir/ports")) { print("Error adding port - file ports is not found"); exit; }
        $_buf1=$_pproto.":".$_pport." direction:".(( trim($direct)!="") ? trim($direct) : "both").(( $_src) ? " src:$srcinv$srcpath" : "").(( $_dst) ? " dst:$dstinv$dstpath" : "");
        if( substr_count(file_get_contents("$iptconf_dir/ports"),$_buf1)==0) {
    	    $pfile=fopen("$iptconf_dir/ports","a");
    	    if( !fwrite($pfile,"$_buf1\n")) { print("Error writing to ports file!"); exit; }
    	    fclose($pfile);
    	} else {
    	    print("Такой порт уже есть в списке...<br>\n");
    	}
    	wlog("Добавление строки в ports: $_buf1",0,FALSE,1,FALSE);
    }

} elseif( $_mode=="edit") {
    if( trim($_run)=="") {
	if( trim($_port)!="") {
	    print("<br><br>\n<div style=\"padding-left:80px\">\n");
	    $aports=load_ports_list();
	    $_src=FALSE; $srcpath="0.0.0.0/0";
	    $_dst=FALSE; $dstpath="0.0.0.0/0";
	    $direct="both";
	    
	    $nncc=coltoks($aports[$_port]," \t");
	    for($nn=1; $nn<=$nncc; $nn++) {
		if( trim($buf0=gettok($aports[$_port],$nn," \t"))!="") {
		    $buf0_1=gettok($buf0,1,":");
		    if( $buf0_1=="src") {
			$_src=TRUE; $srcpath=gettok($buf0,2,":"); 
			$srcinv=( $srcpath[0]=="!") ? "!" : "";
			$srcpath=( $srcpath[0]=="!") ? substr($srcpath,1) : $srcpath;
		    } elseif( $buf0_1=="dst") {
			$_dst=TRUE; $dstpath=gettok($buf0,2,":");
			$dstinv=( $dstpath[0]=="!") ? "!" : "";
			$dstpath=( $dstpath[0]=="!") ? substr($dstpath,1) : $dstpath;
		    } elseif( $buf0_1=="direction") {
			$direct=gettok($buf0,2,":");
		    }
		}
	    }
	    $_pproto=gettok($_port,1,":");
	    $_pport=gettok($_port,2,":");
	    show_add_form();
	    print("</div>\n");
	    exit;
	}
    } else {
	if( trim($_pproto)=="") { print("Error adding port - pproto is empty"); exit; }
	if( trim($_port)=="") { print("Error adding port - port is empty"); exit; }
        if( !file_exists("$iptconf_dir/ports")) { print("Error adding port - file ports is not found"); exit; }
        $_buf1=$_pproto.":".$_pport;
        $_buf1=$_buf1.((trim($direct)!="") ? " direction:".trim($direct) : "");
        $_buf1=($_src) ? $_buf1." src:$srcinv$srcpath" : $_buf1;
        $_buf1=($_dst) ? $_buf1." dst:$dstinv$dstpath" : $_buf1;
        if( (substr_count(file_get_contents("$iptconf_dir/ports"),$_port)==0) and (substr_count(file_get_contents("$iptconf_dir/ports"),$_buf1)==0)) {
    	    $pfile=fopen("$iptconf_dir/ports","a");
    	    if( !fwrite($pfile,"$_buf1\n")) { wlog("Error writing to ports file!",2,TRUE,5,FALSE); exit; }
    	    fclose($pfile);
    	} else {
    	    $tmpfile=tempnam("$iptconf_dir","ports");
    	    $fltmp=fopen($tmpfile,"w");
    	    if( !$fltmp) {
    		wlog("Ошибка создания временного файла $tmpfile в ports.php..",2,TRUE,5,FALSE); exit;
    	    }
    	    $pfile=fopen("$iptconf_dir/ports","r");
    	    if( !$pfile) {
    		wlog("Ошибка создания временного файла $iptconf_dir/ports в ports.php..",2,TRUE,5,FALSE); exit;
    	    }
    	    while( !feof($pfile)) {
    		$string=trim(fgets($pfile));
    		if( $string=="") continue;
    		$line=( gettok($string,1," \t")!="$_port") ? "$string\n" : "$_buf1\n";
    		if( !fwrite($fltmp,$line)) {
    		    wlog("Ошибка записи данных во временный файл $tmpfile...",2,TRUE,5,FALSE); exit;
    		}
    	    }
    	    fclose($fltmp);
    	    fclose($pfile);
    	    if( file_exists("$iptconf_dir/ports.bak")) unlink("$iptconf_dir/ports.bak");
    	    rename("$iptconf_dir/ports","$iptconf_dir/ports.bak");
    	    rename($tmpfile,"$iptconf_dir/ports");
    	    
    	}
    	wlog("Редактирование строки ports: $_buf1",0,FALSE,1,FALSE);
    }
    
} elseif( $_mode=="del") {

    if( trim($_pport)=="") { wlog("Error adding port - port is empty",2,TRUE,5,TRUE); exit; }
    if( !file_exists("$iptconf_dir/ports")) { wlog("Error adding port - file ports is not found",2,TRUE,5,TRUE); exit; }
    $pfile=file("$iptconf_dir/ports");
    if( file_exists("$iptconf_dir/ports.bak")) unlink("$iptconf_dir/ports.bak");
    rename("$iptconf_dir/ports","$iptconf_dir/ports.bak");
    $pfileout=fopen("$iptconf_dir/ports","a+");
    foreach( $pfile as $kk2 => $vv2) {
	if( trim(gettok($pfile[$kk2],1," \t"))!=$_pport) fwrite($pfileout,$pfile[$kk2]);
    }
    fclose($pfileout);
    wlog("Удаление строки ports $_pport",0,FALSE,1,FALSE);
}

if( trim($_pf)!="") {
    if( trim($_pf)=="disable") $_pf="-D";
    if( trim($_pf)=="enable") $_pf="-A";
    $line="$_iptables -t mangle $_pf PORTFILTER -j DROP";
    _exec2($line);

}

if( $_nolist=="") {


    print("<br>\n");
    print("<font class=top1>Порты, фильтрация:</font><br><br>\n");
    if( $_portfilter_enable) {
	$flpf=pf_getstatus();
	print("<div style=\"float:left\">Состояние: <font style=\"color:".(( pf_getstatus()) ? "teal;\">ВКЛЮЧЕН" : "maroon;\">ВЫКЛЮЧЕН")."</font><br>\n");
	print("<a href=\"ports.php?pf=".(( $flpf) ? "disable" : "enable")."\" style=\"color:".(( $flpf) ? "maroon" : "teal").";\" title=\"".(( $flpf) ? "Выключить" : "Включить")."\">".(( $flpf) ? "Выключить" : "Включить")."</a></div>\n");
    } else {
	print("<div style=\"float:left;width:180px;\">Состояние: <br>Отключен в конфигурации</font></div>\n");
    }
    print("<div style=\"position:relative;left:350px\">\n");
    print("<table class=table5d><tr><td style=\"padding-left:10px;padding-right:10px\"><a href=\"ports.php?mode=add\" title=\"Добавить порт\"><img src=\"icons/list-new.gif\" title=\"Добавить порт\"></a></td></tr></table></div>\n<br> \n");

    print("<table id=\"table_t\" class=table1 cellpadding=\"3px\" width=\"610px\"> \n");
    print("<tr><th class=thp> proto </th><th class=thp> port </th><th class=thp> direction </th><th class=thp> source </th><th class=thp> destination </th></tr>\n");
    
    $aap=load_ports_list();
    if( !$aap) { wlog("Error loading ports ... \n",2,TRUE,5,TRUE); exit; }
    $istr=1;
    foreach( $aap as $kk1 => $vv1) {
	    print("<tr><td> ".gettok($kk1,1,":")." </td><td>".gettok($kk1,2,":")."</td>\n");
	    if( trim($vv1)!="") {
		$clt=coltoks($vv1," \t");
		for($n=1;$n<=$clt;$n++) {
		    if( trim($buf0=gettok($vv1,$n," \t"))=="") continue;
		    if( gettok($buf0,1,":")=="src") {
			$bufopt1=gettok($buf0,2,":");
		    } elseif( gettok($buf0,1,":")=="dst") {
			$bufopt2=gettok($buf0,2,":");
		    } elseif( gettok($buf0,1,":")=="direction") {
			$bufdirect=gettok($buf0,2,":");
			$bufdirect=( $bufdirect=="sport") ? "Исходный порт (sport)" : $bufdirect;
			$bufdirect=( $bufdirect=="dport") ? "Порт назначения (dport)" : $bufdirect;
			$bufdirect=( $bufdirect=="both") ? "В обе стороны (both)" : $bufdirect;
		    }
		}
		$pline=" $bufdirect </td><td align=middle> $bufopt1 </td><td align=middle> $bufopt2 ";
	    } else {
		$pline=" </td><td> &nbsp </td><td> ";
		$bufopt1="";
		$bufopt2="";
	    }
	    print("<td> $pline </td>\n");
	    print("<td width=\"100px\" align=center>&nbsp <a href=\"ports.php?p=$kk1&mode=edit\" title=\"Редактировать\"><img src=\"icons/gtk-edit.gif\" title=\"Редактировать\"></a> &nbsp ");
	    print("<a href=\"ports.php?mode=del&pport=$kk1\" title=\"Удалить\"><img src=\"icons/edittrash.gif\" title=\"Удалить\"></a>  </td></tr>\n");    $istr++;
    }
    
    print("</table>\n");
    print("<hr width=\"300px\" align=left><br><br>\n");
    wlog("Просмотр списка портов",0,FALSE,1,FALSE);
    print("<br><br>\n</body></html>  \n");


}

?>