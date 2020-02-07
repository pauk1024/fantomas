<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: pb.php
# Description: 
# Version: 0.2.4.6
###



require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

if( isset($_extifs)) unset($_extifs);
if( isset($_localifs)) unset($_localifs);
$_extifs=array(); $_localifs=array();
load_ifs(2);

$_policy=( isset($_GET["p"])) ? $_GET["p"] : (( isset($_POST["p"])) ? $_POST["p"] : "");
$_pconfig=stripslashes(( isset($_GET["pconf"])) ? $_GET["pconf"] : (( isset($_POST["pconf"])) ? $_POST["pconf"] : ""));
$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"] : "");
$_way=( isset($_GET["way"])) ? $_GET["way"] : (( isset($_POST["way"])) ? $_POST["way"] : "");
$_f=( isset($_GET["f"])) ? $_GET["f"] : (( isset($_POST["f"])) ? $_POST["f"] : $pollist_fmode_default);
$_refmode=( isset($_GET["refmode"])) ? $_GET["refmode"] : (( isset($_POST["refode"])) ? $_POST["refmode"] : "");
$_tmpmode=( isset($_GET["tmpmode"])) ? $_GET["tmpmode"] : (( isset($_POST["tmpmode"])) ? $_POST["tmpmode"] : "");
$_edit=( isset($_GET["edit"])) ? $_GET["edit"] : (( isset($_POST["edit"])) ? $_POST["edit"] : "");
$_index=( isset($_GET["index"])) ? $_GET["index"] : (( isset($_POST["index"])) ? $_POST["index"] : "");
$show=( isset($_GET["s"])) ? TRUE:(( isset($_POST["s"])) ? TRUE:FALSE);

if( trim($_mode)=="") { $_mode=( trim($_edit)=="1") ? "edit" : $_mode; }
if( trim($_edit)=="") { $_edit=( trim($_mode)=="edit") ? "1" : $_edit; }

if( isset($aa1)) unset($aa1);
$aa1=array();

$ptarget=( isset($_GET["ptarget"])) ? $_GET["ptarget"] : (( isset($_POST["ptarget"])) ? $_POST["ptarget"] : "");
$pproto=( isset($_GET["pproto"])) ? $_GET["pproto"] : (( isset($_POST["pproto"])) ? $_POST["pproto"] : "");
$pinv=( isset($_GET["pinv"])) ? TRUE : (( isset($_POST["pinv"])) ? TRUE : FALSE);
$pports=( isset($_GET["pports"])) ? $_GET["pports"] : (( isset($_POST["pports"])) ? $_POST["pports"] : "");
$pdst=( isset($_GET["pdst"])) ? $_GET["pdst"] : (( isset($_POST["pdst"])) ? $_POST["pdst"] : "");
$pquota=( isset($_GET["pquota"])) ? $_GET["pquota"] : (( isset($_POST["pquota"])) ? $_POST["pquota"] : "");
$ptype_direct=( isset($_GET["ptype_direct"])) ? $_GET["ptype_direct"] : (( isset($_POST["ptype_direct"])) ? $_POST["ptype_direct"] : "");
$paddr_direct=( isset($_GET["paddr_direct"])) ? $_GET["paddr_direct"] : (( isset($_POST["paddr_direct"])) ? $_POST["paddr_direct"] : "");

$subparam=( isset($_GET["subparam"])) ? $_GET["subparam"] : (( isset($_POST["subparam"])) ? $_POST["subparam"] : "");
$sfact=( isset($_GET["sfact"])) ? $_GET["sfact"] : (( isset($_POST["sfact"])) ? $_POST["sfact"] : "");
$sfactdst=( isset($_GET["sfactdst"])) ? $_GET["sfactdst"] : (( isset($_POST["sfactdst"])) ? $_POST["sfactdst"] : "");
$sfinout=( isset($_GET["sfinout"])) ? $_GET["sfinout"] : (( isset($_POST["sfinout"])) ? $_POST["sfinout"] : "");
$sfinoutdst=( isset($_GET["sfinoutdst"])) ? $_GET["sfinoutdst"] : (( isset($_POST["sfinoutdst"])) ? $_POST["sfinoutdst"] : "");
$sfmark=( isset($_GET["sfmark"])) ? $_GET["sfmark"] : (( isset($_POST["sfmark"])) ? $_POST["sfmark"] : "");
$sfcount=( isset($_GET["sfcount"])) ? $_GET["sfcount"] : (( isset($_POST["sfcount"])) ? $_POST["sfcount"] : "");
$sfcount_nolog=( isset($_GET["sfcount_nolog"])) ? $_GET["sfcount_nolog"] : (( isset($_POST["sfcount_nolog"])) ? $_POST["sfcount_nolog"] : "");
$sfipsetmode=( isset($_GET["sfipsetmode"])) ? $_GET["sfipsetmode"] : (( isset($_POST["sfipsetmode"])) ? $_POST["sfipsetmode"] : "");
$sfipsetname=( isset($_GET["sfipsetname"])) ? $_GET["sfipsetname"] : (( isset($_POST["sfipsetname"])) ? $_POST["sfipsetname"] : "");
$sftitle=( isset($_GET["sftitle"])) ? $_GET["sftitle"] : (( isset($_POST["sftitle"])) ? $_POST["sftitle"] : "");

$_sskey=( isset($_GET["sskey"])) ? $_GET["sskey"] : (( isset($_POST["sskey"])) ? $_POST["sskey"] : "");

$subitemname=( isset($_GET["subitemname"])) ? $_GET["subitemname"] : (( isset($_POST["subitemname"])) ? $_POST["subitemname"] : "");

$_pstr=( isset($_GET["pstr"])) ? $_GET["pstr"] : (( isset($_POST["pstr"])) ? $_POST["pstr"] : "");
$_used=( isset($_GET["used"])) ? $_GET["used"] : (( isset($_POST["used"])) ? $_POST["used"] : "");
$_direction=( isset($_GET["d"])) ? $_GET["d"] : (( isset($_POST["d"])) ? $_POST["d"] : "");




#---------------------------------------------------------------------

function sess_line_op($fmode=1)
{
    global $_SESSION,$sess_policy,$_index,$_mode,$_policy,$_policy0;
    global $ptarget,$pproto,$pinv,$pports,$pdst,$pquota,$ptype_direct,$paddr_direct;
    
    if( $_mode=="addsaverule") {
	$aacpt=array(); $lastpos=0; $buftitle="";
	$aothers=array();
	foreach($_SESSION[$sess_policy] as $aa1key => $aa1val) {
	    $_w1=trim(gettok($aa1val,1," \t"));
	    if(( $_w1=="accept") or ( $_w1=="reject")) {
		$lastpos=$aa1key;
		$aacpt[count($aacpt)+1]=$aa1val;
	    } elseif( $_w1=="title") {
		$buftitle=$aa1val; 
	    } else {
		$aothers[count($aothers)+1]=$aa1val;
	    }
	}
	$_index="".$lastpos;
    }
    $line="";
    $line=( trim($ptarget)!="") ? trim($ptarget) : "accept";
    $line=$line.(( trim($pproto)!="") ? " ".trim($pproto) : "");
    if( trim($pports)!="") {
	$bufports=(( $pinv) ? "!" : "").trim($pports);
	$bufports=( coltoks($pports,",")>1) ? "(".$bufports.")" : "$bufports";
	$line=$line." ".$bufports;
    }
    if( trim($pdst)!="") {
	$line=$line." ".trim($pdst);
    }
    $line=$line.(( trim($pquota)!="" ) ? " quota ".$pquota : "");
    if( trim($paddr_direct)!="") {
	$line=$line." ".$ptype_direct." ".$paddr_direct;
    }

    $str=""; $bufindex=1;
    if(( $fmode==1) or ( $fmode==3)) {
	foreach($_SESSION[$sess_policy] as $aa1key => $aa1val) {
	    $_w1=trim(gettok($aa1val,1," \t"));
	    if(( $_w1=="accept") or ( $_w1=="reject")) {
		if( $_index==trim("$bufindex")) {
		    if( $fmode==1) {

			return($aa1val);
			break;
		    } elseif( $fmode==3) {
			unset($_SESSION[$sess_policy][$aa1key]);
			break;
		    }
		} else { 
		    $bufindex++; 
		}
	    }
	}
    } elseif( $fmode==2) {
	if( $_mode=="addsaverule") {
	    foreach($_SESSION[$sess_policy] as $aa1key => $aa1val) { unset($_SESSION[$sess_policy][$aa1key]); }
	    $_SESSION[$sess_policy]["$_policy0"]="$buftitle"; 
	    $iicpt=1;
	    foreach($aacpt as $aacptkey => $aacptval) {
		$_SESSION[$sess_policy]["$_policy$iicpt"]="$aacptval"; $iicpt++;
	    }
	    $_SESSION[$sess_policy]["$_policy$iicpt"]="$line"; $iicpt++;
	    foreach($aothers as $aaothkey => $aaothval) {
		$_SESSION[$sess_policy]["$_policy$iicpt"]="$aaothval"; $iicpt++;
	    }
	} elseif( $_mode=="editsaverule") {
	  foreach($_SESSION[$sess_policy] as $aa1key => $aa1val) {
	    $_w1=trim(gettok($aa1val,1," \t"));
	    if(( $_w1=="accept") or ( $_w1=="reject")) {
		if( $_index==trim("$bufindex")) {
		    $_SESSION[$sess_policy][$aa1key]=$line;
		    break;
		} else { 
		    $bufindex++; 
		}
	    }
	  }
	}
    }

}
#---------------------------------------------------------------------
function pb_del_policy($policy)
{
    global $_mode,$iptconf_dir;
    if( $_mode!="del") return("");
    if( trim($policy)=="") { print("Не указана политика для удаления"); exit; }
    if( (!file_exists($iptconf_dir."/policies")) or (!is_readable($iptconf_dir."/policies"))) {
	wlog("Error accessing policies file in pb_save_policy()..",2,TRUE,5,TRUE);
	exit;
    }
    $ptmpfile=tempnam($iptconf_dir,"policies");
    $pfile=fopen($iptconf_dir."/policies","r");
    $pnewfile=fopen($ptmpfile,"a");
    $open=FALSE;
    $strnum=0;
    while( !feof($pfile)) {
	$pstr=_trimline(strtolower(fgets($pfile))); $strnum++;
	if( trim($pstr)=="") continue;
	$buf1=gettok($pstr,1," \t");
	if( $buf1=="policy") {
	    $buf2=gettok($pstr,2," \t");
	    if( $open) print("Statement error in policies file at line $strnum - policy $buf2 defining when previous policy is not closed!");
	    if( $buf2==$policy) {
		$open=TRUE;
		continue;
	    }
	    fwrite($pnewfile,"$pstr\n");
	} elseif($buf1=="}") {
	    if(!$open) fwrite($pnewfile,"$pstr\n\n");
	    $open=FALSE;
	    continue;
	} else {
	    if( !$open) fwrite($pnewfile,$pstr."\n");
	}
    }
    fclose($pnewfile);
    if( file_exists($iptconf_dir."/policies.bak")) unlink($iptconf_dir."/policies.bak");
    rename($iptconf_dir."/policies",$iptconf_dir."/policies.bak");
    rename($ptmpfile,$iptconf_dir."/policies");

}
#---------------------------------------------------------------------
function pb_save_policy($policy,$flman=FALSE)
{
    global $iptconf_dir,$_SESSION,$sess_policy,$_pconfig,$_mode;
    if( (!file_exists($iptconf_dir."/policies")) or (!is_readable($iptconf_dir."/policies"))) {
	wlog("Error accessing policies file in l_save_policy()..",2,TRUE,5,TRUE);
	exit;
    }
    if( !$flman) {
	if( !isset($_SESSION["temp_pmode_$policy"])) {
	    wlog("Не удалось получить режим обработки текущей политики в pb_save_policy()..",2,TRUE,5,TRUE); exit;
	} else {
	    $pmode=$_SESSION["temp_pmode_$policy"];
	}
	if( $pmode=="new") {
	    $aapols=load_policies_list();
	    if( array_key_exists($policy,$aapols)) {
		wlog("Политика с идентификатором $policy уже существует!",2,TRUE,5,TRUE); exit; 
	    }
	    unset($aapols);
	}
    } else {
	$pmode=( trim($_mode)=="meditsave") ? "edit" : "";
	$pmode=( trim($_mode)=="mnewsave") ? "new" : $pmode;
    }	
    $ptmpfile=tempnam($iptconf_dir,"policies");
    $pfile=fopen($iptconf_dir."/policies","r");
    $pnewfile=fopen($ptmpfile,"a");
    if( $pmode=="edit") {
	$open=FALSE;
	$strnum=0;
	while( !feof($pfile)) {
	    $pstr=_trimline(strtolower(fgets($pfile))); $strnum++;
	    if( trim($pstr)=="") continue;
	    $buf1=gettok($pstr,1," \t");
	    if( $buf1=="policy") {
		$buf2=gettok($pstr,2," \t");
		if( $open) print("Statement error in policies file at line $strnum - policy $buf2 defining when previous policy is not closed!");
		if( $buf2==$policy) {
		    $open=TRUE;
		    fwrite($pnewfile,"policy $policy {\n");
		    if( !$flman) {
			foreach($_SESSION[$sess_policy] as $aa1key => $aa1value) {
			    fwrite($pnewfile,trim($aa1value)."\n");
			}
		    } else {
			foreach( explode("\n",$_pconfig) as $pvalue) if( trim($pvalue)!="") fwrite($pnewfile,trim($pvalue)."\n");
		    }
		    fwrite($pnewfile,"}\n\n");
		    continue;
		}
		fwrite($pnewfile,"$pstr\n");
	    } elseif($buf1=="}") {
		if(!$open) fwrite($pnewfile,"$pstr\n\n");
		$open=FALSE;
		continue;
	    } else {
		if( !$open) fwrite($pnewfile,$pstr."\n");
	    }
	}
	fclose($pnewfile);
	if( file_exists($iptconf_dir."/policies.bak")) unlink($iptconf_dir."/policies.bak");
	rename($iptconf_dir."/policies",$iptconf_dir."/policies.bak");
	rename($ptmpfile,$iptconf_dir."/policies");
	
    } elseif( $pmode=="new") {
    
	while( !feof($pfile)) {
	    $pstr=fgets($pfile);
	    if( gettok(_trimline(strtolower($pstr)),1," \t")=="policy") {
		$bufpname=gettok(_trimline(strtolower($pstr)),2," \t");
		if( $bufpname==$policy) {
		    print("Такая политика уже существует!<br>");
		    exit;
		}
	    }
	    fwrite($pnewfile,"$pstr");
	}
	fclose($pfile);
	fwrite($pnewfile,"\n\n");
	fwrite($pnewfile,"policy $policy {\n");
	if( !$flman) {
	    foreach($_SESSION[$sess_policy] as $pkey => $pvv) if( $pvv!="") fwrite($pnewfile,$pvv."\n");
	} else {
	    foreach( explode("\n",$_pconfig) as $pvalue) if( trim($pvalue)!="") fwrite($pnewfile,trim($pvalue)."\n");
	}
	fwrite($pnewfile,"}\n\n");
	fclose($pnewfile);
	if( file_exists($iptconf_dir."/policies.bak")) unlink($iptconf_dir."/policies.bak");
	rename($iptconf_dir."/policies",$iptconf_dir."/policies.bak");
	rename($ptmpfile,$iptconf_dir."/policies");

    }
    if( !$flman) {
	if( isset($_SESSION["temp_pmode_$policy"])) unset($_SESSION["temp_pmode_$policy"]);
	if( isset( $_SESSION[$sess_policy])) unset($_SESSION[$sess_policy]);
	if( in_array($policy,$_SESSION["temp_pollist"])) unset($_SESSION["temp_pollist"][array_search($policy,$_SESSION["temp_pollist"])]);
    }
    wlog("Сохранение политики $policy, режимы: pmode=$pmode, mode=$_mode",0,FALSE,2,FALSE);

}

