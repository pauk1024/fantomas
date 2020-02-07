<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.6
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: options.php
# Description: 
# Version: 0.2.4.6
###



require("./../config.php");
require("iptlib.php");
require("iptlib2.php");
require("shapelib.php");

$flAdminsOnly=TRUE;
require("auth.php");

$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"] : "");
$_group=( isset($_GET["grp"])) ? $_GET["grp"] : (( isset($_POST["grp"])) ? $_POST["grp"] : "");
$_frm_iptconf_dir=( isset($_GET["iptconf_dir"])) ? $_GET["iptconf_dir"] : (( isset($_POST["iptconf_dir"])) ? $_POST["iptconf_dir"] : "");
$_frm_users_dir=( isset($_GET["users_dir"])) ? $_GET["users_dir"] : (( isset($_POST["users_dir"])) ? $_POST["users_dir"] : "");
$_frm_sets_dir=( isset($_GET["sets_dir"])) ? $_GET["sets_dir"] : (( isset($_POST["sets_dir"])) ? $_POST["sets_dir"] : "");
$_frm_initd_dir=( isset($_GET["initd_dir"])) ? $_GET["initd_dir"] : (( isset($_POST["initd_dir"])) ? $_POST["initd_dir"] : "");
$_frm_backup_dir=( isset($_GET["backup_dir"])) ? $_GET["backup_dir"] : (( isset($_POST["backup_dir"])) ? $_POST["backup_dir"] : "");
$_frm_iptables=( isset($_GET["_iptables"])) ? $_GET["_iptables"] : (( isset($_POST["_iptables"])) ? $_POST["_iptables"] : "");
$_frm_ipset=( isset($_GET["_ipset"])) ? $_GET["_ipset"] : (( isset($_POST["_ipset"])) ? $_POST["_ipset"] : "");
$_frm_service=( isset($_GET["_service"])) ? $_GET["_service"] : (( isset($_POST["_service"])) ? $_POST["_service"] : "");
$_frm_grep=( isset($_GET["_grep"])) ? $_GET["_grep"] : (( isset($_POST["_grep"])) ? $_POST["_grep"] : "");
$_frm_sudo=( isset($_GET["_sudo"])) ? $_GET["_sudo"] : (( isset($_POST["_sudo"])) ? $_POST["_sudo"] : "");
$_frm_top=( isset($_GET["_top"])) ? $_GET["_top"] : (( isset($_POST["_top"])) ? $_POST["_top"] : "");
$_frm_chkconfig=( isset($_GET["_chkconfig"])) ? $_GET["_chkconfig"] : (( isset($_POST["_chkconfig"])) ? $_POST["_chkconfig"] : "");
$_frm_sysv_rc_conf=( isset($_GET["_sysv_rc_conf"])) ? $_GET["_sysv_rc_conf"] : (( isset($_POST["_sysv_rc_conf"])) ? $_POST["_sysv_rc_conf"] : "");
$_frm_crond=( isset($_GET["_crond"])) ? $_GET["_crond"] : (( isset($_POST["_crond"])) ? $_POST["_crond"] : "");
$_frm_crontabd=( isset($_GET["_crontabd"])) ? $_GET["_crontabd"] : (( isset($_POST["_crontabd"])) ? $_POST["_crontabd"] : "");
$_frm_crond_logfile=( isset($_GET["crond_logfile"])) ? $_GET["crond_logfile"] : (( isset($_POST["crond_logfile"])) ? $_POST["crond_logfile"] : "");
$_frm_crontab=( isset($_GET["_crontab"])) ? $_GET["_crontab"] : (( isset($_POST["_crontab"])) ? $_POST["_crontab"] : "");
$_frm_whois=( isset($_GET["_whois"])) ? $_GET["_whois"] : (( isset($_POST["_whois"])) ? $_POST["_whois"] : "");
$_frm_tc=( isset($_GET["_tc"])) ? $_GET["_tc"] : (( isset($_POST["_tc"])) ? $_POST["_tc"] : "");
$_frm_lsmod=( isset($_GET["_lsmod"])) ? $_GET["_lsmod"] : (( isset($_POST["_lsmod"])) ? $_POST["_lsmod"] : "");
$_frm_modprobe=( isset($_GET["_modprobe"])) ? $_GET["_modprobe"] : (( isset($_POST["_modprobe"])) ? $_POST["_modprobe"] : "");
$_frm_ifconfig=( isset($_GET["_ifconfig"])) ? $_GET["_ifconfig"] : (( isset($_POST["_ifconfig"])) ? $_POST["_ifconfig"] : "");
$_frm_ip=( isset($_GET["_ip"])) ? $_GET["_ip"] : (( isset($_POST["_ip"])) ? $_POST["_ip"] : "");
$_frm_ps=( isset($_GET["_ps"])) ? $_GET["_ps"] : (( isset($_POST["_ps"])) ? $_POST["_ps"] : "");
$_frm_kill=( isset($_GET["_kill"])) ? $_GET["_kill"] : (( isset($_POST["_kill"])) ? $_POST["_kill"] : "");
$_frm_iptables_config=( isset($_GET["iptables_config"])) ? $_GET["iptables_config"] : (( isset($_POST["iptables_config"])) ? $_POST["iptables_config"] : "");
$_frm_iptables_save=( isset($_GET["iptables_save"])) ? $_GET["iptables_save"] : (( isset($_POST["iptables_save"])) ? $_POST["iptables_save"] : "");

$_frm_filter_web_access=( isset($_GET["filter_web_access"])) ? 1 : (( isset($_POST["filter_web_access"])) ? 1 : 0);
$_frm_crypt_passform=( isset($_GET["_crypt_passform"])) ? TRUE : (( isset($_POST["_crypt_passform"])) ? TRUE : FALSE);
$_frm_allowed_ip=( isset($_GET["allowed_ip"])) ? $_GET["allowed_ip"] : (( isset($_POST["allowed_ip"])) ? $_POST["allowed_ip"] : "");
$_frm_site_to_redirect=( isset($_GET["site_to_redirect"])) ? $_GET["site_to_redirect"] : (( isset($_POST["site_to_redirect"])) ? $_POST["site_to_redirect"] : "");
$_frm_mysql_brief_dbinfo=( isset($_GET["mysql_brief_dbinfo"])) ? TRUE : (( isset($_POST["mysql_brief_dbinfo"])) ? TRUE : FALSE);
$_frm_monitor_def_grp=( isset($_GET["monitor_def_grp"])) ? $_GET["monitor_def_grp"] : (( isset($_POST["monitor_def_grp"])) ? $_POST["monitor_def_grp"] : "");
$_frm_monitor_delay=( isset($_GET["monitor_delay"])) ? $_GET["monitor_delay"] : (( isset($_POST["monitor_delay"])) ? $_POST["monitor_delay"] : "");
$_frm_report_min_procent=( isset($_GET["report_min_procent"])) ? $_GET["report_min_procent"] : (( isset($_POST["monitor_delay"])) ? $_POST["report_min_procent"] : "");
$_frm_rep_whoisurl=( isset($_GET["rep_whoisurl"])) ? $_GET["rep_whoisurl"] : (( isset($_POST["rep_whoisurl"])) ? $_POST["rep_whoisurl"] : "");
$_frm_set_save_onchange=( isset($_GET["_set_save_onchange"])) ? TRUE : (( isset($_POST["_set_save_onchange"])) ? TRUE : FALSE);
$_frm_set_submit_query=( isset($_GET["_set_submit_query"])) ? TRUE : (( isset($_POST["_set_submit_query"])) ? TRUE : FALSE);
$_frm_set_show_listinfo=( isset($_GET["_set_show_listinfo"])) ? TRUE : (( isset($_POST["_set_show_listinfo"])) ? TRUE : FALSE);
$_frm_viewlogs_system=( isset($_GET["viewlogs_system"])) ? $_GET["viewlogs_system"] : (( isset($_POST["viewlogs_system"])) ? $_POST["viewlogs_system"] : "");
$_frm_viewlogs_show_last=( isset($_GET["viewlogs_show_last"])) ? $_GET["viewlogs_show_last"] : (( isset($_POST["viewlogs_system"])) ? $_POST["viewlogs_show_last"] : "");
$_frm_viewlogs_height=( isset($_GET["viewlogs_height"])) ? $_GET["viewlogs_height"] : (( isset($_POST["viewlogs_system"])) ? $_POST["viewlogs_height"] : "");
$_frm_viewlogs_width=( isset($_GET["viewlogs_width"])) ? $_GET["viewlogs_width"] : (( isset($_POST["viewlogs_system"])) ? $_POST["viewlogs_width"] : "");
$_frm_lframe_show_sessid=( isset($_GET["lframe_show_sessid"])) ? TRUE : (( isset($_POST["lframe_show_sessid"])) ? TRUE : FALSE);
$_frm_pollist_poltemp_exitwarn=( isset($_GET["plst_pexiw"])) ? TRUE : (( isset($_POST["plst_pexiw"])) ? TRUE : FALSE);
$_frm_pollist_fmode_default=( isset($_GET["plst_fdef"])) ? $_GET["plst_fdef"] : (( isset($_POST["plst_fdef"])) ? $_POST["plst_fdef"] : "");

