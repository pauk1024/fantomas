#!/bin/php
<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2010 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: update.php
# Description: Fantomas Updater for version 2.8.1 to 2.8.2
# Version: 2.8.2
###

#//------  script options default values:

if( !session_id()) session_start();

$oldpath="";
$httpd_dir="/www";
$init_dir="/etc/init.d";
$_tar="/bin/tar";
$_console=TRUE;
$_passw_md5_enable=TRUE;
$arg_oldpath="";
$ssh_arg_user="root";
$ssh_arg_host="127.0.0.1";
$ssh_arg_port="22";
$noulogd=FALSE;

#//------  end script defaults


$_rctool="";
if( !trim($_chkconfig=shell_exec("which chkconfig"))) {
    if( !trim($_sysvrcocnf=shell_exec("which sysv-rc-conf"))) {
	print("Could not found chkconfig or sysv-rv-conf, exiting..."); exit;
    } else {
	$_rctool="_sysvrcconf";
    }
} else {
    $_rctool="_chkconfig";
}

if(( !trim($_grep=exec("which grep",$aa0,$rr))) or ( $rr>0)) {
    print("Error searching grep, exiting..."); exit;
}

if( !is_readable($_tar)) {
    if(( !trim($_tar=exec("which grep",$aa0,$rr))) or ( $rr>0)) {
	print("Error searching tar, exiting..."); exit;
    }
}



#---------------------------------------------------------
function load_($pfile="") 
{
    $rez=FALSE;
    if( file_exists("./".$pfile)) {
	require($pfile);
	$rez=TRUE;
    } else {
	if( file_exists("./www/".$pfile)) {
	    require("./www/".$pfile);
	    $rez=TRUE;
	}
    }
    return($rez);
}
#---------------------------------------------------------
function exit_($mess)
{
    print("\n$mess\n");
    exit;
}
#---------------------------------------------------------
function _input($varname) 
{
    global $$varname;
    $buf=$$varname;
    if( !trim($ret=trim(shell_exec("read line; echo \$line")))) $ret=$$varname;
    return($ret);
}
#---------------------------------------------------------
function load_provs()
{
    global $oldpath;
    $alist=array();
    if( !file_exists($oldpath."/providers")) return(FALSE); 
    $fprov=fopen($oldpath."/providers","r");
    $_i=0; $flt=FALSE; $_ii=0;
    $open=FALSE;
    while( !feof($fprov)) {
	$str1=strtolower(trim(fgets($fprov)));
	if( strlen($str1)==0) continue;
	if( $str1[0]=="#") continue;
	$buff1=gettok($str1,1," \t");
	if( !$open) {
	    if( $buff1=="link") { 
		$_ppname=gettok($str1,2," \t");
		$_oo0=trim(gettok($str1,3," \t"));
		$_oo=( ($_oo0=="local") or ($_oo0=="localnet")) ? "local" : "";
		$_oo1=""; $_oo2=""; 
		$open=TRUE; continue; 
	    }
	} else {
	    if( $buff1=="}") { 
		$aprov["ip"]=$_oo2;
		$aprov["eth"]=$_oo1;
		$aprov["local"]=$_oo;
		$alist[$_ppname]=$aprov;
		unset($aprov);
		$open=FALSE;
		continue; 
	    } elseif( $buff1=="link") {
		$aprov["ip"]=$_oo2;
		$aprov["eth"]=$_oo1;
		$aprov["local"]=$_oo;
		$alist[$_ppname]=$aprov;
		unset($aprov);
		$open=TRUE;
		continue; 
	    } else {
		$_pp1=gettok($str1,1,"=:");
		$_oo1=( ($_pp1=="extif") or ($_pp1=="ifname")) ? gettok($str1,2,"=") : $_oo1;
		$_oo2=( ($_pp1=="ipaddr") or ($_pp1=="extip")) ? gettok($str1,2,"=") : $_oo2;
	    }
	}
    }
    fclose($fprov);
    return($alist);
}
#---------------------------------------------------------



if( !load_("iptlib.php")) exit_("Error loading iptlib.php");
if( !load_("authlib.php")) exit_("Error loading authlib.php");

if( !file_exists($_tar)) exit_("Please specify location of tar...");


$usage="Usage:\nupdate.php --oldpath=</path/to/old/iptconf_dir> --ssh_user=<username> --ssh_host=<address> --ssh_port=<port> --ulogd_noupdate";