#---------------------------------------------------------------------


function show_rule_form()
{
    global $_mode,$_index,$_policy,$_SESSION,$sess_policy,$_refmode;
    
    if( $_mode=="editrule") {
	if( trim($_index)=="") { print("Не указан индекс правила для редактирования!"); exit; }
	$str=sess_line_op(1);
	if( trim($str)=="") { print("Строка по индексу $_index не найдена"); exit; }
    }
    print("<script type=\"text/javascript\">\n");
    print("function submit1() \n");
    print("{ \n");
    print("	document.getElementById('mode').value='editcanclrule';\n");
    print("	document.getElementById('editrule1').submit();\n");
    print("}\n </script>\n");
    print("<br><br>\n <font class=top1> Редактирование правила</font> <br><br>\n");
    print("<table class=table1 width=\"auto\" cellpadding=\"6px\"> \n");
    print("<form id=\"editrule1\" name=\"editrule1\">\n");
    if( $_mode=="editrule") {
	print("<input type=\"HIDDEN\" name=\"mode\" id=\"mode\" value=\"editsaverule\">\n");
	print("<input type=\"HIDDEN\" name=\"index\" value=\"$_index\">\n");
    } elseif( $_mode=="addrule") {
	print("<input type=\"HIDDEN\" name=\"mode\" id=\"mode\" value=\"addsaverule\">\n");
	print("<input type=\"HIDDEN\" name=\"index\" value=\"0\">\n");
    }
    print("<input type=\"HIDDEN\" name=\"refmode\" id=\"refmode\" value=\"$_refmode\">\n");
    print("<input type=\"HIDDEN\" name=\"edit\" id=\"edit\" value=\"1\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"$_policy\">\n");
    
    $bufact="accept"; $bufproto="tcp"; $bufinv=FALSE; $bufports="";
    $bufdst=""; $bufquota=""; $buf_type_direct=""; $buf_addr_direct="";
    
    if( $_mode=="editrule") {
    
	$aacval=_trimline(trim($str));
	$aact=coltoks($aacval," \t");
	$bufact=gettok($aacval,1," \t");
	$bufproto=( $aact>1) ? gettok($aacval,2," \t") : "";
	if( $aact>2) {
	    for($iict=3;$iict<=$aact;$iict++) {
		$bufword=trim(gettok($aacval,$iict," \t"));
		if( $bufword=="src") {
		    $bufdst=$bufword;
		} elseif( $bufword=="dst") {
		    $bufdst=$bufword;
		} elseif( $bufword=="quota") { 
		    $iict++; $bufquota=trim(gettok($aacval,$iict," \t")); 
		} elseif(( $bufword=="to") or ( $bufword=="from")) {
		    $buf_type_direct=$bufword;
		    $iict++; 
		    $buf_addr_direct=trim(gettok($aacval,$iict," \t"));
		} else {
		    $bufports=( $bufword[0]=="(") ? substr($bufword,1) : $bufword;
		    $bufports=( $bufports[strlen($bufports)-1]==")") ? substr($bufports,0,-1) : $bufports;
		    $bufinv=( $bufports[0]=="!") ? TRUE : FALSE;
		    $bufports=( $bufports[0]=="!") ? substr($bufports,1) : $bufports;
		
		}
	    }
	}
    }
    print("<tr><td> Режим: </td><td> <span class=seldiv> <SELECT name=\"ptarget\"> \n");
    print("<option value=\"accept\" ".(($bufact=="accept") ? "SELECTED" : "").">Accept</option>\n");
    print("<option value=\"reject\" ".(($bufact=="reject") ? "SELECTED" : "").">Reject</option>\n");
    print("</SELECT><span>\n </td></tr>\n");
    print("<tr><td> Трафик </td><td> <span class=seldiv> <SELECT name=\"pproto\"> \n");
    $aapr=array("all","tcp","udp","icmp","esp","ah","sctp");
    foreach($aapr as $aaprkey => $aaprval) {
	print("<option value=\"$aaprval\" ".(($bufproto==$aaprval) ? "SELECTED" : "").">$aaprval</option>\n");
    }
    print("</SELECT></span> \n");
    print("<input type=\"CHECKBOX\" name=\"pinv\" id=\"pinv\" value=\"1\" ".(($bufinv) ? "CHECKED" : "")."><label for=\"pinv\"> <font style=\"FONT: 11pt Tahoma\"><b>\"!\"</b> &#8594 </font> </label> \n");
    print("<input type=\"TEXT\" name=\"pports\" size=\"40\" value=\"$bufports\"> </td></tr>\n ");
    print("<tr><td> Направление </td><td><span class=seldiv> <SELECT name=\"pdst\"> \n");
    print("<option value=\"\" ".(($bufdst=="") ? "SELECTED" : "").">---</option>\n");
    print("<option value=\"dst\" ".(($bufdst=="dst") ? "SELECTED" : "").">dst</option>\n");
    print("<option value=\"src\" ".(($bufdst=="src") ? "SELECTED" : "").">src</option>\n");
    print("</SELECT></span>\n </td></tr>\n");
    print("<tr><td> Квота </td><td> <input type=\"TEXT\" name=\"pquota\" size=\"40\" value=\"$bufquota\"> </td></tr> \n");
    print("<tr><td> Адресат </td><td><span class=seldiv> <SELECT name=\"ptype_direct\"> \n");
    print("<option value=\"\" ".((trim($buf_type_direct)=="") ? "SELECTED" : "").">---</option>\n");
    print("<option value=\"to\" ".(($buf_type_direct=="to") ? "SELECTED" : "").">to</option>\n");
    print("<option value=\"from\" ".(($buf_type_direct=="from") ? "SELECTED" : "").">from</option>\n");
    print("</SELECT></span> &nbsp <input type=\"TEXT\" name=\"paddr_direct\" size=\"50\" value=\"$buf_addr_direct\">");
    print("</td></tr><tr><td colspan=2 align=right> <input type=\"BUTTON\" name=\"sbmt1\" value=\"Отмена\" OnClick=\"javascript: document.getElementById('mode').value='editcanclrule'; document.getElementById('editrule1').submit(); \">  <input type=\"SUBMIT\" name=\"sbmt\" value=\" Ok \"> </td></tr>\n");
    print("</form>\n </table>\n");
    



}
#---------------------------------------------------------------------
function show_policies_list($apols,$fmode=0)
{
    print("<br>\n<table class=notable><tr><td class=tdpadd> <a href=\"pb.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"pb.php\" title=\"Назад\">Назад</a> </td>\n");
    print("<td class=tdpadd> <a href=\"pb.php?mode=new\" title=\"Создать новую политику\"><img src=\"icons/list-new.gif\" title=\"Создать новую политику\"></a> </td><td>  <a href=\"pb.php?mode=new\" title=\"Создать новую политику\">Новая политика</a> </td>\n");
    print("</tr></table>\n");
    print("<br><br>\n<font class=top1> Политики: </font><br><br>\n");
    print("<table class=table1 width=\"auto\" cellpadding=\"6px\"> \n");
    if( $fmode==1) {
	print("<tr><th> Идентификатор </th><th> Детали </th><th> Действия </th></tr>\n ");
    } elseif( $fmode==0) {
	print("<tr><th> Идентификатор </th><th> Описание </th><th> <img src=\"icons/stock_people.gif\" title=\"Испольование\"> </th><th> Действия </th></tr>\n ");
    }
    foreach($apols as $apolicy => $apoltitle) {
	if( isset($ausers)) unset($ausers);
	$ausers=get_policy_users($apolicy);
	print("<tr><td> \n");
	if( $fmode==1) {
	    if( isset($aapol0)) unset($aapol0);
	    $aapol0=policy2array($apolicy,TRUE,TRUE);
	    foreach($aapol0 as $key0 => $val0) {
		$bufw="";
		if(!$bufw=trim(gettok($val0,1," \t"))) continue;
		if(( $bufw=="in") or ( $bufw=="out")) {
		    $bufio=$val0; break 1;
		}
	    }

	    print("<font style=\"FONT: bold 12pt Tahoma;\"> $apolicy </font> </td><td> \n");
	    print("<table class=table5 cellpadding=\"1px;\" style=\"border-collapse:collapse; padding-left:30px;\">\n");
	    print("<tr><td class=desci>Описание: </td><td class=tddesk33> \"$apoltitle\"</td></tr>\n");
	    print("<tr><td class=desci>Используют: </td><td class=tddesk33>".count($ausers)." <a href=\"users.php?pol=$apolicy&edit=1&mode=showpolicyusers\" title=\"Показать список клиентов, использующих политику\"> <img src=\"icons/stock_people.gif\" title=\"Показать список клиентов, использующих политику\"></a> </td></tr>\n");
	    print("<tr><td class=desci>Направленность: </td><td class=tddesk33> $bufio</td></tr>\n");
	    print("</table>\n ");
	    print("</td><td> \n");
	    print(" <a href=\"pb.php?mode=edit&p=$apolicy&refmode=pollist&f=$fmode\" title=\"Открыть в конструкторе политик\"><img src=\"icons/pb22.gif\" title=\"Открыть в конструкторе политик\"></a> &nbsp ");
	    print(" <a href=\"pb.php?mode=medit&p=$apolicy&refmode=pollist&f=$fmode\" title=\"Ручное редактирование\"><img src=\"icons/gtk-edit.gif\" title=\"Ручное редактирование\"></a> &nbsp ");
	    print(" <a href=\"pb.php?mode=view&p=$apolicy&edit=1&refmode=pollist&f=$fmode\" title=\"Просмотр конфига политики (без редактирования)\"><img src=\"icons/eog.gif\" title=\"Просмотр конфига политики (без редактирования)\"></a> &nbsp ");
	    if( count($ausers)==0) { 
		print("<a href=\"pb.php?mode=del&p=$apolicy&f=$fmode\" title=\"Удалить политику\"><img src=\"icons/edittrash.gif\" title=\"Удалить политику\"></a>  </td></tr>\n");
	    } else {
		print("<img src=\"icons/edittrash.gif\" title=\"Нельзя удалять используемую политику\">  </td></tr>\n");
	    }
	} elseif( $fmode==0) {
	    print("<font style=\"FONT: bold 10pt Arial;\"> $apolicy </font> </td><td> $apoltitle </td><td> ".count($ausers)." </td><td>\n");
	    print(" <a href=\"pb.php?mode=edit&p=$apolicy&refmode=pollist&f=$fmode\" title=\"Открыть в конструкторе\"><img src=\"icons/pb16.gif\" title=\"Открыть в конструкторе\"></a> \n");
	    print(" <a href=\"pb.php?mode=medit&p=$apolicy&refmode=pollist&f=$fmode\" title=\"Ручное редактирование\"><img src=\"icons/gtk-edit16.gif\" title=\"Ручное редактирование\"></a> \n");
	    print(" <a href=\"pb.php?mode=view&p=$apolicy&edit=1&refmode=pollist&f=$fmode\" title=\"Просмотр конфига политики (без редактирования)\"><img src=\"icons/eog16.gif\" title=\"Просмотр конфига политики (без редактирования)\"></a> \n");
	    if( count($ausers)==0) {
		print("<a href=\"pb.php?mode=del&p=$apolicy&f=$fmode\" title=\"Удалить политику\"><img src=\"icons/cancel16.gif\" title=\"Удалить политику\"></a>\n");
	    }
	}
    }
    print("</table>\n<br><br>");

    print("<br>\n<table class=notable><tr><td class=tdpadd> <a href=\"pb.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"pb.php\" title=\"Назад\">Назад</a> </td>\n");
    print("<td class=tdpadd> <a href=\"pb.php?mode=new\" title=\"Создать новую политику\"><img src=\"icons/list-new.gif\" title=\"Создать новую политику\"></a> </td><td>  <a href=\"pb.php?mode=new\" title=\"Создать новую политику\">Новая политика</a> </td>\n");
    print("</tr></table>\n");

    print("<br><br>\n");

}
#---------------------------------------------------------------------
function show_subform($formid,$param,$fopt="")
{
    global $_extifs,$iptconf_dir;
    if( trim($formid)=="") { print("Не указан идентификатор формы в show_subform()..."); exit; }

    if( $formid=="act") {

	print("<script type=\"text/javascript\">\n");
	print("function _ch()\n{\n");
	print("	var act=document.getElementById('sfact').value;\n");
	print("	document.getElementById('sfactdst').disabled=((act!='masquerade')&&(act!='input')) ? false : true;\n");
	print("	document.getElementById('sfactdstlist').disabled=((act!='masquerade')&&(act!='input')) ? false : true;\n");
	print("}\n</script>\n");
	print("<table class=table5 cellpadding=\"1px\" width=\"auto\" style=\"border-collapse:collapse;padding:2px;margin:0;\"><form name=\"editact1\" id=\"editact1\" action=\"pb.php\">\n");
	print("$fopt \n <input type=\"HIDDEN\" name=\"subparam\" value=\"act\">\n <tr><td class=tddesk><span class=seldiv> <SELECT id=\"sfact\" name=\"sfact\" OnChange=\"javascript: _ch();\"  class=tdfrm> \n");
	$sbuf_ic=1; $sbuf_act=""; $sbuf_actdst="";
	if( trim($param)!="") {
	    $sbuf_ic=coltoks($param," \t");
	    $sbuf_act=( $sbuf_ic>0) ? gettok($param,1," \t") : $param;
	    $sbuf_actdst=( $sbuf_ic>1) ? gettok($param,2," \t") : "";
	}
	$aa=array("","Masquerade","SNAT","DNAT","INPUT");
	foreach($aa as $aakey => $aavalue) {
	    print("<option value=\"".strtolower($aavalue)."\" ".(( $sbuf_act==strtolower($aavalue)) ? "SELECTED" : "").">".(( trim($aavalue)=="") ? "---" : $aavalue)."</option>\n");
	}
	print("</SELECT></span> \n </td><td class=tddesk> <input type=\"TEXT\" name=\"sfactdst\" id=\"sfactdst\" size=\"20\" class=tdfrm value=\"$sbuf_actdst\"> \n");
	print("</td><td class=tddesk> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Ok\" class=tdfrm2> \n");
	print("<input type=\"BUTTON\" name=\"sbmt1\" value=\"Отмена\" class=tdfrm2 OnClick=\"javascript: document.getElementById('mode').value='subeditcancl'; document.getElementById('editact1').submit(); \"> \n");
	print("</td></tr>\n<tr><td class=tddesk> &nbsp </td><td class=tddesk>\n");
	print("<span class=seldiv><SELECT name=\"sfactdstlist\" id=\"sfactdstlist\" class=tdfrm style=\"width:120px;\" ".(( $sbuf_act=="masquerade") ? "DISABLED" : "")."\n");
	print("OnChange=\"javascript: document.getElementById('sfactdst').value=this.value;\">\n");
	print("<option value=\"\" ".(( $sbuf_actdst=="") ? "SELECTED" : "").">---</option>\n");
	foreach($_extifs as $extifskey => $extifsvalue) {
	    print("<option value=\"$extifsvalue\" ".(( $sbuf_actdst==trim($extifsvalue)) ? "SELECTED" : "").">$extifsvalue</option>\n");
	}
	print("</SELECT>\n</span></td><td class=tddesk> &nbsp </td></tr></form></table>\n");

    } elseif( $formid=="inout") {

	$sbuf_ic=1; $sbuf_inout=""; $sbuf_inoutdst="";
	if( trim($param)!="") {
	    $sbuf_ic=coltoks($param," \t");
	    $sbuf_inout=( $sbuf_ic>0) ? gettok($param,1," \t") : $param;
	    $sbuf_inoutdst=( $sbuf_ic>1) ? gettok($param,2," \t") : "";
	}
	print("<table class=table5 cellpadding=\"1px\" width=\"auto\" style=\"border-collapse:collapse;padding:2px;margin:0;\"> <form name=\"inoutedit1\" id=\"inoutedit1\" action=\"pb.php\"> \n");
	print("$fopt \n <input type=\"HIDDEN\" name=\"subparam\" value=\"inout\">\n <tr><td class=tddesk><span class=seldiv> <SELECT id=\"sfinout\" name=\"sfinout\" class=tdfrm> \n");    
	$aa=array("in","out");
	foreach($aa as $aakey => $aavalue) {
	    print("<option value=\"".strtolower($aavalue)."\" ".(( $sbuf_inout==strtolower($aavalue)) ? "SELECTED" : "").">".(( trim($aavalue)=="") ? "---" : $aavalue)."</option>\n");
	}
	unset($aa); $aa=load_ifs(3);
	$aa[count($aa)+1]="";
	print("</SELECT></span>\n</td><td class=tddesk><span class=seldiv> <SELECT id=\"sfinoutdst\" name=\"sfinoutdst\" class=tdfrm>\n");

	foreach($aa as $aakey => $aavalue) {
	    print("<option value=\"".strtolower($aavalue)."\" ".(( trim($sbuf_inoutdst)==trim(strtolower($aavalue))) ? "SELECTED" : "").">".(( trim($aavalue)=="") ? "---" : $aavalue)."</option>\n");
	}
	print("</SELECT></span> \n </td><td class=tddesk> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Ok\" class=tdfrm2> \n");
	print("<input type=\"BUTTON\" name=\"sbmt1\" value=\"Отмена\" class=tdfrm2 OnClick=\"javascript: document.getElementById('mode').value='subeditcancl'; document.getElementById('inoutedit1').submit(); \"> \n");
	print("</td></tr></table>\n");

    } elseif(( $formid=="mark") or ( $formid=="manglemark")){

	$sbuf_ic=1; $sbuf_mark=""; 
	if( trim($param)!="") {
	    $sbuf_ic=coltoks($param," \t");
	    $sbuf_mark=( $sbuf_ic>1) ? gettok($param,2," \t") : $param;
	}
	print("<div style=\"float:left;padding-left:1px;width:300px;\">\n");
	print("<table class=table5 cellpadding=\"1px\" width=\"auto\" style=\"border-collapse:collapse;padding:2px;margin:0;\"> <form name=\"markedit1\" id=\"markedit1\" action=\"pb.php\"> \n");
	print("$fopt \n <input type=\"HIDDEN\" name=\"subparam\" value=\"mark\">\n <tr><td class=tddesk> manglemark  \n");    
	print("</td><td class=tddesk> <input type=\"TEXT\" name=\"sfmark\" id=\"sfmark\" value=\"$sbuf_mark\" class=tdfrm size=8> \n");
	print("</td><td class=tddesk> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Ok\" class=tdfrm2> \n");
	print("<input type=\"BUTTON\" name=\"sbmt1\" value=\"Отмена\" class=tdfrm2 OnClick=\"javascript: document.getElementById('mode').value='subeditcancl'; document.getElementById('markedit1').submit(); \"> \n");
	print("</td></tr></table>\n");
	print("</div>\n<div style=\"float:left;padding-left:1px;\">\n");
	print("<a href=\"pb.php?mode=showmarkers\" target=_blank title=\"Открыть сводную таблицу маркеров и политик В НОВОМ ОКНЕ\"><img src=\"icons/eog.gif\" title=\"Открыть сводную таблицу маркеров и политик В НОВОМ ОКНЕ\"></a>\n");
	print("</div>\n");
	
    } elseif( $formid=="count") {
	
	$sbuf_ic=1; $sbuf_count=""; $sbuf_count_nolog="";
	if( trim($param)!="") {
	    $sbuf_ic=coltoks($param," \t");
	    $sbuf_count=( $sbuf_ic>0) ? gettok($param,1," \t") : $param;
	    $sbuf_count_nolog=( $sbuf_ic>1) ? gettok($param,2," \t") : $param;
	}
	print("<table class=table5 cellpadding=\"1px\" width=\"auto\" style=\"border-collapse:collapse;padding:2px;margin:0;\"> <form name=\"countedit1\" id=\"countedit1\" action=\"pb.php\"> \n");
	print("$fopt \n <input type=\"HIDDEN\" name=\"subparam\" value=\"count\">\n <tr><td class=tddesk>  \n");
	print("<input type=\"CHECKBOX\" name=\"sfcount\" id=\"sfcount\" value=\"count\" ".(( trim($sbuf_count)=="count") ? "CHECKED" : "")."><label for=\"sfcount\">Считать трафик (count)</label><br>\n");
	print("<input type=\"CHECKBOX\" name=\"sfcount_nolog\" id=\"sfcount_nolog\" value=\"nolog\" ".(( trim($sbuf_count_nolog)=="nolog") ? "CHECKED" : "")."><label for=\"sfcount_nolog\">Не сохранять статистику в базе ulogd (nolog)</label>\n");
	print("</td><td class=tddesk> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Ok\" class=tdfrm2> \n");
	print("<input type=\"BUTTON\" name=\"sbmt1\" value=\"Отмена\" class=tdfrm2 OnClick=\"javascript: document.getElementById('mode').value='subeditcancl'; document.getElementById('countedit1').submit(); \"> \n");
	print("</td></tr></table>\n");
	
    } elseif( $formid=="ipset") {
	
	$sbuf_ic=1; $sbuf_ipsetmode=""; $sbuf_ipsetname="";
	if( trim($param)!="") {
	    $sbuf_ic=coltoks($param," \t");
	    $sbuf_ipsetmode=( $sbuf_ic>0) ? gettok($param,1," \t") : $param;
	    $sbuf_ipsetname=( $sbuf_ic>1) ? gettok($param,2," \t") : $param;
	}
	print("<table class=table5 cellpadding=\"1px\" width=\"auto\" style=\"border-collapse:collapse;padding:2px;margin:0;\"> <form name=\"ipsetedit1\" id=\"ipsetedit1\" action=\"pb.php\"> \n");
	print("$fopt \n <input type=\"HIDDEN\" name=\"subparam\" value=\"ipset\">\n <tr><td class=tddesk><span class=seldiv> <SELECT name=\"sfipsetmode\" id=\"sfipsetmode\" class=tdfrm> \n");
	print("<option value=\"blacklist\" ".(( $sbuf_ipsetmode=="blacklist") ? "SELECTED" : "").">blacklist</option>\n");
	print("<option value=\"allow_only\" ".(( $sbuf_ipsetmode=="allow_only") ? "SELECTED" : "").">allow_only</option>\n");
	print("</SELECT></span>\n</td><td class=tddesk><span class=seldiv> <SELECT id=\"sfipsetname\" name=\"sfipsetname\" class=tdfrm>\n");
	if( isset($aa)) unset($aa);
	if( !is_readable($iptconf_dir."/ipsetlist")) {
	    wlog("Ошибка! Файл Ipsetlist недоступен для чтения!",2,TRUE,5,TRUE); _exit(); 
	}
	$aa=file($iptconf_dir."/ipsetlist");
	foreach($aa as $aakey => $aavalue) {
	    if( trim($aavalue)=="") continue;
	    if( $aavalue[0]=="#") continue;
	    print("<option value=\"".strtolower(trim($aavalue))."\" ".(( $sbuf_ipsetname==strtolower(trim($aavalue))) ? "SELECTED" : "").">".trim($aavalue)."</option>\n");
	}
	print("</SELECT></span> \n </td><td class=tddesk> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Ok\" class=tdfrm2> \n");
	print("<input type=\"BUTTON\" name=\"sbmt1\" value=\"Отмена\" class=tdfrm2 OnClick=\"javascript: document.getElementById('mode').value='subeditcancl'; document.getElementById('ipsetedit1').submit(); \"> \n");
	print("</td></tr></table>\n");

    } elseif( $formid=="title") {
	
	print("<table class=table5 cellpadding=\"1px\" width=\"auto\" style=\"border-collapse:collapse;padding:2px;margin:0;\"> <form name=\"titleedit1\" id=\"titleedit1\" action=\"pb.php\"> \n");
	print("$fopt \n <input type=\"HIDDEN\" name=\"subparam\" value=\"title\">\n <tr><td class=tddesk> <input type=\"TEXT\" name=\"sftitle\" id=\"sftitle\" size=\"45\" value=\"$param\" class=frmdesk> \n");
	print("</td><td class=tddesk> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Ok\" class=tdfrm2> \n");
	print("<input type=\"BUTTON\" name=\"sbmt1\" value=\"Отмена\" class=tdfrm2 OnClick=\"javascript: document.getElementById('mode').value='subeditcancl'; document.getElementById('titleedit1').submit(); \"> \n");
	print("</td></tr></table>\n");

    }
}
#---------------------------------------------------------------------
function show_subselect()
{
    global $_SESSION,$sess_policy,$_policy,$_edit,$refmode,$mode;
    $aa0=array( "title" => "Описание (title)",
	    "act" => "Действие (target)",
	    "inout" => "Направление (in/out)", 
	    "mark" => "Маркировка (manglemark)", 
	    "count" => "Подсчет трафика", 
	    "ipset" => "Cписки ipset (blacklist/allow_only)"
	);
    foreach($_SESSION[$sess_policy] as $sesskey => $sessvalue) {
	$_w1=trim(gettok(_trimline($sessvalue," \t"),1," \t"));
	if( $_w1=="action") {
	    unset($aa0["act"]);
	} elseif(( $_w1=="in") or ( $_w1=="out")) {
	    unset($aa0["inout"]);
	} elseif( $_w1=="manglemark") {
	    unset($aa0["mark"]);
	} elseif( $_w1=="allow_only") {
	    unset($aa0["ipset"]);
	} elseif( $_w1=="count") {
	    unset($aa0["count"]);
	} elseif( $_w1=="title") {
	    unset($aa0["title"]);
	}
    }
    if( count($aa0)==0) return("");
    print("<br><br>\n<table class=table5 cellpadding=\"8px\" style=\"border-collapse:collapse;padding:8px;\"><form name=\"subposadd\" id=\"subposadd\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" id=\"mode\" value=\"subitemadd\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"$_policy\">\n");
    print("<input type=\"HIDDEN\" name=\"refmode\" value=\"$refmode\">\n");
    print("<input type=\"HIDDEN\" name=\"edit\" value=\"$_edit\">\n");
    
    print("<tr><td class=td42ye0> Добавить элемент: </td><td class=td41ye0> \n");

    print("<span class=seldiv><SELECT name=\"subitemname\" id=\"subitemname\" class=tdfrm11>\n");
    foreach($aa0 as $aa0key => $aa0value) {
	print("<option value=\"$aa0key\">$aa0value</option>\n");
    }
    print("</SELECT></span>\n </td><td class=td41ye0> \n");
    print("<input type=\"SUBMIT\" name=\"sbmt\" value=\"Добавить\" class=tdfrm2> </td></tr>\n</form></table>\n");

}
#---------------------------------------------------------------------