$_frm_portfilter_enable=( isset($_GET["_portfilter_enable"])) ? TRUE : (( isset($_POST["_portfilter_enable"])) ? TRUE : FALSE);
$_frm_logs_enable=( isset($_GET["_logs_enable"])) ? TRUE : (( isset($_POST["_logs_enable"])) ? TRUE : FALSE);
$_frm_logs_logged_checkpass_nolog=( isset($_GET["_logs_logged_checkpass_nolog"])) ? TRUE : (( isset($_POST["_logs_logged_checkpass_nolog"])) ? TRUE : FALSE);
$_frm_logs_dir=( isset($_GET["_logs_dir"])) ? $_GET["_logs_dir"] : (( isset($_POST["_logs_dir"])) ? $_POST["_logs_dir"] : "");
$_frm_logs_level=( isset($_GET["_logs_level"])) ? $_GET["_logs_level"] : (( isset($_POST["_logs_level"])) ? $_POST["_logs_level"] : "");
$_frm_syslog=( isset($_GET["syslog"])) ? $_GET["syslog"] : (( isset($_POST["syslog"])) ? $_POST["syslog"] : "");
$_frm_logs_log_maxsize=( isset($_GET["_logs_log_maxsize"])) ? $_GET["_logs_log_maxsize"] : (( isset($_POST["_logs_log_maxsize"])) ? $_POST["_logs_log_maxsize"] : "");

$_frm_shaper_enable=( isset($_GET["_shaper_enable"])) ? TRUE : (( isset($_POST["_shaper_enable"])) ? TRUE : FALSE);
$_frm_shaper_default_ifname=( isset($_GET["_shaper_defifname"])) ? $_GET["_shaper_defifname"] : (( isset($_POST["_shaper_defifname"])) ? $_POST["_shaper_defifname"] : "");
$_frm_shaper_default_ifbifname=( isset($_GET["_shaper_defifb"])) ? $_GET["_shaper_defifb"] : (( isset($_POST["_shaper_defifb"])) ? $_POST["_shaper_defifb"] : "");

$_frm_ssh_enable=( isset($_GET["ssh_enable"])) ? TRUE : (( isset($_POST["ssh_enable"])) ? TRUE : FALSE);

$_frm_default_rctool=( isset($_GET["default_rctool"])) ? $_GET["default_rctool"] : (( isset($_POST["default_rctool"])) ? $_POST["default_rctool"] : "");
$_frm_chkconfig_mode=( isset($_GET["chkconfig_mode"])) ? $_GET["chkconfig_mode"] : (( isset($_POST["chkconfig_mode"])) ? $_POST["chkconfig_mode"] : "");

$_frm_iptables_initmode=( isset($_GET["iptables_initmode"])) ? $_GET["iptables_initmode"] : (( isset($_POST["iptables_initmode"])) ? $_POST["iptables_initmode"] : "");

$script="options.php";
#-------------------------------------------------------------------------

