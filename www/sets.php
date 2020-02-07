<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: sets.php
# Description: 
# Version: 2.8.2
###



require("./../config.php");
require("iptlib.php");
require("iptlib2.php");
require("setlib.php");

$flAdminsOnly=TRUE;
require("auth.php");

$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"] : "");
$_set=( isset($_GET["set"])) ? $_GET["set"] : (( isset($_POST["set"])) ? $_POST["set"] : "");
$_pset=( isset($_GET["pset"])) ? $_GET["pset"] : (( isset($_POST["pset"])) ? $_POST["pset"] : "");
$_addr=( isset($_GET["addr"])) ? $_GET["addr"] : (( isset($_POST["addr"])) ? $_POST["addr"] : "");
$_settype=( isset($_GET["settype"])) ? $_GET["settype"] : (( isset($_POST["settype"])) ? $_POST["settype"] : "");
$_p1name=( isset($_GET["p1name"])) ? $_GET["p1name"] : (( isset($_POST["p1name"])) ? $_POST["p1name"] : "");
$_p2name=( isset($_GET["p2name"])) ? $_GET["p2name"] : (( isset($_POST["p2name"])) ? $_POST["p2name"] : "");
$_p1value=( isset($_GET["p1value"])) ? $_GET["p1value"] : (( isset($_POST["p1value"])) ? $_POST["p1value"] : "");
$_p2value=( isset($_GET["p2value"])) ? $_GET["p2value"] : (( isset($_POST["p2value"])) ? $_POST["p2value"] : "");
$_hashsize=( isset($_GET["hashsize"])) ? $_GET["hashsize"] : (( isset($_POST["hashsize"])) ? $_POST["hashsize"] : "");
$_probes=( isset($_GET["probes"])) ? $_GET["probes"] : (( isset($_POST["probes"])) ? $_POST["probes"] : "");
$_resize=( isset($_GET["resize"])) ? $_GET["resize"] : (( isset($_POST["resize"])) ? $_POST["resize"] : "");
$_timeout=( isset($_GET["timeout"])) ? $_GET["timeout"] : (( isset($_POST["timeout"])) ? $_POST["timeout"] : "");
$_size=( isset($_GET["size"])) ? $_GET["size"] : (( isset($_POST["size"])) ? $_POST["size"] : "");
$_qptr=( isset($_GET["qptr"])) ? $_GET["qptr"] : (( isset($_POST["qptr"])) ? $_POST["qptr"] : "");
$_run=( isset($_GET["run"])) ? $_GET["run"] : (( isset($_POST["run"])) ? $_POST["run"] : "");
$_refer=( isset($_GET["ref"])) ? $_GET["ref"] : (( isset($_POST["ref"])) ? $_POST["ref"] : "");
$_show=( isset($_GET["s"])) ? TRUE : (( isset($_POST["s"])) ? TRUE:FALSE);

$NoHeadEnd=TRUE;
require("include/head1.php");
if($_mode=="set_addto") {
    $_line0="<base target=_self>\n</head>\n<body>\n";
} else {
    $_line0="</head>\n<body>\n";
}
print($_line0);


#-------------------------------------------------------------------------

function calc_pol_uses($pset)
{
    $aa1=load_policies_list();
    $acol=0;
    foreach( $aa1 as $akk => $avv) {
	
	if( trim($akk)=="") continue;
	$buf1=get_policy_param($akk,"blacklist","",1,FALSE);
	foreach($buf1 as $bkk => $bvv) {
	    if( trim($bvv)==$pset) $acol++;
	}
	unset($buf1);
	unset($bkk); unset($bvv);
	$buf1=get_policy_param($akk,"allow_only","",1,FALSE);
	foreach($buf1 as $bkk => $bvv) {
	    if( trim($bvv)==$pset) $acol++;
	}
	unset($buf1);
    }
    return($acol);

}

#-------------------------------------------------------------------------