function showPolicy($mode)
{
    global $_policy,$_edit,$_SESSION,$sess_policy,$subitemname,$_sskey,$_refmode;
    
    
    print("<script type=\"text/javascript\">\n");
    print("function mOver(itemId)\n");
    print("{\n");
    print("	document.getElementById(itemId).className = \"divbtnon\";\n");
    print("}\n");
    print("function mOut(itemId)\n");
    print("{\n");
    print("	document.getElementById(itemId).className = \"divbtnout\";\n");
    print("}\n");
    print("</script>\n");
    
    if( $mode=="subitemadd") $_edit=0;
    
    print("<br><br>\n<font class=top1>Редатирование политики</font><br><br>\n");
    print("<table class=table4 cellpadding=\"2px\" width=\"680px\">\n");
    print("<tr class=wbrd><td class=tah10> <b>Идентификатор</b><br>(краткое имя):</td><td class=tah11c> <b>$_policy </b> </td></tr>\n");
    $aacpts=array(); 
    if( $mode=="new") {

	print("<tr class=wbrd><td class=tah10> <b>Описание:</b> </td><td class=tah11c> ");
	if( $mode=="subedittitle") {
	    show_subform("title",$buf1,$formln);
	} else {
	    print($buf1);
	    if( $_edit=="1") print(" <a href=\"pb.php?mode=subedittitle&p=$_policy&refmode=$mode&edit=0\" title=\"Редактировать параметр\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать параметр\"></a>");
	}
	print("</td></tr>\n");
	print("<tr><td colspan=2 class=tah10 > <b>Правила:</b> </td></tr>\n");
	print("<tr><td colspan=2> \n");
	print("<table class=table5 cellpadding=\"0px\" width=\"auto\" style=\"background-color:transparent;margin-left:1px;padding-left:1px;padding-right:1px;border-collapse:collapse;border-style:solid;\">\n");
	print("<tr><th class=thpol>Режим</th><th class=thpol>Протокол</th><th class=thpol>Инв.</th><th class=thpol>Порты</th><th class=thpol><table class=notable><tr><td>Направление</td><td><img src=\"icons/stock_dialog-question.gif\" title=\"src/dst - от локального порта/к порту удаленного сервера\"></td></tr></table></th><th class=thpol>Квота</th><th class=thpol><table class=notable><tr><td>Адресат</td><td>&nbsp<img src=\"icons/stock_dialog-question.gif\" title=\"to addr/from addr - к удаленному адресу/от удаленного адреса\"></td></tr></table><th class=thpol style=\"background-color:FFFBF0;\"> <a href=\"pb.php?mode=addrule&p=$_policy&refmode=$mode\" title=\"Добавить новое правило\"><img src=\"icons/gtk-add16.gif\" title=\"Добавить новое правило\"></a> </th></tr>\n");
	print("</table>\n");

    } elseif( $mode!="new") {
	foreach($_SESSION[$sess_policy] as $aa1key => $aa1val) {
	    $_w1=trim(gettok($aa1val,1," \t"));
	    if(( $_w1=="accept") or ( $_w1=="reject")) {
		$aacpts[count($aacpts)+1]=$aa1val;
	    }
	}
	reset($_SESSION[$sess_policy]);
	foreach($_SESSION[$sess_policy] as $aa1key => $aa1val) {

	    $aa2val=_trimline($aa1val," \t");
	    $_w1=trim(gettok($aa2val,1," \t"));
	    $formln="<input type=\"HIDDEN\" name=\"mode\" id=\"mode\" value=\"subeditsave\">\n";
	    $formln=$formln."<input type=\"HIDDEN\" name=\"p\" value=\"$_policy\">\n";
	    $formln=$formln."<input type=\"HIDDEN\" name=\"refmode\" value=\"$_refmode\">\n";
	    $formln=$formln."<input type=\"HIDDEN\" name=\"sskey\" value=\"$aa1key\">\n";
	    $formln=$formln."<input type=\"HIDDEN\" name=\"edit\" value=\"$_edit\">\n";

	    if( $_w1=="title") {
		$buf1=trim(str_replace("\"","",str_replace("title","",$aa1val)));
		print("<tr class=wbrd><td class=tah10> <b>Описание:</b> </td><td class=tah11c> ");
		if( $mode=="subedittitle") {
		    show_subform("title",$buf1,$formln);
		} else {
		    print($buf1);
		    if( $_edit=="1") print(" <a href=\"pb.php?mode=subedittitle&p=$_policy&refmode=$mode&edit=0\" title=\"Редактировать параметр\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать параметр\"></a>");
		}
		print("</td></tr>\n");
		print("<tr><td colspan=2 class=tah10 > <b>Правила:</b> </td></tr>\n");
		print("<tr><td colspan=2> \n");
		print("<table class=table5 cellpadding=\"0px\" width=\"auto\" style=\"background-color:transparent;margin-left:1px;padding-left:1px;padding-right:1px;border-collapse:collapse;border-style:solid;\">\n");
		print("<tr><th class=thpol>Режим</th><th class=thpol>Протокол</th><th class=thpol>Инв.</th><th class=thpol>Порты</th><th class=thpol><table class=notable><tr><td>Направление</td><td><img src=\"icons/stock_dialog-question.gif\" title=\"src/dst - от локального порта/к порту удаленного сервера\"></td></tr></table></th><th class=thpol>Квота</th><th class=thpol><table class=notable><tr><td>Адресат</td><td>&nbsp<img src=\"icons/stock_dialog-question.gif\" title=\"to addr/from addr - к удаленному адресу/от удаленного адреса\"></td></tr></table><th class=thpol style=\"background-color:FFFBF0;\"> <a href=\"pb.php?mode=addrule&p=$_policy&refmode=$mode\" title=\"Добавить новое правило\"><img src=\"icons/gtk-add16.gif\" title=\"Добавить новое правило\"></a> </th></tr>\n");
		foreach($aacpts as $aackey => $aacval) {
		    $bufact=""; $bufproto=""; $bufinv=FALSE;
		    $bufports=""; $bufdst=""; $bufquota=""; $buf_type_direct=""; $buf_addr_direct="";
		    $aacval=_trimline(trim($aacval));
		    $aact=coltoks($aacval," \t");
		    $bufact=gettok($aacval,1," \t");
		    $bufproto=( $aact>1) ? gettok($aacval,2," \t") : "";
		    
		    if( $aact>2) {
			for($iict=3;$iict<=$aact;$iict++) {
			    $bufword=trim(gettok($aacval,$iict," \t"));
			    if( $bufword=="src") { 
				$bufdst=$bufword; 
			    } elseif( $bufword=="dst") {
				$bufdst=$bufword;
			    } elseif( $bufword=="quota") { 
				$iict++; $bufquota=trim(gettok($aacval,$iict," \t")); 
			    } elseif(( $bufword=="to") or ( $bufword=="from")) {
				$buf_type_direct=$bufword;
				$iict++; 
				$buf_addr_direct=trim(gettok($aacval,$iict," \t"));
			    } else {
				$bufword=( $bufword[0]=="(") ? substr($bufword,1) : $bufword;
				$bufword=( $bufword[strlen($bufword)-1]==")") ? substr($bufword,0,-1) : $bufword;
				$bufinv=( $bufword[0]=="!") ? TRUE : FALSE;
				$bufports=( $bufword[0]=="!") ? substr($bufword,1) : $bufword;
			    }
			}
		    }
		    $line="<tr><td class=td42c> $bufact </td><td class=td42c> $bufproto </td><td class=td42c> ".(( $bufinv) ? "!" : "")." </td><td class=td42c> $bufports </td><td class=td42c> $bufdst </td><td class=td42c> $bufquota </td><td class=td42c> $buf_type_direct $buf_addr_direct </td>\n";
		    $line=$line." <td class=td42> ".(( $_edit=="1") ? "<a href=\"pb.php?mode=editrule&p=$_policy&index=$aackey&refmode=$mode\" title=\"Редактировать\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать\"></a> <a href=\"pb.php?mode=delrule&p=$_policy&index=$aackey&refmode=$mode\" title=\"Удалить\"><img src=\"icons/gtk-delete16.gif\" title=\"Удалить\"></a>" : " &nbsp ")."</td></tr>\n";
		    print($line);
		}
		print("</table>\n &nbsp </td></tr>\n");
	    } elseif(( $_w1=="accept") or ( $_w1=="reject")) {
		continue;
	    } elseif( $_w1=="action") {
		print("<tr class=wbrd><td class=tah10> <b>Действие:</b> </td><td class=tah11c> ");
		$buf1act=trim(str_replace($_w1,"",$aa1val));
		if( $mode=="subeditact") {
		    show_subform("act",$buf1act,$formln);
		} else {
		    print("<table class=notable1 width=\"100%\"><tr><td>".$buf1act." </td><td align=right> ");
		    if( $_edit=="1") {
			print("<a href=\"pb.php?mode=subeditact&sskey=$aa1key&p=$_policy&refmode=$mode&edit=0\" title=\"Редактировать параметр\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать параметр\"></a> \n");
			print(" <a href=\"pb.php?mode=subposdel&sskey=$aa1key&p=$_policy&refmode=$mode&edit=$_edit\" title=\"Удалить параметр\"><img src=\"icons/gtk-delete16.gif\" title=\"Удалить параметр\"></a> ");
		    }
		    print("</td></tr></table> ");
		}
		print(" </td></tr>\n");
	    } elseif(( $_w1=="in") or ( $_w1=="out")) {
		print("<tr class=wbrd><td class=tah10> <b>Направление:</b> </td><td class=tah11c> \n");
		if( $mode=="subeditinout") {
		    show_subform("inout",trim($aa1val),$formln);
		} else {
		    print("<table class=notable1 width=\"100%\"><tr><td>".trim($aa1val)." </td><td align=right> ");
		    if( $_edit=="1") {
			print("<a href=\"pb.php?mode=subeditinout&sskey=$aa1key&p=$_policy&refmode=$mode&edit=0\" title=\"Редактировать параметр\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать параметр\"></a> \n");
			print(" <a href=\"pb.php?mode=subposdel&sskey=$aa1key&p=$_policy&refmode=$mode&edit=$_edit\" title=\"Удалить параметр\"><img src=\"icons/gtk-delete16.gif\" title=\"Удалить параметр\"></a> ");
		    }
		    print("</td></tr></table> ");
		}
		print(" </td></tr>\n");
	    } elseif( $_w1=="manglemark") {
		print("<tr class=wbrd><td class=tah10> <b>Маркировка:</b> </td><td class=tah11c> \n");
		if(( $mode=="subeditmark") and ( $aa1key==$_sskey)) {
		    show_subform("mark",trim($aa1val),$formln);
		} else {
		    print("<table class=notable1 width=\"100%\"><tr><td>".trim($aa1val)." </td><td align=right> ");
		    if( $_edit=="1") {
			print("<a href=\"pb.php?mode=subeditmark&sskey=$aa1key&p=$_policy&refmode=$mode&edit=0\" title=\"Редактировать параметр\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать параметр\"></a> \n");
			print(" <a href=\"pb.php?mode=subposdel&sskey=$aa1key&p=$_policy&refmode=$mode&edit=$_edit\" title=\"Удалить параметр\"><img src=\"icons/gtk-delete16.gif\" title=\"Удалить параметр\"></a> ");
		    }
		    print("</td></tr></table> ");
		}
		print(" </td></tr>\n");
	    } elseif( $_w1=="count") {
		print("<tr class=wbrd><td class=tah10> <b>Подсчет<br>трафика:</b> </td><td class=tah11c> \n");
		if( $mode=="subeditcount") {
		    show_subform("count",trim($aa1val),$formln);
		} else {
		    print("<table class=notable1 width=\"100%\"><tr><td>".trim($aa1val)." </td><td align=right> ");
		    if( $_edit=="1") {
			print("<a href=\"pb.php?mode=subeditcount&sskey=$aa1key&p=$_policy&refmode=$mode&edit=0\" title=\"Редактировать параметр\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать параметр\"></a> \n");
			print(" <a href=\"pb.php?mode=subposdel&sskey=$aa1key&p=$_policy&refmode=$mode&edit=$_edit\" title=\"Удалить параметр\"><img src=\"icons/gtk-delete16.gif\" title=\"Удалить параметр\"></a> ");
		    }
		    print("</td></tr></table> ");
		}
		print("</td></tr>\n");
	    } elseif(( $_w1=="blacklist") or ( $_w1=="allow_only")) {
		print("<tr class=wbrd><td class=tah10> <b>Списки ipset:</b> </td><td class=tah11c> \n");
		if(( $mode=="subeditipset") and ( $aa1key==$_sskey)) {
		    show_subform("ipset",trim($aa1val),$formln);
		} else {
		    print("<table class=notable1 width=\"100%\"><tr><td>".trim($aa1val)." </td><td align=right> ");
		    if( $_edit=="1") {
			print(" <a href=\"pb.php?mode=subeditipset&sskey=$aa1key&p=$_policy&refmode=$mode&edit=0\" title=\"Редактировать параметр\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать параметр\"></a> \n");
			print(" <a href=\"pb.php?mode=subposdel&sskey=$aa1key&p=$_policy&refmode=$mode&edit=$_edit\" title=\"Удалить параметр\"><img src=\"icons/gtk-delete16.gif\" title=\"Удалить параметр\"></a> ");
		    }
		    print("</td></tr></table> ");
		}
		print(" </td></tr>\n");
	    } else {
		print("<tr class=wbrd><td> <font color=red> Unknown</font> </td><td class=tah11c> <font color=red>Error in string: </font> $aa1val </td></tr>\n");
	    }
	}
	if(($mode=="subitemadd") and ( trim($subitemname)!="")) {
	    $aa0=array( "title" => "Описание",
		"act" => "Действие",
		"inout" => "Направление", 
		"mark" => "Маркировка", 
		"count" => "Подсчет трафика", 
		"ipset" => "Cписки ipset", 
		);
	    print("<tr class=wbrd><td class=tah10> <b>".$aa0[trim($subitemname)]."</b> </td><td class=tah11c> \n");
	    if( isset($formln)) unset($formln);
	    $formln="<input type=\"HIDDEN\" name=\"mode\" id=\"mode\" value=\"subaddsave\">\n";
	    $formln=$formln."<input type=\"HIDDEN\" name=\"p\" value=\"$_policy\">\n";
	    $formln=$formln."<input type=\"HIDDEN\" name=\"refmode\" value=\"edit\">\n";
	    $formln=$formln."<input type=\"HIDDEN\" name=\"edit\" value=\"1\">\n";
	    show_subform($subitemname,"",$formln);
	    print("</td></tr>\n");
	    
	}


    }

	print("<tr><td class=tah10 colspan=2> \n");
	print("<div style=\"padding:5px; margin-left:180px;\">\n<br>\n");
	$lnktext=" <a href=\"pb.php\" title=\"Сохранить данные в буфер сессии и отложить редактирование\">";
	print("<div id=\"divdrop\" class=divbtnout onMouseOver=\"javascript:mOver('divdrop');\" onMouseOut=\"javascript:mOut('divdrop');\" style=\"width:115px;display:inline;position:relative;left:10px;padding:2px;\"> &nbsp $lnktext Отложить </a> &nbsp </div>\n");
	$lnktext=" <a href=\"pb.php?mode=cancledit&p=$_policy\" title=\"Отменить редактирование политики\">";
	print("<div id=\"divcancl\" class=divbtnout onMouseOver=\"javascript:mOver('divcancl');\" onMouseOut=\"javascript:mOut('divcancl');\" style=\"width:115px;display:inline;position:relative;left:50px;padding:2px;\"> &nbsp $lnktext Отмена </a> &nbsp </div>\n");

	$lnktext=" <a href=\"pb.php?mode=applyedit&p=$_policy\" title=\"Сохранить изменения\">";
	print("<div id=\"divapply\" class=divbtnout onMouseOver=\"javascript:mOver('divapply');\" onMouseOut=\"javascript:mOut('divapply');\" style=\"width:115px;display:inline;position:relative;left:90px;padding:2px;\"> &nbsp $lnktext Записать </a> &nbsp </div>\n");
	print("</div>\n </td></tr>\n");


    print("</table>\n");
    
    if(( $mode!="subitemadd") and ( $_edit=="1")) {
	show_subselect();
    }

}

