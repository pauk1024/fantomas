<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8.2
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: statlib.php
# Description: 
# Version: 2.8.2.7
###



#-------------------------------------------------------------------------


function show_repair_table()
{
    print("<form name=\"repair1\" action=\"sysstat.php\" style=\"padding:0px;margin:5px;\">\n");
    print("<input type=\"hidden\" name=\"run\" value=\"1\">\n");
    print("<input type=\"hidden\" name=\"p\" value=\"ulog\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"repair\">\n");
    print("<table class=tb1 cellpadding=\"2px\"><tr><td style=\"color:330066;\">\n");
    print("Repair table ulog: </td><td><span class=seldiv><SELECT name=\"smode\">\n");
    print("<option value=\"\" selected>без параметров\n<option value=\"QUICK\">QUICK\n<option value=\"EXTENDED\">EXTENDED\n");
    print("</SELECT></span></td><td><input type=\"SUBMIT\" name=\"repairsbmt\" value=\"Ok\"></td></tr></table>\n</form>\n");


}


#--------------------------------------------------------------

function show_check_table()
{
    print("<form name=\"check1\" action=\"sysstat.php\" style=\"padding:0px;margin:5px;\">\n");
    print("<input type=\"hidden\" name=\"run\" value=\"1\">\n");
    print("<input type=\"hidden\" name=\"p\" value=\"ulog\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"check\">\n");
    print("<table class=tb1 cellpadding=\"2px\"><tr><td style=\"color:330066;\">\n");
    print("Check table ulog: </td><td><span class=seldiv><SELECT name=\"smode\">\n");
    $aa1=array( "FOR UPGRADE", "QUICK", "FAST", "MEDIUM", "EXTENDED", "CHANGED" );
    print("<option value=\"\" selected>без параметров\n");
    foreach( $aa1 as $aakk1 => $aavv1) print("<option value=\"$aavv1\">$aavv1\n");
    print("</SELECT></span></td><td><input type=\"SUBMIT\" name=\"checksbmt\" value=\"Ok\"></td></tr></table>\n</form>\n");


}


#--------------------------------------------------------------



function display_logfile($file="")
{
    global $viewlogs_show_last;
    if( trim($file)=="") return(FALSE);
    list($rr1,$lfile)=_exec2("cat $file");
    if( $rr1!=0) { print("Error reading $file in display_logfile()<br>\n"); exit; }
    krsort($lfile);
    $ich=0;
    foreach( $lfile as $kk => $vv) {
	if( $ich>=$viewlogs_show_last) { break; } else {
	    $lfile[$kk]=htmlentities($lfile[$kk],ENT_NOQUOTES,"koi8r");
	    print(trim($lfile[$kk])."<br>\n");
	    $ich++;
	}
    }
    unset($lfile);
    wlog("Просмотр лог-файла $file",0,FALSE,1,FALSE);
}


#--------------------------------------------------------------


function get_process_info($proc,$nobr=FALSE,$nob=FALSE)
{
    global $_top,$_grep;
    if( trim($proc)=="") return("");
    list($rr,$sys1)=_exec2("$_top -n1 -b| $_grep $proc");
    if( count($sys1)>0) $sys1[min(array_keys($sys1))]=_trimline($sys1[min(array_keys($sys1))]," \t");
    if( $rr==0) {
	if( !$nobr) print("<br>\n");
	print("<font class=text32".(( $nob) ? "":"b").">Состояние процесса <i>$proc:</i><br>\n");
	if( !$nobr) print("<br>\n");
	for( $_id=1; $_id<=count($sys1); $_id++) {
	    $_ii=$_id-1;
	    $sys1[$_ii]=_trimline($sys1[$_ii]," \t");
	    $ul_pid=gettok($sys1[$_ii],1," \t");
	    $ul_res=gettok($sys1[$_ii],6," \t");
	    $ul_cpu=gettok($sys1[$_ii],9," \t");
	    $ul_mem=gettok($sys1[$_ii],10," \t");
	    $ul_cmd=gettok($sys1[$_ii],12," \t");
	    print("<table class=table3 width=\"95%\">\n");
	    print("<tr><td>Процесс, pid: </td><td><i> <b>$ul_cmd</b>, $ul_pid</i></td></tr> \n");
	    print("<tr><td>%CPU: </td><td><i> $ul_cpu</i></td></tr> \n");
	    print("<tr><td>%MEM: </td><td><i> $ul_mem</i></td></tr> \n");
	    print("<tr><td>RES: </td><td><i> ".rebytes($ul_res)."</i></td></tr> \n");
	    print("</table> \n");
	}
    } else {
	print("Не удалось выполнить <i>\"sudo top -n1 -b | grep $proc\"</i>.<br>  \n");
	wlog("Не удалось выполнить <i>\"sudo top -n1 -b | grep $proc\"</i>.<br>  \n",2,FALSE,4,FALSE);
    }


}


#----------------------------------------------------------------


function get_process_tabinfo($proc)
{
    global $_top,$_grep;
    if( trim($proc)=="") return("");
    list($rr,$sys1)=_exec2("$_top -n1 -b| $_grep $proc");
    if( count($sys1)==0) return(FALSE);
    $sys1[0]=_trimline($sys1[0]," \t");
    if( $rr==0) {
	for( $_id=1; $_id<=count($sys1); $_id++) {
	    $_ii=$_id-1;
	    $sys1[$_ii]=_trimline($sys1[$_ii]," \t");
	    $ul_pid=gettok($sys1[$_ii],1," \t");
	    $ul_res=gettok($sys1[$_ii],6," \t");
	    $ul_status=gettok($sys1[$_ii],8," \t");
	    $ul_cpu=gettok($sys1[$_ii],9," \t");
	    $ul_mem=gettok($sys1[$_ii],10," \t");
	    $ul_cmd=gettok($sys1[$_ii],12," \t");
	    
	    $ul_status=(trim($ul_status)=="S") ? "sleeping" : $ul_status;
	    $ul_status=(trim($ul_status)=="R") ? "running" : $ul_status;
	    $ul_status=(trim($ul_status)=="D") ? "uninterruptable sleep" : $ul_status;
	    $ul_status=(trim($ul_status)=="T") ? "traced or stopped" : $ul_status;
	    $ul_status=(trim($ul_status)=="Z") ? "zombie" : $ul_status;

	    print("<tr><td> <i> <b>$ul_cmd</b></td><td><i> $ul_pid</i></td><td><i> $ul_status </td><td><i> $ul_cpu</i> </td><td><i> $ul_mem</i></td><td><i> ".rebytes($ul_res)."</i></td></tr> \n  \n");
	}
    } else {
	print("Не удалось выполнить <i>\"sudo top -n1 -b | grep $proc\"</i>.<br>  \n");
	wlog("Не удалось выполнить <i>\"sudo top -n1 -b | grep $proc\"</i>.<br>  \n",2,FALSE,4,FALSE);
    }


}

#----------------------------------------------------------------