function show_set($pset)
{
    global $iptconf_dir,$sets_dir,$_ipset,$_iptables;
    if( trim($pset)=="") exit;
    $flViewOnly=( $pset=="locals") ? TRUE : FALSE;
    list($rv,$aout)=_exec2("$_ipset -nL $pset | grep Type");
    if( $rv>0 ) { wlog("Ошибка проверки сетлиста $pset в show_set()...<br>debug: pset .$pset. rv .$rv.",2,TRUE,5,TRUE); exit; }
    if( substr_count($rrez,"Unknown")>0 ) { 
	$flLoaded=FALSE;
	wlog("Ошибка: сетлист $pset не загружен в память ОС",2,TRUE,5,TRUE); exit; 
    } else {
	$flLoaded=TRUE;
    }
    unset($aout); unset($rrez); unset($rv);
    list($rv,$aout)=_exec2("$_ipset -nL $pset");
    if( $rv>0 ) { wlog("Error running sudo-ipset in sets.php show_set()...",2,TRUE,5,TRUE); exit; }
    
    print("<div style=\"padding-left:35px\">\n");
    
    print("<br><br><br>\n<div class=top1 style=\"padding-left:30px\">Сет-лист &nbsp&nbsp <i>$pset</i></div><br><br>\n");
    print("<table class=table5 width=\"400px\" cellpadding=\"6px\">\n ");
    print("<tr><td class=td33i align=right>Состояние:</td><td class=td42> ".(( !$flLoaded) ? "<font style=\"color:maroon\">Не загружен</font>" : "<font style=\"color:teal\">Загружен</font>")."</td></tr>\n");
    
    $fldel=FALSE;
    $flin=FALSE; 
    foreach($aout as $akk => $avv) {
	$buf1=trim(gettok($avv,1,":"));
	if( $buf1=="Members") {
	    print("\n<tr><td class=td33i align=right>В списке:</td> \n");
	    $iipos=0;
	    $flin=TRUE;
	    continue;
	} elseif( $buf1=="Bindings") {
	    $fldel=( $iipos==0) ? TRUE : FALSE;
	    print("<td class=td42> $iipos элемент(ов) <a href=\"sets.php?set=$pset&mode=listview#members\">открыть&#8594</a></td></tr>  \n");
	    //print("<tr><td>&nbsp</td><td class=text41t><a href=\"sets.php?set=$pset&mode=showfull\">Показать полный список</a> </td></tr>\n");
	    print("<tr><td class=td33i align=right>Биндинги:</td>\n");
	    $iipos=0;
	    $flin=TRUE;
	    continue;
	} else {
	    if( !$flin) {
		$line="";
		$line=( $buf1=="Name") ? "<tr><td class=td33i align=right>Имя:</td><td class=td42> ".trim(gettok($avv,2,":"))."</td></tr>\n" : $line;
		$line=( $buf1=="Type") ? "<tr><td class=td33i align=right>Тип:</td><td class=td42> ".trim(gettok($avv,2,":"))."</td></tr>\n" : $line;
		$line=( $buf1=="References") ? "<tr><td class=td33i align=right>Ссылок:</td><td class=td42> ".trim(gettok($avv,2,":"))."</td></tr>\n" : $line;
		$line=( $buf1=="Default binding") ? "<tr><td class=td33i align=right>Бинд по-умолчанию:</td><td class=td42> ".trim(gettok($avv,2,":"))."</td></tr>\n" : $line;
		$line=( $buf1=="Header") ? "<tr><td class=td33i align=right>Опции:</td><td class=td42> ".trim(str_replace("Header:","",$avv))."</td></tr>\n" : $line;
		
		print("$line");
	    } else {
		if( ( isset($avv))and (trim($avv)!="")) $iipos++;
	    }
	
	}
    }
    print("<td class=td42> $iipos элемент(ов) <a href=\"sets.php?set=$pset&mode=listview#binds\">открыть&#8594</a></td></tr></table>  \n");
    
    print("<br><font class=text42>Инструменты:</font>\n");
    print("<table class=table5d width=\"400px\" cellpadding=\"6px\"><tr>\n");
    print("<td><a href=\"sets.php?set=$pset&mode=listview&ref=show\" title=\"Открыть список\"><img src=\"icons/evolution-tasklist.gif\" title=\"Открыть список\"></a></td> \n");
    print("<td><a href=\"sets.php?set=$pset&mode=listview&qptr=1&ref=show\" title=\"Открыть список, Определить имена хостов членов списка. ВНИМАНИЕ: это может занять время, подождите появления страницы.\"><img src=\"icons/evolution-tg.gif\" title=\"Открыть список, Определить имена хостов членов списка. ВНИМАНИЕ: это может занять время, подождите появления страницы.\"></a></td> \n");
    if( !$flViewOnly) {
	print("<td><a href=\"sets.php?set=$pset&mode=set_flush\" title=\"ОЧИСТИТЬ Сет-лист (Flush).\"><img src=\"icons/set_flush.gif\" title=\"ОЧИСТИТЬ Сет-лист (Flush).\"></a></td> \n");
	$line="<img src=\"icons/edittrash.gif\" title=\"Удалить cет-лист (Destroy).\">";
	$line=( $fldel) ? "<td><a href=\"sets.php?set=$pset&mode=delset\" title=\"Удалить cет-лист (Destroy).\">$line</a> \n" : "$line";
	print("<td>".$line."</td>\n");
	$line=( !$flLoaded) ? "<a href=\"sets.php?set=$pset&mode=set_load&run=1&ref=show\"><img src=\"icons/play22.gif\" title=\"Загрузить сетлист\"></a> " : "<a href=\"sets.php?set=$pset&mode=set_unload&ref=show\" title=\"Выгрузить сетлист\"><img src=\"icons/stop22.gif\" title=\"Выгрузить сетлист\"></a> ";
	print("<td>".$line."</td>");
	$line=( !$flLoaded) ? "<img src=\"icons/pyrenamer22.gif\" title=\"Переименовать сетлист\"> " : "<a href=\"sets.php?set=$pset&mode=set_rename&ref=show\" title=\"Переименовать сетлист\"><img src=\"icons/pyrenamer22.gif\" title=\"Переименовать сетлист\"></a> ";
	print("<td>".$line."</td>");
	$line=( !$flLoaded) ? "<img src=\"icons/download22.gif\" title=\"Сохранить сетлист в файл\"> " : "<a href=\"sets.php?set=$pset&mode=set_save&ref=show\" title=\"Сохранить сетлист в файл\"><img src=\"icons/download22.gif\" title=\"Сохранить сетлист в файл\"></a> ";
	print("<td>".$line."</td>");
	$line=( !$flLoaded) ? "<img src=\"icons/binding22.gif\" title=\"Создать биндинг\"> " : "<a href=\"sets.php?set=$pset&mode=set_binding&ref=show\" title=\"Создать биндинг\"><img src=\"icons/binding22.gif\" title=\"Создать биндинг\"></a> ";
	print("<td>".$line."</td>");
    }
    print("</td></tr></table><br>\n");
    if( !$flViewOnly) show_setaddmember_form($pset);
    print("<br><br><br>\n");
    print("<table class=notable><tr><td valign=middle align=center>\n");
    print("<a href=\"sets.php\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td><td> <a href=\"sets.php\" class=text33>Назад</a></td></tr></table>");
    
    print("</div>\n");
    
    wlog("Просмотр формы состояния сетлиста $pset",0,FALSE,1,FALSE);
}