function config_save($grp)
{
    global $iptconf_dir, $users_dir, $sets_dir, $initd_dir, $backup_dir;
    global $_iptables, $_ipset, $_service, $_grep, $_sudo, $_top, $_chkconfig;
    global $script,$_group;
    global $_ifconfig, $_sysv_rc_conf, $_frm_sysv_rc_conf;
    global $iptables_config,$iptables_save;

    global $filter_web_access,$allowed_ip,$site_to_redirect;    
    global $mysql_brief_dbinfo,$monitor_def_grp,$monitor_delay,$report_min_procent,$rep_whoisurl;
    global $_set_save_onchange,$_set_submit_query,$_set_show_listinfo;
    global $viewlogs_system,$viewlogs_show_last,$viewlogs_height,$viewlogs_width;
    global $lframe_show_sessid, $_crypt_passform;
    global $_crond,$_crontabd,$crond_logfile,$_crontab,$_whois;
    global $_portfilter_enable;
    global $pollist_poltemp_exitwarn,$pollist_fmode_default;
    global $_tc,$_lsmod,$_modprobe,$_ifconfig,$_ip;
    global $_frm_iptables_config,$_frm_iptables_save;
    global $_ps,$_kill;

    global $_frm_iptconf_dir,$_frm_users_dir,$_frm_sets_dir,$_frm_initd_dir,$_frm_backup_dir;
    global $_frm_iptables,$_frm_ipset,$_frm_service,$_frm_grep,$_frm_sudo,$_frm_top,$_frm_chkconfig;
    global $_frm_crond,$_frm_crontabd,$_frm_crontab,$_frm_crond_logfile,$_frm_whois;
    global $_frm_tc,$_frm_lsmod,$_frm_modprobe,$_frm_ifconfig,$_frm_ip;
    global $_frm_ps,$_frm_kill;

    global $_frm_filter_web_access,$_frm_allowed_ip,$_frm_site_to_redirect;    
    global $_frm_mysql_brief_dbinfo,$_frm_monitor_def_grp,$_frm_monitor_delay,$_frm_report_min_procent,$_frm_rep_whoisurl;
    global $_frm_set_save_onchange,$_frm_set_submit_query,$_frm_set_show_listinfo;
    global $_frm_viewlogs_system,$_frm_viewlogs_show_last,$_frm_viewlogs_height,$_frm_viewlogs_width;
    global $_frm_lframe_show_sessid,$_frm_crypt_passform;
    global $_frm_pollist_poltemp_exitwarn,$_frm_pollist_fmode_default;

    global $_frm_portfilter_enable, $_frm_logs_enable, $_frm_logs_logged_checkpass_nolog, $_frm_logs_dir;
    global $_frm_logs_level, $_frm_syslog, $_frm_logs_log_maxsize;
    global $_frm_shaper_enable,$_frm_shaper_default_ifname,$_frm_shaper_default_ifbifname;

    global $_logs_enable, $_logs_logged_checkpass_nolog, $_logs_dir;
    global $_logs_level, $syslog, $_logs_log_maxsize;
    global $_shaper_enable,$_shaper_default_ifname,$_shaper_default_ifbifname;    

    global $_sudo, $_iptables;

    global $ssh_enable,$_frm_ssh_enable;
    global $default_rctool, $_frm_default_rctool;
    global $chkconfig_mode, $_frm_chkconfig_mode;
    global $iptables_initmode,$_frm_iptables_initmode;

	$conf="./../config.php";

	if( !file_exists($conf)) { wlog("ВНИМАНИЕ! Куда Вы дели config.php????",2,TRUE,5,TRUE); exit; }
	if( !is_writable($conf)) { wlog("Файл config.php недоступен для записи...",2,TRUE,5,TRUE); exit; }
    $aafrm=array();

    if( $grp=="paths") {
	
	wlog("Сохранение параметров переменных группы pathы",0,FALSE,1,FALSE);
	
	if( $iptconf_dir!=$_frm_iptconf_dir) $aafrm["iptconf_dir"]=$_frm_iptconf_dir;
	if( $sets_dir!=$_frm_sets_dir) $aafrm["sets_dir"]=$_frm_sets_dir;
	if( $users_dir!=$_frm_users_dir) $aafrm["users_dir"]=$_frm_users_dir;
	if( $backup_dir!=$_frm_backup_dir) $aafrm["backup_dir"]=$_frm_backup_dir;
	if( $initd_dir!=$_frm_initd_dir) $aafrm["initd_dir"]=$_frm_initd_dir;
	if( $_iptables!=$_frm_iptables) $aafrm["_iptables"]=$_frm_iptables;
	if( $_ipset!=$_frm_ipset) $aafrm["_ipset"]=$_frm_ipset;
	if( $_service!=$_frm_service) $aafrm["_service"]=$_frm_service;
	if( $_grep!=$_frm_grep) $aafrm["_grep"]=$_frm_grep;
	if( $_sudo!=$_frm_sudo) $aafrm["_sudo"]=$_frm_sudo;
	if( $_top!=$_frm_top) $aafrm["_top"]=$_frm_top;
	if( $_chkconfig!=$_frm_chkconfig) $aafrm["_chkconfig"]=$_frm_chkconfig;
	if( $_sysv_rc_conf!=$_frm_sysv_rc_conf) $aafrm["_sysv_rc_conf"]=$_frm_sysv_rc_conf;
	if( $_crond!=$_frm_crond) $aafrm["_crond"]=$_frm_crond;
	if( $_crontabd!=$_frm_crontabd) $aafrm["_crontabd"]=$_frm_crontabd;
	if( $_crontab!=$_frm_crontab) $aafrm["_crontab"]=$_frm_crontab;
	if( $crond_logfile!=$_frm_crond_logfile) $aafrm["crond_logfile"]=$_frm_crond_logfile;
	if( $_whois!=$_frm_whois) $aafrm["_whois"]=$_frm_whois;
	if( $_tc!=$_frm_tc) $aafrm["_tc"]=$_frm_tc;
	if( $_lsmod!=$_frm_lsmod) $aafrm["_lsmod"]=$_frm_lsmod;
	if( $_modprobe!=$_frm_modprobe) $aafrm["_modprobe"]=$_frm_modprobe;
	if( $_ifconfig!=$_frm_ifconfig) $aafrm["_ifconfig"]=$_frm_ifconfig;
	if( $_ip!=$_frm_ip) $aafrm["_ip"]=$_frm_ip;
	if( $_ps!=$_frm_ps) $aafrm["_ps"]=$_frm_ps;
	if( $_kill!=$_frm_kill) $aafrm["_kill"]=$_frm_kill;
	if( $iptables_config!=$_frm_iptables_config) $aafrm["iptables_config"]=$_frm_iptables_config;
	if( $iptables_save!=$_frm_iptables_save) $aafrm["iptables_save"]=$_frm_iptables_save;
	
	
    } elseif( $grp=="web") {
	
	wlog("Сохранение параметров переменных группы web",0,FALSE,1,FALSE);
    
	if( $filter_web_access != $_frm_filter_web_access) $aafrm["|nostr|filter_web_access"]=$_frm_filter_web_access;
	if( $_crypt_passform != $_frm_crypt_passform) $aafrm["|nostr|_crypt_passform"]=toster($_frm_crypt_passform);
	if( $allowed_ip != $_frm_allowed_ip) $aafrm["allowed_ip"]=$_frm_allowed_ip;
	if( $site_to_redirect != $_frm_site_to_redirect) $aafrm["site_to_redirect"]=$_frm_site_to_redirect;
	if( $mysql_brief_dbinfo != $_frm_mysql_brief_dbinfo) $aafrm["|nostr|mysql_brief_dbinfo"]=toster($_frm_mysql_brief_dbinfo);
	if( $monitor_def_grp != $_frm_monitor_def_grp) $aafrm["monitor_def_grp"]=$_frm_monitor_def_grp;
	if( $monitor_delay != $_frm_monitor_delay) $aafrm["|nostr|monitor_delay"]=$_frm_monitor_delay;
	if( $report_min_procent != $_frm_report_min_procent) $aafrm["report_min_procent"]=$_frm_report_min_procent;
	if( $rep_whoisurl != $_frm_rep_whoisurl) $aafrm["rep_whoisurl"]=$_frm_rep_whoisurl;
	if( $_set_save_onchange != $_frm_set_save_onchange) $aafrm["|nostr|_set_save_onchange"]=toster($_frm_set_save_onchange);
	if( $_set_submit_query != $_frm_set_submit_query) $aafrm["|nostr|_set_submit_query"]=toster($_frm_set_submit_query);
	if( $_set_show_listinfo != $_frm_set_show_listinfo) $aafrm["|nostr|_set_show_listinfo"]=toster($_frm_set_show_listinfo);
	if( $viewlogs_system != $_frm_viewlogs_system) $aafrm["viewlogs_system"]=$_frm_viewlogs_system;
	if( $viewlogs_show_last != $_frm_viewlogs_show_last) $aafrm["|nostr|viewlogs_show_last"]=$_frm_viewlogs_show_last;
	if( $viewlogs_height != $_frm_viewlogs_height) $aafrm["|nostr|viewlogs_height"]=$_frm_viewlogs_height;
	if( $viewlogs_width != $_frm_viewlogs_width) $aafrm["|nostr|viewlogs_width"]=$_frm_viewlogs_width;
	if( $lframe_show_sessid != $_frm_lframe_show_sessid) $aafrm["|nostr|lframe_show_sessid"]=toster($_frm_lframe_show_sessid);
	if( $pollist_poltemp_exitwarn != $_frm_pollist_poltemp_exitwarn) $aafrm["|nostr|pollist_poltemp_exitwarn"]=toster($_frm_pollist_poltemp_exitwarn);
	if( $pollist_fmode_default != $_frm_pollist_fmode_default) $aafrm["pollist_fmode_default"]=$_frm_pollist_fmode_default;

    } elseif( $grp=="system") {
	
	wlog("Сохранение параметров переменных группы system",0,FALSE,1,FALSE);
	    
	if( $_portfilter_enable != $_frm_portfilter_enable) $aafrm["|nostr|_portfilter_enable"]=toster($_frm_portfilter_enable);
	if( $ssh_enable != $_frm_ssh_enable) $aafrm["|nostr|ssh_enable"]=toster($_frm_ssh_enable);
	if( !$_frm_portfilter_enable) {
	    _exec2("$_iptables -t mangle -D PORTFILTER -j DROP");
	}

	if( $default_rctool!=$_frm_default_rctool) $aafrm["default_rctool"]=$_frm_default_rctool;
	if( $chkconfig_mode != $_frm_chkconfig_mode) $aafrm["|nostr|chkconfig_mode"]=$_frm_chkconfig_mode;
	
	if( $iptables_initmode != $_frm_iptables_initmode) $aafrm["|nostr|iptables_initmode"]=$_frm_iptables_initmode;

	if( $_logs_enable != $_frm_logs_enable) $aafrm["|nostr|_logs_enable"]=toster($_frm_logs_enable);
	
	if( $_logs_logged_checkpass_nolog != $_frm_logs_logged_checkpass_nolog) $aafrm["|nostr|_logs_logged_checkpass_nolog"]=toster($_frm_logs_logged_checkpass_nolog);
	if( $_logs_dir != $_frm_logs_dir) $aafrm["_logs_dir"]=$_frm_logs_dir;
	if( $_logs_level != $_frm_logs_level) $aafrm["|nostr|_logs_level"]=$_frm_logs_level;
	if( $syslog != $_frm_syslog) $aafrm["syslog"]=$_frm_syslog;
	if( $_logs_log_maxsize != $_frm_logs_log_maxsize) $aafrm["_logs_log_maxsize"]=$_frm_logs_log_maxsize;
	
	if( $_shaper_enable != $_frm_shaper_enable) $aafrm["|nostr|_shaper_enable"]=toster($_frm_shaper_enable);
	if( !$_frm_shaper_enable) {
	    if( shape_getstatus()) {
		list($rr,$aa)=_exec2("$_ifconfig | $_grep 'Link encap' | awk '{ print $1 }' | $_grep ifb");
		foreach($aa as $aav) shaper_down($aav);
	    }
	}
	
	if( $_shaper_default_ifname != $_frm_shaper_default_ifname) $aafrm["_shaper_defifname"]=$_frm_shaper_default_ifname;
	if( $_shaper_default_ifbifname != $_frm_shaper_default_ifbifname) $aafrm["_shaper_defifb"]=$_frm_shaper_default_ifbifname;
	
    }

	
    if( !options_save($aafrm)) {
	wlog("Ошибка сохранения параметров ");
    }

}