function topmonitor($target)
{
    global $_timeout,$_top;
    print("<html>\n");
    print("<head>\n");
    print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">\n");
    print("<meta http-equiv=\"Expires\" content=\"Mon, 01 Jan 1990 00:00:00 GMT\">\n");
    print("<link rel=\"stylesheet\" type=\"text/css\" href=\"css/text1.css\">\n");
    print("<title>Fantomas page</title>\n<base target=\"chartframe\">\n</head>\n<body>\n");

    if( $target=="toolbox") {
    
	print("<form name=\"fgdsfh\" action=\"sysstat.php\"> \n");
	print("<input type=\"hidden\" name=\"p\" value=\"topmon\">  \n");
	print("<input type=\"hidden\" name=\"t\" value=\"chart\">  \n");
	print("<table class=table5 width=\"400px\" cellpadding=\"5px\">  \n");
	print("<tr><td> Таймаут: </td><td> <input type=\"text\" name=\"w\" size=3 value=\"$_timeout\"> </td>\n");
	print("<td> <input type=\"submit\" value=\"Set\"></td> \n");
	print("<td> <a href=\"sysstat.php?p=system\" target=\"basefrm\" title=\"Забить\"> <img src=\"icons/gtk-undo.gif\" title=\"Забить\"></a> </td> \n");
	print("</tr>\n</table>\n</form>\n");
    
    } elseif( $target=="chart") {
	
	if( isset($sys1)) unset($sys1);
	list($rr,$sys1)=_exec2("$_top -n1 -b");
	if( $rr==0) {
	    print("<font class=top1>top results<br>\n");
	    print("<table class=table21 width=\"600px\" cellpadding=\"3px\">\n");
	    print("<tr><th> Процесс </th><th> pid </th><th> Статус </th><th> %CPU </th><th> %MEM </th><th> RES </th></tr> \n");

	    for( $_id=8; $_id<=count($sys1); $_id++) {
		$_ii=$_id-1;
		$sys1[$_ii]=_trimline($sys1[$_ii]," \t");
		$ul_pid=gettok($sys1[$_ii],1," \t");
		$ul_res=gettok($sys1[$_ii],6," \t");
		$ul_status=gettok($sys1[$_ii],8," \t");
		$ul_cpu=gettok($sys1[$_ii],9," \t");
		$ul_mem=gettok($sys1[$_ii],10," \t");
		$ul_cmd=gettok($sys1[$_ii],12," \t");
	    
		$ul_status=(trim($ul_status)=="S") ? "sleeping" : $ul_status;
		$ul_status=(trim($ul_status)=="R") ? "running" : $ul_status;
		$ul_status=(trim($ul_status)=="D") ? "uninterruptable sleep" : $ul_status;
		$ul_status=(trim($ul_status)=="T") ? "traced or stopped" : $ul_status;
		$ul_status=(trim($ul_status)=="Z") ? "zombie" : $ul_status;

		print("<tr><td> <b>$ul_cmd</b></td><td> $ul_pid </td><td><i> $ul_status </td><td><i> $ul_cpu</i> </td><td><i> $ul_mem</i></td><td><i> ".rebytes($ul_res)."</i></td></tr> \n  \n");
	    }
	} else {
	    print("Не удалось выполнить <i>\"sudo top -n1 -b | grep $proc\"</i>.<br>  \n");
	    wlog("Не удалось выполнить <i>\"sudo top -n1 -b | grep $proc\"</i>.<br>  \n",2,FALSE,3,FALSE);
	}
	
	print("<script type=\"text/javascript\"> \n");
	print("setTimeout(\"document.location.replace('sysstat.php?p=topmon&t=chart&w=$_timeout');\",$_timeout*1000);\n</script>\n");
    }
    print("</body>\n</html>\n");


}


#----------------------------------------------------------------

function show_top_form()
{
    print("<br>\n");
    print("<table class=table32 cellpadding=\"5px\">\n");
    print("<form name=\"formdsg\" action=\"sysstat.php\">\n");
    print("<input type=\"hidden\" name=\"p\" value=\"system\">\n");
    print("<img src=\"icons/system-search.gif\"><b>запросы к top:</b><br><br>\n ");
    print("<tr><td> Процесс(ы)<br><font style=\"FONT: italic 8pt Arial;\">(один или несколько через запятую)</font></td>\n");
    print("<td> <input type=\"text\" name=\"monproc\" size=30 value=\"\" /> </td>  \n");
    print("<td> <input type=\"submit\" value=\" Ok \"></td></tr>\n");
    print("</form>\n</table>\n");

}
#----------------------------------------------------------------

function get_system_info($shfull=FALSE)
{

    global $syspage_show_procs,$_monproc,$viewlogs_system,$viewlogs_show_last;
    global $viewlogs_height,$viewlogs_width,$_top,$_grep;
    $sys1=posix_uname();

    print("<font class=text4>Системная информация:</font>\n<br><br>\n");
    print("ОС: <i>".$sys1["sysname"]." ".$sys1["machine"]." ".$sys1["release"]."<br></i>\n Hostname: <i>".$sys1["nodename"]."</i><br>\n");

    $rr=0; unset($sys2);
    list($rr,$sys2)=_exec2("$_top -n1 -b| $_grep Cpu");
    if( $rr==0) {
	$_cpu_used=str_replace("us","",gettok(str_replace("Cpu(s): ","",$sys2[0]),1,","));
	print("<br>CPU загружен на <i> $_cpu_used</i><br> \n");
    }
    
    $dproc=disk_free_space("/")/(disk_total_space("/")/100);
    
    print("<b>Свободное место на диске: </b><br>\n");
    $sys2=array();
    $rr=0;
    list($rr,$sys2)=_exec2("df -Plh");
    if( $rr==0) {
	print("<table class=table3>\n");
	foreach($sys2 as $sys2key => $sys2value) {
	    if( isset($asys2)) unset($asys2);
	    $asys2=explode(" ",_trimline($sys2value));
#	Don't show info about tmpfs
	    if( trim($asys2[0])=="tmpfs") continue;
	    print("<tr>\n");
	    if( $sys2key==0) {
		for($n=0;$n<(count($asys2)-1);$n++) print("<td><b>".$asys2[$n]."</b></td>\n");
	    } else {
		substr_replace("%","",$buf=$asys2[4]);
		$flWarning=($buf<=5) ? TRUE:FALSE;
		foreach($asys2 as $asys2value) 	print("<td $flWarning>".(( $flWarning) ? "<font color=red><b>":"").$asys2value.(( $flWarning) ? "</b></font>":"")."</td>\n");
	    }
	    print("</tr>\n");
	}
	print("</table>\n<br>\n");
    }


    $sys2=array();
    $rr=0;
    list($rr,$sys2)=_exec2("$_top -n1 -b| $_grep Mem");
    if( $rr==0) {
	$_mem_all=str_replace("Mem: ","",$sys2[0]);
	$_mem_total=bytes2mega(mega2bytes(trim(str_replace("total","",gettok($_mem_all,1,",")))));
	$_mem_used=bytes2mega(mega2bytes(trim(str_replace("used","",gettok($_mem_all,2,",")))));
	$_mem_free=bytes2mega(mega2bytes(trim(str_replace("free","",gettok($_mem_all,3,",")))));
	print("<b>Оперативная память:</b><table class=table3><tr><td>всего:</td><td><i> $_mem_total</td></tr><tr><td>использовано:</td><td><i> $_mem_used</td></tr><tr><td>свободно:</td><td><i> $_mem_free</td></tr></table>\n");
    }

/*  if( $shfull) {
    print("<br>\n<b>Системные логи:</b><br>");
    $ccol=coltoks($viewlogs_system,";");
    for($ii=1;$ii<=$ccol;$ii++) {
	$file1=trim(gettok($viewlogs_system,$ii,";"));
	if( $file1=="") continue;
	if(!file_exists($file1)) continue;
	$fsize=bytes2mega("".filesize($file1));
	$ftime=date("d-m-Y H:i:s",filemtime($file1));
	print("<tr><td style=\"FONT: 10pt Verdana\">$file1</td><td> <font class=subt1>размер:</font> $fsize </td><td> <font class=subt1>дата:</font> $ftime</td><td> <a href=\"sysstat.php?p=system&shlog=$file1&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" title=\"Смотреть лог\"><img src=\"icons/log.gif\" title=\"Смотреть лог\"></a> </td></tr>\n");
    }
    print("</table>\n<br>\n");
  }
*/
    print("<br>\n<table class=table2><tr>\n");
    print("<tr>\n");
    print("<td><a href=\"sysstat.php?p=systemlogs\" title=\"Системные логи\"><img src=\"icons/system-monitor.gif\" title=\"Системные логи\"></a></td> \n");
    print("<td><a href=\"sysstat.php?p=systemlogs\" title=\"Системные логи\" style=\"FONT: bold 11pt Arial;\">Системные логи </a></td>\n");
    print("</tr><tr>\n");
    print("<td><a href=\"pmonitor.php\" title=\"Монитор процессов\"><img src=\"icons/system-monitor.gif\" title=\"Монитор процессов\"></a></td> \n");
    print("<td><a href=\"pmonitor.php\" title=\"Монитор процессов\" style=\"FONT: bold 11pt Arial;\">Монитор процессов </a></td>\n");
    print("</tr><tr>\n");
    print("<td><a href=\"services.php\" title=\"Системные сервисы\"><img src=\"icons/system-monitor.gif\" title=\"Системные сервисы\"></a></td> \n");
    print("<td><a href=\"services.php\" title=\"Системные сервисы\" style=\"FONT: bold 11pt Arial;\"><b>Системные сервисы </a></td>\n");
    print("</tr>\n</table>\n ");
    show_top_form();
  if( trim($_monproc)!="") {
    $pcol=coltoks($_monproc,",;/");
    print(" <a href=\"sysstat.php?p=system&monproc=$_monproc\" title=\"Обновить результат запроса\"><img src=\"icons/stock_redo.gif\" title=\"Обновить результат запроса\"></a>&nbsp <b><i>results:</i></b><br><br>\n");
    print("<table class=table21 width=\"600px\" cellpadding=\"3px\">\n");
    if( $pcol>0) {
	print("<tr><th> Процесс </th><th> pid </th><th> Статус </th><th> %CPU </th><th> %MEM </th><th> RES </th></tr> \n");
	if( $pcol>1) {
	    for( $ii=1; $ii<=$pcol; $ii++) {
		get_process_tabinfo(gettok($syspage_show_procs,$ii,",;/"));
	    }
	    print("</table>\n");
	} else {
	    get_process_tabinfo(gettok($_monproc,1,",;/"));
	}
    }
  }
  wlog("Просмотр системной информации",0,FALSE,1,FALSE);
  

}