#-------------------------------------------------------------------------

function showfull_set($pset)
{
    global $iptconf_dir,$sets_dir,$_ipset,$_iptables,$_qptr,$_refer,$rep_whoisurl;
    if( trim($pset)=="") exit;
    
    if( ($_refer=="show") or (trim($_refer)=="")) {
	$lnkBack="sets.php?set=$pset";
    } elseif($_refer=="list") {
	$lnkBack="sets.php";
    } else {
	$lnkBack="sets.php?set=$pset";
    }
    $flViewOnly=($pset=="locals") ? TRUE : FALSE;
    list($rv,$aout)=_exec2("$_ipset -nL $pset | grep Type");
    if( $rv>0 ) { wlog("Error running sudo-ipset-grep in sets.php show_set()...",2,TRUE,5,TRUE); exit; }
    $flag=FALSE;
    foreach($aout as $akey => $aval) $flag=( substr_couont($aval,"Unknown")>0) ? TRUE:$flag;
    if( $flag) { wlog("Ошибка: сетлист $pset не загружен в память ОС",2,TRUE,5,TRUE); exit; }
    unset($aout); unset($rrez); unset($rv);
    list($rv,$aout)=_exec2("$_ipset -nL $pset");
    if( $rv>0 ) { wlog("Error running sudo-ipset in sets.php show_set()...",2,TRUE,5,TRUE); exit; }
    
    print("<a name=\"members\" class=nolnk10>&nbsp</a><br><br><br><font class=top1>Сет-лист $pset<br><br>\n<font class=text32>\n ");
    print("<a href=\"$lnkBack\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a><a href=\"$lnkBack\" class=text33>Назад</a><br>\n");
    print("<br><a href=\"#binds\" style=\"font-size:10pt\">Биндинги&#8594</a><br> \n");
    
    $flin=FALSE; $flinb=FALSE;
    foreach($aout as $akk => $avv) {
	if( trim($avv)=="") continue;
	$buf1=trim(gettok($avv,1,":"));
	if( $buf1=="Members") {
	    print("\n<br>\n");
	    $iipos=0;
	    print("В списке:<br>\n");
	    print("<table class=table4 width=\"600px\" cellpadding=\"2px\">\n");
	    $flin=TRUE; $flinb=FALSE;
	    continue;
	} elseif( $buf1=="Bindings") {
	    print("</table>\n</td></tr></table>\n<br><a name=\"binds\" class=nolnk10>&nbsp</a><br>\n");
	    print("<a href=\"#members\" style=\"font-size:10pt\">В списке&#8594</a><br><br><font class=text32>\nБиндинги:</font><br> \n");

	    $iipos=0;
	    print("<table class=table4 width=\"600px\" cellpadding=\"2px\">\n");
	    $flin=TRUE; $flinb=TRUE;
	    continue;
	} else {
	    if( trim($_qptr)!="") {
		$havv=gethostbyaddr($avv);
		$hostname=( !$flinb) ? "<font style=\"FONT: 9pt\"><br><i>hostname:</i> <a href=\"http://$havv\" target=_other>$havv</a></font>" : "";
	    } else {
		$hostname="";
	    }
	    if( $flin) {
		$iipos++;
		$pqptr=( trim($_qptr)!="") ? "&qptr=1" : "";
		if( !$flViewOnly) {
		    if( !$flinb) {
			$infoline="<a href=\"$rep_whoisurl$avv\" target=\"_Otherframe\" class=atools>whois</a><br><a href=\"http://$avv\" target=\"_Otherframe\" class=atools>http</a>";
			$line="<tr><td class=td4> $iipos. </td><td style=\"padding-left:20px;padding-right:15px\"> $avv $hostname</td><td> <div style=\"float:left\"><a href=\"sets.php?set=$pset&mode=set_killpos&addr=$avv$pqptr\" title=\"Удалить\"><img src=\"icons/cancel16.gif\" title=\"Удалить\"></a> &nbsp&nbsp <a href=\"sets.php?set=$pset&mode=set_binding&addr=$avv\" title=\"Создать биндинг для элемента\"><img src=\"icons/_binding1_16.gif\" title=\"Создать биндинг для элемента\"></a></div><div style=\"float:right\">$infoline</div></td></tr>\n";
		    } else {
			$buf1=str_replace("->","|",$avv);
			$bufaddr=gettok($buf1,1,"|"); 
			$bufp1value=gettok($buf1,2,"|"); 
			$infoline="<a href=\"$rep_whoisurl$bufaddr\" target=\"_Otherframe\" class=atools>whois</a><br><a href=\"http://$bufaddr\" target=\"_Otherframe\" class=atools>http</a>";
			$line="<tr><td class=td4> $iipos. </td><td style=\"padding-left:20px;padding-right:15px\"> $avv $hostname</td><td> <div style=\"float:left\"><a href=\"sets.php?set=$pset&mode=set_unbind&addr=$bufaddr&p1value=$bufp1value\" title=\"Удалить биндинг\"><img src=\"icons/cancel16.gif\" title=\"Удалить биндинг\"></a></div><div style=\"float:right\">$infoline</div></td></tr>\n";
		    }
		} else {
		    $line="<tr><td class=td4> $iipos. </td><td style=\"padding-left:20px;padding-right:15px\"> $avv $hostname</td><td> <div style=\"float:left\">&nbsp</div><div style=\"float:right\">$infoline</div></td></tr>\n";
		}
		print("$line");
	    }
	
	}
    }
    print("</table>\n");
    print("<br><a href=\"$lnkBack\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a><a href=\"$lnkBack\" class=text33>Назад</a>\n");
    print("</blockquote><br><br><br>\n");
    
    wlog("Просмотр сетлиста $pset",0,FALSE,1,FALSE);

}