#---------------------------------------------------------------------
function show_medit_form()
{
    global $_mode,$_policy,$_refmode,$_f;
    if( trim($_policy)=="") { 
	print("<font class=error>Не указан идентификатор политики.</font><br><br>"); 
	print("<table class=notable><tr><td> <a href=\"pb.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"pb.php\" title=\"Назад\">Назад</a> </td></tr></table>\n");
	exit;
    }
    if(( $_mode!="medit") and ( $_mode!="mnew")) { 
	wlog("Ошибка режима _mode=\"$_mode\" в функции show_medit_form() в pb.php.",2,TRUE,4,TRUE); exit;
    }
    if( $_mode=="medit") {
	if(!$aapol=policy2array($_policy,TRUE,TRUE)) {
	    wlog("Ошибка чтения политики $_policy в функции show_medit_form() в pb.php.",2,TRUE,5,TRUE); exit;
	}
    }
    print("<script type=\"text/javascript\">\n");
    print("function mOver(itemId)\n");
    print("{\n");
    print("	document.getElementById(itemId).className = \"divbtnon\";\n");
    print("}\n");
    print("function mOut(itemId)\n");
    print("{\n");
    print("	document.getElementById(itemId).className = \"divbtnout\";\n");
    print("}\n");
    print("</script>\n");

    print("<br><br>\n<font class=top1>Ручное редактирование политики</font>\n<br><br>\n");
    print("<font class=text42s>Идентификатор: <b>$_policy</b></font><br><br>\n");
    print("<table class=table4 cellpadding=\"4px\">\n");
    print("<form name=\"medit1\" id=\"medit1\" action=\"pb.php\" method=\"POST\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" value=\"".$_mode."save\">\n");
    print("<input type=\"HIDDEN\" name=\"edit\" value=\"1\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"$_policy\">\n");
    print("<tr><td> <TEXTAREA name=\"pconf\" rows=\"30\" cols=\"80\" wrap=\"off\">\n");
    if( $_mode=="medit") {
	foreach($aapol as $aaval) print((( trim($aaval)!="") ? trim($aaval)."\n" : ""));
    }
    print("</TEXTAREA>\n</td></tr>\n");
    print("<tr><td class=tah10> \n");
    print("<div style=\"padding:5px; margin-left:180px;\">\n<br>\n");
    $lnktext=( trim($_refmode)!="") ? "?mode=$_refmode" : "";
    $lnktext=(( trim($lnktext)!="") ? trim($lnktext)."&" : "?").(( trim($_f)!="") ? "f=$_f" : "");
    $lnktext=(( trim($lnktext)!="") ? trim($lnktext)."&" : "?")."edit=1";
    $lnktext=" <a href=\"pb.php".$lnktext."\" title=\"Отменить редактирование политики\">";
    print("<div id=\"divcancl\" class=divbtnout onMouseOver=\"javascript:mOver('divcancl');\" onMouseOut=\"javascript:mOut('divcancl');\" style=\"width:115px;display:inline;position:relative;left:50px;padding:2px;\"> &nbsp $lnktext Отмена </a> &nbsp </div>\n");

    $lnktext=" <a href=\"#\" title=\"Сохранить изменения\" OnClick=\"javascript: document.getElementById('medit1').submit();\">";
    print("<div id=\"divapply\" class=divbtnout onMouseOver=\"javascript:mOver('divapply');\" onMouseOut=\"javascript:mOut('divapply');\" style=\"width:115px;display:inline;position:relative;left:90px;padding:2px;\"> &nbsp $lnktext Записать </a> &nbsp </div>\n");
    print("</div>\n </td></tr>\n");
    
    print("</form>\n</table>\n");

}
#---------------------------------------------------------------------
function get_tmpname($policy,$tmpmode="")
{
    global $_SESSION,$_mode;
    $tmp1="policy_tmp_$policy";
    if( trim($tmpmode)=="") {
	if( $_mode=="editcanclrule") $tmpmode=$_mode;
	if( $_mode=="editsaverule") $tmpmode=$_mode;
    }
    if(( $_mode=="edit") or ( $_mode=="new")) {
	if( isset($_SESSION[$tmp1])) {
	    if(( $tmpmode=="continue") or ( $tmpmode=="editcanclrule") or ( $tmpmode=="editsaverule")) {
		return($tmp1);
	    } elseif( $tmpmode=="clear") {
		unset($_SESSION[$tmp1]);
	    } else {
		print("<br><br>\nОбнаружена существующая временная запись редактируемой политики <b>$policy</b>:<br><br>\n");
		print("<table class=table5 width=\"350px\" style=\"margin-left:50px; padding:5px;\">\n");
		foreach($_SESSION[$tmp1] as $ssk => $ssv) {	print("<tr><td> $ssk </td><td> $ssv </td></tr>\n"); }
		print("</table>\n<br><br>\n");
		$lnktext="<a href=\"pb.php?mode=$_mode&p=$policy&tmpmode=clear\" title=\"Очистить запись\">";
		print("<table class=notable>\n<tr><td> $lnktext <img src=\"icons/gtk-cancel.gif\" title=\"Очистить запись\"></a> </td><td> $lnktext Очистить запись и продолжить</a> </td></tr>\n ");
		$lnktext="<a href=\"pb.php?mode=$_mode&p=$policy&tmpmode=continue\" title=\"Продолжить редактирование\">";
		print("<tr><td> $lnktext <img src=\"icons/gtk-apply.gif\" title=\"Продолжить редактирование\"></a>  </td><td> $lnktext Продолжить редактирование записи</a> </td></tr>\n</table>\n ");
		exit;
	    }
	}    
    } else {
	if( isset($_SESSION[$tmp1])) {
	    return($tmp1);
	}
    }
    if( !isset($_SESSION["temp_pollist"])) {
	$_SESSION["temp_pollist"]=array();
    }
    $_SESSION["temp_pollist"]=array_values($_SESSION["temp_pollist"]);
    if( !in_array($policy,$_SESSION["temp_pollist"])) {
	$_SESSION["temp_pollist"][count($_SESSION["temp_pollist"])]=$policy;
    }
    $_SESSION[$tmp1]=array();
    $_SESSION["temp_pmode_$policy"]=$_mode;
    return($tmp1);
}

