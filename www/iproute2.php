<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.6
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: iproute2.php
# Description: 
# Version: 0.2.4.6
###


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
require("auth.php");

$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"]:"");
$_zone=( isset($_GET["zone"])) ? $_GET["zone"] : (( isset($_POST["zone"])) ? $_POST["zone"]:"");
$_zlines=( isset($_GET["zlines"])) ? $_GET["zlines"] : (( isset($_POST["zlines"])) ? $_POST["zlines"]:"");
$_rtbid=( isset($_GET["rtbid"])) ? $_GET["rtbid"] : "";
$_rtbname=( isset($_GET["rtbname"])) ? $_GET["rtbname"] : "";
$_func=( isset($_GET["f"])) ? $_GET["f"] : "";
$_p=( isset($_GET["p"])) ? $_GET["p"] : "";
$_optroutabled=( isset($_GET["optrtbled"])) ? (( $_GET["optrtbled"]=="1") ? TRUE:FALSE):FALSE;
$_optinit=( isset($_GET["optinit"])) ? $_GET["optinit"] : "";

$self="iproute2.php";


#-------------------------------------------------------------------------
function get_rtbid($pmode=1)
{
    global $iproute_dir;
    if( !is_readable("$iproute_dir/rt_tables")) {
	wlog("Error read accessing to file $iproute_dir/rt_tables in get_rtbid().",2,TRUE,4,TRUE); exit;
    }
    $aart=file("$iproute_dir/rt_tables");
    $maxid="200";
    $aaid=array();
    $aatb=array();
    foreach($aart as &$aavalue) {
	if( trim($aavalue)=="") continue;
	if( $aavalue[0]=="#") continue;
	if( !$bufid=trim(gettok($aavalue,1," \t"))) continue;
	if( !$bufname=trim(gettok($aavalue,2," \t"))) continue;
	if(( $bufid=="255") or ( $bufid=="254") or ( $bufid=="253")) {
	    continue;
	} else {
	    $maxid=( $maxid<$bufid) ? $bufid:$maxid;
	    $aaid[count($aaid)]=$bufid;
	    $aatb[$bufid]=array("id" => $bufid, "name" => $bufname);
	}
    }
    if( $pmode==1) {
	return($maxid+1);
    } elseif( $pmode==2) {
	return($aaid);
    } elseif( $pmode==3) {
	return($aatb);
    }
}
#-------------------------------------------------------------------------
function view_rtb()
{
    global $_mode,$_rtbname;
    global $_sudo,$_ip,$self;
    list($rr,$aaout)=_exec2("$_ip route list table $_rtbname"); 
    if( $rr>0) {
	wlog("Error quering routing table name $_rtbname in view_rtb()!",2,TRUE,5,TRUE); exit;
    }
    print("<br><br>\n<font class=top1>Таблица маршрутизации $_rtbname</font>\n<br><br>\n");
    print("<table class=table5 cellpadding=\"4px\">\n");
    foreach($aaout as $aaitem) print("<tr><td class=td41ye> $aaitem </td></tr>\n");
    print("</table>\n<br><br>\n");
    print("<table class=notable><tr><td> <a href=\"$self\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"$self\" title=\"Назад (без сохранения)\">Назад</a>  \n");    
}
#-------------------------------------------------------------------------
function save_opt()
{
    global $_mode,$_optroutabled,$_optinit;
    global $iproute_dir,$iptconf_dir,$_sudo,$_echo;

}
#-------------------------------------------------------------------------
function save_rtb()
{
    global $_mode,$_rtbid,$_rtbname;
    global $iproute_dir,$_sudo,$_echo;
    if( $_mode=="addrtbsave") {
	if( trim($_rtbname)=="") {
	    wlog("Routing table name cannot be blank!",2,TRUE,4,TRUE); exit;
	}
	if( in_array($_rtbid,get_rtbid(2))) { 
	    wlog("Specified routing table id is allready exists!",2,TRUE,5,TRUE); exit;
	}
	$line="$_echo -e \"$_rtbid\t$_rtbname\" | $_sudo tee -a $iproute_dir/rt_tables  ";
	list($rr,$aaout)=_exec2($line,TRUE);
	
	if( $rr>0) {
	    wlog("Error saving routing table $_rtbname..",2,TRUE,5,TRUE); exit;
	}
    } elseif( $_mode=="editrtbsave") {
	if( trim($_rtbname)=="") {
	    wlog("Routing table name cannot be blank!",2,TRUE,4,TRUE); exit;
	}
	if( !in_array($_rtbid,get_rtbid(2))) { 
	    wlog("Specified routing table id is not found!",2,TRUE,5,TRUE); exit;
	}
	if( file_exists($iproute_dir."/rt_tables.bak")) _exec2("rm -f $iproute_dir/rt_tables.bak");
	list($rr,$aaout)=_exec2("cp -f -T $iproute_dir/rt_tables $iproute_dir/rt_tables.bak");
	if( $rr>0) {
	    wlog("Error backuping file rt_tables in save_rtb() mode edit..",2,TRUE,5,TRUE); exit;
	}
	unset($aaout);
	$line="awk -v rtbid=\"$_rtbid\" -v rtbname=\"$_rtbname\" '{ if( $1==rtbid) { print($1\"\t\"rtbname); } else { print($0); } }' $iproute_dir/rt_tables | $_sudo tee $iproute_dir/rt_tables";
	list($rr,$aaout)=_exec2($line,TRUE);
	if( $rr>0) {
	    wlog("Error saving routing table $_rtbname..",2,TRUE,5,TRUE); exit;
	}

    } elseif( $_mode=="delrtb") {
	if( trim($_rtbid)=="") {
	    wlog("Routing table id cannot be blank!",2,TRUE,4,TRUE); exit;
	}
	if( !in_array($_rtbid,get_rtbid(2))) { 
	    wlog("Routing table id $_rtbid is not found!",2,TRUE,5,TRUE); exit;
	}
	if( file_exists($iproute_dir."/rt_tables.bak")) _exec2("rm -f $iproute_dir/rt_tables.bak");
	list($rr,$aaout)=_exec2("cp -f -T $iproute_dir/rt_tables $iproute_dir/rt_tables.bak");
	if( $rr>0) {
	    wlog("Error backuping file rt_tables in save_rtb() mode del..",2,TRUE,5,TRUE); exit;
	}
	unset($aaout);
	$line="awk -v rtbid=\"$_rtbid\" '{ if( $1!=rtbid) { print($0); } }' $iproute_dir/rt_tables | $_sudo tee $iproute_dir/rt_tables";
	list($rr,$aaout)=_exec2($line,TRUE);
	if( $rr>0) {
	    wlog("Error deleting routing table $_rtbid..",2,TRUE,5,TRUE); exit;
	}

    }
	
    
}
#-------------------------------------------------------------------------
function show_rtb_form()
{
    global $self,$_mode,$_rtbid;
    print("<br><br>\n<font class=top1>".(( $_mode=="addrtb") ? "Добавление":"Редактирование")." таблицы маршрутизации</font>\n<br><br>\n");
    if( $_mode=="editrtb") {
	if( !array_key_exists($_rtbid,$aatb=get_rtbid(3))) {
	    wlog("Таблица маршрутизации с номером $_rtbid не существует!",2,TRUE,5,TRUE); exit;
	}
    }
    print("<table class=table4 cellpadding=\"3px\">\n");
    print("<form name=\"addrtb1\" id=\"addrtb1\" action=\"$self\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" value=\"".$_mode."save\">\n");
    print("<tr><td> Номер: </td><td> <input type=\"TEXT\" name=\"rtbid\" id=\"rtbid\" size=8 value=\"".(( $_mode=="addrtb") ? get_rtbid():$_rtbid)."\"> </td></tr>\n");
    print("<tr><td> Имя: </td><td> <input type=\"TEXT\" name=\"rtbname\" size=15 value=\"".(( $_mode=="addrtb") ? "":$aatb[$_rtbid]["name"])."\"> </td></tr>\n");
    print("<tr><td colspan=2 align=right> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Сохранить\"> </td></tr>\n ");
    print("</form>\n</table>\n<br><br>\n");
    print("<div class=text4 style=\"width:380px;\"><b>*</b> При добавлении или изменении таблиц результаты становятся доступны только после перезагрузки сервера.</div><br>\n");
    if( $_mode=="editrtb") print("<script type=\"text/javascript\">\n document.getElementById('rtbid').disabled = true; \n </script>\n");
    print("<br><br>\n");
    print("<table class=notable><tr><td> <a href=\"$self\" title=\"Назад (без сохранения)\"><img src=\"icons/gtk-undo.gif\" title=\"Назад (без сохранения)\"></a> </td><td> <a href=\"$self\" title=\"Назад (без сохранения)\">Назад</a>  \n");
}
#-------------------------------------------------------------------------
function show_init_form()
{
    global $iproute_dir,$iptconf_dir,$self;
    global $iproutabled,$iproute_init;
    global $_sudo,$_grep;
    
    if( !$iproutabled) {
	print("Работа с iproute2 отключена в конфигурации..."); exit;
    }
    
}
#-------------------------------------------------------------------------
function init_del_block($zone)
{
    global $iproute_dir,$iptconf_dir,$self;
    global $iproutabled,$iproute_init;
    global $_sudo,$_grep,$_zlines;
    
    if( !$iproutabled) return("");
    if( !trim($zone)) return("");
    if( trim($zone)=="rules_zone") return(FALSE);
    if( !file_exists($iproute_init)) return("");
    list($rr,$ainit)=_exec2("cat $iproute_init");
    if(( $rr>0) || ( count($ainit)==0)) return("");
    
    $flopen=FALSE;
    $alines=array();
    $alines[]="[ -f $iproute_init ] && $_sudo mv $iproute_init $iproute_init.bak";
    foreach($ainit as $akey => $line) {
	$word1=gettok($line,1," \t");
	$word2=gettok($line,2," \t");
	if( !$flopen) {
	    if($word1=="###" && ( $word2=="table")) if( trim( $word3=gettok($line,3," \t"))==trim($zone)) {
		$flopen=TRUE;
		continue;
	    }
	    $alines[]="$_sudo echo \"$line\" >> $iproute_init";
	    continue;
	} else {
	    if( trim($line)=="######### end zone") {
		$flopen=FALSE;
	    }
	}
    }
    _exec2($alines,TRUE,TRUE);
}
#-------------------------------------------------------------------------
function init_save_block($zone,$flnew=FALSE)
{
    global $iproute_dir,$iptconf_dir,$self;
    global $iproutabled,$iproute_init;
    global $_sudo,$_grep,$_zlines,$_echo;
    
    if( !$iproutabled) return("");
    if( !trim($zone)) return("");
    if( !file_exists($iproute_init)) return("");
    list($rr,$ainit)=_exec2("cat $iproute_init");
    if(( $rr>0) || ( count($ainit)==0)) return("");
    
    $flopen=FALSE;
    $azln=explode("\n",$_zlines);
    $alines=array();
    $alines[]="[ -f $iproute_init ] && $_sudo mv $iproute_init $iproute_init.bak";
    if( $flnew) {
	$alines[]="$_sudo $_echo \"### table $zone zone\" >> $iproute_init";
	$alines[]="$_sudo $_echo \"\" >> $iproute_init";
	$alines[]="$_sudo $_echo \"######### end zone\" >> $iproute_init";
    }
    foreach($ainit as $akey => $line) {
	$word1=gettok($line,1," \t");
	$word2=gettok($line,2," \t");
	if( !$flopen) {
	    if($word1=="###" && ( $word2=="table")) if( trim( $word3=gettok($line,3," \t"))==trim($zone)) {
		$flopen=TRUE;
	    }
	    if(( $zone=="rules_zone") && ( trim($line)=="### rules zone")) {
		$flopen=TRUE;
	    }
	    if(( $zone=="at_startup_zone") && ( trim($line)=="### at_startup zone")) {
		$flopen=TRUE;
	    }
	    if(( $zone=="at_down_zone") && ( trim($line)=="### at_down zone")) {
		$flopen=TRUE;
	    }
	    $alines[]="$_sudo $_echo \"$line\" >> $iproute_init";
	    continue;
	} else {
	    if( isset($azln)) {
		foreach($azln as $zline) $alines[]="$_sudo $_echo \"".trim($zline)."\" >> $iproute_init";
		unset($azln);
	    }
	    if( trim($line)=="######### end zone") {
		$flopen=FALSE;
		$alines[]="$_sudo echo \"$line\" >> $iproute_init";
		continue;
	    }
	}
    }
    _exec2($alines,TRUE,TRUE);
}
#-------------------------------------------------------------------------
function show_init_blocks()
{
    global $iproute_dir,$iptconf_dir,$self;
    global $iproutabled,$iproute_init;
    global $_sudo,$_grep;
    
    if( !$iproutabled) return("");
    if( !file_exists($iproute_init)) return("");
    $ablocks=array();
    list($rr,$ainit)=_exec2("cat $iproute_init");
    if(( $rr>0) || ( count($ainit)==0)) return("");
    
    $currzone="";
    foreach($ainit as $akey => $line) {
	if( !trim($line)) continue;
	$word1=gettok($line,1," \t");
	$word2=gettok($line,2," \t");
	if(( $word1=="###") && ( $word2=="table")) {
	    if( trim( $word3=gettok($line,3," \t"))!="") {
		$ablocks[$word3]=array( "name" => $word3, "type" => "table","flopen" => TRUE);
		$currzone=$word3;
	    }
	}
	if(( $word1=="###") && ( $word2=="rules")) {
	    $ablocks["rules_zone"]=array( "name" => "rules zone", "type" => "rules","flopen" => TRUE);
	    $currzone="rules_zone";
	}
	if(( $word1=="###") && ( $word2=="at_startup")) {
	    $ablocks["at_startup_zone"]=array( "name" => "at_startup zone", "type" => "startup","flopen" => TRUE);
	    $currzone="at_startup_zone";
	}
	if(( $word1=="###") && ( $word2=="at_down")) {
	    $ablocks["at_down_zone"]=array( "name" => "at_down zone", "type" => "startup","flopen" => TRUE);
	    $currzone="at_down_zone";
	}
	if( trim($line)=="######### end zone") {
	    $ablocks[$currzone]["flopen"]=FALSE;
	    continue;
	}
    }
    print("<div align=left><font class=text32b>Доступные секции конфига: </font></div>\n");
    print("<table class=table5e cellpadding=\"3px\" width=\"100%\">\n");
    print("<form name=\"zoneditfrm\" id=\"zoneditfrm\" actions=\"iptoute2.php\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" value=\"init_editzone\">\n");
    foreach($ablocks as $akey => $azone) print("<tr><td> <input type=\"RADIO\" name=\"zone\" id=\"id_".$akey."\" value=\"".$akey."\"> <label for=\"id_".$akey."\">".$azone["name"]."</label> </td></tr>\n");
    print("<tr><td align=right> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Редактировать\"> </td></tr>\n");
    print("</table>\n");
}
#-------------------------------------------------------------------------
function show_edit_block($zone)
{
    global $iproute_dir,$iptconf_dir,$self;
    global $iproutabled,$iproute_init;
    global $_sudo,$_grep;
    
    if( !$iproutabled) return("");
    if( !trim($zone)) return("");
    if( !file_exists($iproute_init)) return("");
    $ablocks=array();
    list($rr,$ainit)=_exec2("cat $iproute_init");
    if(( $rr>0) || ( count($ainit)==0)) return("");
    
    $flopen=FALSE;
    $alines=array();
    foreach($ainit as $akey => $line) {
	if( !trim($line)) continue;
	$word1=gettok($line,1," \t");
	$word2=gettok($line,2," \t");
	if( !$flopen) {
	    if($word1=="###" && ( $word2=="table")) if( trim( $word3=gettok($line,3," \t"))==trim($zone)) {
		$flopen=TRUE;
		continue;
	    }
	    if(( $zone=="rules_zone") && ( trim($line)=="### rules zone")) {
		$flopen=TRUE;
		continue;
	    }
	    if(( $zone=="at_startup_zone") && ( trim($line)=="### at_startup zone")) {
		$flopen=TRUE;
		continue;
	    }
	    if(( $zone=="at_down_zone") && ( trim($line)=="### at_down zone")) {
		$flopen=TRUE;
		continue;
	    }
	} else {
	    if( trim($line)=="######### end zone") {
		$flopen=FALSE;
		break;
	    } else {
		$alines[]=$line;
	    }
	}
    }
    print("<br><br><br>\n");
    print("<table class=table5e cellpadding=\"1px\">\n");
    print("<form name=\"zoneditfrm\" name=\"zoneditfrm\" method=\"POST\" action=\"iproute2.php\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" value=\"init_savezone\">\n");
    print("<input type=\"HIDDEN\" name=\"zone\" value=\"$zone\">\n");
    print("<tr><td style=\"color:330066\"> Редактирование секции конфига $zone: </td></tr>\n");
    print("<tr><td> <textarea rows=\"5\" cols=\"70\" name=\"zlines\">\n");
    foreach($alines as $line) print(trim($line)."\n");
    print("</textarea></td></tr>\n");
    print("<tr><td> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Сохранить\"> </td></tr>\n");
    print("</form>\n");
    print("</table>\n");
    print("<br><br><br>\n");
    print("<table class=notable><tr><td> <a href=\"iproute2.php\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"iproute2.php\" title=\"Назад\">Назад</a> </td></tr></table>\n");
    exit;
}
#-------------------------------------------------------------------------
function show_panel()
{
    global $iproute_dir,$iptconf_dir,$self;
    global $iproutabled,$iproute_init;
    global $_sudo,$_grep,$_mode;
    
    print("<br>\n");
    print("<font class=top3>Iproute2</font>\n<br>\n");
    print("<hr size=1 align=left width=\"95%\"><br>\n");
    if( !is_readable("$iproute_dir/rt_tables")) {
	wlog("Error read accessing to file $iproute_dir/rt_tables in show_panel().",2,TRUE,4,TRUE); exit;
    }
    $aart=file("$iproute_dir/rt_tables");

    print("<table class=notable width=\"95%\" cellpadding=\"3px\">\n<tr>\n");
    
    print("<td colspan=2 width=\"100%\" style=\"padding-left:15px; padding-right:10px; padding-top:10px; padding-bottom:20px; border-bottom-width:1px; border-bottom-style:solid; border-bottom-color:A6CAF0;\">");

    if( $_mode!="opt") {
	$class1="td41ye0";
	$class2="td42c";
	print("<div style=\"float:left;\"><font class=text32b>Настройки:</font></div>\n");
	print("<span style=\"padding:1px;margin-left:130px;\">\n");
	print("<a href=\"$self?mode=opt\" title=\"Редактировать настройки\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать настройки\"></a>\n");
	print("</span>");
    } else {
	$class1="td41ye0";
	$class2="td42c";

	print("<font class=text32b>Настройки:</font>\n");	
    }
    print("<br>\n");
    $inpadd=" style=\"padding:4px;\"";

    
    if( $_mode=="opt") {
	print("<form name=\"opt1\" action=\"$self\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"optsave\" />\n");
    }
    print("<table class=table5 cellpadding=\"2px\" width=\"100%\">\n");
    print("<tr><td class=$class1 $inpadd width=\"50%\"> Загружать правила iproute2<br> при процедуре RELOAD и при старте: </td>\n");
    print("<td class=$class2 $inpadd width=\"50%\"> \n");
    if( $_mode=="opt") {
	print("<span class=seldiv><SELECT name=\"optrtbled\" id=\"optrtbled\">\n");
	print("<option value=\"1\" ".(( $iproutabled) ? " SELECTED":"").">Да</option>\n");
	print("<option value=\"0\" ".(( !$iproutabled) ? " SELECTED":"").">Нет</option>\n");
	print("</SELECT></span>\n");
    } else {
	print("<b>".(( $iproutabled) ? "Да":"Нет")."</b>");
    }
    print("</td></tr>\n");
    print("<tr><td class=$class1 $inpadd width=\"50%\"> Конфигурационный скрипт iproute2: </td>\n");
    print("<td class=$class2 $inpadd width=\"50%\"> \n");
    if( $_mode=="opt") {
	print("<input type=\"TEXT\" name=\"optinit\" size=45 value=\"$iproute_init\" />");
    } else {
	print((( is_readable($iproute_init)) ? "$iproute_init":"отсутствует")." ".genframe($iproute_init));
    }
    print("</td></tr>\n");
    print("</table>\n");
    print("<br>\n");
    if( $_mode=="opt") {
	print("<table class=notable width=\"550px\"><tr>\n");
	print("<td width=\"50%\" align=left>\n <a href=\"$self\" title=\"Назад (без сохранения)\"><img src=\"icons/gtk-undo.gif\" title=\"Назад (без сохранения)\"> Назад</a></td>\n");
	print("<td width=\"50%\" align=right>\n <input type=\"SUBMIT\" name=\"sbmt2\" value=\" Ok \"></td>\n");
	print("</tr></table>\n");
	print("</form>\n");
    }
    

    print("</td></tr><tr>\n");

    print("<td valign=top align=left width=\"50%\" style=\"padding-left:15px; padding-top:25px; padding-bottom:10px; padding-right:10px; border-right-width:1px; border-right-style:solid; border-right-color:A6CAF0;\">");

    print("<div style=\"float:left;\"><font class=text32b>Таблицы маршрутизации:</font></div>\n");
    print("<span style=\"padding:1px;margin-left:20px;\">\n");
    print("<a href=\"$self?mode=addrtb\" title=\"Добавить таблицу маршрутизации\"><img src=\"icons/list-new16.gif\" title=\"Добавить таблицу маршрутизации\"></a>\n");
    print("</span>");
    print("<br>\n");
    print("<table class=table5 cellpadding=\"2px\" width=\"100%\">\n");
    print("<tr><th class=thpol> Номер </th><th class=thpol> Имя </th><th class=thpol> ! </th></tr>\n");
    foreach($aart as &$aavalue) {
	if( trim($aavalue)=="") continue;
	if( $aavalue[0]=="#") continue;
	if( !$bufid=trim(gettok($aavalue,1," \t"))) continue;
	if( !$bufname=trim(gettok($aavalue,2," \t"))) continue;
	print("<tr>\n");
	print("<td class=td41ye> $bufid </td><td class=td41ye> $bufname </td>");
	
	if(( $bufid=="255") or ( $bufid=="254") or ( $bufid=="253")) {
	    print("<td class=td41ye align=middle>\n");
	} else {
	    print("<td class=td41ye align=middle> ");
	    print("<a href=\"$self?mode=editrtb&rtbid=$bufid\" title=\"Редактировать таблицу маршрутизации\"><img src=\"icons/gtk-edit16.gif\" title=\"Редактировать таблицу маршрутизации\"></a> ");
	    print("<a href=\"$self?mode=delrtb&rtbid=$bufid&rtbname=$bufname\" title=\"Удалить таблицу маршрутизации\"><img src=\"icons/cancel16.gif\" title=\"Удалить таблицу маршрутизации\"></a> ");
	}
	print("<a href=\"$self?mode=viewrtb&rtbname=$bufname\" title=\"Просмотр таблицы маршрутизации\"><img src=\"icons/eog16.gif\" title=\"Просмотр таблицы маршрутизации\"></a> ");
	print("</td>");
	print("</tr>");
    }
    print("</table>\n");
    print("<div class=text4 style=\"width:380px;\"><b>*</b> При добавлении или изменении таблиц результаты становятся доступны только после перезагрузки сервера.</div><br>\n");
    print("<br>\n");

    print("</td><td valign=top align=right width=\"50%\" style=\"padding-left:15px; padding-top:25px; padding-bottom:10px; padding-right:10px;\">");

    show_init_blocks();
    
    print("</td></tr></table>\n");

}