foreach($argv as $argkk => $argvv) {
    if( $argkk==0) continue;    
    $bufarg=gettok($argvv,1,":=");
    if( $bufarg == "--oldpath") {
	$arg_oldpath=gettok($argvv,2,":=");
    } elseif( $bufarg == "--ssh_user") {
	$ssh_arg_user=gettok($argvv,2,":=");
    } elseif( $bufarg == "--ssh_host") {
	$ssh_arg_host=gettok($argvv,2,":=");
    } elseif( $bufarg == "--ssh_port") {
	$ssh_arg_port=gettok($argvv,2,":=");
    } elseif( $bufarg == "--ulogd_noupdate") {
	$noulogd=TRUE;
    } else {
	exit_($usage);
    }
}


$oldpath=( trim($arg_oldpath=="")) ? $oldpath : $arg_oldpath;

if( !trim($oldpath)) exit_($usage);

if( count($argv)==0) {
    if(( !file_exists($oldpath."/iptconf.php")) or ( !file_exists($oldpath."/www/config.php"))) {
	exit_("Error: oldpath .$oldpath. is not found...\n\n$usage");
    }
    if(( !file_exists($httpd_dir."/conf/httpd.conf")) or ( !is_writable($httpd_dir."/conf/httpd.conf"))) {
	exit_("Error: httpd_dir is not found or httpd.conf is not accessible...\n\n$usage");
    }
}
print("Updating directory $oldpath...\n \n");

#=========


#=========

if( !function_exists("ssh2_connect")) {
    print("AHTING! PHP ssh2 functions is not available, please install pecl/ssh2 module!\n");
    exit;
}

print("... Function ssh2_connect() is supported!\n");
while( TRUE) {
    if(( !isset($ssh_host)) || ( !trim($ssh_host))) $ssh_host=$ssh_arg_host;
    if(( !isset($ssh_port)) || ( !trim($ssh_port))) $ssh_port=$ssh_arg_port;
    if(( !isset($ssh_user)) || ( !trim($ssh_user))) $ssh_user=$ssh_arg_user;
    if(( !isset($ssh_pass)) || ( !trim($ssh_pass))) $ssh_pass="12456";
    print("\n");
    print("... Please specify options for SSH-connection:\n");
    print("ip or hostname [$ssh_host]: "); $ssh_host=_input("ssh_host");
    print("port number [$ssh_port]: "); $ssh_port=_input("ssh_port");
    print("login name [$ssh_user]: "); $ssh_user=_input("ssh_user");
    print("password [$ssh_pass]: "); $ssh_pass=_input("ssh_pass");
    print("\n");

    print("checking connection:\n");

    print("... connecting to ".$ssh_host.":".$ssh_port."...");
    if( !$link=ssh2_connect($ssh_host,$ssh_port)) {
	print("FAILED\n");
	$ssh_host=""; $ssh_port=""; $ssh_user=""; $ssh_pass="";
	continue;
    } else {
	print("OK\n");
    }
    print("... auth by $ssh_user....");
    if( !@ssh2_auth_password($link,$ssh_user,$ssh_pass)) {
	print("FAILED\n");
	$ssh_host=""; $ssh_port=""; $ssh_user=""; $ssh_pass="";
	continue;
    } else {
	print("OK\n");
    }
    break;
}
print("\n");
if( $ssh_user!="root") {
    if( !trim(shell_exec("cat /etc/sudoers | grep ".$ssh_user))) {
	print("User $ssh_user is not have rights for using sudo, I will create record for thos user in /etc/sudoers...\n");
	system("echo \"##Fantomas configured\" >> /etc/sudoers");
	system("echo \"$ssh_user ALL=(ALL) NOPASSWD:ALL\" >> /etc/sudoers");
	system("echo \"##End Fantomas configured\" >> /etc/sudoers");
    }
}

#=========
print("\n");


print("Reading oldpath's config.php file ...");

$conffile=$oldpath."/config.php";
if( file_exists($conffile)) require($conffile);

$oconf=fopen($conffile,"r");
$flconf=FALSE;

$aconfig=array();
$aconfig["ssh_host"]="\"".$ssh_host."\"";
$aconfig["ssh_port"]="\"".$ssh_port."\"";
$aconfig["ssh_user"]="\"".$ssh_user."\"";
$aconfig["ssh_pass"]="\"".$ssh_pass."\"";

while( !feof($oconf)) {
    $string=trim(fgets($oconf));
    $flconf=( str_replace(" ","",$string)=="###Configzone") ? TRUE : FALSE;
    $flconf=( str_replace(" ","",$string)=="#########Endofconfigzone") ? FALSE : TRUE;
    if( !$flconf) { break; } else {
	$bufopt=str_replace("\$","",str_replace(" ","",gettok($string,1,":=")));
	$bufval=str_replace(";","",str_replace(" ","",gettok($string,2,":=")));
	$aconfig[$bufopt]=$bufval;
    }
}
fclose($oconf);