#-------------------------------------------------------------------------

function options_paths($pmode)
{
    global $iptconf_dir, $users_dir, $sets_dir, $initd_dir, $backup_dir;
    global $_iptables, $_ipset, $_service, $_grep, $_sudo, $_top, $_chkconfig;
    global $script;
    global $_crond,$_crontabd,$_crontab,$crond_logfile,$_whois;
    global $_frm_iptconf_dir,$_frm_users_dir,$_frm_sets_dir,$_frm_initd_dir,$_frm_backup_dir;
    global $_frm_iptables,$_frm_ipset,$_frm_service,$_frm_grep,$_frm_sudo,$_frm_top,$_frm_chkconfig;
    global $_frm_crond,$_frm_crontabd,$_frm_crontab,$_frm_crond_logfile,$_frm_whois;
    global $_frm_tc,$_frm_lsmod,$_frm_modprobe,$_frm_ifconfig,$_frm_ip;
    global $_tc,$_lsmod,$_modprobe,$_ifconfig,$_ip;
    global $_frm_iptables_config,$_frm_iptables_save;
    global $iptables_config,$iptables_save;
    global $_ps,$_kill,$_frm_ps,$_frm_kill;
    global $_sysv_rc_conf, $_frm_sysv_rc_conf, $default_rctool;


    
    if( ( $pmode=="") or ( $pmode=="show")) {

	print("<html>\n");
	$NoHeadEnd=FALSE;
	require("include/head1.php");
	print("<body>\n");
    
	print("<br><font class=top1>Параметры путей к каталогам и файлам</font><br><br><br>\n<font class=text42>\n");
	print("<table class=table4 cellpadding=\"0px\" width=\"90%\">\n");
	print("<form name=\"paths1\" action=\"$script\" method=\"POST\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"paths\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"save\">\n");
	print("<tr><td colspan=2> <b>Каталоги:</b> </td></tr>\n");    
	print("<tr><td class=td410>Fantomas Iptconf </td><td> <input type=\"text\" name=\"iptconf_dir\" value=\"$iptconf_dir\" size=\"50\"> ".genframe($iptconf_dir)."</td></tr>\n");
	print("<tr><td class=td410>Группы клиентов </td><td> <input type=\"text\" name=\"users_dir\" value=\"$users_dir\" size=\"50\"> ".genframe($users_dir)."</td></tr>\n");    
	print("<tr><td class=td410>Сет-листы </td><td> <input type=\"text\" name=\"sets_dir\" value=\"$sets_dir\" size=\"50\"> ".genframe($sets_dir)."</td></tr>\n");    
	print("<tr><td class=td410>Бекапы </td><td> <input type=\"text\" name=\"backup_dir\" value=\"$backup_dir\" size=\"50\"> ".genframe($backup_dir)."</td></tr>\n");    
	print("<tr><td class=td410>Init.d </td><td> <input type=\"text\" name=\"initd_dir\" value=\"$initd_dir\" size=\"50\"> ".genframe($initd_dir)."</td></tr>\n");    
	print("<tr><td colspan=2> <b>Программы:</b> </td></tr>\n");    
	print("<tr><td class=td410>Iptables </td><td> <input type=\"text\" name=\"_iptables\" value=\"$_iptables\" size=\"50\"> ".genframe($_iptables)."</td></tr>\n");
	print("<tr><td class=td410>Ipset </td><td> <input type=\"text\" name=\"_ipset\" value=\"$_ipset\" size=\"50\"> ".genframe($_ipset)."</td></tr>\n");    
	print("<tr><td class=td410>Service </td><td> <input type=\"text\" name=\"_service\" value=\"$_service\" size=\"50\"> ".genframe($_service)."</td></tr>\n");    
	print("<tr><td class=td410>Grep </td><td> <input type=\"text\" name=\"_grep\" value=\"$_grep\" size=\"50\"> ".genframe($_grep)."</td></tr>\n");
	print("<tr><td class=td410>Sudo </td><td> <input type=\"text\" name=\"_sudo\" value=\"$_sudo\" size=\"50\"> ".genframe($_sudo)."</td></tr>\n");
	print("<tr><td class=td410>Top </td><td> <input type=\"text\" name=\"_top\" value=\"$_top\" size=\"50\"> ".genframe($_top)."</td></tr>\n");
	if( $default_rctool=="chkconfig") {
	    print("<tr><td class=td410>Chkconfig </td><td> <input type=\"text\" name=\"_chkconfig\" value=\"$_chkconfig\" size=\"50\"> ".genframe($_chkconfig)."</td></tr>\n");
	} elseif( $default_rctool=="sysv_rc_conf") {
	    print("<tr><td class=td410>Sysv_rc_conf </td><td> <input type=\"text\" name=\"_sysv_rc_conf\" value=\"$_sysv_rc_conf\" size=\"50\"> ".genframe($_sysv_rc_conf)."</td></tr>\n");
	} else {
	    print("<tr><td class=td410 colspan=2> \$default_rctool = \"".$default_rctool."\" - неизвестный инструмент управления конфигурацией системных сервисов.</td></tr>\n");
	}
	print("<tr><td class=td410>Crond </td><td> <input type=\"text\" name=\"_crond\" value=\"$_crond\" size=\"50\"> ".genframe($_crond)."</td></tr>\n");
	print("<tr><td class=td410>Crontab(программа) </td><td> <input type=\"text\" name=\"_crontabd\" value=\"$_crontabd\" size=\"50\"> ".genframe($_crontabd)."</td></tr>\n");
	print("<tr><td class=td410>Whois(программа) </td><td> <input type=\"text\" name=\"_whois\" value=\"$_whois\" size=\"50\"> ".genframe($_whois)."</td></tr>\n");
	print("<tr><td class=td410>Tc </td><td> <input type=\"text\" name=\"_tc\" value=\"$_tc\" size=\"50\"> ".genframe($_tc)."</td></tr>\n");
	print("<tr><td class=td410>Lsmod </td><td> <input type=\"text\" name=\"_lsmod\" value=\"$_lsmod\" size=\"50\"> ".genframe($_lsmod)."</td></tr>\n");
	print("<tr><td class=td410>Modprobe </td><td> <input type=\"text\" name=\"_modprobe\" value=\"$_modprobe\" size=\"50\"> ".genframe($_modprobe)."</td></tr>\n");
	print("<tr><td class=td410>Ifconfig </td><td> <input type=\"text\" name=\"_ifconfig\" value=\"$_ifconfig\" size=\"50\"> ".genframe($_ifconfig)."</td></tr>\n");
	print("<tr><td class=td410>Ip </td><td> <input type=\"text\" name=\"_ip\" value=\"$_ip\" size=\"50\"> ".genframe($_ip)."</td></tr>\n");
	print("<tr><td class=td410>ps </td><td> <input type=\"text\" name=\"_ps\" value=\"$_ps\" size=\"50\"> ".genframe($_ps)."</td></tr>\n");
	print("<tr><td class=td410>kill </td><td> <input type=\"text\" name=\"_ps\" value=\"$_kill\" size=\"50\"> ".genframe($_kill)."</td></tr>\n");
	print("<tr><td colspan=2> <b>Прочее:</b> </td></tr>\n");    
	print("<tr><td class=td410>Crontab(конфиг) </td><td> <input type=\"text\" name=\"_crontab\" value=\"$_crontab\" size=\"50\"> ".genframe($_crontab)."</td></tr>\n");
	print("<tr><td class=td410>Crontab(лог) </td><td> <input type=\"text\" name=\"crond_logfile\" value=\"$crond_logfile\" size=\"50\"> ".genframe($crond_logfile)."</td></tr>\n");
	print("<tr><td class=td410>Iptables(конфиг) </td><td> <input type=\"text\" name=\"iptables_config\" value=\"$iptables_config\" size=\"50\"> ".genframe($iptables_config)."</td></tr>\n");
	print("<tr><td class=td410>Iptables-save(правила) </td><td> <input type=\"text\" name=\"iptables_save\" value=\"$iptables_save\" size=\"50\"> ".genframe($iptables_save)."</td></tr>\n");

	print("<tr><td colspan=2 align=right style=\"padding-right:80px\">  <input type=\"submit\" name=\"sbmt\" value=\"Сохранить\"> </td></tr>\n");
	print("</form>\n</table>\n");
	wlog("Просмотр параметров переменных группы paths",0,FALSE,1,FALSE);
	exit;

    } elseif( $pmode=="dir") {

	print("<html>\n");
	$NoHeadEnd=FALSE;
	require("include/head1.php");
	print("<body>\n");

	print("<br><font class=top1>Параметры путей</font><br><br><br>\n<font class=text42>\n");
	print("<table class=table4 cellpadding=\"0px\" width=\"90%\">\n");
	print("<form name=\"paths1\" action=\"$script\" method=\"POST\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"paths\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"save\">\n");
	print("<tr><td colspan=2> <b>Каталоги:</b> </td></tr>\n");    
	print("<tr><td class=td410>Fantomas Iptconf </td><td> <input type=\"text\" name=\"iptconf_dir\" value=\"$iptconf_dir\" size=\"50\"> ".genframe($iptconf_dir)."</td></tr>\n");
	print("<tr><td class=td410>Группы клиентов </td><td> <input type=\"text\" name=\"users_dir\" value=\"$users_dir\" size=\"50\"> ".genframe($users_dir)."</td></tr>\n");    
	print("<tr><td class=td410>Сет-листы </td><td> <input type=\"text\" name=\"sets_dir\" value=\"$sets_dir\" size=\"50\"> ".genframe($sets_dir)."</td></tr>\n");    
	print("<tr><td class=td410>Бекапы </td><td> <input type=\"text\" name=\"backup_dir\" value=\"$backup_dir\" size=\"50\"> ".genframe($backup_dir)."</td></tr>\n");    
	print("<tr><td class=td410>Init.d </td><td> <input type=\"text\" name=\"initd_dir\" value=\"$initd_dir\" size=\"50\"> ".genframe($initd_dir)."</td></tr>\n");    

	print("<tr><td colspan=2 align=right style=\"padding-right:80px\">  <input type=\"submit\" name=\"sbmt\" value=\"Сохранить\"> </td></tr>\n");
	print("</form>\n</table>\n");
	wlog("Просмотр параметров переменных группы paths-dir",0,FALSE,1,FALSE);
	exit;

    } elseif( $pmode=="bin") {

	print("<html>\n");
	$NoHeadEnd=FALSE;
	require("include/head1.php");
	print("<body>\n");
    
	print("<br><font class=top1>Параметры путей</font><br><br><br>\n<font class=text42>\n");
	print("<table class=table4 cellpadding=\"0px\" width=\"90%\">\n");
	print("<form name=\"paths1\" action=\"$script\" method=\"POST\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"paths\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"save\">\n");
	print("<tr><td colspan=2> <b>Программы:</b> </td></tr>\n");    
	print("<tr><td class=td410>Iptables </td><td> <input type=\"text\" name=\"_iptables\" value=\"$_iptables\" size=\"50\"> ".genframe($_iptables)."</td></tr>\n");
	print("<tr><td class=td410>Ipset </td><td> <input type=\"text\" name=\"_ipset\" value=\"$_ipset\" size=\"50\"> ".genframe($_ipset)."</td></tr>\n");    
	print("<tr><td class=td410>Service </td><td> <input type=\"text\" name=\"_service\" value=\"$_service\" size=\"50\"> ".genframe($_service)."</td></tr>\n");    
	print("<tr><td class=td410>Grep </td><td> <input type=\"text\" name=\"_grep\" value=\"$_grep\" size=\"50\"> ".genframe($_grep)."</td></tr>\n");
	print("<tr><td class=td410>Sudo </td><td> <input type=\"text\" name=\"_sudo\" value=\"$_sudo\" size=\"50\"> ".genframe($_sudo)."</td></tr>\n");
	print("<tr><td class=td410>Top </td><td> <input type=\"text\" name=\"_top\" value=\"$_top\" size=\"50\"> ".genframe($_top)."</td></tr>\n");
	if( $default_rctool=="chkconfig") {
	    print("<tr><td class=td410>Chkconfig </td><td> <input type=\"text\" name=\"_chkconfig\" value=\"$_chkconfig\" size=\"50\"> ".genframe($_chkconfig)."</td></tr>\n");
	} elseif( $default_rctool=="sysv_rc_conf") {
	    print("<tr><td class=td410>Sysv_rc_conf </td><td> <input type=\"text\" name=\"_sysv_rc_conf\" value=\"$_sysv_rc_conf\" size=\"50\"> ".genframe($_sysv_rc_conf)."</td></tr>\n");
	} else {
	    print("<tr><td class=td410 colspan=2> \$default_rctool = \"".$default_rctool."\" - неизвестный инструмент управления конфигурацией системных сервисов.</td></tr>\n");
	}
	print("<tr><td class=td410>Crond </td><td> <input type=\"text\" name=\"_crond\" value=\"$_crond\" size=\"50\"> ".genframe($_crond)."</td></tr>\n");
	print("<tr><td class=td410>Crontab(программа) </td><td> <input type=\"text\" name=\"_crontabd\" value=\"$_crontabd\" size=\"50\"> ".genframe($_crontabd)."</td></tr>\n");
	print("<tr><td class=td410>Whois(программа) </td><td> <input type=\"text\" name=\"_whois\" value=\"$_whois\" size=\"50\"> ".genframe($_whois)."</td></tr>\n");
	print("<tr><td class=td410>Tc </td><td> <input type=\"text\" name=\"_tc\" value=\"$_tc\" size=\"50\"> ".genframe($_tc)."</td></tr>\n");
	print("<tr><td class=td410>Lsmod </td><td> <input type=\"text\" name=\"_lsmod\" value=\"$_lsmod\" size=\"50\"> ".genframe($_lsmod)."</td></tr>\n");
	print("<tr><td class=td410>Modprobe </td><td> <input type=\"text\" name=\"_modprobe\" value=\"$_modprobe\" size=\"50\"> ".genframe($_modprobe)."</td></tr>\n");
	print("<tr><td class=td410>Ifconfig </td><td> <input type=\"text\" name=\"_ifconfig\" value=\"$_ifconfig\" size=\"50\"> ".genframe($_ifconfig)."</td></tr>\n");
	print("<tr><td class=td410>Ip </td><td> <input type=\"text\" name=\"_ip\" value=\"$_ip\" size=\"50\"> ".genframe($_ip)."</td></tr>\n");
	print("<tr><td class=td410>ps </td><td> <input type=\"text\" name=\"_ps\" value=\"$_ps\" size=\"50\"> ".genframe($_ps)."</td></tr>\n");
	print("<tr><td class=td410>kill </td><td> <input type=\"text\" name=\"_ps\" value=\"$_kill\" size=\"50\"> ".genframe($_kill)."</td></tr>\n");

	print("<tr><td colspan=2 align=right style=\"padding-right:80px\">  <input type=\"submit\" name=\"sbmt\" value=\"Сохранить\"> </td></tr>\n");
	print("</form>\n</table>\n");
	wlog("Просмотр параметров переменных группы paths-bin",0,FALSE,1,FALSE);
	exit;

    } elseif( $pmode=="ini") {

	print("<html>\n");
	$NoHeadEnd=FALSE;
	require("include/head1.php");
	print("<body>\n");
    
	print("<br><font class=top1>Параметры путей</font><br><br><br>\n<font class=text42>\n");
	print("<table class=table4 cellpadding=\"0px\" width=\"90%\">\n");
	print("<form name=\"paths1\" action=\"$script\" method=\"POST\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"paths\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"save\">\n");
	print("<tr><td colspan=2> <b>Прочее:</b> </td></tr>\n");    
	print("<tr><td class=td410>Crontab(конфиг) </td><td> <input type=\"text\" name=\"_crontab\" value=\"$_crontab\" size=\"50\"> ".genframe($_crontab)."</td></tr>\n");
	print("<tr><td class=td410>Crontab(лог) </td><td> <input type=\"text\" name=\"crond_logfile\" value=\"$crond_logfile\" size=\"50\"> ".genframe($crond_logfile)."</td></tr>\n");
	print("<tr><td class=td410>Iptables(конфиг) </td><td> <input type=\"text\" name=\"iptables_config\" value=\"$iptables_config\" size=\"50\"> ".genframe($iptables_config)."</td></tr>\n");
	print("<tr><td class=td410>Iptables-save(правила) </td><td> <input type=\"text\" name=\"iptables_save\" value=\"$iptables_save\" size=\"50\"> ".genframe($iptables_save)."</td></tr>\n");

	print("<tr><td colspan=2 align=right style=\"padding-right:80px\">  <input type=\"submit\" name=\"sbmt\" value=\"Сохранить\"> </td></tr>\n");
	print("</form>\n</table>\n");
	wlog("Просмотр параметров переменных группы paths-ini",0,FALSE,1,FALSE);
	exit;

    } elseif( $pmode=="save") {
	
	config_save("paths");	
	
    }

}


