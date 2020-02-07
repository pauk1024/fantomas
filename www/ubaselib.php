<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: ubaselib.php
# Description: 
# Version: 2.8.2.3
###




function conv2koi($str5="")
{
    if(trim($str5)=="") return("");
    $rez5=htmlentities($str5,ENT_NOQUOTES,"koi8r");
    return($rez5);
}

#-----------------------------------------------------------------------

function ubase_show_status($dbname,$briefly=FALSE,$showFull=FALSE)
{
    global $ulog_dbname, $mysql_host, $mysql_user, $mysql_password;
    global $mysql_ulogd_host, $mysql_ulogd_user, $mysql_ulogd_password;
    global $viewlogs_height,$viewlogs_width;
    
    if( ($dbname=="") or ( !$dbname)) return("");
    $link1=mysql_connect($mysql_ulogd_host,$mysql_ulogd_user,$mysql_ulogd_password);
    if( !$link1) { wlog("Error connecting to mysql in ubase_show_status()!",2,TRUE,5,TRUE); exit; }
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link1);
    
    if( !mysql_select_db($dbname)) {
	wlog("Error using database $dbname in ubase_show_status()!",2,TRUE,5,TRUE); exit;
    }
    $result=mysql_query("SHOW TABLE STATUS");
    $tbsize=0; $dbsize=0;
    $dbname=conv2koi($dbname);
    print("<table class=table3 width=\"95%\">\n");
    print("<tr><td> <b> mysql_host </b></td><td><b> $mysql_ulogd_host</b> </td></tr>  \n");
    print("<tr><td> <b>".conv2koi("БД:")."</b></td><td><b> $dbname</b> </td></tr>  \n");
    $tbcol=0; $tbrec=0;
    while( $row1=mysql_fetch_array($result)) {
	$tablename= $row1["Name"];
	$tbsize += $row1["Data_length"]+$row1["Index_length"];
	$recs=$row1["Rows"];
	$updated=$row1["Update_time"];
	$bupd1=gettok($updated,1," \t");
	$updated=gettok($bupd1,3,"-")."-".gettok($bupd1,2,"-")."-".gettok($bupd1,1,"-")." ".gettok($updated,2," \t");
	$tbcol++;
	$tbrec +=$recs;
	$tablename=conv2koi($tablename);
	$recs=conv2koi($recs);
	$updated=conv2koi($updated);
	if( !$briefly) {
	    print("<tr><td> ".conv2koi("Имя таблицы:")." </td><td> $tablename </td></tr> \n");
	    print("<tr><td> ".conv2koi("Размер:")." </td><td> ".conv2koi(bytes2mega($tbsize))." </td></tr> \n");
	    print("<tr><td> ".conv2koi("Записей:")." </td><td> $recs </td></tr> \n");
	    print("<tr><td> ".conv2koi("Последнее обновление:")." </td><td> $updated </td></tr> \n");
	    print("<tr><td colspan=2> &nbsp&nbsp&nbsp </td></tr>");
	}
	
	$dbsize +=$tbsize;
    }
    if( $briefly) {
	print("<tr><td> ".conv2koi("Таблиц в БД:")." </td><td><i> $tbcol </i></td></tr> \n");
	print("<tr><td> ".conv2koi("Всего записей:")." </td><td><i> $tbrec </i></td></tr> \n");
    }
    print("<tr><td><b> ".conv2koi("Размер БД:")." </b></td><td><b><i> ".conv2koi(bytes2mega($dbsize))." </i></b></td></tr> \n");

    mysql_free_result($result);

    if( $showFull) {
	print("<tr><td colspan=2> &nbsp&nbsp&nbsp </td></tr>");
	$line1="SELECT MIN(oob_time_sec) as oob_time1, DATE_FORMAT(FROM_UNIXTIME(MIN(oob_time_sec)),\"%d-%m-%Y\") as date1, PERIOD_DIFF(DATE_FORMAT(FROM_UNIXTIME(MAX(oob_time_sec)),\"%Y%m\"),DATE_FORMAT(FROM_UNIXTIME(MIN(oob_time_sec)),\"%Y%m\")) as period1 FROM ulog";
	$result2=mysql_query($line1);
	if( !$result2) { wlog("Ошибка второго запроса данных в процедуре ubase_show_status(): ".mysql_error(),2,TRUE,5,TRUE); exit; }
	$row2=mysql_fetch_array($result2);
	print("<tr><td> ".conv2koi("Дата первой записи в БД:")." </td><td> ".$row2["date1"]."</td></tr>\n");
	print("<tr><td> ".conv2koi("Месяцев с информацией:")." </td><td> ".$row2["period1"]."</td></tr>\n");

    }    

    print("</table>");
    mysql_close($link1);
    wlog("Просмотр состояния БД Ulogd",0,FALSE,1,FALSE);
    
}

#-------------------------------------------------------------


?>