#---------------------------------------------------------------------

function find_policies()
{
    global $_pstr,$_direction,$_used,$_mode,$_f;

    if( isset($aa1)) { unset($aa1); }
    $aa1=array();
    $arez=array();
    if( !$aa1=load_policies_list()) {
	print("Error load policies list in runsearch\n"); exit;
    } else {
	$level=( trim($_pstr)!="") ? 1 : 0;
	if( trim($_direction)!="") $level++;
	if( trim($_used)!="") $level++;
	if( $level==0) { showPanel(); exit; }
	foreach( $aa1 as $key1 => $vv1) {
	    $rr=0;

	    if( $_pstr!="") {
		if(( substr_count($key1,$_pstr)!=0) or ( substr_count($vv1,$_pstr)!=0)) $rr++;
	    }
	    $bufout=get_policy_param($key1,"out","",0,TRUE);
	    $bufin=get_policy_param($key1,"in","",0,TRUE);
	    $bufio=trim($bufin).trim($bufout);
	    if( trim($_direction)!="") {
		if( $_direction==$bufio) $rr++;
	    }
	    if( trim($_used)!="") {
		if( isset($ausers)) unset($ausers);
		$ausers=get_policy_users($key1);
		if( $_used=="us") {
		    if( count($ausers)>0) $rr++;
		} elseif( $_used=="not") {
		    if( count($ausers)==0) $rr++;
		}
	    }
	    if( $rr==$level) $arez[$key1]=$vv1;
	}
    }

    if( count($arez)>0) {

	print("<br><font class=top1>Результаты поиска:</font><br><br>\n");
	$ptext="";
	$ptext=( trim($_pstr)!="") ? "\"".trim($_pstr)."\"" : $ptext;
	$abuf0=array("in" => "Входящие", "out" => "Исходящие");
	$ptext=$ptext.(( trim($_direction)!="") ? " ".$abuf0[$_direction] : "");
	unset($abuf0);
	$abuf0=array("us" => "Используемые", "not" => "Не используемые");
	$ptext=$ptext.(( trim($_used)!="") ? " ".$abuf0[$_used] : "").".\n";
	print("<font style=\"FONT: italic 8pt Tahoma; color:330066;\">Условия: $ptext <br>Всего найдено: ".count($arez)." политик(и)</font><br><br>\n");
	print("<br><br>\n");
	show_policies_list($arez,$_f);

    } else {
	print("Нет политик, соответствующих заданным параметрам.<br><br>\n");
	print("<table class=notable><tr><td> <a href=\"pb.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"pb.php\" title=\"Назад\">Назад</a> </td></tr></table>\n");
    }
}