#------------------------------------------------------------------------

function get_system_logs($shfull=FALSE)
{

    global $syspage_show_procs,$_monproc,$viewlogs_system,$viewlogs_show_last;
    global $viewlogs_height,$viewlogs_width,$_top,$_grep,$_sort;
    $sys1=posix_uname();

    print("<font class=text4>Системные логи:</font>\n<br><br>\n");
    
    print("<table class=notable><tr>\n");
    print("<a href=\"sysstat.php?p=system\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td>\n");
    print("<a href=\"sysstat.php?p=system\" title=\"Назад\">Назад</a> </td>\n");
    print("</tr></table>\n<br>\n");

    $bufsort="";
    if(( trim($_sort)=="named") || ( !trim($_sort))) {
	$bufsort=" -r";
    } elseif( trim($_sort)=="nameu") {
	$bufsort="";
    } elseif( trim($_sort)=="sized") {
	$bufsort=" -S";
    } elseif( trim($_sort)=="sizeu") {
	$bufsort=" -Sr";
    } elseif( trim($_sort)=="dated") {
	$bufsort=" -t";
    } elseif( trim($_sort)=="dateu") {
	$bufsort=" -tr";
    }
    $sys2=array();
    $rr=0;
    list($rr,$sys2)=_exec2("ls -1 /var/log ".$bufsort."| awk '$0!~/.[0123456789]/'");
    if( $rr>0) {
	print("Ошибка получения списка файлов каталога /var/logs.");
	exit;
    }
    print("<table class=table31 cellpadding=\"3px\" width=\"85%\">\n");
    print("<tr>\n");
    print("<th> Name <a href=\"sysstat.php?p=systemlogs&sort=nameu\" title=\"По возрастанию\">&#8593;</a><a href=\"sysstat.php?p=systemlogs&sort=named\" title=\"По убыванию\">&#8595;</a> </th>\n");
    print("<th> Size <a href=\"sysstat.php?p=systemlogs&sort=sizeu\" title=\"По возрастанию\">&#8593;</a><a href=\"sysstat.php?p=systemlogs&sort=sized\" title=\"По убыванию\">&#8595;</a> </th>\n");
    print("<th> Date <a href=\"sysstat.php?p=systemlogs&sort=dateu\" title=\"По возрастанию\">&#8593;</a><a href=\"sysstat.php?p=systemlogs&sort=dated\" title=\"По убыванию\">&#8595;</a> </th>\n");
    print("<th> View </th>\n");
    print("</tr>\n");
    foreach($sys2 as $sk => $file) {
	if( !trim($file)) continue;
	$file1="/var/log/".$file;
	if(!file_exists($file1)) continue;
	$fsize=bytes2mega("".filesize($file1));
	$ftime=date("d-m-Y H:i:s",filemtime($file1));
	print("<tr><td style=\"FONT: 10pt Verdana\">$file1</td><td> <font class=subt1>размер:</font> $fsize </td><td> <font class=subt1>дата:</font> $ftime</td><td> <a href=\"sysstat.php?p=system&shlog=$file1&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" title=\"Смотреть лог\"><img src=\"icons/log.gif\" title=\"Смотреть лог\"></a> </td></tr>\n");
    }
    print("</table>\n<br><br>\n");
    print("<table class=notable><tr>\n");
    print("<a href=\"sysstat.php?p=system\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a> </td>\n");
    print("<a href=\"sysstat.php?p=system\" title=\"Назад\">Назад</a> </td>\n");
    print("</tr></table>\n<br>\n");

    wlog("Просмотр списка системных логов",0,FALSE,1,FALSE);
  

}


#------------------------------------------------------------------------

function get_ulog_info()
{
    global $ulog_logfile, $show_last_loglines, $ulog_dbname,$mysql_brief_dbinfo;
    global $mysql_host,$mysql_user, $mysql_password;
    global $mysql_ulogd_host,$mysql_ulogd_user, $mysql_ulogd_password;
    global $viewlogs_height,$viewlogs_width;
    print("<font class=top3>ULOG<br>\n");
    print("<hr size=1 align=left width=\"95%\">\n<br>\n");

    print("<table class=notable width=\"450px\">\n");
    print("<tr>\n<td valign=top align=left>\n");

    print("<font class=text32b>Состояние БД:</font><br>\n");
    ubase_show_status("$ulog_dbname",FALSE,TRUE);
    print("<br>\n");

    get_process_info("ulogd",TRUE);
    print("<br>\n");

    if( ( !file_exists($ulog_logfile)) or ( !is_readable($ulog_logfile))) {
	wlog("Ulogd log file $ulog_logfile is not found or not accessible..",2,TRUE,5,TRUE);
    } else {
	print("<font class=text32b>Состояние лога:</font><br>\n");
	print("<table class=table31 cellpadding=\"3px\" width=\"95%\">\n");
	print("<tr><td> Файл </td><td> <font class=text41><b> $ulog_logfile </b></td></tr>\n");
	print("<tr><td> Размер </td><td> <i>".bytes2mega(filesize($ulog_logfile))."</i></td></tr>\n");
	print("<tr><td> Дата изменения: </td><td> ".date("r",filemtime($ulog_logfile))."</font></td></tr>\n");
	print("<tr><td colspan=2 align=right>\n");
	print("<a href=\"sysstat.php?p=ulog&shlog=$ulog_logfile&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" title=\"Просмотр $ulog_logfile\"><img src=\"icons/sinfo_32.gif\" title=\"Посмотреть лог\"></a>\n");
	print("&nbsp&nbsp<a href=\"sysstat.php?p=ulogd&prst=1&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" title=\"Просмотр $ulog_logfile\"><img src=\"icons/apps_32.gif\" title=\"Service ulogd restart\"></a>\n");
	print("</td></tr>\n</table>\n");
    }
    print("<br>\n");

    print("<font class=text32b>Обслуживание БД:</font><br>\n");
    show_check_table();
    show_repair_table();
    print("<div style=\"padding-left:140px\">\n");
    print("<a href=\"sysstat.php?p=ulog&mode=optimize&run=1\" title=\"Оптимизация базы\" class=a3>Оптимизировать БД</a><br>\n");
    print("<a href=\"sysstat.php?p=ulog&mode=cutdb\" title=\"Урезать базу до даты...\" class=a3>Урезать БД до даты...</a><br>\n");
    print("</div>\n");

    print("</td></tr>\n</table>\n");
    wlog("Просмотр информации об ULOG",0,FALSE,1,FALSE);
}

#-------------------------------------------------------------------------