#-------------------------------------------------------------------------



print("<html>\n");
require("include/head1.php");
print("<body>\n");
if( $_mode=="addrtb") {

    show_rtb_form();
    exit;

} elseif( $_mode=="editrtb") {
    
    if( trim($_rtbid)=="") {
	wlog("Routing table id is missing..",2,TRUE,4,TRUE); exit;
    }

    show_rtb_form();
    exit;

} elseif( $_mode=="delrtb") {
    
    if( trim($_rtbid)=="") {
	wlog("Routing table id is missing..",2,TRUE,4,TRUE); exit;
    }
    save_rtb();
    init_del_block($_rtbname);
    wlog("Удаление таблицы маршрутизации $_rtbid..",0,FALSE,1,FALSE);

} elseif( $_mode=="viewrtb") {
    
    if( trim($_rtbname)=="") {
	wlog("Routing table name is missing..",2,TRUE,4,TRUE); exit;
    }
    view_rtb();
    wlog("Просмотр таблицы маршрутизации $_rtbname..",0,FALSE,1,FALSE);
    exit;

} elseif( $_mode=="addrtbsave") {
    save_rtb();
    init_save_block($_rtbname,TRUE);
    wlog("Добавление таблицы маршрутизации id:$_rtbid $_rtbname..",0,FALSE,1,FALSE);

} elseif( $_mode=="optsave") {
    $aaopt=array();
    $aaopt["|nostr|iproutabled"]=toster($_optroutabled);
    if( $iproutabled!=$_optroutabled) $iproutabled=$_optroutabled;
    $aaopt["iproute_init"]=$_optinit;
    if( !options_save($aaopt)) {
	wlog("Ошибка сохранения настроек iproute2..",2,TRUE,5,TRUE);
    } else {
	wlog("Сохранение настроек iproute2..",0,FALSE,1,FALSE);
    }

} elseif( $_mode=="editrtbsave") {
    save_rtb();
    wlog("Редактирование таблицы маршрутизации id:$_rtbid $_rtbname..",0,FALSE,1,FALSE);

} elseif( $_mode=="init_editzone") {
    show_edit_block($_zone);

} elseif( $_mode=="init_savezone") {
    init_save_block($_zone);

} elseif( $_mode=="initedit") {
    show_init_form();
    exit;

}

show_panel();
wlog("Просмотр панели iproute2..",0,FALSE,1,FALSE);


?>
</body>
</html>