#---------------------------------------------------------------------
function show_polname_form()
{
    print("<br><br>\n<font class=top1>Создание политики</font><br><br>\n");
    print("<font class=text42s>Введите идентификатор (краткое имя) создаваемой политики:</font><br><br>\n");
    print("<table class=table4 cellpadding=\"4px\" style=\"border-collapse:collapse;width:500px;\">\n");
    print("<form name=\"polname1\" id=\"polname1\" action=\"pb.php\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" id=\"mode\" value=\"new\">\n");
    print("<input type=\"HIDDEN\" name=\"tmpmode\" id=\"tmpmode\" value=\"continue\">\n");
    print("<input type=\"HIDDEN\" name=\"edit\" id=\"edit\" value=\"1\">\n");
    print("<tr><td style=\"padding:15px;\"> Идентификатор: </td><td style=\"padding:15px;\"><input type=\"TEXT\" name=\"p\" id=\"p\" size=45 value=\"\"> </td></tr>\n");
    print("<tr><td style=\"padding:15px;\"> Способ: </td><td style=\"padding:15px;\"><span class=seldiv><SELECT name=\"way\" id=\"way\">\n");
    print("<option value=\"\">В конструкторе</option> \n <option value=\"man\">Вручную</option>\n");
    print("</SELECT></span>\n</td></tr><tr><td style=\"padding:15px;\" colspan=2>\n");
    print("<div style=\"width:500px;\" align=right>\n");
    print("<input type=\"BUTTON\" name=\"cncl\" id=\"cncl\" value=\" Отмена \" OnClick=\"javascript: document.getElementById('p').value=''; document.getElementById('edit').value=''; document.getElementById('mode').value=''; document.getElementById('polname1').submit();\"> &nbsp&nbsp&nbsp \n");
    print("<input type=\"SUBMIT\" name=\"sbmt\" value=\" Создать \">\n");
    print("</div>\n ");
    print("</td></tr>\n</form>\n</table>\n<br>\n");

}
#---------------------------------------------------------------------
function view_policy($policy)
{
    global $_mode,$_edit,$_refmode;
    if( trim($policy)=="") { 
	print("<font class=error>Не указан идентификатор политики для просмотра.</font><br><br>"); 
	print("<table class=notable><tr><td> <a href=\"pb.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"pb.php\" title=\"Назад\">Назад</a> </td></tr></table>\n");
	exit;
    }
    if( $_mode!="view") { 
	wlog("Ошибка режима _mode=\"$_mode\" в функции view_policy() в pb.php.",2,TRUE,4,TRUE); exit;
    }
    if(!$atmppol=policy2array($policy,TRUE,TRUE)) {
	wlog("Ошибка чтения политики $policy в функции view_policy() в pb.php.",2,TRUE,5,TRUE); exit;
    }
    print("<br><br>\n<font class=top1>Просмотр политики \"$policy\":</font><br><br>\n");
    print("<table class=table5 style=\"border-collapse:collapse;margin-left:30px\">\n");
    $ii=0;
    foreach($atmppol as $aval) { 
	$ii++; 
	print((( trim($aval)!="") ? "<tr><td class=td41ye style=\"padding-left:10px;padding-right:10px;\">$ii</td><td class=td41ye style=\"padding-left:15px;padding-right:15px;\"> $aval </td></tr>\n" : ""));
    }
    print("\n</table>\n<br><br>\n");
    if( $_edit=="1") {
	print("<font class=text42s><b>Действия:</b></font>\n<br><br>\n");
	print("<table class=notable><tr> \n");
	print("<td class=tdpadd><a href=\"pb.php?mode=edit&p=$policy&edit=1\" title=\"Открыть в конструкторе\"><img src=\"icons/pb22.gif\" title=\"Открыть в конструкторе\"></a> </td> \n");
	print("<td> &nbsp <a href=\"pb.php?mode=edit&p=$policy&edit=1\" title=\"Открыть в конструкторе\"> Открыть в конструкторе</a> </td>  \n");
	$refln=( trim($_refmode)!="") ? "?mode=$_refmode&edit=1" : "";
	print("</tr><tr><td class=tdpadd> <a href=\"pb.php$refln\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td>\n");
	print("<td> &nbsp <a href=\"pb.php$refln\" title=\"Назад\">Назад</a> </td></tr></table>\n");
    }
    
}
#---------------------------------------------------------------------
function showPanel()
{
    global $_SESSION,$_f;
    print(" \n");
    if( !$aa0=load_policies_list()) {
	wlog("Error load policies list in show_search_form()\n",2,TRUE,4,FALSE); exit;
    }
    print("<font class=top2>Политики</font><br><br> \n");
    print("<table class=table4 width=\"60%\" > \n<tr class=wbrd><td colspan=2 style=\"padding-left:30px;\">\n");
    
    print("<font class=text42s><b>Статистика:</b></font><br><br>\n");
    print("<table class=table5 cellpadding=\"4px\" style=\"border-collapse:collapse;padding:4px;margin:0;\">\n");
    $cused=0; $cin=0; $cout=0;
    foreach($aa0 as $key => $vv) {
	if( count(get_policy_users($key))>0) $cused++;
    }
    print("<tr><td class=td41ye> Всего политик: </td><td class=td41ye> <b>".count($aa0)."</b></td></tr>\n");
    print("<tr><td class=td41ye> Используется:  </td><td class=td41ye> <b>$cused</b></td></tr>\n");
    print("</table>\n<br></td></tr>\n");
    print("<tr class=wbrd><td colspan=2 class=wbrd style=\"padding-left:30px;\">\n");
    print("<font class=text42s><b>Маркировки:</b></font><br><br>\n");
    print("<table class=table5 cellpadding=\"4px\" style=\"border-collapse:collapse;padding:4px;margin:0;\">\n");
    print("<tr><td class=td41ye> Всего используется политиками: </td><td class=td41ye> <b>".count(getmarkers())."</b> маркеров</td></tr>\n");
    print("</table>\n<a href=\"pb.php?mode=showmarkers\" title=\"Открыть сводную таблицу маркеров, используемых политиками\"><font class=text40v>Сводная таблица маркеров и политик</font></a><br>&nbsp \n");
    print("</td></tr>\n");
    print("<tr class=wbrd><td colspan=2 class=wbrd1>\n");

    print("<table class=notable1 cellpadding=\"3px\"> \n<tr><td colspan=2>\n");
    print("<form name=\"form1\" action=\"pb.php\"> \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"psearch\">  \n");
    print("<br> <div style=\"padding-left:30px;\"><font class=text42s><b>Поиск:</b></font></div></td></tr>\n");
    print("<tr><td> по имени/описанию:</td>\n<td><input type=\"TEXT\" name=\"pstr\" size=\"45\"></td></tr> \n");
    print("<tr><td> по направленности:</td><td><span class=seldiv> <SELECT name=\"d\">\n <option value=\"\" selected>Пофиг <option value=\"in\">Входящие\n<option value=\"out\">Исходящие \n</SELECT></span>\n</td></tr>\n");
    print("<tr><td> по использованию: </td><td><span class=seldiv><SELECT name=\"used\">\n <option value=\"\">Любые </option>\n");
    print("<option value=\"us\">Используемые </option>\n <option value=\"not\">Не используемые </option>\n");
    print("</SELECT></span>\n </td></tr>\n");
    print("<tr><td colspan=2>&nbsp&nbsp&nbsp<input type=\"submit\" value=\"Искать\"></form><br> &nbsp \n");
    print("</td></tr> </table>\n </td></tr>\n");

    print("<tr class=wbrd><td colspan=2 class=wbrd1 style=\"padding:6px;padding-left:30px;\"> \n");
    print("<font class=text42s><b>Полный список политик:</b></font><br><br>\n");

    print("<table class=notable1><tr><td><a href=\"pb.php?mode=pollist\" title=\"Полный список политик\"><img src=\"icons/setlist22.gif\" title=\"Полный список политик\"></a> </td><td>\n");
    print("<a href=\"pb.php?mode=pollist&f=1\"> <font class=text40v>Расширенный список</font></a> </td></tr></table>\n");

    print("<table class=notable1><tr><td><a href=\"pb.php?mode=pollist\" title=\"Полный список политик\"><img src=\"icons/setlist22.gif\" title=\"Полный список политик\"></a> </td><td>\n");
    print("<a href=\"pb.php?mode=pollist&f=0\"> <font class=text40v>Компактный список</font></a> </td></tr></table>\n<br>\n ");
    
    print("</td></tr><tr class=wbrd><td colspan=2 style=\"padding:6px;padding-left:30px;\"> <br>\n");
    print("<table class=notable1><tr><td><a href=\"pb.php?mode=new\" title=\"Создать новую политику\"><img src=\"icons/list-new.gif\" title=\"Создать новую политику\"></a> </td><td>\n");
    print("<a href=\"pb.php?mode=new\"> <font class=text42s><b>Создать новую политику</b></font></a> </td></tr></table>\n <br>\n");

    if(( isset($_SESSION["temp_pollist"])) and ( count($_SESSION["temp_pollist"])>0)) {
	print("</td></tr><tr class=wbrd><td colspan=2 style=\"padding:6px;padding-left:30px;\"> \n");
	print("<font class=text42s><b>Буфер сессии :</b></font><br>\n");
	print("<font style=\"FONT: normal 8pt Tahoma; color:330066;\"><b>*</b> временные записи обрабатываемых политик -  &#171черновики&#187</font><br> &nbsp \n");
	print("<table class=notable1 cellpadding=\"3px\">\n");
	foreach($_SESSION["temp_pollist"] as $tpkey => $tpvalue) {
	    $bufpolicy=str_replace("policy_tmp_","",$tpvalue);
	    print("<tr><td> policy_tmp_$tpvalue </td><td> <a href=\"pb.php?mode=edit&p=$bufpolicy&tmpmode=continue\" title=\"Продолжить редактирование\"><img src=\"icons/pb16.gif\" title=\"Продолжить редактирование\"></a> \n");
	    print(" <a href=\"pb.php?mode=cancledit&p=$bufpolicy&tmpmode=continue\" title=\"Удалить временную запись\"><img src=\"icons/cancel16.gif\" title=\"Удалить временную запись\"></a>  </td></tr>\n");
	}
	print("</table>\n");
    }

    print(" </td></tr> \n</table><br><br>\n");



}