#-------------------------------------------------------------------------

function options_system($pmode)
{

    global $_portfilter_enable;    

    global $script;

    global $_frm_portfilter_enable, $_frm_logs_enable, $_frm_logs_logged_checkpass_nolog, $_frm_logs_dir;
    global $_frm_logs_level, $_frm_syslog, $_frm_logs_log_maxsize;
    global $_frm_shaper_enable,$_frm_shaper_default_ifname,$_frm_shaper_default_ifbifname;

    global $_logs_enable, $_logs_logged_checkpass_nolog, $_logs_dir;
    global $_logs_level, $syslog, $_logs_log_maxsize;
    global $_shaper_enable,$_shaper_default_ifname,$_shaper_default_ifbifname;
    
    global $ssh_enable;
    global $_sysv_rc_conf, $_frm_sysv_rc_conf, $default_rctool, $_frm_default_rctool;
    global $chkconfig_mode, $_frm_chkconfig_mode;
    global $iptables_initmode,$_frm_iptables_initmode;

    if( ( $pmode=="") or ( $pmode=="show")) {
    
	print("<html>\n");
	$NoHeadEnd=FALSE;
	require("include/head1.php");
	print("<body>\n");
	
	print("<script type=\"text/javascript\">\n");
	print("function chkVar() \n{\n");
	print("  var status = (document.getElementById('_logs_enable').checked==true) ? false : true;\n");
	print("  var rr=(status==true) ? \"true\" : \"false\";\n");
	print("  document.getElementById('_logs_logged_checkpass_nolog').disabled=status;\n");
	print("  document.getElementById('_logs_dir').disabled=status;\n");
	print("  document.getElementById('syslog').disabled=status;\n");
	print("  document.getElementById('_logs_level').disabled=status;\n");
	print("  document.getElementById('_logs_log_maxsize').disabled=status;\n");
	print("}\n</script>\n\n");
	
	print("<br><font class=top1>Системные параметры</font><br><br><br>\n<font class=text42>\n");
	print("<form name=\"paths1\" action=\"$script\" method=\"POST\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"system\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"save\">\n");
	print("<table class=table4 cellpadding=\"3px\" width=\"85%\">\n");

	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Доступ к системе: </b></td></tr>\n");    
	print("<tr><td class=td410 colspan=2>\n");
	print(" <input type=\"checkbox\" name=\"ssh_enable\" id=\"ssh_enable\" value=\"1\"".(($ssh_enable ) ? " checked" : "")."><label for=\"ssh_enable\"> Использовать SSH-соединение для работы с системой и с Iptables</label><br>");
	print("<div style=\"padding-left:30px;margin:5px; FONT: italic 8pt Arial;\"> * <b>Если включено</b>, то для выполнения команд, требующих права root, устанавливается SSH-соединение и команды выполняются через это соединение с использованием sudo.<br>\n");
	print("* <b>Если выключено</b>, то команды, требующие привелегий root, выполняются из под логина под которым работает Apache, используя sudo.<br>\n");
	print("** Параметры для установки SSH-соединения настраиваются в config.php в переменных, начинающихся с \$ssh_.</div> </td></tr>\n");
	print("<tr><td class=td410>Менеджер системных сервисов по умолчанию: </td><td> <span class=seldiv><SELECT name=\"default_rctool\" id=\"default_rctool\" > \n");
	print("<option value=\"chkconfig\"".(($default_rctool=="chkconfig") ? " SELECTED" : "").">chkconfig\n");
	print("<option value=\"sysv_rc_conf\"".(($default_rctool=="sysv_rc_conf") ? " SELECTED" : "").">sysv_rc_conf\n");
	print("</SELECT></span>\n</td></tr>\n");
	if( $default_rctool=="chkconfig") {
	print("<tr><td class=td410>Стиль синтаксиса chkconfig: </td><td> <span class=seldiv><SELECT name=\"chkconfig_mode\" id=\"chkconfig_mode\" > \n");
	print("<option value=\"1\"".(($chkconfig_mode==1) ? " SELECTED" : "").">redhat sintax style\n");
	print("<option value=\"2\"".(($chkconfig_mode==2) ? " SELECTED" : "").">debian sintax style\n");
	print("</SELECT></span>\n</td></tr>\n");
	}

	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Загрузка: </b></td></tr>\n");    
	print("<tr><td class=td410>Загрузка счетчиков и правил Iptables при старте ОС: </td><td> <span class=seldiv><SELECT name=\"iptables_initmode\" id=\"iptables_initmode\" > \n");
	print("<option value=\"0\"".(($iptables_initmode==0) ? " SELECTED" : "").">Использовать iptables-save и iptables-restore </option>\n");
	print("<option value=\"2\"".(($iptables_initmode==1) ? " SELECTED" : "").">Выгрузка и загрузка счетчиков средствами Fantomas Iptconf </option>\n");
	print("</SELECT></span>\n</td></tr>\n");

	
	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Порт-фильтр: </b></td></tr>\n");    
	print("<tr><td class=td410 colspan=2>\n");
	print(" <input type=\"checkbox\" name=\"_portfilter_enable\" id=\"_portfilter_enable\" value=\"1\"".(($_portfilter_enable ) ? " checked" : "")."><label for=\"_portfilter_enable\"> Включить фильтр трафика по списку портов</label><br>");
	print("<div style=\"padding-left:30px;margin:5px; FONT: italic 8pt Arial;\"> * Фильтруется весь трафик, как подлежащий маршрутизации, так и направленный локальным приложениям.<br>\n");
	print("** При включении: чтобы фильтр заработал - выполните перезагрузку конфигурации (RELOAD).</div> </td></tr>\n");

	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Шейпинг сетевого трафика: </b></td></tr>\n");    
	print("<tr><td class=td410 colspan=2> <input type=\"checkbox\" name=\"_shaper_enable\" id=\"_shaper_enable\" value=\"1\"".(($_shaper_enable ) ? " checked" : "")." ><label for=\"_shaper_enable\"> Включить ограничение скорости сетевого трафика клиентов (шейпер)</label></td></tr>\n");
	print("<tr><td class=td410>HW интерфейс для шейпинга: <span class=seldiv><SELECT name=\"_shaper_defifname\" id=\"_shaper_defifname\">\n");
	$alist=get_iflist(1);
	foreach($alist as $alistvalue) print("<option value=\"$alistvalue\" ".(( trim($alistvalue)==trim($_shaper_default_ifname)) ? " SELECTED":"").">$alistvalue</option>\n");
	print("</SELECT></span>\n</td><td class=td410> IFB интерфейс шейпинга: <input type=\"TEXT\" name=\"_shaper_defifname\" id=\"_shaper_defifname\" size=6 value=\"$_shaper_default_ifbifname\"> </td></tr> \n");

	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Логи: </b></td></tr>\n");    
	print("<tr><td class=td410 colspan=2> <input type=\"checkbox\" name=\"_logs_enable\" id=\"_logs_enable\" value=\"1\"".(($_logs_enable ) ? " checked" : "")." onClick=\"javascript: chkVar();\"><label for=\"_logs_enable\"> Включить запись лога событий программы </label></td></tr>\n");
	print("<tr><td class=td410 colspan=2> <input type=\"checkbox\" name=\"_logs_logged_checkpass_nolog\" id=\"_logs_logged_checkpass_nolog\" value=\"1\"".(($_logs_logged_checkpass_nolog ) ? " checked" : "").((!$_logs_enable) ? " disabled" : "")."><label for=\"_logs_logged_checkpass_nolog\"> Не логировать события о проверках пароля на каждой странице, если логин валиден. </label></td></tr>\n");
	print("<tr><td class=td410>Каталог логов: </td><td> <input type=\"text\" id=\"_logs_dir\" name=\"_logs_dir\" value=\"$_logs_dir\" size=\"50\"".((!$_logs_enable) ? " disabled" : "")."> ".genframe($_logs_dir)."</td></tr>\n");	
	print("<tr><td class=td410>Файл лога: </td><td> <input type=\"text\" name=\"syslog\" id=\"syslog\" value=\"$syslog\" size=\"50\" ".((!$_logs_enable) ? " disabled" : "")."> ".genframe($_logs_dir."/".$syslog)."</td></tr>\n");		
	print("<tr><td class=td410>Уровень лога: </td><td> <span class=seldiv><SELECT name=\"_logs_level\" id=\"_logs_level\" ".((!$_logs_enable) ? " disabled" : "")."> \n");
	print("<option value=\"5\"".(($_logs_level==5) ? " SELECTED" : "").">Только критические ошибки\n");
	print("<option value=\"4\"".(($_logs_level==4) ? " SELECTED" : "").">Предупреждения, все ошибки\n");
	print("<option value=\"3\"".(($_logs_level==3) ? " SELECTED" : "").">Отладка, предупреждения, все ошибки\n");
	print("<option value=\"2\"".(($_logs_level==2) ? " SELECTED" : "").">Системная инфо, отладка, предупреждения, все ошибки\n");
	print("<option value=\"1\"".(($_logs_level==1) ? " SELECTED" : "").">Полный лог - вся информация\n");
	print("</SELECT></span>\n</td></tr>\n");	
	print("<tr><td class=td410>Макс.размер файла: </td><td> <input type=\"text\" name=\"_logs_log_maxsize\" id=\"_logs_log_maxsize\" value=\"$_logs_log_maxsize\" size=\"4\" ".((!$_logs_enable) ? " disabled" : "")."> <font style=\"FONT: italic 8pt Arial;\">* M - 'Мегабайт', G - 'Гигабайт', k - 'Килобайт'</font></td></tr>\n");	
	print("<tr><td colspan=2 align=right style=\"padding-right:80px\">  <input type=\"submit\" name=\"sbmt\" value=\"Сохранить\"> </td></tr>\n");

	print("</table>\n</form>\n");
	wlog("Просмотр параметров переменных группы system",0,FALSE,1,FALSE);
	exit;
    
    } elseif( $pmode=="save") {
	
	config_save("system");	
	
    }

}


