#!/bin/php
<?php
####
# Name: Fantomas Iptconf manager
# Version: 2.8.2
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: mak@coreit.ru
# Web: http://coreit.ru/fantomas/
#
# Script name: iptconf.php
# Description: CLI 
# Version: 2.8.2.2
####


$wwwdir="/usr/local/fantomas/www";
$iptconf_dir="/usr/local/fantomas";
$_console=TRUE;
$_lockfile="iptconf.lck";

$aareqs=array( "config.php", "iptlib.php", "iptlib2.php", "userlib.php", "shapelib.php");

#----------------------------------------------------------------------------------

function show_cli_usage()
{
    echo "Fantomas Iptconf CLI version 2.8\n";
    echo "Usage:\n";
    echo "\n";
    echo "- RELOAD configuration:\n";
    echo "   iptconf.php [NoKeepCounts] Reload \n";
    echo "\n";
    echo "- Save traffic counters to file for all clients:\n";
    echo "   iptconf.php SaveCounts \n";
    echo "\n";
    echo "- New period opening:\n";
    echo "   iptconf.php NewPeriod \n";
    echo "\n";
    echo "- Get clients group list:\n";
    echo "   iptconf.php list \n";
    echo "\n";
    echo "- Get clients list in specified group:\n";
    echo "   iptconf.php group=<groupname> list \n";
    echo "\n";
    echo "- Client policies and traffic counters status info:\n";
    echo "   iptconf.php group=<groupname> client=<client_ipaddr> [ListPolicies] [ShowTraffic] \n";
    echo " or \n";
    echo "   iptconf.php --grp=<groupname> --usr=<client_ipaddr> [lspols] [shtraf] \n";
    echo "\n";
    echo "- (Add and load) or (unload and delete) policy for client:\n";
    echo "   iptconf.php group=<groupname> client=<client_ipaddr> policy=<policyname> [AddPolicy|DeletePolicy]\n";
    echo " or \n";
    echo "   iptconf.php --grp=<groupname> --usr=<client_ipaddr> --pp=<policyname> [addpol|delpol]\n";
    echo "\n";
    echo "\n";
    echo "\n";
}

#----------------------------------------------------------------------------------

if( getcwd()!=$iptconf_dir) if( !chdir($iptconf_dir)) { print("Error changing directory to $iptconf_dir in iptconf.php... "); exit; }

if( !file_exists($iptconf_dir."/config.php")) {
    print("Config file $iptconf_dir/config.php not found!\n"); exit;
} else {
    $atmp1=posix_getpwuid(fileowner($iptconf_dir."/config.php"));
    $tmp_owner=$atmp1["name"];
    unset($atmp1);
}

foreach( $aareqs as $aark => $aarv) {
    if( file_exists("./".$aarv)) {
	require("./".$aarv);
    } else {
	require($wwwdir."/".$aarv);
    }
}


# параноим и лочимся
if( ! file_exists($_iptables)) { wlog("Iptables is not found, exiting...",1); _exit(); }
if( file_exists($_lockfile)) { wlog("Lock file $_lockfile is founded, exiting...",1); _exit(); }
$_lck=fopen($_lockfile,"w");
if( ! fwrite($_lck," ")) { wlog("Can't create lock file in $_lockfile",1); _exit(); }


load_ifs();

$flnoexport=FALSE;
$_client="";
$_group="";
$_policy="";