#-------------------------------------------------------------------------


function show_setlist_old()
{
    global $iptconf_dir,$_ipset,$_grep,$_set_show_listinfo;
    $ifile=$iptconf_dir."/ipsetlist";
    if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	wlog("Error write access to $ifile...",2,TRUE,5,TRUE); exit; 
    }
    print("<table width=\"650px\" cellpadding=\"4px\"><tr><td width=\"100%\">   \n");
    print("<br><br><br><div style=\"float:left\"><font class=top1>Сет-листы<br> \n <font class=text41><i>( Листы, загружаемые программой )</div>\n");
    print("<div style=\"float:right\"><table class=table5d cellpadding=\"0px\"><tr><td style=\"padding-left:10px;padding-right:10px\">");
    print("<a href=\"sets.php?mode=lst_search\"><img src=\"icons/edit-find.gif\" title=\"Поиск по сет-листам\"></a>&nbsp&nbsp\n");
    print("<a href=\"sets.php?mode=lst_addset\"><img src=\"icons/set_new22.gif\" title=\"Создать новый сет-лист\"></a>\n");
    print("</div>\n</td></tr></table>\n</td></tr></table>\n ");
    print("<hr width=\"650px\" align=left>\n");
    print("<table class=table1 width=\"650px\" cellpadding=\"4px\">\n");
    
    if( file_exists($ifile)) {
	$iff=fopen($ifile,"r");
	while( !feof($iff)) {
	    $str1=trim(fgets($iff));
	    if( trim($str1)=="") continue;
	    if( $str1[0]=="#") continue;
	    $buftype="";
	    list($rv,$rout)=_exec2("$_ipset -nL $str1 | $_grep Type");
	    if( $rv!=0) {
	        $buftype="<table class=table5 cellpadding=\"4px\"  style=\"border-collapse:collapse\"><tr><td class=desci>Статус:</td><td class=tddesk><font color=maroon>Не загружен</font></td></tr></table>\n";
	        $flLoaded=FALSE;
	    } else {
	        $rez=str_replace("Type:","",trim($rout[min(array_keys($rout))]));
		$buftype=(substr_count($rez,"Unknown")>0) ? "<font color=maroon>Не загружен</font>" : "<font color=teal>Загружен</font>";
		$buftype="<table class=table5 cellpadding=\"1px\" style=\"border-collapse:collapse\"><tr><td class=desci>Статус:</td><td class=tddesk>$buftype</td></tr>\n<tr><td class=desci>Тип:</td><td class=tddesk style=\"color:333399\">$rez</td></tr>";
		if( $_set_show_listinfo) {
		    $buftype=$buftype."<tr><td class=desci>Элементы:</td><td class=tddesk>".set_getstat($str1,1)."</td></tr>";
		    $buftype=$buftype."<tr><td class=desci>Биндинги:</td><td class=tddesk>".set_getstat($str1,2)."</td></tr>";
		}
		$buftype=$buftype."</table>";
		$flLoaded=TRUE;
	    }

	    $lOpt="<img src=\"icons/seahorse-preferences.gif\" title=\"Свойства сет-листа\">";
	    $lOpt=($flLoaded) ? "<a href=\"sets.php?set=$str1&mode=show\">$lOpt</a>" : "$lOpt";
	    $lList="<img src=\"icons/evolution-tasklist.gif\" title=\"Открыть список\">";
	    $lList=($flLoaded) ? "<a href=\"sets.php?set=$str1&mode=listview&ref=list\">$lList</a>" : "$lList";
	    if( trim($str1)!="locals") {
		print("<tr><td width=\"44%\"><font style=\"font-size:12pt\"><b> $str1 </b></font></td><td width=\"17%\">\n$buftype</td><td class=td3 width=\"15%\"><center>$lOpt&nbsp&nbsp\n");
		print("$lList&nbsp&nbsp\n");
		if( $flLoaded) {
		    $line="<a href=\"sets.php?set=$str1&mode=set_unload&ref=list\"><img src=\"icons/stop22.gif\" title=\"Выгрузить сет-лист\"></a>\n";
		} else {
		    $line="<a href=\"sets.php?set=$str1&mode=set_load&run=1&ref=list\"><img src=\"icons/play22.gif\" title=\"Загрузить сет-лист\"></a>\n";
		}
		print("$line");
		print("</center></td></tr> \n");
		unset($rrv); unset($rout); unset($rez);
	    } else {
		print("<tr><td width=\"47%\" style=\"font-size:12pt\"><b> $str1</b><br><font style=\"font-size:9pt\"><i>[ Сет со списком локальных сетей, используется в правилах для их идентификации. Не изменять и не удалять! ] </i></td><td>\n$buftype</td><td class=td3><center>$lOpt</center> </td></tr>  \n");
	    }
	}
	fclose($iff);
    }
    print("</td></tr></table>\n");
    wlog("Просмотр списка сетлистов",0,FALSE,1,FALSE);

}