function get_crond_info($pagemode="view")
{
    global $viewlogs_height,$viewlogs_width,$iptconf_dir,$crond_logfile;
    global $_crontab, $_service, $_crond, $_sudo, $_top, $_grep, $_crontabd;
    global $viewlogs_height,$viewlogs_width,$_tasknum;
    global $_pshell,$_ppath,$_pmailto,$_phome;
    global $_p1,$_p2,$_p3,$_p4,$_p5,$_pn;
    
    print("<font class=top3><b>Crond</b></font><br>\n");
    get_process_info("crond");
    print("<hr width=\"330px\" align=left size=1>\n");
    print("<br>\n<font style=\"FONT: bold 13pt Arial; color:000080;\">crontab:</font><br><br>\n");
    if(( !file_exists($_crontab)) or( !is_readable($_crontab))) {
	wlog("Файл $_crontab не существует или недоступен для чтения",2,TRUE,5,TRUE); 
    } else {
	print("<font class=text21>\n");
	print("Файл: $_crontab <br>\nРазмер: <i>".bytes2mega(filesize($_crontab))."</i><br>");
	print("Дата изменения: ".date("r",filemtime($_crontab))."</font><br>\n<br>\n");
	$afile=file($_crontab);
	$pshell=""; $ppath=""; $pmailto=""; $phome="";
	$astr=array();
	$flin=FALSE;
	$ii=0;
	foreach($afile as $afkk => $afvv) {
	    $afvv=trim(strtolower($afvv));
	    if( $afvv=="") continue;
	    if( $afvv[0]=="#") continue;
	    $tagname=trim(gettok($afvv,1,"="));
	    if( $tagname == "shell") {
		$pshell = gettok($afvv,2,"=");
	    } elseif( $tagname == "path" ) {
		$ppath = gettok($afvv,2,"=");
	    } elseif( $tagname == "mailto" ) {
		$pmailto = gettok($afvv,2,"=");
	    } elseif( $tagname == "home" ) {
		$phome = gettok($afvv,2,"=");
	    } else {
		$ii++;
		if(( $pagemode=="deltask") and ( $_tasknum=="$ii")) { 
		    $ii--; $pagemode="save"; 
		    continue; 
		}
		$astr["$ii"]=$afvv;
	    }

	}
	if( $pagemode=="editedtop") {
	    $pshell=( $pshell != $_pshell) ? $_pshell : $pshell;
	    $ppath=( $ppath != $_ppath) ? $_ppath : $ppath;
	    $pmailto=( $pmailto != $_pmailto) ? $_pmailto : $pmailto;
	    $phome=( $phome != $_phome) ? $_phome : $phome;
	    $pagemode="save";
	    wlog("Crond: сохранение редактирования параметра",0,FALSE,1,FALSE);
	}
	if( $pagemode=="editedtask") {
	    $astr[$_tasknum]="$_p1 $_p2 $_p3 $_p4 $_p5 $_pn";
	    $pagemode="save";
	    wlog("Crond: сохранение редактирования задания",0,FALSE,1,FALSE);
	}
	if( $pagemode=="addedtask") {
	    $astr[$ii+1]="$_p1 $_p2 $_p3 $_p4 $_p5 $_pn";
	    $pagemode="save";
	    wlog("Crond: сохранение добавления задания ",0,FALSE,1,FALSE);
	}
	
	
	if( $pagemode=="save") {
	    if( file_exists($_crontab.".bak")) _exec2("rm -f $_crontab.bak");
	    $_crontabtmp=$iptconf_dir."/crontmp";
	    if( file_exists($_crontabtmp)) unlink($_crontabtmp);
	    _exec2("$_service crond stop");
	    _exec2("$_crontabd -u root -r");
	    _exec2("echo \"SHELL=$pshell\" >> $_crontabtmp");
	    _exec2("echo \"PATH=$ppath\" >> $_crontabtmp");
	    _exec2("echo \"MAILTO=$pmailto\" >> $_crontabtmp");
	    _exec2("echo \"HOME=$phome\" >> $_crontabtmp");
	    _exec2("echo \" \" >> $_crontabtmp");
	    _exec2("echo \"# run-parts\" >> $_crontabtmp");
	    foreach($astr as $askey => $asval) _exec2("echo \"$asval\" >> $_crontabtmp");
	    _exec2("mv ".$_crontab." ".$_crontab.".bak");
	    _exec2("mv ".$_crontabtmp." ".$_crontab);
	    _exec2("$_crontabd -u root $_crontab");
	    _exec2("$_service crond start");
	    print("<script type=\"text/javascript\">\n document.location.replace('sysstat.php?p=crond');\n </script>");
	    exit;
	}
	if( $pagemode!="edittop") {
	    if(($pagemode=="") or ( $pagemode=="view")) print("<div style=\"position:absolute;left:130px;\"> <a href=\"sysstat.php?p=crond&pagemode=edittop\" title=\"Редактировать параметры\"><img src=\"icons/gtk-edit.gif\" title=\"Редактировать параметры\"></a>\n</div>\n");
	    print("<b>Параметры:</b><br><br>\n<table class=table3 cellpadding=\"4px\" style=\"padding-left:35px;float:left;\">\n");
	    print("<tr><td> SHELL </td><td>  $pshell </td></tr>\n");
	    print("<tr><td> PATH </td><td> $ppath </td></tr>\n");
	    print("<tr><td> MAILTO </td><td> $pmailto </td></tr>\n");
	    print("<tr><td> HOME </td><td> $phome </td></tr>\n");
	    print("</table>\n");
	    print("<br><br>\n");
	    
	    print("<br><br><br><br><br>\n");
	
	} elseif( $pagemode=="edittop") {
	    print("<b>Параметры:</b><br>\n");
	    print("<form name=\"cronedit\" action=\"sysstat.php\" method=\"POST\">\n");
	    print("<input type=\"hidden\" name=\"p\" value=\"crond\">\n");
	    print("<input type=\"hidden\" name=\"pagemode\" value=\"editedtop\">\n");
	    print("<table class=table3 cellpadding=\"4px\" style=\"padding-left:35px;\">\n");
	    print("<tr><td> SHELL </td><td> <input type=\"text\" name=\"pshell\" size=\"45\" value=\"$pshell\"> </td></tr>\n");
	    print("<tr><td> PATH </td><td> <input type=\"text\" name=\"ppath\" size=\"45\" value=\"$ppath\"> </td></tr>\n");
	    print("<tr><td> MAILTO </td><td> <input type=\"text\" name=\"pmailto\" size=\"45\" value=\"$pmailto\"> </td></tr>\n");
	    print("<tr><td> HOME </td><td> <input type=\"text\" name=\"phome\" size=\"45\" value=\"$phome\"> </td></tr>\n");
	    print("</table>\n<br>\n");
	    print("<a href=\"sysstat.php?p=crond\" title=\"Вернуться без сохранения\"><img src=\"icons/gtk-undo.gif\" title=\"Вернуться без сохранения\"></a>\n");
	    print("<input type=\"SUBMIT\" name=\"sbmt\" value=\"Сохранить\"> \n");
	    print("<br>\n</form>\n");
	}
	$ii=0;
	if(( $pagemode=="") or ( $pagemode=="view")) {
	    print("<div style=\"position:absolute;left:130px;\">\n");
	    print(" <a href=\"sysstat.php?p=crond&pagemode=addtask&tasknum=9999\" title=\"Добавить задание\"><img src=\"icons/list-new.gif\" title=\"Добавить задание\"></a><br><br>\n ");
	    print("</div>\n");
	}
	$bufline1=(( $pagemode=="") or ( $pagemode=="view")) ? "style=\"display:inline-block;position:relative;left:5px;\"" : "";
	print("<div>\n");
	print("<div $bufline1>\n<b>Задания:</b><br><br>\n<table class=table3 cellpadding=\"4px\" style=\"padding-left:35px;\">\n");
	foreach( $astr as $askk => $asvv) {
	    $ii++;
	    $asvv=_trimline($asvv);
	    $p1=gettok($asvv,1," \t");
	    $p2=gettok($asvv,2," \t");
	    $p3=gettok($asvv,3," \t");
	    $p4=gettok($asvv,4," \t");
	    $p5=gettok($asvv,5," \t");
	    $pn=str_replace("$p1 $p2 $p3 $p4 $p5","",$asvv);
	    
	    if( $pagemode!="edittask") {

		print("<tr><td> $p1 </td><td> $p2 </td><td> $p3 </td><td> $p4 </td><td> $p5 </td><td> $pn </td>\n ");
		print("<td> <a href=\"sysstat.php?p=crond&pagemode=edittask&tasknum=$ii\" title=\"Редактировать задание\"><img src=\"icons/gtk-edit.gif\" title=\"Редактировать задание\"></a> \n");
		print("&nbsp <a href=\"sysstat.php?p=crond&pagemode=deltask&tasknum=$ii\" title=\"Удалить задание\"><img src=\"icons/gtk-delete.gif\" title=\"Удалить задание\"></a> </td></tr>\n");
	    
	    } elseif( $pagemode=="edittask" ) {
		if( $_tasknum!="$ii") {
		    print("<tr><td> $p1 </td><td> $p2 </td><td> $p3 </td><td> $p4 </td><td> $p5 </td><td> $pn </td>\n ");
		    print("<td> &nbsp </td></tr>\n");
		} else {
		    print("<form name=\"cronedit\" action=\"sysstat.php\" method=\"POST\">\n");
		    print("<input type=\"hidden\" name=\"p\" value=\"crond\">\n");
		    print("<input type=\"hidden\" name=\"pagemode\" value=\"editedtask\">\n");
		    print("<input type=\"hidden\" name=\"tasknum\" value=\"$_tasknum\">\n");
		    print("<tr><td> <input type=\"text\" name=\"p1\" size=\"6\" value=\"$p1\"> \n");
		    print("</td><td> <input type=\"text\" name=\"p2\" size=\"6\" value=\"$p2\"> \n");
		    print("</td><td> <input type=\"text\" name=\"p3\" size=\"6\" value=\"$p3\"> \n");
		    print("</td><td> <input type=\"text\" name=\"p4\" size=\"6\" value=\"$p4\"> \n");
		    print("</td><td> <input type=\"text\" name=\"p5\" size=\"6\" value=\"$p5\"> \n");
		    print("</td><td> <input type=\"text\" name=\"pn\" size=\"35\" value=\"$pn\"> \n ");
		    print("</td><td> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Ok\"> \n");
		    print("<a href=\"sysstat.php?p=crond\" title=\"Вернуться без сохранения\"><img src=\"icons/gtk-undo.gif\" title=\"Вернуться без сохранения\"></a></td></tr>\n</form>\n");		    
		    wlog("Crond: Редактирование задания",0,FALSE,1,FALSE);
		}	    
	    }
		
	}
	if( $pagemode=="addtask") {
	    print("<form name=\"cronadd\" id=\"cronadd\" action=\"sysstat.php\" method=\"POST\">\n");
	    print("<input type=\"hidden\" name=\"p\" value=\"crond\">\n");
	    print("<input type=\"hidden\" name=\"pagemode\" value=\"addedtask\">\n");
	    print("<input type=\"hidden\" name=\"tasknum\" value=\"9999\">\n");
	    print("<tr><td> <input type=\"text\" name=\"p1\" size=\"6\" value=\"02\"> \n");
	    print("</td><td> <input type=\"text\" name=\"p2\" size=\"6\" value=\"*\"> \n");
	    print("</td><td> <input type=\"text\" name=\"p3\" size=\"6\" value=\"*\"> \n");
	    print("</td><td> <input type=\"text\" name=\"p4\" size=\"6\" value=\"*\"> \n");
	    print("</td><td> <input type=\"text\" name=\"p5\" size=\"6\" value=\"*\"> \n");
	    print("</td><td> <input type=\"text\" name=\"pn\" size=\"35\" value=\"root \"> \n ");
	    print("</td><td> <input type=\"button\" name=\"ok\" onClick=\"javascript: document.getElementById('cronadd').submit(); \" value=\"Ok\"> \n");
	    print("<a href=\"sysstat.php?p=crond\" title=\"Вернуться без сохранения\"><img src=\"icons/gtk-undo.gif\" title=\"Вернуться без сохранения\"></a></td></tr>\n</form>\n");		    
	    wlog("Crond: Добавление нового задания",0,FALSE,1,FALSE);
	}
	print("</table>\n</div>\n");
    }
    
    print("<br><br>\n<hr width=\"330px\" align=left size=1 style=\"position:relative:left:5px;\">\n");

    print("<br>\n");
    if( ( !file_exists($crond_logfile)) or ( !is_readable($crond_logfile))) {
	wlog("Crond log file $crond_logfile is not found or not accessible..",1,TRUE,3,TRUE);
    } else {
	print("Состояние лога:<br><br>\n <font class=text41><b> $crond_logfile </b><br> Размер &nbsp&nbsp <i>".bytes2mega(filesize($crond_logfile))."</i><br> Дата изменения: ".date("r",filemtime($crond_logfile))."</font><br>\n<hr width=\"330px\" align=left><br>\n");
	print("<a href=\"sysstat.php?p=ulog&shlog=$crond_logfile&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" title=\"Просмотр $crond_logfile\"><img src=\"icons/sinfo_32.gif\" title=\"Посмотреть лог\"></a>\n");
	print("&nbsp&nbsp<a href=\"sysstat.php?p=crond&prst=1&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" title=\"Service crond restart\"><img src=\"icons/apps_32.gif\" title=\"Service crond restart\"></a><br>\n");
    }

    print("</div\n");


}