#-------------------------------------------------------------------------

function options_web($pmode)
{

    global $filter_web_access,$allowed_ip,$site_to_redirect;    
    global $mysql_brief_dbinfo,$monitor_def_grp,$monitor_delay,$report_min_procent,$rep_whoisurl;
    global $_set_save_onchange,$_set_submit_query,$_set_show_listinfo;
    global $viewlogs_system,$viewlogs_show_last,$viewlogs_height,$viewlogs_width;
    global $lframe_show_sessid,$_crypt_passform;
    global $pollist_poltemp_exitwarn,$pollist_fmode_default;

    global $script;

    global $_frm_filter_web_access,$_frm_allowed_ip,$_frm_site_to_redirect;    
    global $_frm_mysql_brief_dbinfo,$_frm_monitor_def_grp,$_frm_monitor_delay,$_frm_report_min_procent,$_frm_rep_whoisurl;
    global $_frm_set_save_onchange,$_frm_set_submit_query,$_frm_set_show_listinfo;
    global $_frm_viewlogs_system,$_frm_viewlogs_show_last,$_frm_viewlogs_height,$_frm_viewlogs_width;
    global $_frm_lframe_show_sessid,$_frm_crypt_passform;
    global $_frm_pollist_poltemp_exitwarn,$_frm_pollist_fmode_default;


    if( ( $pmode=="") or ( $pmode=="show")) {
    
	print("<html>\n");
	$NoHeadEnd=FALSE;
	require("include/head1.php");
	print("<body>\n");

	print("<br><font class=top1>Параметры веб-интерфейса</font><br><br><br>\n<font class=text42>\n");
	print("<form name=\"paths1\" action=\"$script\" method=\"POST\">\n");
	print("<input type=\"HIDDEN\" name=\"grp\" value=\"web\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" value=\"save\">\n");
	print("<table class=table4 cellpadding=\"3px\" width=\"650px\">\n");
	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Ограничение входа: </b></td></tr>\n");    
	print("<tr><td class=td410 colspan=2> <input type=\"checkbox\" name=\"filter_web_access\" id=\"filter_web_access\" value=\"1\"".(($filter_web_access==1) ? " checked" : "")."><label for=\"filter_web_access\"> Фильтровать доступ к веб-интерфейсу</label> </td></tr>\n");
	print("<tr><td class=td410 colspan=2> <input type=\"checkbox\" name=\"_crypt_passform\" id=\"_crypt_passform\" value=\"1\"".(($_crypt_passform) ? " checked" : "")."><label for=\"_crypt_passform\"> Шифровать данные на странице авторизации </label> </td></tr>\n");
	print("<tr><td class=td410>Разрешенные IP </td><td> <input type=\"text\" name=\"allowed_ip\" value=\"$allowed_ip\" size=\"60\"> </td></tr>\n");    
	print("<tr><td class=td410>Сайт для подмены </td><td> <input type=\"text\" name=\"site_to_redirect\" value=\"$site_to_redirect\" size=\"60\"> </td></tr>\n");    
	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Общие: </b></td></tr>\n");    
	print("<tr><td class=td410 colspan=2> <input type=\"checkbox\" name=\"mysql_brief_dbinfo\" id=\"mysql_brief_dbinfo\" value=\"TRUE\" ".(($mysql_brief_dbinfo) ? " checked" : "")."><label for=\"mysql_brief_dbinfo\"> Не показывать таблицы при просмотре информации о БД в разделе MySQL</label> </td></tr>\n");    
	print("<tr><td class=td410 colspan=2> <input type=\"checkbox\" name=\"lframe_show_sessid\" id=\"lframe_show_sessid\" value=\"TRUE\" ".(($lframe_show_sessid) ? " checked" : "")."><label for=\"lframe_show_sessid\"> Показывать ID сессии</label> </td></tr>\n");    

	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Политики: </b></td></tr>\n");    
	print("<tr><td class=td410> Вид списка по-умолчанию </td><td> <span class=seldiv> <SELECT name=\"plst_fdef\" id=\"plst_fdef\">\n");
	print("<option value=\"1\"".(( $pollist_fmode_default=="1") ? " SELECTED" : "").">Расширенный режим</option>\n<option value=\"0\"".(( $pollist_fmode_default=="0") ? " SELECTED" : "").">Компактный режим</option>\n");
	print("</SELECT></span>\n</td></tr>\n");
	print("<tr><td class=td410 colspan=2> \n");
	print("<input type=\"CHECKBOX\" name=\"plst_pexiw\" id=\"plst_pexiw\" value=\"1\"".(( $pollist_poltemp_exitwarn) ? " CHECKED" : "")."><label for=\"plst_pexiw\"> Предупреждать при выходе о несохраненных записях политик в буфере сеанса</label>\n");
	print("</td></tr>\n");
	
	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Монитор трафика:</b> </td></tr>\n");
	print("<tr><td class=td410> Группа по умолчанию <span class=seldiv><SELECT name=\"monitor_def_grp\">\n");
	$link=mysql_getlink();
	if( $res=mysql_query("SELECT * FROM groups WHERE 1")) {
	    while( $row=mysql_fetch_array($res)) {
		$bufgrp=$row["name"];
		print("<option value=\"".$bufgrp."\"".(($bufgrp==$monitor_def_grp) ? " selected" : "").">".$bufgrp."\n");
	    }
	}
	print("<option value=\"all\"".(($monitor_def_grp=="all") ? " selected" : "").">Все\n");
	print("</SELECT></span>\n</td><td class=td410> Таймаут по умолчанию <input type=\"text\" name=\"monitor_delay\" value=\"$monitor_delay\" size=\"3\"> </td></tr>\n");    
	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Отчеты: </b></td></tr>\n");    
	print("<tr><td class=td410> Минимальный % трафика <input type=\"text\" name=\"report_min_procent\" value=\"$report_min_procent\" size=\"3\"> </td><td> Whois url <input type=\"text\" name=\"rep_whoisurl\" value=\"$rep_whoisurl\" size=\"48\"> </td></tr>\n");
	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Сет-панель: </b></td></tr>\n");    
	print("<tr><td class=td410 colspan=2> <input type=\"checkbox\" name=\"_set_save_onchange\" id=\"_set_save_onchange\" value=\"TRUE\" ".(($_set_save_onchange) ? " checked" : "")."><label for=\"_set_save_onchange\"> При изменении сохранять сетлист в соответствующий файл</label> <br>\n");
	print(" <input type=\"checkbox\" name=\"_set_show_listinfo\" id=\"_set_show_listinfo\" value=\"TRUE\" ".(($_set_show_listinfo) ? " checked" : "")."><label for=\"_set_show_listinfo\"> Показывать информацию об элементах сетлиста в сет-панели</label> <br>\n");
	print(" <input type=\"checkbox\" name=\"_set_submit_query\" id=\"_set_submit_query\" value=\"TRUE\" ".(($_set_submit_query) ? " checked" : "")."><label for=\"_set_submit_query\"> Запрашивать подтверждение при операциях с сетлистом</label> </td></tr>\n");    
	print("<tr><td colspan=2 style=\"padding-left:40px\"><b> Просмотр логов </b></td></tr>\n");    
	print("<tr><td class=td410 colspan=2>Логи на странице системы <input type=\"text\" name=\"viewlogs_system\" value=\"$viewlogs_system\" size=\"70\"> </td></tr>\n");    
	print("<tr><td class=td410 colspan=2>Показывать последние <input type=\"text\" name=\"viewlogs_show_last\" value=\"$viewlogs_show_last\" size=\"6\"> записей </td></tr>\n");
	print("<tr><td class=td410 colspan=2>Размеры окна: высота <input type=\"text\" name=\"viewlogs_height\" value=\"$viewlogs_height\" size=\"6\">px / ширина <input type=\"text\" name=\"viewlogs_width\" value=\"$viewlogs_width\" size=\"6\">px</td></tr>\n");
	print("<tr><td colspan=2 align=right style=\"padding-right:80px\">  <input type=\"submit\" name=\"sbmt\" value=\"Сохранить\"> </td></tr>\n");
	print("</table>\n</form>\n");
	wlog("Просмотр параметров переменных группы web",0,FALSE,1,FALSE);
	exit;
    
    } elseif( $pmode=="save") {
	
	config_save("web");	
	
    }

}