#-------------------------------------------------------------------------

function show_setlist()
{
    global $iptconf_dir,$_ipset,$_grep,$_set_show_listinfo;
    $ifile=$iptconf_dir."/ipsetlist";
    
    list($rr,$aafile)=_exec2("cat $ifile");
    $aatable=array();        
    if(( $rr>0) || ( count($aafile)==0)) {
	wlog("Error reading access to $ifile...",2,TRUE,5,TRUE); exit; 
    }
    $aatable=array(); $u=0;
    foreach($aafile as $aakey => $str1) {
	if( trim($str1)=="") continue;
	if( $str1[0]=="#") continue;
	$buftype="";
	list($rv,$rout)=_exec2("$_ipset -nL $str1 | $_grep Type");
	if( $rv!=0) {
	    $buftype="<table class=table5 cellpadding=\"4px\"  style=\"border-collapse:collapse\"><tr><td class=desci>Статус:</td><td class=tddesk><font color=maroon>Не загружен</font></td></tr></table>\n";
	    $flLoaded=FALSE;
	} else {
	    $rez=str_replace("Type:","",trim($rout[min(array_keys($rout))]));
	    $buftype=(substr_count($rez,"Unknown")>0) ? "<font color=maroon>Не загружен</font>" : "<font color=teal>Загружен</font>";
	    $buftype="<table class=table5 cellpadding=\"1px\" style=\"border-collapse:collapse\"><tr><td class=desci>Статус:</td><td class=tddesk>$buftype</td></tr>\n<tr><td class=desci>Тип:</td><td class=tddesk style=\"color:333399\">$rez</td></tr>";
	    if( $_set_show_listinfo) {
		$buftype=$buftype."<tr><td class=desci>Элементы:</td><td class=tddesk>".set_getstat($str1,1)."</td></tr>";
		$buftype=$buftype."<tr><td class=desci>Биндинги:</td><td class=tddesk>".set_getstat($str1,2)."</td></tr>";
	    }
	    $buftype=$buftype."</table>";
	    $flLoaded=TRUE;
	}

	$lOpt="<img src=\"icons/seahorse-preferences.gif\" title=\"Свойства сет-листа\">";
	$lOpt=($flLoaded) ? "<a href=\"sets.php?set=$str1&mode=show\">$lOpt</a>" : "$lOpt";
	$lList="<img src=\"icons/evolution-tasklist.gif\" title=\"Открыть список\">";
	$lList=($flLoaded) ? "<a href=\"sets.php?set=$str1&mode=listview&ref=list\">$lList</a>" : "$lList";
	if( trim($str1)!="locals") {
	    $aatable[$u]="<tr><td width=\"44%\"><font style=\"font-size:12pt\"><b> $str1 </b></font></td><td width=\"17%\">\n$buftype</td>";
	    $aatable[$u].="<td class=td3 width=\"15%\"><center>$lOpt&nbsp&nbsp\n$lList&nbsp&nbsp\n";
	    if( $flLoaded) {
		$aatable[$u].="<a href=\"sets.php?set=$str1&mode=set_unload&ref=list\"><img src=\"icons/stop22.gif\" title=\"Выгрузить сет-лист\"></a>\n";
	    } else {
	        $aatable[$u].="<a href=\"sets.php?set=$str1&mode=set_load&run=1&ref=list\"><img src=\"icons/play22.gif\" title=\"Загрузить сет-лист\"></a>\n";
	    }
	    $aatable[$u].="</center></td></tr> \n";
	    unset($rrv); unset($rout); unset($rez);
	} else {
	    $aatable[$u]="<tr><td width=\"47%\" style=\"font-size:12pt\"><b> $str1</b><br><font style=\"font-size:9pt\"><i>[ Сет со списком локальных сетей, используется в правилах для их идентификации. Не изменять и не удалять! ] </i></td><td>\n$buftype</td><td class=td3><center>$lOpt</center> </td></tr>  \n";
	}
	$u++;
    }

    print("<table width=\"650px\" cellpadding=\"4px\"><tr><td width=\"100%\">   \n");
    print("<br><br><br><div style=\"float:left\"><font class=top1>Сет-листы<br> \n <font class=text41><i>( Листы, загружаемые программой )</div>\n");
    print("<div style=\"float:right\"><table class=table5d cellpadding=\"0px\"><tr><td style=\"padding-left:10px;padding-right:10px\">");
    print("<a href=\"sets.php?mode=lst_search\"><img src=\"icons/edit-find.gif\" title=\"Поиск по сет-листам\"></a>&nbsp&nbsp\n");
    print("<a href=\"sets.php?mode=lst_addset\"><img src=\"icons/set_new22.gif\" title=\"Создать новый сет-лист\"></a>\n");
    print("</div>\n</td></tr></table>\n</td></tr></table>\n ");
    print("<hr width=\"650px\" align=left>\n");
    print("<table class=table1 width=\"650px\" cellpadding=\"4px\">\n");
    foreach($aatable as $aakey => $aaval) print($aaval);
    print("</td></tr></table>\n");
    wlog("Просмотр списка сетлистов",0,FALSE,1,FALSE);

}