if( count($argv) > 1) {
# ну и чего нам тут понаписали?
foreach($argv as $_i => $argvv) {
	if( $_i==0) continue;
	if( trim($argv[$_i])=="") continue;
	if( strtolower($argv[$_i]) == "savecounts") {

	    if( !file_exists($iptconf_dir."/1stconf")) counts_export();

	} elseif(strtolower($argv[$_i]) == "nokeepcounts") {

	    $flnoexport=TRUE;

	} elseif(strtolower($argv[$_i]) == "reload") {

	    if( !$flnoexport) counts_export();
	    fillconf();
	} elseif(strtolower($argv[$_i]) == "newperiod") {

	    open_new_period();

	} elseif((gettok(strtolower($argv[$_i]),1,":=") == "client") or ( gettok(strtolower($argv[$_i]),1,":=") == "--cli") or ( gettok(strtolower($argv[$_i]),1,":=") == "--usr")) {

	    $_client=gettok($argv[$_i],2,":=");
	    if( substr_count($_client,".")!=3) {
		print("Bad client ip address\n"); _exit();
	    }

	} elseif((gettok(strtolower($argv[$_i]),1,":=") == "briefly") or ( gettok(strtolower($argv[$_i]),1,":=") == "--br")) {

	    $cli_brief_mode=(( gettok($argv[$_i],2,":=")=="on") ? TRUE : $cli_brief_mode);
	    $cli_brief_mode=(( gettok($argv[$_i],2,":=")=="off") ? FALSE : $cli_brief_mode);

	} elseif((gettok(strtolower($argv[$_i]),1,":=") == "group") or ( gettok(strtolower($argv[$_i]),1,":=") == "--grp")) {

	    $_group=gettok($argv[$_i],2,":=");

	} elseif((gettok(strtolower($argv[$_i]),1,":=") == "policy") or ( gettok(strtolower($argv[$_i]),1,":=") == "--pp")) {

	    $_policy=gettok($argv[$_i],2,":=");

	} elseif((strtolower($argv[$_i]) == "deletepolicy") or (strtolower($argv[$_i]) == "delpol")) {

	    if(trim($_client)=="") {
		print("Client must be specified before calling DeletePolicy...\n"); _exit();
	    }
	    if(trim($_group)=="") {
		print("Group of client must be specified before calling DeletePolicy...\n"); _exit();
	    }
	    if(trim($_policy)=="") {
		print("Policy must be specified before calling DeletePolicy...\n"); _exit();
	    }
	    print("Deleting policy $_policy....  ");
	    $line=(( !user_delpol($_group,$_client,$_policy)) ? "OK" : "FAILED");
	    print($line."\n");
	    


	} elseif((strtolower($argv[$_i]) == "addpolicy") or (strtolower($argv[$_i]) == "addpol")) {

	    if(trim($_client)=="") {
		print("Client must be specified before calling AddPolicy...\n"); _exit();
	    }
	    if(trim($_group)=="") {
		print("Group of client must be specified before calling AddPolicy...\n"); _exit();
	    }
	    if(trim($_policy)=="") {
		print("Policy must be specified before calling AddPolicy...\n"); _exit();
	    }
	    print("Applying policy $_policy....  ");
	    $line=(( !user_addpol($_group,$_client,$_policy)) ? "OK" : "FAILED");
	    print($line."\n");
	    

    	} elseif((strtolower($argv[$_i]) == "list") or (strtolower($argv[$_i]) == "lspols") or (strtolower($argv[$_i]) == "ls")) {

	    $link=mysql_getlink();
	    if(trim($_group)=="") {
		if( !$res=mysql_query("SELECT groups.name, groups.default_policy, groups.title, (SELECT COUNT(*) FROM clients WHERE clients.group_id=groups.id) AS userc FROM groups WHERE 1")) return("");
		if( mysql_num_rows($res)==0) return("");
		print("Available groups:\n");
		print("----------------\n");
		while( $row=mysql_fetch_array($res)) {
		    print(" ".$row["name"]." ( clients:".$row["userc"].", default_policy:".$row["default_policy"]." )\n");
		}
		_exit();
	    }
	    if(trim($_client)=="") {
		if( !trim($_group)) return("");
		if( !$_id=get_usr_param($_group,"group_id")) return("");
		if( !$res=mysql_query("SELECT * FROM clients WHERE group_id=$_id")) return("");
		if( mysql_num_rows($res)==0) return("");
		print("Clients available in group $_group:\n");
		print("----------------------------------\n");
		while( $row=mysql_fetch_array($res)) {
		    print($row["ip"]." ( cname:".$row["cname"].", policies:".$row["policies"]." )\n");
		}
		_exit();
	    }
	    $pollist=get_usr_param($_group,$_client,"policy",TRUE);
	    $defpolicy=get_usr_param($_group,"_default_policy","",TRUE," =\t");
	    $pollist=( trim($pollist)=="") ? $defpolicy : $pollist;
	    if( !$cli_brief_mode) {
		print("---------\n");
		print("Mode: view client policies list\n");
		print("group: $_group\n");
		print("client: $_client\n");
		print("cname: ".get_usr_param($_group,$_client,"cname",TRUE)."\n---------\n");
		print("Policies:\n");
	    }
	    $cp=coltoks($pollist,",");
	    for($ii=1;$ii<=$cp;$ii++) {
		$currpol=( $cp==1) ? $pollist : gettok($pollist,$ii,",");
		print((( !$cli_brief_mode) ?  "   -> " : "").$currpol." -> status:".(( is_policy_loaded($currpol,$_client)) ? "Loaded" : "NOT Loaded")."\n");
	    }
	    print("\n"); 
	    
	    
	} elseif((strtolower($argv[$_i]) == "showtraffic") or (strtolower($argv[$_i]) == "shtraf")) {

	    if(trim($_client)=="") {
		print("Client must be specified before calling ShowTraffic...\n"); _exit();
	    }
	    if(trim($_group)=="") {
		print("Group of client must be specified before calling ShowTraffic...\n"); _exit();
	    }
	    if( isset($atraf)) unset($atraf);
	    if( !($atraf=process_counters($_client,$_group))) { unset($atraf); $atraf=array(); }
	    $pollist=get_usr_param($_group,$_client,"policy",TRUE);
	    $defpolicy=get_usr_param($_group,"_default_policy","",TRUE," =\t");
	    $pollist=(( $_policy=="") ? (( trim($pollist)=="") ? $defpolicy : $pollist) : $_policy);
	    if( count($atraf)==0) {
		print("Is not found policies-provided traffic counters for client addr $_client.\n"); _exit();
	    } else {
		if( !$cli_brief_mode) {
		    print("---------\n");
		    print("Mode: view client traffic counters\n");
		    print("group: $_group\n");
		    print("client: $_client\n");
		    if( $_policy!="") print("policy: $_policy\n");
		    print("cname: ".get_usr_param($_group,$_client,"cname",TRUE)."\n---------\n");
		}
	    }
	    $cp=coltoks($pollist,",");
	    for($ii=1;$ii<=$cp;$ii++) {
		$currpol=( $cp==1) ? $pollist : gettok($pollist,$ii,",");
		print("Policy: ".$currpol."\n");
		foreach( $atraf as $aak => $aav) {
		    if( !isset($atraf[$aak]["policy"]["policyname"])) { continue; } else {
			if( $atraf[$aak]["policy"]["policyname"]!=$currpol) continue;
		    }
		    $tmode=""; 
		    if(trim($tmode=get_policy_param($currpol,"accept","",0,TRUE))=="") {
			$tmode=get_policy_param($currpol,"reject","",0,TRUE);
		    }
		    print("   -> target \"$tmode ".$atraf[$aak]["policy"]["proto"].":".str_replace(" ","",$atraf[$aak]["policy"]["ports"])."\" (pkts/traf) -> ");
		    print(" IN: ".$atraf[$aak]["in_pkts"]."/".bytes2mega($atraf[$aak]["in_bytes"])." ");
		    print(" OUT: ".$atraf[$aak]["out_pkts"]."/".bytes2mega($atraf[$aak]["out_bytes"])." \n");
		}
		print("\n");
	    }
	    print("\n"); 
	    
	} elseif((strtolower($argv[$_i]) == "help") or ( trim($argv[$_i])=="?")) {

	    show_cli_usage();

	} else {
	    echo "Parameter is unknownn: $argvv\n";
	}
}
} else {
    show_cli_usage();

}


unlink($_lockfile);

exec("cd $iptconf_dir; chown -R $tmp_owner .");




?>
