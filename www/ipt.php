<?php

###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.6
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: ipt.php
# Description: ipt modes
# Version: 0.2.4.6
###



require("./../config.php");
require("iptlib.php");
require("iptlib2.php");
require("shapelib.php");

$flAdminsOnly=TRUE;
require("auth.php");
print("<html>\n");
require("include/head.php");
print("<body>\n");

$proc=( isset($_GET["p"])) ? $_GET["p"] : "";
$file=( isset($_GET["file"])) ? $_GET["file"] : "";
$counts_no_export=( isset($_GET["cnoex"])) ? $_GET["cnoex"] : "";
$ip=( isset($_GET["ip"])) ? $_GET["ip"] : "";
$show=( isset($_GET["s"])) ? TRUE : FALSE;
$ctraf=( isset($_GET["ctraf"])) ? $_GET["ctraf"] : "";
$pkey=( isset($_GET["pkey"])) ? $_GET["pkey"] : "";

#------------------------------------------------------------------


if( trim($proc)=="") exit;

if( $proc=="viewcounts") {
    
    if( (!file_exists($file)) or (!is_readable($file))) { 
	wlog("Error accessing file $cfile<br>",2,TRUE,5,FALSE); exit; 
    }
    $aa1=file($file);
    foreach($aa1 as $kk1 => $vv1) {
	print(c2koi("$aa1[$kk1]")."<br>");
    }
    unset($aa1);
    wlog("Просмотр файла счетчиков $file",0,FALSE,1,FALSE);

} elseif( $proc=="reload") {
    
  if( !$show) {
    show_load("ipt.php?p=reload&cnoex=$counts_no_export&s=1","Перезагрузка конфигурации...");
  } else {
    $_time1=time();
    $_console=FALSE;
    if( !chdir($iptconf_dir)) { wlog("Error changing directory to $iptconf_dir in ipt.php",2,TRUE,5,FALSE); exit; }
    if( file_exists($_lockfile)) { 
	wlog("Lockfile found, may be some other instance of reload is running....",2,TRUE,5,FALSE); exit; 
    } else {
	$_lck=fopen($_lockfile,"w");
	fwrite($_lck," ");
	fclose($_lck);
    }
    load_ifs();
    print("<font class=text41> \n");
    if( $counts_no_export=="") {
	print("<br>Saving counters...");
	counts_export(TRUE);
	print("...<font style=\"color:32CD32; font-style: italic;\">OK</font><br><br>\n");
    }
    print("<br>Loading configuration...");
    fillconf(TRUE);
    $_wtime=time()-$_time1;
    print("...<font style=\"color:32CD32; font-style: italic;\">OK</font><br><br>\n");
    print("<br>Done.<br><br><br>\n");
    unlink($_lockfile);
    print("<font style=\"font-size:8pt; font-style:italic; color: 696969;\">completed in ".round(($_wtime/60),2)."min. (".round($_wtime,2)."sec.)\n");
    print("<br><br><a href=\"ipt.php?p=servicesave\"><img src=\"icons/apps_16.gif\"> Выполнить \"service iptcldr save\"</a><br>  \n");
    print("</font><br><br><a href=\"sysstat.php?p=iptables\">Назад</a><br>\n");
    wlog("Запуск RELOAD",0,FALSE,1,FALSE);
  }
} elseif( $proc=="exportcounts") {

  if( !$show) {
    show_load("ipt.php?p=exportcounts&s=1","Обработка счетчиков...");
  } else {

    $_time1=time();
    $_console=FALSE;
    if( !chdir($iptconf_dir)) { wlog("Error changing directory to $iptconf_dir in ipt.php",2,TRUE,5,FALSE); exit; }
    if( file_exists($_lockfile)) { 
	wlog("Lockfile found, may be some other instance of reload is running....",2,TRUE,5,FALSE); exit; 
    } else {
	$_lck=fopen($_lockfile,"w");
	fwrite($_lck," ");
	fclose($_lck);
    }
    load_ifs();
    print("<font class=text41> \n");
    print("<br>Saving counters...");
    counts_export(TRUE);
    $_wtime=time()-$_time1;
    print("...<font style=\"color:32CD32; font-style: italic;\">OK</font><br><br><br>\n");
    print("<font style=\"font-size:8pt; font-style:italic; color: 696969;\">completed in ".round(($_wtime/60),2)."min. (".round($_wtime,2)."sec.)\n");
    print("</font><br><br><a href=\"sysstat.php?p=iptables\">Назад</a>");
    unlink($_lockfile);
    wlog("Запуск выгрузки счетчиков в файл",0,FALSE,1,FALSE);
  }

} elseif( $proc=="replcounts") {
    $_time1=time();
    $_console=FALSE;
    if( !chdir($iptconf_dir)) { wlog("Error changing directory to $iptconf_dir in ipt.php",2,TRUE,5,FALSE); exit; }
    if( file_exists($_lockfile)) { 
	wlog("Lockfile found, may be some other instance of reload is running....",2,TRUE,5,FALSE); exit; 
    } else {
	$_lck=fopen($_lockfile,"w");
	fwrite($_lck," ");
	fclose($_lck);
    }
    load_ifs();
    print("<font class=text41> \n");
    print("<br>Saving current counters...");
    counts_export(TRUE);
    print("...<font style=\"color:32CD32; font-style: italic;\">OK</font><br><br>\n");
    $flch=FALSE;
    if( file_exists("counters")) {
	$aac=file("counters");
	foreach($aac as $key2 => $vv2) {
	    $buf1=strtolower(trim(gettok($aac[$key2],1,";")));
	    if($buf1!=$ip) { continue; } else {
		$buf1=trim(gettok($aac[$key2],2,";"));
		if( gettok($buf1,1,":")!="policy") { continue; } else {
		    if( gettok($buf1,2,":")!=$pkey) { continue; } else {
			$buf1=strtolower(trim(gettok($aac[$key2],3,";")));
			$buf1=str_replace("in(","",$buf1); 
			$buf1=str_replace(")","",$buf1); 
			$bufin_pkts=gettok($buf1,1,"/");
			$bufin_pkts=( trim($bufin_pkts)=="0") ? "1" : $bufin_pkts;
			$aac[$key2]=substr($aac[$key2],0,strpos($aac[$key2],"in(")-1)." in($bufin_pkts/$ctraf); ".substr($aac[$key2],strpos($aac[$key2],"out("));
			$flch=TRUE;
			break;
		    }
		}
	    }
	}
	
	unlink("counters");
	$cfile=fopen("counters","a");
	foreach($aac as $key2 => $vv2) fwrite($cfile,$aac[$key2]);
	if( !$flch) fwrite($cfile,"$ip; policy:$pkey; in(1/$ctraf); out(1/1);\n");
	fclose($cfile);
    } else {
	$cfile=fopen("counters","a");
	fwrite($cfile,"$ip; policy:$pkey; in(1/$ctraf); out(1/1);\n");
	fclose($cfile);
    }
    print("<br><br>Результат записан в файл счетчиков <a href=\"ipt.php?p=viewcounts&file=$iptconf_dir/counters&height=750&width=700\" class=\"thickbox\" title=\"Просмотр $iptconf_dir/counters\">$iptconf_dir/counters</a><br><br> \n");
    print("Теперь можно запустить перезагрузку конфигурации (без экспорта счетчиков) чтобы применить изменения.<br><br>\n");
    print("<a href=\"ipt.php?p=reload&cnoex=1\" title=\"Запустить Reload\"><img src=\"icons/apps_32.gif\" title=\"Запустить Reload\"></a><br><br>");

    unlink($_lockfile);
    wlog("Перезапись счетчиков в файле counters для $ip по ключу $pkey",0,FALSE,1,FALSE);

} elseif( $proc=="newperiod") {

  if( !$show) {
    show_load("ipt.php?p=newperiod&s=1","Обработка данных...");
  } else {

    $_time1=time();
    $_console=FALSE;
    
    $link2=mysql_connect($mysql_host,$mysql_user,$mysql_password);
    if( !$link2) { wlog("Error connecting mysql in ipt.php in newperiod section..",2,TRUE,5,FALSE); exit; }
    mysql_select_db("fantomas");
    
    if( !chdir($iptconf_dir)) { wlog("Error changing directory to $iptconf_dir in ipt.php",2,TRUE,5,FALSE); exit; }
    if( file_exists($_lockfile)) { 
	wlog("Lockfile found, may be some other instance of reload is running....",2,TRUE,5,FALSE); exit; 
    } else {
	$_lck=fopen($_lockfile,"w");
	fwrite($_lck," ");
	fclose($_lck);
    }
    load_ifs();
    print("<font class=text41> \n");
    print("<br>Saving counters...");
    counts_export(TRUE);
    print("...<font style=\"color:32CD32; font-style: italic;\">OK</font><br><br><br>\n");
    print("Clearing kernel counters...\n");
    list($r,$rout)=_exec2("$_iptables -t mangle -Z PREROUTING");
    if($r!=0) print("Error zero mangle PREROUTING chain... ");
    unset($rout); unset($r); 
    list($r,$rout)=_exec2("$_iptables -t mangle -Z FORWARD");
    if($r!=0) print("Error zero mangle FORWARD chain... ");
    unset($rout); unset($r); 
    list($r,$rout)=_exec2("$_iptables -t mangle -Z COUNT_IN");
    if($r!=0) print("Error zero mangle COUNT_IN chain... ");
    unset($rout); unset($r);
    list($r,$rout)=_exec2("$_iptables -t mangle -Z COUNT_OUT");
    if($r!=0) print("Error zero mangle COUNT_OUT chain... ");
    unset($rout); unset($r);
    
    print("...<font style=\"color:32CD32; font-style: italic;\">OK</font><br><br><br>\n");
    
    $_wtime=time()-$_time1;
    print("<font style=\"font-size:8pt; font-style:italic; color: 696969;\">completed in ".round(($_wtime/60),2)."min. (".round($_wtime,2)."sec.)\n");
    print("</font><br><br><a href=\"sysstat.php?p=iptables\">Назад</a>");
    
    $rez1=mysql_query("UPDATE fantomas SET p_start_date=CURRENT_TIMESTAMP() ");
    
    mysql_close($link2);
    
    unlink($_lockfile);
    
    wlog("Открытие нового периода",0,FALSE,1,FALSE);
  }

} elseif( $proc=="servicesave") {
    
    load_ifs();
    print("<br>Saving ...");
    list($rr,$out)=_exec2("$_service iptcldr save");
    $rz=( $rr==0) ? "OK" : "FAILED";
    print("...<font style=\"color:32CD32; font-style: italic;\">$rz</font><br><br><br>\n");
    print("</font><br><br><a href=\"sysstat.php?p=iptables\"><img src=\"icons/gtk-undo.gif\"> Назад</a>");
    wlog("Выполнение service iptcldr save",0,FALSE,1,FALSE);    

} elseif( $proc=="servicerestart") {
    
    load_ifs();
    print("<br>Restarting ...");
    list($rr,$out)=_exec2("$_service iptcldr restart");
    $rz=( $rr==0) ? "OK" : "FAILED";
    print("...<font style=\"color:32CD32; font-style: italic;\">$rz</font><br><br><br>\n");
    
    print("OUTPUT:<br><table class=table5 width=\"600px\"><tr><td>\n");
    foreach($out as $okk => $ovv) print("$ovv <br>\n");
    print("</td></tr></table>\n");
    
    print("</font><br><br><a href=\"sysstat.php?p=iptables\"><img src=\"icons/gtk-undo.gif\"> Назад</a>");
    wlog("Выполнение service iptcldr restart",0,FALSE,1,FALSE);    
}

print("</body></html>\n");


?>