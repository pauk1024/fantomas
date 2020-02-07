<?php
### Config zone

#-- options are managable from web:

$iptconf_dir = "/usr/local/fantomas";
$backup_dir = "/usr/local/fantomas/_backup";
$sets_dir = "/usr/local/fantomas/sets";
$users_dir = "/usr/local/fantomas/usr";
$initd_dir = "/etc/init.d";
$iproute_dir = "/etc/iproute2";

$_iptables = "/usr/local/sbin/iptables";
$_ipset = "/usr/local/sbin/ipset";
$_service = "/sbin/service";
$_grep = "/bin/grep";
$_awk = "/usr/bin/awk";
$_nmap = "/usr/bin/nmap";
$_sudo = "/usr/bin/sudo";
$_echo = "/bin/echo";
$_top = "/usr/bin/top";
$_chkconfig = "/sbin/chkconfig";
$_sysv_rc_conf = "/usr/sbin/sysv-rc-conf";
$_crond = "/usr/sbin/crond";
$_crontabd = "/usr/bin/crontab";
$_whois = "/usr/bin/whois";
$_tc = "/sbin/tc";
$_lsmod = "/sbin/lsmod";
$_modprobe = "/sbin/modprobe";
$_ifconfig = "/sbin/ifconfig";
$_ip = "/sbin/ip";
$_ps = "/bin/ps";
$_kill = "/bin/kill";
$_mysql = "/usr/bin/mysql";
$_netstat_nat = "/usr/bin/netstat-nat";
$iptables_config = "/usr/local/fantomas/iptables-config";
$iptables_save = "/usr/local/fantomas/iptables.save";

$default_rctool = "chkconfig";

# chkconfig mode:
# 1 - redhat style
# 2 - debian style
$chkconfig_mode = 1;

# iptables init mode:
# 0 - counters are in the IPTABLES_DATA
# 1 - counters are in the Fantomas counters
$iptables_initmode = 0;

$iproutabled = FALSE;
$iproute_init = "/usr/local/fantomas/iproute2-init";

$_crontab = "/etc/crontab";
$crond_logfile = "/var/log/cron";


$filter_web_access = 1;
$allowed_ip = "127.0.0.1,192.168.0.1";
$site_to_redirect = "http://coreit.ru";
$_portfilter_enable = FALSE;
$_crypt_passform = TRUE;

$_shaper_enable = FALSE;
$_shaper_default_ifbifname = "ifb0";
$_shaper_default_ifname = "eth1";

$mysql_brief_dbinfo = TRUE;
$lframe_show_sessid = TRUE;

$monitor_def_grp = "all";
$monitor_delay = 15;

$report_min_procent = "0.01";
$rep_whoisurl = "https://www.nic.ru/whois/?query=";

$_set_save_onchange = TRUE;
$_set_submit_query = TRUE;
$_set_show_listinfo = TRUE;

$_logs_enable = TRUE;
$_logs_dir = "/usr/local/fantomas/logs";
$_logs_level = 2;
$_logs_logged_checkpass_nolog = TRUE;
$syslog = "fantomas.log";
$_logs_log_maxsize = "5M";

$viewlogs_system = "/var/log/messages;/var/log/secure;/var/log/maillog;/var/log/cron";
$viewlogs_show_last = 100;
$viewlogs_height = 650;
$viewlogs_width = 700;

$lang_dir = "/usr/local/fantomas/lang";
$lang_codepage="koi8r";
$lang="russian";

$pollist_fmode_default = "1";
$pollist_poltemp_exitwarn = TRUE;

$ssh_enable = TRUE;

#-- options are NOT managable from web:

$ssh_host = "127.0.0.1";
$ssh_port = "22";
$ssh_user = "fantomas";
$ssh_pass = "12345";

$_lockfile="iptconf.lck";

$tmpdir="/usr/local/iptconf";

$quota_chk_arg="50k";

$_passw_crypt_key = "kweedfjhgsdjf";
$_passw_md5_enable=TRUE;

$keep_counts_at_polunload=TRUE;

$mysql_host = "127.0.0.1";
$mysql_user = "root";
$mysql_password = "12345";
$mysql_logfile = "/var/log/mysql.log";
$mysql_fantomas_db="fantomas";

$mysql_ulogd_host = "";
$mysql_ulogd_user = "";
$mysql_ulogd_password = "";

$ulog_dbname="ulogd";
$ulog_logfile="/var/log/ulog/ulogd.log";

$syspage_show_procs="ulogd,mysqld,named,httpd,sshd";

$index_freename="ayadvornik";
$index_freepass="xtkjdtrcfvjktn";
#                человексамолет

$index_login_timeout=0;

$usr_cname_spaces=TRUE;

$_exec_errlevel=1;

$cli_brief_mode=TRUE;

######### End of config zone

# some init checks
$users_dir=( trim($users_dir)=="") ? $iptconf_dir : $users_dir;
$sets_dir=( trim($sets_dir)=="") ? $iptconf_dir : $sets_dir;
$backup_dir=( trim($backup_dir)=="") ? $iptconf_dir : $backup_dir;

$pollist_fmode_default=( trim($pollist_fmode_default)=="") ? "1" : $pollist_fmode_default;

$aiprg=array(
"iptables" => "_iptables",
"ipset" => "_ipset",
"grep" => "_grep",
"sudo" => "_sudo",
"top" => "_top",
"chkconfig" => "_chkconfig",
"sysv-rc-conf" => "_sysv_rc_conf",
"crond" => "_crond",
"crontabd" => "_crontabd",
"whois" => "_whois"
);

foreach($aiprg as $kprg => $vprg) {
if( !file_exists($$vprg)) {
$rez=exec("which $kprg &>/dev/null; [ \$? -eq 0 ] && which $kprg || echo ".$$vprg);
$$vprg=$rez;
}
}

unset($aiprg);
unset($vprg); unset($kprg);
unset($rez);

$mysql_ulogd_host = ( !trim($mysql_ulogd_host)) ? $mysql_host:$mysql_ulogd_host;
$mysql_ulogd_user = ( !trim($mysql_ulogd_user)) ? $mysql_user:$mysql_ulogd_user;
$mysql_ulogd_password = ( !trim($mysql_ulogd_password)) ? $mysql_password:$mysql_ulogd_password;

date_default_timezone_set(@date_default_timezone_get());

# end init

?>