#---------------------------------------------------------------------
function showMarkers()
{
    print("<br>\n<table class=notable><tr><td class=tdpadd> <a href=\"pb.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"pb.php\" title=\"Назад\">Назад</a> </td>\n");
    print("</tr></table>\n");
    print("<br><br>\n<font class=top1> Маркеры трафика, используемые политиками: </font><br><br>\n");
    print("<table class=table1 width=\"auto\" cellpadding=\"6px\"> \n");
    print("<tr><th> Маркер<br># </th><th> Политики </th></tr>\n ");
    $amarkers=getmarkers();
    foreach($amarkers as $amarker => $apols) {
	if( $amarker) {
	    print("<tr><td align=middle> <b>$amarker</b> </td><td> \n");
	    if( count($apols)>0) {
		print("<table class=notable>");
		foreach($apols as $apol => $atitle) print("<tr><td>\n<b>$apol</b> - </td><td> \n <i>\"$atitle\"</i> </td><td>\n <a href=\"pb.php?mode=view&p=$apol&edit=1&refmode=showmarkers\" title=\"Перейти к просмотру политики\"><img src=\"icons/eog16.gif\" title=\"Перейти к просмотру политики\"></a> </td></tr>\n");
	    }
	    print("</table>\n</td></tr>\n");
	}
    }
    print("</table>\n<br><br>\n");
    print("<br>\n<table class=notable><tr><td class=tdpadd> <a href=\"pb.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"pb.php\" title=\"Назад\">Назад</a> </td>\n");    
    
}
#---------------------------------------------------------------------