#-------------------------------------------------------------------------

function del_set($pset,$prun)
{
    global $iptconf_dir,$sets_dir,$_ipset,$_sudo,$_grep;
    list($rv,$aout)=_exec2("$_ipset -nL $pset | grep Type");
    if( $rv>0 ) { wlog("Error running sudo-ipset-grep in sets.php show_set()...",2,TRUE,5,TRUE); exit; }
    $flag=FALSE;
    foreach($aout as $akey => $aval) $flag=( substr_count($aval,"Unknown")>0) ? TRUE:$flag;
    if( $flag ) { wlog("Ошибка: сетлист $pset не загружен в память ОС",2,TRUE,5,TRUE); exit; }
    unset($aout); unset($rv);
    
    if( trim($prun)=="") {
	print("<br><br><table class=table1 width=\"350px\" cellpadding=\"6px\"><tr><td align=center>\n");
	print("<br>Удалить сет-лист $pset?<br><br> \n");
	print("<a href=\"sets.php?set=$pset&mode=delset&run=1\" class=a14>Да</a>&nbsp&nbsp&nbsp<a href=\"sets.php?set=$pset&mode=show\" class=a14>Нет</a>\n");
	print("</td></tr></table>\n");
	exit;

    } else {
	
	$line="$_ipset -F $pset";
	list($rr,$aa1)=_exec2($line);
	if( $rr>0) { wlog("Ошибка очистки сетлиста $pset в del_set()...",2,TRUE,5,TRUE); exit; }
	$line="$_ipset -X $pset";
	list($rr,$aa1)=_exec2($line);
	if( $rr>0) { wlog("Ошибка удаления сетлиста $pset в del_set()...",2,TRUE,5,TRUE); exit; }

	$ifile=$sets_dir."/".$pset;
	if( ( file_exists($ifile)) and ( !is_writable($ifile))) {
	    wlog("Error write access to $ifile...",2,TRUE,5,TRUE); exit; 
	} 
	$lstfile=$iptconf_dir."/ipsetlist";
	$lstnewfile=tempnam($iptconf_dir,$lstfile);
	if( ( file_exists($lstfile)) and ( !is_writable($lstfile))) {
	    wlog("Error write access to $lstfile...",2,TRUE,5,TRUE); exit; 
	}
	$lstff=fopen($lstfile,"r");
	$lstnewff=fopen($lstnewfile,"a");
	while( !feof($lstff)) {
	    $str1=trim(fgets($lstff));
	    if( ($str1!=$pset) and ( trim($str1)!="")) fwrite($lstnewff,$str1."\n");
	}
	fclose($lstff);
	fclose($lstnewff);
	if( file_exists($lstfile.".bak")) unlink($lstfile.".bak");
	rename($lstfile,$lstfile.".bak");
	rename($lstnewfile,$lstfile);
	if( file_exists($ifile.".bak")) unlink($ifile.".bak");
	rename($ifile,$ifile.".bak");
	
	wlog("Удаление сетлиста $pset",0,FALSE,1,FALSE);
    }

}