#-------------------------------------------------------------------------

function mysql_cutdb($dbname,$tbname,$pdate,$prun)
{
    global $mysql_host,$mysql_user, $mysql_password;
    if( trim($prun)=="") {
	
	$bufdate1=( trim($pdate)=="") ? "01-".date("m-Y") : $pdate;
	
	print("<br><br><font class=top1>Удаление информации из таблицы ulog</font><br><br>\n");
	print("<form name=\"cut01\" action=\"sysstat.php\">\n");
	print("<input type=\"hidden\" name=\"p\" value=\"ulog\">\n");
	print("<input type=\"hidden\" name=\"mode\" value=\"cutdb\">\n");
	print("<input type=\"hidden\" name=\"run\" value=\"1\">\n");
	print("<table class=table5 width=\"400px\" cellpadding=\"10px\">\n");
	print("<tr><td> до даты </td><td> <input id=\"d1\" name=\"d1\" value=\"$bufdate1\" size=20 /> \n");
	print("</td><td> <input type=\"submit\" name=sbmt value=\"Удалить\"> </td></tr>\n </table>\n</form>\n");
	print("<br><br>\n<a href=\"sysstat.php?p=ulog\" title=\"Назад (Отмена)\"><img src=\"icons/gtk-undo.gif\" title=\"Назад (Отмена)\">Назад (отмена)</a>\n");
    } else {
	
	if( trim($pdate)=="") {
	    print("Дата не указана!<br>");
	    return("");
	}
	
	$oobdate1=strdate2stamp($pdate."-23-59-59");

	$link=mysql_connect($mysql_host,$mysql_user,$mysql_password);
	if( !$link) wlog("Ошибка соединения с mysql в mysql_cutdb()...<br>\n mysql_err: ".mysql_error(),2,TRUE,5,TRUE);
	if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
        mysql_select_db($dbname);

	print("<br><br><font class=top1>Удаляются данные, подождите... <br><br></font>\n");
    
    
        $line1="DELETE FROM $tbname WHERE oob_time_sec<$oobdate1";
        if( !mysql_query($line1)) {
	    wlog("Ошибка запроса данных в mysql_cutdb()...<br>\n mysql_err: ".mysql_error(),2,TRUE,5,TRUE);
	}

	print("<br><br>\nУдаление завершено... <br><br>\n");
	print("<br><br><a href=\"sysstat.php?p=ulog\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\">Назад</a><br>\n");
	
	wlog("Удаление информации из БД ".$dbname."->".$tbname." старше $pdate",0,FALSE,1,FALSE);
    }


}