print("<html>\n");
$NoHeadEnd=FALSE;
require("include/head.php");


print("<body>\n");


if( $_mode=="edit") {
    
    $sess_policy=get_tmpname($_policy,$_tmpmode);

    if( $_tmpmode!="continue") {
	$_SESSION[$sess_policy]=policy2array($_policy,TRUE,TRUE);
    } 

    showPolicy($_mode);
    wlog("Редактирование политики $_policy.",0,FALSE,2,FALSE);
    exit;

} elseif( $_mode=="medit") {
    
    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }

    show_medit_form();
    wlog("Ручное редактирование политики $_policy.",0,FALSE,2,FALSE);
    exit;


} elseif(( $_mode=="subeditact") or ( $_mode=="subeditinout") or ( $_mode=="subeditmark") or ( $_mode=="subeditcount") or ( $_mode=="subeditipset") or ( $_mode=="subedittitle")) {
    
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    $_tmpmode="continue";
    if( $_tmpmode!="continue") {
	$_SESSION[$sess_policy]=policy2array($_policy,TRUE,TRUE);
	    
    } 
    
    showPolicy($_mode);
    exit;

} elseif( $_mode=="subitemadd") {

    $sess_policy=get_tmpname($_policy,$_tmpmode);
    $_tmpmode="continue";
    if( $_tmpmode!="continue") {
	$_SESSION[$sess_policy]=policy2array($_policy,TRUE,TRUE);
    } 

    showPolicy($_mode);
    exit;

} elseif( $_mode=="subeditcancl") {

    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);

    $_mode=( trim($_refmode)!="") ? $_refmode : $_mode;
    $_tmpmode="continue";
    $_edit=1;

    showPolicy($_mode);
    exit;

} elseif( $_mode=="subposdel") {

    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    if( trim($_sskey)=="") { wlog("Error! temp policy key is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    
    if( !array_key_exists(trim($_sskey),$_SESSION[$sess_policy])) {
	print("Указанный ключ для удаления записи не существует во временном массиве.");
    } else {
	unset($_SESSION[$sess_policy][trim($_sskey)]);
    }
    $_mode=( trim($_refmode)!="") ? $_refmode : $_mode;
    $_tmpmode="continue";

    showPolicy($_mode);
    exit;

} elseif(( $_mode=="subeditsave") or ( $_mode=="subaddsave")) {


    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    
    if( $_mode=="subaddsave") {
	$_sskey=count($_SESSION[$sess_policy]);
	$_SESSION[$sess_policy]=array_values($_SESSION[$sess_policy]);
	$_SESSION[$sess_policy][$_sskey]="";
	$_edit="1";
    }

    if( $subparam=="act") {
	if((( $sfact=="snat") or ( $sfact=="dnat")) and ( trim($sfactdst)=="")) {
	    print("При использовании $sfact должен указываться внешний адрес назначения..");
	} else {
	    $_SESSION[$sess_policy][$_sskey]="action ".$sfact.(( $sfact!="masquerade") ? " ".$sfactdst : "");
	}
    } elseif( $subparam=="inout") {
	if( trim($sfinout)=="") { 
	    print("Ошибка! Параметр in/out не может быть пуст!");
	} else {
	    $_SESSION[$sess_policy][$_sskey]=$sfinout." ".trim($sfinoutdst);
	}
    } elseif( $subparam=="mark") {
	if( trim($sfmark)=="") {
	    print("Ошибка! Пропущен параметр manglemark!");
	} else {
	    $_SESSION[$sess_policy][$_sskey]="manglemark ".trim($sfmark);
	}
    } elseif( $subparam=="count") {
	if( trim($sfcount)=="") {
	    print("Ошибка! Пропущен параметр count!");
	} else {
	    $_SESSION[$sess_policy][$_sskey]=trim($sfcount)." ".trim($sfcount_nolog);
	}
    } elseif( $subparam=="ipset") {
	if( trim($sfipsetmode)=="") {
	    print("Ошибка! Пропущен параметр blacklist или allow_only!");
	} else {
	    $_SESSION[$sess_policy][$_sskey]=trim($sfipsetmode)." ".trim($sfipsetname);
	}
    } elseif( $subparam=="title") {
	$_SESSION[$sess_policy][$_sskey]="title ".trim($sftitle);
    }

    $_mode=( trim($_refmode)!="") ? $_refmode : $_mode;
    $_tmpmode="continue";
    $_edit=1;

    showPolicy($_mode);
    exit;

} elseif( $_mode=="editrule") {
    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    if( trim($_index)=="") { wlog("Error! Not specified rule index number ...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    show_rule_form();
    exit;
    
} elseif( $_mode=="addrule") {
    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    show_rule_form();
    exit;

} elseif( $_mode=="delrule") {
    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    if( trim($_index)=="") { wlog("Error! Not specified rule index number ...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    sess_line_op(3);
    $_mode=( trim($_refmode)!="") ? $_refmode : $_mode;
    $_edit="1";
    $_tmpmode="continue";
    showPolicy($_mode);
    exit;

} elseif( $_mode=="editcanclrule") {
    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    $_mode=( trim($_refmode)!="") ? $_refmode : $_mode;
    $_tmpmode="continue";
    showPolicy($_mode);
    exit;

} elseif( $_mode=="editsaverule") {
    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    if( trim($_index)=="") { wlog("Error! Not specified rule index number ...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    
    sess_line_op(2);

    $_mode=( trim($_refmode)!="") ? $_refmode : $_mode;
    $_tmpmode="continue";

    showPolicy($_mode);
    exit;

} elseif( $_mode=="addsaverule") {
    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    
    sess_line_op(2);

    $_mode=( trim($_refmode)!="") ? $_refmode : $_mode;
    $_tmpmode="continue";

    showPolicy($_mode);
    exit;

} elseif( $_mode=="cancledit") {
    
    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);

    if( isset($_SESSION["temp_pmode_$_policy"])) unset($_SESSION["temp_pmode_$_policy"]);
    if( in_array($_policy,$_SESSION["temp_pollist"])) unset($_SESSION["temp_pollist"][array_search($_policy,$_SESSION["temp_pollist"])]);

    if( isset($_SESSION[$sess_policy])) {
	unset($_SESSION[$sess_policy]);
    }
    wlog("Отмена редактирования политики $_policy.",0,FALSE,2,FALSE);

} elseif( $_mode=="applyedit") {

    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    $sess_policy=get_tmpname($_policy,$_tmpmode);
    
    pb_save_policy($_policy);
    
    if( isset($_SESSION[$sess_policy])) {
	unset($_SESSION[$sess_policy]);
    }
    wlog("Сохранение политики $_policy.",0,FALSE,1,FALSE);
    
} elseif(( $_mode=="meditsave") or ( $_mode=="mnewsave")) {

    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    
    pb_save_policy($_policy,TRUE);
    
    wlog("Сохранение политики $_policy, режим mode $_mode.",0,FALSE,1,FALSE);

} elseif( $_mode=="del") {

    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    if( isset($aa0)) unset($aa0);
    $aa0=get_policy_users($_policy);
    if( count($aa0)>0) { 
	wlog("Политика используется! Для удаления необходимо сначала отменить действие политики у клиентов.",2,TRUE,4,TRUE);
	exit;
    }
    
    pb_del_policy($_policy);
    wlog("Удаление политики $_policy.",0,FALSE,1,FALSE);
    show_load("pb.php?mode=pollist&f=$_f&s=1","Чтение списка политик...");
    

} elseif( $_mode=="new") {
    
    if( trim($_policy)=="") {
	show_polname_form();
	exit;
    } else {

	if( isset($apols)) unset($apols);
	$apols=load_policies_list();
	if( array_key_exists($_policy,$apols)) {
	    print("<br><br><font class=error>Указанное имя политики уже существует!</font><br><br><br>\n");
	    print("<table class=notable><tr><td> <a href=\"pb.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"pb.php\" title=\"Назад\">Назад</a> </td></tr></table>\n");
	    print("<br>\n");
	    print("<table class=notable><tr><td> <a href=\"pb.php?mode=new\" title=\"Продолжить\"><img src=\"icons/redo.gif\" title=\"Продолжить\"></a> </td><td> <a href=\"pb.php?mode=new\" title=\"Продолжить\">Продолжить</a> </td></tr></table>\n");
	    exit;
	} else {
	    if(( trim($_way)=="") or ( trim($_way)=="pb")) {
		$sess_policy=get_tmpname($_policy,$_tmpmode);
		$_SESSION[$sess_policy][count($_SESSION[$sess_policy])]="title \" \"";
		$_mode="edit";
		showPolicy($_mode);
	    } elseif( trim($_way)=="man") {
		$_mode="mnew";
		show_medit_form();
	    }
	    exit;
	}
    }
    wlog("Создание новой политики с идентификатором $_policy, режимы: mode=$_mode way=$_way.",0,FALSE,1,FALSE);

} elseif( $_mode=="pollist") {

    if( !$show) {
	show_load("pb.php?mode=pollist&f=$_f&s=1","Чтение списка политик...");
    } else {
	if( !isset($apols)) $apols=load_policies_list();
	$_f=( trim($_f)=="") ? $pollist_fmode_default : $_f;
	show_policies_list($apols,$_f);
	wlog("Просмотр списка политик.",0,FALSE,1,FALSE);
	exit;
    }

} elseif( $_mode=="view") {

    if( trim($_policy)=="") { wlog("Error! Policy is not specified for this operation...<br> \n",2,TRUE,5,TRUE); exit; }
    view_policy($_policy);
    wlog("Просмотр конфигурации политики $_policy.",0,FALSE,1,FALSE);
    exit;

} elseif( $_mode=="showmarkers") {

    if( !$show) {
	show_load("pb.php?mode=showmarkers&s=1","Построение списка соответствий...");
    } else {
	showMarkers();
	wlog("Просмотр списка используемых маркеров.",0,FALSE,1,FALSE);
	exit;
    }
    
} elseif( $_mode=="psearch") {
    
    if(( trim($_pstr)=="") and ( trim($_direction)=="") and ( trim($_used)=="")) {
	showPanel(); 
    } else {
	wlog("Выполнение поиска политик по условиям.",0,FALSE,1,FALSE);
	find_policies();
    }
    exit;

}

wlog("Просмотр панели политик.",0,FALSE,1,FALSE);
if( !$show) {
    show_load("pb.php?s=1","Обработка данных...");
} else {
    showPanel();
}


print("</body></html>\n");


?>