$odefconf=fopen("./config.php","r");
$flconf=FALSE;
$bufconf="./config.ini";
$obufconf=fopen($bufconf,"w");

print("done\n");

#=========


print("Patching new config.php file ...");

while( !feof($odefconf)) {
    $string=fgets($odefconf);
    $trstring=str_replace(" ","",trim($string));
    if( $trstring=="###Configzone") {
	$flconf=TRUE;
	fwrite($obufconf,$string);
	continue;
    }
    if( $trstring=="#########Endofconfigzone") {
	$flconf=FALSE;
	fwrite($obufconf,$string);
	continue;
    }
    if( $flconf) {
	$bufopt=str_replace("\$","",gettok($string,1,":="));
	$bufval=str_replace(";","",gettok($string,2,":="));
	reset($aconfig); $flok=FALSE;
	foreach($aconfig as $aconfkk => $aconfvv) {
	    if( $bufopt == $aconfkk) { $flok=TRUE; break; }
	}
	if( $flok) {
	    if( $aconfig[$bufopt]!=$bufval) {
		$lineout="\$$bufopt = ".$aconfig[$bufopt].";\n";
	    } else {
		$lineout=$string;
	    }
	} else {
	    $lineout=$string;
	}
    } else {
	$lineout=$string;
    }
    if(( isset($lineout)) and ($lineout!="")) fwrite($obufconf,$lineout);
}
fclose($odefconf);

print("done\n");

#=========

if( file_exists("./config.php.default")) rename("./config.php.default","./config.php.default.bak");
rename("./config.php","./config.php.default");
rename("./config.ini","./config.php");

if( !load_("config.php")) exit_("Error loading new patched config.php");

$currdir=getcwd();
chdir(substr($oldpath,0,strrpos($oldpath,"/")));

print("\nBackuping directory with previos Fantomas Iptconf version ...");
system("$_tar -cjf fantomas.iptconf.oldversion-backup.tar.gz ".substr($oldpath,strrpos($oldpath,"/")+1)."/");

chdir($currdir);

print("done\n");

#=========


print("\nUpdating mysql db ...");

$link=mysql_getlink();

$adb=array(
    "ALTER TABLE `clients` ADD `islocked` INT( 1 ) NOT NULL",
    "ALTER TABLE `clients` ADD INDEX ( `islocked` );",
    "DROP TABLE IF EXISTS `services`",
    "CREATE TABLE `services` (`servicename` VARCHAR( 100 ) NOT NULL, `config_path` TEXT NOT NULL, `log_path` TEXT NOT NULL)",
    "ALTER TABLE `services` DEFAULT CHARACTER SET koi8r COLLATE koi8r_general_ci"
);

foreach($adb as $adbkey => $aline) {
    if( !$res=mysql_query($aline)) {
	print("AHTUNG! Got an error while querying mysql: ".mysql_error($link)."\n");
	exit;
    }
}

print("done\n");

#=========

/*
print("\nUpdating Ulogd MySQL tables ...");

$ulogdb=(trim($ulog_dbname)!="") ? $ulog_dbname : "ulogd";
mysql_select_db($ulogdb);

$rez=mysql_query("ALTER TABLE `ulog` ADD INDEX ( `oob_time_sec` ), ADD INDEX ( `ip_saddr` ), ADD INDEX ( `ip_daddr` )");

*/



print("\nCleaning old version files from oldpath directory ...");

$afiles=array(
	    "networks",
	    "providers",
	    "usr",
	    "ipsetlist.1",
	    "config.php.1",
	    "f-setup",
	    "update.php",
	    "iptcldr.default",
	    "README.En",
	    "Changelog.En",
	    "Changelog.Ru.koi8r"
	);
system("rm -f ".$oldpath."/*.bak");
foreach($afiles as $afkk => $afvv) {
    if( !file_exists($oldpath."/".$afvv)) continue;
    if( is_file($oldpath."/".$afvv)) {
	if( !unlink($oldpath."/".$afvv)) exit_("Error deleting file $oldpath/$afvv...");
    } else {
	system("rm -f ".$oldpath."/".$afvv."/*; rmdir ".$oldpath."/".$afvv);
    }
}

#=========


print("\nCopying new files to oldpath directory ...");

if( !is_dir($oldpath."/www/man")) {
    if( !mkdir($oldpath."/www/man")) exit_("Error creatind directory $oldpath/www/man...");
}
if( !is_dir($oldpath."/logs")) {
    if( !mkdir($oldpath."/logs")) exit_("Error creatind directory $oldpath/logs...");
}