#-------------------------------------------------------------------------

function mysql_repair_table($dbname,$tbname,$mode="")
{
    global $mysql_host,$mysql_user, $mysql_password;
    ob_start();
    print("<br><br><font class=top1>Ремонт таблицы ulog выполняется, подождите... <br><br></font>\n");
    flush();
    ob_flush();
    
    $link=mysql_connect($mysql_host,$mysql_user,$mysql_password);
    if( !$link) {
	wlog("Ошибка соединения с mysql в mysql_repair_table()...<br>\n mysql_err: ".mysql_error(),2,TRUE,5,TRUE);
	exit;
    }
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    mysql_select_db($dbname);
    
    $line1="REPAIR TABLE $tbname $mode";
    if( !mysql_query($line1)) {
	wlog("Ошибка запроса данных в mysql_repair_table()...<br>\n mysql_err: ".mysql_error(),2,TRUE,5,TRUE);
    }
    
    $row=mysql_fetch_array($rez);
    print("<font class=text42>\n");
    print("Результат:<br><blockquote>Тип: ".$row["Msg_type"]."<br>Сообщение: ".$row["Msg_text"]."</blockquote>\n");
    print("<br><br><a href=\"sysstat.php?p=ulog\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\">Назад</a><br>\n");
    flush();
    ob_end_flush();
    wlog("Ремонт таблицы БД ".$dbname."->".$tbname,0,FALSE,1,FALSE);

}

#-------------------------------------------------------------------------


function mysql_check_table($dbname,$tbname,$mode="")
{
    global $mysql_host,$mysql_user, $mysql_password;
    if( (trim($dbname)=="") or (trim($tbname)=="")) return("");
    print("<br><br><font class=top1>Проверка таблицы ulog выполняется, подождите... <br><br></font>\n");
    
    $link=mysql_connect($mysql_host,$mysql_user,$mysql_password);
    if( !$link) {
	wlog("Ошибка соединения с mysql в mysql_repair_table()...<br>\n mysql_err: ".mysql_error(),2,TRUE,5,TRUE);
	exit;
    }
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    mysql_select_db($dbname);
    
    $line1="CHECK TABLE $tbname $mode";
    if( !mysql_query($line1)) {
	wlog("Ошибка запроса данных в mysql_check_table()...<br>\n mysql_err: ".mysql_error(),2,TRUE,5,TRUE);
	exit;
    }
    
    $row=mysql_fetch_array($rez);
    print("<font class=text42>\n");
    print("Результат:<br><blockquote>Тип: ".$row["Msg_type"]."<br>Сообщение: ".$row["Msg_text"]."</blockquote>\n");
    print("<br><br><a href=\"sysstat.php?p=ulog\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\">Назад</a><br>\n");
    wlog("Проверка таблицы БД ".$dbname."->".$tbname,0,FALSE,1,FALSE);
}



#-------------------------------------------------------------------------


function mysql_optimize_table($dbname,$tbname)
{
    global $mysql_host,$mysql_user, $mysql_password;
    if( (trim($dbname)=="") or (trim($tbname)=="")) return("");
    print("<br><br><font class=top1>Проверка таблицы ulog выполняется, подождите... <br><br></font>\n");
    
    $link=mysql_connect($mysql_host,$mysql_user,$mysql_password);
    if( !$link) {
	wlog("Ошибка соединения с mysql в mysql_repair_table()...<br>\n mysql_err: ".mysql_error(),2,TRUE,5,TRUE);
	exit;
    }
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    mysql_select_db($dbname);
    
    $line1="OPTIMIZE TABLE $tbname";
    if( !mysql_query($line1)) {
	wlog("Ошибка запроса данных в mysql_optimize_table()...<br>\n mysql_err: ".mysql_error(),2,TRUE,5,TRUE);
	exit;
    }
    
    $row=mysql_fetch_array($rez);
    print("<font class=text42>\n");
    print("Результат:<br><blockquote>Тип: ".$row["Msg_type"]."<br>Сообщение: ".$row["Msg_text"]."</blockquote>\n");
    print("<br><br><a href=\"sysstat.php?p=ulog\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\">Назад</a><br>\n");
    wlog("Оптимизация таблицы ".$dbname."->".$tbname,0,FALSE,1,FALSE);
    
}



#-------------------------------------------------------------------------


function show_ulogdb_panel($showInfo=FALSE)
{
    global $ulog_logfile, $show_last_loglines, $ulog_dbname,$mysql_brief_dbinfo;
    global $mysql_host,$mysql_user, $mysql_password;
    global $mysql_ulogd_host,$mysql_ulogd_user, $mysql_ulogd_password;
    print("<font class=top1><b>База данных $ulog_dbname</b><br>\n");
    print("<br><br><font class=text31>Состояние БД:<br>\n");
    ubase_show_status("$ulog_dbname");
    ob_start();
    print("<font class=text41>\n<h2 id=start>Выполняется запрос, подождите...</h2>\n");
    flush();
    ob_flush();
    
    $link=mysql_connect($mysql_ulogd_host,$mysql_ulogd_user,$mysql_ulogd_password);
    if( !$link) {
	wlog("Ошибка соединения с mysql в get_ulog_info()...",2,TRUE,5,TRUE);
	exit;
    }
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    mysql_select_db($ulog_dbname);
    $line="SELECT MIN(oob_time_sec) as min_timesec, MIN(DATE(FROM_UNIXTIME(oob_time_sec))) as min_date1 FROM ulog";
    $rez=mysql_query($line);
    if( !$rez) {
	wlog("Ошибка запроса данных в процедуре show_ulogdb_panel() в statlib.php...",2,TRUE,5,TRUE);
	exit;
    }
    $row=mysql_fetch_array($rez);
    print("БД Инфо:<br>\nСамая давняя дата в БД: ".$row["min_date1"]."<br>\n");
    print("Месяцев в БД: ".(idate("m")-idate("m",$row["min_timesec"]))."<br>\n");
    
    print("</font>\n");
    print("<script type=\"text/javascript\">\n");
    print("document.getElementById(\"start\").innerHTML=\"\"\n");
    print("</script>\n");
    
    flush();
    ob_flush();
    ob_end_flush();
    
}

#-------------------------------------------------------------------------


