<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: sysstat.php
# Description: 
# Version: 2.8.2.2
###




$context="sysstat.php";

require("./../config.php");
require("iptlib.php");
require("iptlib2.php");
require("ubaselib.php");

require("statlib.php");

$flAdminsOnly=TRUE;
require("auth.php");


$self="sysstat.php";

$_proc=( isset($_GET["p"])) ? strtolower($_GET["p"]):""; 
$_proc=( isset($_POST["p"]) ) ? $_POST["p"] : $_proc; 
$_date1=( isset($_GET["d1"])) ? strtolower($_GET["d1"]) : (( isset($_POST["d1"]) ) ? $_POST["d1"] : "");
$_run=( isset($_GET["run"])) ? strtolower($_GET["run"]) : (( isset($_POST["run"]) ) ? $_POST["run"] : "");
$_mode=( isset($_GET["mode"])) ? strtolower($_GET["mode"]) : (( isset($_POST["mode"]) ) ? $_POST["mode"] : "");
$_pagemode=( isset($_GET["pagemode"])) ? strtolower($_GET["pagemode"]) : (( isset($_POST["pagemode"]) ) ? $_POST["pagemode"] : "");
$_tasknum=( isset($_GET["tasknum"])) ? strtolower($_GET["tasknum"]) : (( isset($_POST["tasknum"]) ) ? $_POST["tasknum"] : "0");
$_smode=( isset($_GET["smode"])) ? strtolower($_GET["smode"]) : (( isset($_POST["smode"]) ) ? $_POST["smode"] : "");
$_target=( isset($_GET["t"])) ? strtolower($_GET["t"]) : (( isset($_POST["t"]) ) ? $_POST["t"] : "");
$_cfile=( isset($_GET["cf"])) ? strtolower($_GET["cf"]) : (( isset($_POST["cf"]) ) ? $_POST["cf"] : "");
$_timeout=( isset($_GET["w"])) ? strtolower($_GET["w"]) : (( isset($_POST["w"]) ) ? $_POST["w"] : "");
$_monproc=( isset($_GET["monproc"])) ? strtolower($_GET["monproc"]) : "";
$_showlog=( isset($_GET["shlog"])) ? strtolower($_GET["shlog"]) : "";
$_sqldbinfo=( isset($_GET["sqldbinfo"])) ? strtolower($_GET["sqldbinfo"]) : "";
$_prestart=( isset($_GET["prst"])) ? strtolower($_GET["prst"]) : "";
$show=( isset($_GET["s"])) ? TRUE:(( isset($_POST["s"]) ) ? TRUE:FALSE);;
$_sort=( isset($_GET["sort"])) ? strtolower($_GET["sort"]):"nameu"; 

$_pshell=( isset($_GET["pshell"])) ? strtolower($_GET["pshell"]) : (( isset($_POST["pshell"]) ) ? $_POST["pshell"] : "");
$_ppath=( isset($_GET["ppath"])) ? strtolower($_GET["ppath"]) : (( isset($_POST["ppath"]) ) ? $_POST["ppath"] : "");
$_pmailto=( isset($_GET["pmailto"])) ? strtolower($_GET["pmailto"]) : (( isset($_POST["pmailto"]) ) ? $_POST["pmailto"] : "");
$_phome=( isset($_GET["phome"])) ? strtolower($_GET["phome"]) : (( isset($_POST["phome"]) ) ? $_POST["phome"] : "");

$_p1=( isset($_GET["p1"])) ? strtolower($_GET["p1"]) : (( isset($_POST["p1"]) ) ? $_POST["p1"] : "");
$_p2=( isset($_GET["p2"])) ? strtolower($_GET["p2"]) : (( isset($_POST["p2"]) ) ? $_POST["p2"] : "");
$_p3=( isset($_GET["p3"])) ? strtolower($_GET["p3"]) : (( isset($_POST["p3"]) ) ? $_POST["p3"] : "");
$_p4=( isset($_GET["p4"])) ? strtolower($_GET["p4"]) : (( isset($_POST["p4"]) ) ? $_POST["p4"] : "");
$_p5=( isset($_GET["p5"])) ? strtolower($_GET["p5"]) : (( isset($_POST["p5"]) ) ? $_POST["p5"] : "");
$_pn=( isset($_GET["pn"])) ? strtolower($_GET["pn"]) : (( isset($_POST["pn"]) ) ? $_POST["pn"] : "");

if( trim($_timeout)=="") {
    $_timeout=( $monitor_delay>0) ? "$monitor_delay" : "10";
}

if( $_showlog!="") {
    display_logfile($_showlog);
    exit;
}

if( $_sqldbinfo!="") {
    ubase_show_status($_sqldbinfo,$mysql_brief_dbinfo);
    exit;
}

if( $_prestart!="") {
    if( $_proc=="") exit;
    system("sudo service $_proc stop");
    print("<br>\n");
    system("sudo service $_proc start");
    exit;

}

if( $_proc!="topmon") {
    print("<html>\n");
    require("include/head.php");
    print("<body>\n");
    print("<table class=table2 width=\"85%\" cellpadding=\"10px\"><tr><td> \n");
}

if( !$show) {
    $url=""; $f1=TRUE;
    foreach($_GET as $gkey => $gvalue) {
	if( $f1) { $url=$url."?"; $f1=FALSE; } else { $url=$url."&"; }
	$url=$url.$gkey."=".$gvalue;
    }
    $url=$self.$url.(( !trim($url)) ? "?":"&")."s=1";
    show_load($url,"Подготовка данных...");
}

if( ($_proc=="system") or ($_proc=="sys")) {
    get_system_info(TRUE);
} elseif( ($_proc=="systemlogs") or ($_proc=="syslogs")) {
    get_system_logs();
} elseif( $_proc=="topmon") {
    topmonitor($_target);
} elseif( ($_proc=="ulog") or ($_proc=="ulogd")) {
    if( $_mode=="") {
	get_ulog_info();
    } elseif( $_mode=="repair") {
	if( trim($_run)!="") {
	    mysql_repair_table($ulog_dbname,"ulog",$_smode);
	} else {
	    show_repair_table();
	}
    } elseif( $_mode=="check") {
	if( trim($_run)!="") {
	    mysql_check_table($ulog_dbname,"ulog",$_smode);
	} else {
	    show_check_table();
	}
    } elseif( $_mode=="optimize") {
	if( trim($_run)!="") {
	    mysql_optimize_table($ulog_dbname,"ulog");
	}
    } elseif( $_mode=="cutdb") {
	mysql_cutdb($ulog_dbname,"ulog",$_date1,$_run);
    }
} elseif( $_proc=="ulogdb") {
    show_ulogdb_panel();
} elseif( $_proc=="crond") {
    get_crond_info($_pagemode);
} elseif( ($_proc=="mysql") or ($_proc=="mysqld")) {
    get_mysql_info();
} elseif( ($_proc=="iptables") or ($_proc=="ipt")) {
    get_iptables_info();
} elseif( $_proc=="archview") {
    show_counts_archive();
} elseif( $_proc=="arhdel") {
    counts_archive_del();
} elseif( $_proc=="usecfile") {
    counts_archive_usecfile();
} elseif( $_proc=="php") {
    print("<font style=\"FONT: normal 9pt Arial;color:000000;\">\n");
    phpinfo();
    print("</font>\n");

}



print("</td></tr></table>\n</body>\n</html>");

?>