$afiles=array(  "f-setup", 
		"VERSION",
		"iptcldr",
		"iproute2-init",
		"iptconf.php",
		"shaperconf",
		"lang",
		"logs",
		"mysql",
		"tools",
		"www/css",
		"www/icons",
		"www/include",
		"www/js",
		"www/man",
		"www"
	    );
foreach($afiles as $afkk => $afvv) {
    if( is_file($afvv)) {
	if( !unlink($oldpath."/".$afvv)) exit_("Error deleting file $oldpath/$afvv...");
	if( !copy("./".$afvv,$oldpath."/".$afvv)) exit_("Error copying file $afvv...");
    } else {
	if( isset($anewfls)) unset($anewfls);
	if( !file_exists($oldpath."/$afvv")) {
	    mkdir($oldpath."/$afvv");
	}
	if( file_exists("./$afvv")) {
	    $anewfls=scandir("./$afvv");
	    if( !count($anewfls)) continue;
	    foreach($anewfls as $anfkk => $anfvv) {
		if( trim($anfvv)=="") continue;
		if( !is_file("$afvv/$anfvv")) { continue; } else {
		    if( file_exists("$oldpath/$afvv/$anfvv")) {
			if(!unlink("$oldpath/$afvv/$anfvv")) exit_("Error deleting file $oldpath/$afvv/$anfvv");
		    }
		    if( !copy("./$afvv/$anfvv","$oldpath/$afvv/$anfvv")) exit_("Error copying file $afvv/$anfvv");
		}
	    }
	}
    }
}

system("chmod 755 ".$oldpath."/tools/tabloid");
system("chmod 755 ".$oldpath."/tools/ifb");
system("chmod 755 ".$oldpath."/tools/sbnetscan");
system("chmod 755 ".$oldpath."/tools/eat");


print("done\n");

if( file_exists($oldpath."/ports")) {
    $aaprt=file($oldpath."/ports");
    if( !($newports=fopen($oldpath."/ports.new","a"))) exit_("Errir creating file ports.new in $oldpath");
    foreach( $aaprt as $aaprtkey => $aaprtvalue) {
	if( trim($aaprtvalue)=="") continue;
	fwrite($newports,trim($aaprtvalue)." destrination:both src:0.0.0.0/0 dst:0.0.0.0/0\n");
    }
    fclose($newports);
    rename("$oldpath/ports","$oldpath/ports.prev011.backup");
    rename("$oldpath/ports.new","$oldpath/ports");
} else {
    if( !copy("./ports","$oldpath/ports")) exit_("Error copying file ports to $oldpath");
}

$rr=0;
exec("iptables -t mangle -L PORTFILTER",$oo,$rr);
if( $rr!=0) {
    exec("iptables -t mangle -N PORTFILTER",$oo,$rr);
    exec("service iptables save",$oo,$rr);
}

if( file_exists($init_dir."/iptcldr")) {
    copy($init_dir."/iptcldr",$oldpath."/iptcldr.pre282");
    unlink($init_dir."/iptcldr");
    copy("./iptcldr",$init_dir."/iptcldr");
    exec("chmod 755 $init_dir/iptcldr");
    if( !trim(shell_exec($$_rctool." --list iptcldr | $_grep ':on'"))) {
	if( $_rctool=="_chkconfig") {
	    $cmd="$_chkconfig --add iptcldr";
	} elseif( $_rctool=="_sysvrcconf") {
	    $cmd="$_sysvrcconf --level 345 iptcldr on";
	}
	exec($cmd);
    }
}


$owner=gettok(exec("ls $oldpath/config.php -l"),3," \t");
rename("$oldpath/config.php","$oldpath/config.ph_old.backup");
copy("./config.php","$oldpath/config.php");

if( !$noulogd) {

    print("\nUpdating indexes for Ulogd MySQL tables ...");

    $ulogdb=(trim($ulog_dbname)!="") ? $ulog_dbname : "ulogd";
    mysql_select_db($ulogdb);
    $rez=mysql_query("ALTER TABLE `ulog` ADD INDEX ( `oob_time_sec` ), ADD INDEX ( `ip_saddr` ), ADD INDEX ( `ip_daddr` )");

    mysql_close($link);

}

print("\nUpdating owner .$owner. to oldpath directory ...");

exec("chown -R $owner $oldpath");    
exec("chmod 755 $oldpath/iptcldr");
exec("chmod 755 $oldpath/iptconf.php");
exec("chmod 755 $oldpath/f-setup");
print("done\n\nUpdate complete!\n");


?>