#------------------------------------------------------------------------


print("<html>\n");
require("include/head.php");
print("<body\n>");

if( $_set=="") {
    if(( $_mode=="") or ( $_mode=="list")) {
	if( !$_show) {
	    show_load("sets.php?s=1","Чтение списка сет-листов");
	} else {
	    show_setlist();
	}
    } elseif( $_mode=="lst_addset") {
	print("<br><br><br>\n");
	show_addset_form();
    } elseif( $_mode=="lst_search") {
	print("<br><br><br>\n");
	sets_search($_p1value,$_run);
    } elseif( $_mode=="ipset") {
	print("<br><br><font class=top2>Ipset</font><br><font class=text33>\n");
	list($rv,$aa1)=_exec2("$_ipset --version");
	print("версия: ".$aa1[min(array_keys($aa1))]."<br><br>");
	show_addset_form();
	sets_search($_p1value,$_run);
	print("<table width=\"350px\"><tr><td align=center>\n");
	print("<a href=\"sets.php?mode=list\"><img src=\"icons/setlist22.gif\" title=\"Cет-панель\"></a>\n");
	print("&nbsp&nbsp&nbsp\n");
	print("<a href=\"sets_edit.php\"><img src=\"icons/edit22.gif\" title=\"Редактор сет-листов\"></a>\n");
	print("</td></tr></table>\n");

    } elseif( $_mode=="set_addto") {

	set_addto($_set,$_addr,$_run);

    }
    
} else {
    
    if( ($_mode=="") or ($_mode=="show")) {

	show_set($_set);
	
    } elseif( $_mode=="listview") {
    
	showfull_set($_set);

    } elseif( $_mode=="addset") {

	add_set($_set,$_settype,$_mode);

    } elseif( $_mode=="addset2") {

	add_set($_set,$_settype,$_mode);
	show_setlist();

    } elseif( $_mode=="delset") {

	del_set($_set,$_run);
	show_setlist();

    } elseif( $_mode=="set_addmember") {
    
	set_addmember($_set,$_addr);
	show_set($_set);
	
    } elseif( $_mode=="set_killpos") {

	set_killpos($_set,$_addr,$_run);
	showfull_set($_set);

    } elseif( $_mode=="set_flush") {

	set_flush($_set,$_run);
	show_set($_set);

    } elseif( $_mode=="set_load") {

	set_load($_set,$_run);
	if( (trim($_refer)=="") or (trim($_refer)=="list")) {
	    show_setlist();
	} elseif( trim($_refer)=="show") {
	    show_set($_set);
	}

    } elseif( $_mode=="set_unload") {

	set_unload($_set,$_run);
	if( (trim($_refer)=="") or (trim($_refer)=="list")) {
	    show_setlist();
	} elseif( trim($_refer)=="show") {
	    show_set($_set);
	}

    } elseif( $_mode=="set_rename") {

	set_rename($_set,$_run);
	if( (trim($_refer)=="") or (trim($_refer)=="list")) {
	    show_setlist();
	} elseif( trim($_refer)=="show") {
	    show_set($_set);
	}

    } elseif( $_mode=="set_save") {

	set_save($_set);
	if( (trim($_refer)=="") or (trim($_refer)=="show")) {
	    show_set($_set);
	} elseif( trim($_refer)=="list") {
	    show_setlist();
	}

    } elseif( $_mode=="set_binding") {

	set_binding($_set,$_addr,$_run);
	if( (trim($_refer)=="") or (trim($_refer)=="show")) {
	    show_set($_set);
	} elseif( trim($_refer)=="list") {
	    show_setlist();
	}

    } elseif( $_mode=="set_unbind") {

	set_unbind($_set,$_addr,$_run);
	showfull_set($_set);

    } elseif( $_mode=="set_addto") {

	set_addto($_set,$_addr,$_run);
	exit;

    }


}





?>
<br><br><br><br><br><br>
</body>
</html>