function get_mysql_info()
{
    global $mysql_host, $mysql_user, $mysql_password,$mysql_brief_dbinfo,$mysql_logfile;
    global $viewlogs_height,$viewlogs_width,$ulog_logfile,$mysql_fantomas_db;

    print("<font class=top3><b>MySQL</b><br>\n");
    print("<hr size=1 align=left width=\"95%\">\n");
    print("<br>\n<font class=text4>\n");
    
    print("<table class=notable width=\"450px\">\n");
    print("<tr><td valign=top>\n");

    print("<font class=text32b>Состояние сервера MySQL:</font><br>\n");
    print("<font class=text41 color=696969>\n");
    $link=mysql_connect($mysql_host,$mysql_user,$mysql_password);
    if( !$link) {
	print("<b>Попытка соединения с сервером прошла неудачно</b><br><br>\n");
	wlog("Ошибка соединения с mysql в get_mysql_info()",0,FALSE,1,FALSE);
    } else {
	print("<b>Попытка соединения:</b> <i>успешно</i><br>\n");
    }
    if( !mysql_ping($link)) {
	print("<b>Проверка mysql_ping:</b> <i> нет соединения</i><br>  \n");
    } else {
	print("<b>Проверка mysql_ping:</b> <i> успешно</i><br> \n");
    }
    print("<br> \n");

    get_process_info("mysqld",TRUE,TRUE);
    print("<br>\n");

    $astatus=explode('  ',mysql_stat($link));
    if( count($astatus)>0) {
	print("Текущий статус сервера: <br>\n");
	print("<table class=table3 cellpadding=\"3px\" width=\"95%\">\n");
	foreach($astatus as $var) print("<tr><td align=right style=\"padding-right:8px;\"> ".trim(gettok($var,1,":")).": </td><td> ".trim(gettok($var,2,":"))." </td></tr>\n");
	print("</table>\n<br>\n");
    }


    $dblist=mysql_list_dbs($link);
    print("<font class=text32b>Информация о БД:</font>\n");
    $ic=0;
    print("<table class=notable cellpadding=\"3px\" cellspacing=\"3px\" width=\"95%\">\n");
    print("<form name=\"dbinfo1\" id=\"dbinfo1\" title=\"Database info\" class=\"thickbox\" action=\"sysstat.php\">\n");
    print("<tr><td> Выбрать БД:&nbsp; </td>\n");
    print("<td> <span class=\"seldiv\"><SELECT name=\"sqldbinfo\" id=\"sqldbinfo\" onChange=\"javascript: document.getElementById('dbtn1').alt='sysstat.php?p=mysql&sqldbinfo='+this.value+'&height=$viewlogs_height&width=$viewlogs_width'; \">\n");
    while( $row=mysql_fetch_object($dblist)) {
	print("<option value=\"".$row->Database."\" ".(( $row->Database==$mysql_fantomas_db) ? "selected":"")."> ".$row->Database."</option>\n");
    }
    print("</SELECT></span>&nbsp; \n</td>\n<td>\n");
    print("<input alt=\"sysstat.php?p=mysql&sqldbinfo=$mysql_fantomas_db&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" id=\"dbtn1\" title=\"information_schema database info\" type=\"button\" value=\"Открыть\">\n");
    print("<input type=\"submit\" value=\"Открыть\" style=\"display:none;\">\n");
    print("</td>\n");
    print("</form>\n");
    print("</table><br>\n");

    if( file_exists($mysql_logfile)) {
	print("<font class=text32b>Состояние лога:</font><br>\n");
	print("<table class=table3 cellpadding=\"3px\" width=\"95%\">\n");
	print("<tr><td> Файл </td><td> $mysql_logfile </td></tr>\n");
	print("<tr><td> Размер </td><td> <i>".bytes2mega(filesize($mysql_logfile))."</i></td></tr>\n");
	print("<tr><td> Дата изменения: </td><td> ".date("r",filemtime($mysql_logfile))."</td></tr>\n");
	print("</table>\n<br>\n");
    } else {
	print("<font class=text41><b>Лог файл $mysql_logfile не существует или недоступен!</b><br></font><br><br>\n");
    }
    print("<a href=\"sysstat.php?p=mysql&shlog=$mysql_logfile&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" title=\"Просмотр $mysql_logfile\"><img src=\"icons/sinfo_32.gif\" title=\"Посмотреть лог\"></a>\n</font>\n");
    print("&nbsp&nbsp<a href=\"sysstat.php?p=mysqld&prst=1&height=$viewlogs_height&width=$viewlogs_width\" class=\"thickbox\" title=\"Просмотр $ulog_logfile\"><img src=\"icons/apps_32.gif\" title=\"Service mysqld restart\"></a><br>\n");




    print("</td></tr></table>\n");
    
    wlog("Просмотр страницы состояния MySQL",0,FALSE,1,FALSE);
}

#-------------------------------------------------------------------------