#-------------------------------------------------------------------------



if( $_group=="") {
    print("<html>\n");
    $NoHeadEnd=FALSE;
    require("include/head1.php");
    print("<body>\n");

    print("<br><br>\n<blockquote>\n<font class=top2>Параметры программы</font><br><br><br>\n<font class=text41t>\n");
    print("<a href=\"$script?grp=paths\" title=\"Настройка путей\">Пути к файлам и папкам</a><br><br>\n");
    print("<a href=\"$script?grp=web\" title=\"Настройка параметров веб-интерфейса\">Параметры веб-интерфейса</a><br><br>\n");
    print("<a href=\"$script?grp=system\" title=\"Настройка системных параметров\">Системные параметры</a><br><br>\n");
    print("</font>\n");
    print("</blockquote>\n");
    exit;

} elseif( $_group=="paths") {
    if(  $_mode=="save") {
	options_paths($_mode);
	header("Location: $script?grp=paths");
    } else {
	options_paths($_mode);
    } 
} elseif( $_group=="web") {
    if( ($_mode=="") or ($_mode=="show")) {
	options_web($_mode);
    } elseif( $_mode=="save") {
	options_web($_mode);
	header("Location: $script?grp=web");
    }
} elseif( $_group=="system") {
    if( ($_mode=="") or ($_mode=="show")) {
	options_system($_mode);
    } elseif( $_mode=="save") {
	options_system($_mode);
	header("Location: $script?grp=system");
    }
}




?>
<br><br><br><br><br><br>
</body>
</html>