function get_iptables_info()
{
    global $_iptables,$iptconf_dir,$users_dir,$backup_dir;
    global $_sudo,$_grep;
    global $mysql_host,$mysql_user,$mysql_password;
    $link1=mysql_connect($mysql_host,$mysql_user,$mysql_password);
    if( !$link1) {
	wlog("Error connecting to mysql in get_iptables_info()...",2,TRUE,5,TRUE); exit;
    }
    
    mysql_select_db("fantomas");
    $rez1=mysql_query("SELECT * FROM fantomas");
    $row1=mysql_fetch_array($rez1);
    $date1=( !empty($row1["p_start_date"])) ? $row1["p_start_date"] : "";
    
    print("<font class=top3>Iptables</font><br>\n");
    print("<hr size=1 align=left width=\"95%\">\n<br>\n");

    print("<table class=notable width=\"95%\">\n");
    print("<tr><td valign=top>\n");
    
        
    print("<font class=text32b>Процедуры:</font><br>\n");

    print("<table class=table5e cellpadding=\"3px\" width=\"500px\">\n");
    print("<form name=\"newperiod\" action=\"ipt.php\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"newperiod\">\n");
    print("<tr><td style=\"FONT: bold 11pt Arial; color:330066;padding-left:15px;\"> Открытие нового периода </td></tr>\n");
    print("<tr><td>\n <table class=notable><tr><td valign=top><img src=\"icons/stock_dialog-question.gif\"> </td><td class=td41> &nbsp Снять и сбэкапить показания счетчиков, затем их очистить и текущую дату записать как новую дату начала периода. </td></tr></table>\n </td></tr>\n");
    print("<tr><td align=right class=borderbottom> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Выполнить\"> </td></tr>\n");
    print("</form>\n");
    
    print("<form name=\"export1\" action=\"ipt.php\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"exportcounts\">\n");
    print("<tr><td style=\"FONT: bold 11pt Arial; color:330066;padding-left:15px;\"> Выгрузка показаний счетчиков в файл </td></tr>\n");
    print("<tr><td>\n <table class=notable><tr><td valign=top><img src=\"icons/stock_dialog-question.gif\"> </td><td class=td41> &nbsp Выгрузить показания счетчиков в файл counters <font style=\"font-size:8pt; font-style: italic\">(Без перезагрузки конфигурации.)</font></td></tr></table>\n </td></tr>\n");
    print("<tr><td align=right class=borderbottom> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Выполнить\"> </td></tr>\n");
    print("</form>\n");

    print("<form name=\"reload1\" action=\"ipt.php\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"reload\">\n");
    print("<tr><td style=\"FONT: bold 11pt Arial; color:330066;padding-left:15px;\"> Перезагрузка конфигурации (RELOAD) </td></tr>\n");
    print("<tr><td> <input type=\"checkbox\" name=\"cnoex\" id=\"cnoex\" value=\"1\"><label for=\"cnoex\" class=td41>Не выполнять экспорт счетчиков <i>(использовать предыдущие показания из файла counters)</i></label> </td></tr>\n");
    print("<tr><td>\n <table class=notable><tr><td valign=top><img src=\"icons/stock_dialog-question.gif\"> </td><td class=td41> &nbsp Перезагрузка конфигурации iptables и ipset <font style=\"font-size:8pt; font-style: italic\">(Внимание! Все правила, сделанные вручную, будут утеряны!)</font></td></tr></table>\n </td></tr>\n");
    print("<tr><td align=right class=borderbottom> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Выполнить\"> </td></tr>\n");
    print("</form>\n");

    print("<form name=\"servicesave1\" action=\"ipt.php\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"servicesave\">\n");
    print("<tr><td style=\"FONT: bold 11pt Arial; color:330066;padding-left:15px;\"> Сохранение набора правил Iptables </td></tr>\n");
    print("<tr><td>\n <table class=notable><tr><td valign=top><img src=\"icons/stock_dialog-question.gif\"> </td><td class=td41> &nbsp Выполнение \"service iptcldr save\" - сохранение правил iptables в файле на диске.</font></td></tr></table>\n </td></tr>\n");
    print("<tr><td align=right class=borderbottom> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Выполнить\"> </td></tr>\n");
    print("</form>\n");

    print("<form name=\"servicerestart1\" action=\"ipt.php\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"servicerestart\">\n");
    print("<tr><td style=\"FONT: bold 11pt Arial; color:330066;padding-left:15px;\"> Перезапуск сервиса iptcldr </td></tr>\n");
    print("<tr><td>\n <table class=notable><tr><td valign=top><img src=\"icons/stock_dialog-question.gif\"> </td><td class=td41> &nbsp Выполнение \"service iptcldr restart\" - Очистка конфигурации iptables и ipset, перезагрузка iptables, затем формирование конфигурации заново..</font></td></tr></table>\n </td></tr>\n");
    print("<tr><td align=right class=borderbottom> <input type=\"SUBMIT\" name=\"sbmt\" value=\"Выполнить\"> </td></tr>\n");
    print("</form>\n</table>\n");

    print("</td><td valign=top>\n");

    print("<font class=text32b>Информация:</font><br>\n");
    print("<table class=table5 cellpadding=\"4px\" width=\"96%\"><tr>\n");
    print("<td class=td41ye>Дата начала периода: </td><td class=td41ye>".(( $date1!="") ? "$date1" : "неизвестна")."\n");
    print("</td></tr></table>\n<br>\n");


    print("<br>\n");
    print("<font class=text32b>Текущий файл счетчиков:</font><br>\n");
    $ffile=$iptconf_dir."/counters";
    if( file_exists($ffile)) {
	$finfo=stat($ffile);
	print("<table class=table5 cellpadding=\"4px\" width=\"96%\">\n");
	print("<tr><td class=td41ye><a href=\"ipt.php?p=viewcounts&file=".$iptconf_dir."/counters&height=750&width=700\" class=\"thickbox\" style=\"background-color:transparent;\" title=\"Просмотр файла $iptconf_dir/counters\">$iptconf_dir/counters</a> </td></tr>\n");
	print("<tr><td class=td41ye>size: ".bytes2mega($finfo["size"])."</td></tr>\n");
	print("<tr><td class=td41ye>date: ".strftime("%d %b %Y %T",$finfo["mtime"])."</td></tr>\n");
	print("</table>\n");
	print("<br>\n");

    $files=scandir($backup_dir);
    rsort($files,SORT_STRING);
    $i=0;
    foreach($files as $k2 => $val2) if( substr($files[$k2],0,8)=="counters") $i++;

	print("<br>\n<a href=\"sysstat.php?p=archview\" style=\"color:330066;\"><font class=text42s>Архив файлов счетчиков</font></a><br> <font style=\"FONT:italic 9pt Arial;\">Всего: $i файлов(а)</font>\n<br>\n");
    }
    
    print("</td></tr>\n</tr><td colspan=2>\n");


    

    print("<br><br>\n");
    print("</td></tr>\n</table>\n");
    wlog("Просмотр сраницы состояния Iptables",0,FALSE,1,FALSE);
    
}
#-------------------------------------------------------------------------
function show_counts_archive()
{
    global $backup_dir,$iptconf_dir;
    print("<br>\n<font class=top3>Архив файлов счетчиков</font><br><br>\n");

    print("<table class=notable><tr><td><a href=\"sysstat.php?p=iptables\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a></td>\n");
    print("<td><a href=\"sysstat.php?p=iptables\" title=\"Назад\">Назад</a> </td></tr></table>");

    print("<font class=text42s> &nbsp </font><br>\n");
?>
<script type="text/javascript">
function setAll(pvalue) {
    var frm=document.getElementById('arh');
    for(var i=0; i<frm.elements.length; i++) {
	if( frm.elements[i].type=='checkbox') {
	    frm.elements[i].checked=pvalue;
	}
    }
}
</script>
<?php
    print("<form name=\"arh\" id=\"arh\" method=\"POST\">\n");
    print("<input type=\"HIDDEN\" name=\"p\" value=\"arhdel\">\n");
    print("<table class=notable width=\"85%\">\n<tr>\n");
    print("<td><font class=text41> <input type=\"CHECKBOX\" name=\"chkall\" id=\"chkall\" onClick=\"javascript: setAll(this.checked)\"><label for=\"chkall\"> Выбрать все</label> </font></td>\n");
    print("<td align=right><input type=\"button\" name=\"sbmt\" id=\"sbmt\" value=\"Удалить выбранные\" onClick=\"javascript: document.getElementById('arh').submit();\"></td>\n");
    print("</tr>\n</table>\n");
    print("<table class=table5 cellpadding=\"4px\" width=\"85%\">\n");
    
    $files=scandir($backup_dir);
    rsort($files,SORT_STRING);
    $i=0;
    foreach($files as $k2 => $val2) {
	if( isset($finfo)) unset($finfo); 
	$finfo=stat($backup_dir."/".$files[$k2]);
	if( substr($files[$k2],0,8)=="counters") {
	    print("<tr>\n");
	    $chkname="arh".substr($val2,8,-4);
	    print("<td class=td41ye> <input type=\"CHECKBOX\" name=\"$chkname\" id=\"$chkname\" value=\"$chkname\"> </td>\n");
#	    print("<td class=td41ye><a href=\"ipt.php?p=viewcounts&file=$backup_dir/$val2&height=750&width=700\" class=\"thickbox\" style=\"background-color:transparent;\" title=\"Просмотр файла $iptconf_dir/counters\">$val2</a> </td>\n");
	    print("<td class=td41ye>$val2 </td>\n");
	    print("<td class=td41ye>size: ".bytes2mega($finfo["size"])."</td>\n");
	    print("<td class=td41ye>date: ".strftime("%d %b %Y %T",$finfo["mtime"])."</td>\n");
	    print("<td class=td41ye> \n");
	    print("<a href=\"ipt.php?p=viewcounts&file=$backup_dir/$val2&height=750&width=700\" class=\"thickbox\" style=\"background-color:transparent;\" title=\"Просмотр файла Просмотр файла $backup_dir/$val2\"><img src=\"icons/eog.gif\" title=\"Просмотр файла $backup_dir/$val2\"></a> &nbsp \n");
	    print("<a href=\"sysstat.php?p=usecfile&cf=".$val2."\" title=\"Запустить процедуру RELOAD с применением показаний счетчиков из этого файла\"><img src=\"icons/gtk-apply.gif\" title=\"Запустить процедуру RELOAD с применением показаний счетчиков из этого файла\"></a>\n");
	    print("</td>\n");
	    print("</tr>\n");

	}
	$i++; 
    }
    print("</form>\n</table>\n<br><br>\n");
    print("<table class=notable><tr><td><a href=\"sysstat.php?p=iptables\" title=\"Назад\"><img src=\"icons/gtk-undo.gif\" title=\"Назад\"></a></td>\n");
    print("<td><a href=\"sysstat.php?p=iptables\" title=\"Назад\">Назад</a> </td></tr></table>");
    print("<br><br>\n");


}
#-------------------------------------------------------------------------
function counts_archive_del()
{
    global $backup_dir,$iptconf_dir,$_POST,$_proc;
    if( $_proc!="arhdel") return(FALSE);
    
    foreach($_POST as $pkey => $pval) {
	$file="counters".str_replace($iid=substr($pkey,0,3),"",$pkey).".bkp";
	if( $iid=="arh") {
	    if( !is_writable($backup_dir."/".$file)) { print("Файл $file недоступен для удаления!<br>\n"); continue; }
	    unlink($backup_dir."/".$file);
	}
    }
    show_counts_archive();
    wlog("Удаление файлов из архива файлов счетчиков",0,FALSE,1,FALSE);
}
#-------------------------------------------------------------------------
function counts_archive_usecfile()
{
    global $backup_dir,$iptconf_dir,$_POST,$_proc,$_cfile;
    if( $_proc!="usecfile") return(FALSE);
    if( !trim($_cfile)) return(FALSE);
    
    if( !is_readable($backup_dir."/".$_cfile)) { print("Файл $_cfile недоступен для чтения!<br>\n"); return(FALSE); }
    if( !is_writable($iptconf_dir."/counters")) { print("Файл counters недоступен для записи!<br>\n"); return(FALSE); }
    if( !copy($iptconf_dir."/counters",$backup_dir."/counters".strftime("%Y%m%d%H%M").".bkp")) {
	wlog("Ошибка копирования файла counters в каталог $backup_dir",2,TRUE,5,TRUE); return(FALSE);
    }
    unlink($iptconf_dir."/counters");
    if( !copy($backup_dir."/".$_cfile,$iptconf_dir."/counters")) {
	wlog("Ошибка копирования файла $_cfile в каталог $iptconf_dir",2,TRUE,5,TRUE); return(FALSE);
    }

    wlog("Применение файла счетчиков $_cfile",0,FALSE,1,FALSE);
    print("<script type=\"text/javascript\">\n");
    print("top.frames('basefrm').document.location.replace('ipt.php?cnoex=1&p=reload');\n");
    print("</script>\n");

}

?>