<?php
####
# Name: Fantomas Iptconf manager
# Version: 0.2.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: userlib.php
# Description: 
# Version: 0.2.1
####

#-------------------------------------------------------------------------------

function user_chkexists($addr="",$pgetall=FALSE,$pexactly=TRUE)
{
    global $iptconf_dir,$users_dir;
    if( trim($addr)=="") {
	return(FALSE);
    }
    $rez=( !$pgetall) ? "" : array();
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM groups WHERE 1")) {
	wlog("Ошибка выполнения запроса при поиске списка групп",2,TRUE,5,TRUE);
	return(FALSE);
    }
    while( $row=mysql_fetch_array($res)) {
	if(( !trim($row["id"])) || ( !trim($row["name"]))) continue;
	$line="SELECT * FROM clients WHERE";
	$line.=( $pexactly) ? " group_id=".$row["id"]:" LOCATE(\"".$row["id"]."\",ip)>0";
	if( isset($res1)) unset($res1);
	if( !$res1=mysql_query($line)) continue;
	if( mysql_num_rows($res1)==0) {
	    continue;
	} else {
	    while( $rowcl=mysql_fetch_array($res1)) {
		if( $pgetall) {
		    $rez[$row["name"]]=$rowcl["ip"];
		} else {
		    $rez=$row["name"];
		}
	    }
	    unset($rowcl);
	}
	mysql_free_result($res1);
    }
    return($rez);

}

#-------------------------------------------------------------------------------


function user_delpol($_group,$_client,$_policy)
{
    global $users_dir,$_console;
    if( !$_console) {
	if( !isadmin()) return("");
    }
    
    if( ($_policy=="empty") or ($_policy=="")) return(FALSE);
    if( trim($_client)=="") return(FALSE);
    $pollist=get_usr_param($_group,$_client,"policies",TRUE);
    if( !$_id=get_usr_param($_group,"group_id")) {
	wlog("Указанная группа не найдена!",2,TRUE,4,TRUE); _exit();
    }
    if( substr_count($pollist,$_policy)==0) {
	wlog("Policy $_policy could not present in client config...",2,TRUE,4,FALSE); 
	_exit();
    } else {
	_loadpolicy($_client,$_policy,TRUE);
	$ct=coltoks($pollist,",");
	$bufpollist="";
	if( $ct>1) {
	    foreach(explode(",",$pollist) as $pkey => $pol) {
		if( trim($pol)==trim($_policy)) continue;
		$bufpollist=( $bufpollist=="") ? $pol : $bufpollist.",".$pol;
	    }
	} elseif( $ct==1) {
	    if( trim($pollist)==trim($_policy)) $bufpollist="";
	}
	
	$link=mysql_getlink();
	$line="UPDATE clients SET policies=\"".$bufpollist."\" WHERE (ip=\"$_client\") && (group_id=$_id)";
	if( !mysql_query($line)) {
	    wlog("Ошибка выполнения запроса при удалении у клиента политики!",2,TRUE,4,TRUE);
	    _exit();
	}
    }
    
    wlog("Удаление политики $_policy с клиента $_client, группа $_group",0,FALSE,1,FALSE);
}


#-------------------------------------------------------------

function user_addpol($_group,$_client,$_policy="")
{
    
    global $_policy,$users_dir,$_console;
    
    if( !$_console) {    
	if( !isadmin()) return("");
    }
    
    if( ($_policy=="empty") or ($_policy=="")) return("");
    if( trim($_client)=="") return("");
    $pollist=get_usr_param($_group,$_client,"policies");
    $defpolicy=get_usr_param($_group,"_default_policy");
    $_id=get_usr_param($_group,"group_id");

    if( substr_count($pollist,$_policy)>0) {
	wlog("Политика $_policy уже загружена для указанного клиента ...",2,TRUE,4,FALSE); 
	_exit();
    } else {
	$link=mysql_getlink();
	$bufpollist=( trim($pollist)=="") ? $_policy:trim($pollist).",".$_policy;
	$line="UPDATE clients SET policies=\"".$bufpollist."\" WHERE (ip=\"$_client\") && (group_id=$_id)";
	if( !mysql_query($line)) {
	    wlog("Ошибка выполнения запроса при добавлении клиенту политики!",2,TRUE,4,TRUE);
	    _exit();
	}

    }
    
    _loadpolicy($_client,$_policy,FALSE,TRUE);
    
    wlog("Добавление политики $_policy клиенту $_client, группа $_group",0,FALSE,1,FALSE);

}

#------------------------------------------------------------

function user_delete($_group,$_client)
{
    global $users_dir,$_console;
    
    if( !$_console) {
	if( !isadmin()) return("");
    }
    
    $pollist=get_usr_param($_group,$_client,"policies");
    if( !$_id=get_usr_param($_group,"group_id")) {
	wlog("Группа $_group не найдена!",2,TRUE,4,TRUE); exit;
    }
    $pollist=( trim($pollist)=="") ? get_usr_param($_group,"_default_policy","",TRUE,"=") : $pollist;
    for($pi=1; $pi<=coltoks($pollist,","); $pi++) {
	$curr_policy=gettok($pollist,$pi,",");
	if( is_policy_loaded($curr_policy,$_client)) _loadpolicy($_client,$curr_policy,TRUE);
    }
    
    $link=mysql_getlink();
    if( !mysql_query("DELETE QUICK FROM cliets WHERE (group_id=$_id) && (ip=\"$_client\")")) {
	wlog("Ошибка выполнения запроса при удалении клиента $_client!",2,TRUE,4,TRUE); exit;
    }
    
    if( !$_console) {
	f_list_users($_group,TRUE);
	show_useradd_form();
    }
    wlog("Удаление клиента $_client из группы $_group",0,FALSE,1,FALSE);
    _exit();


}


#-------------------------------------------------------------

function user_add($_group,$_client,$_cname="",$_policy="")
{
    global $users_dir,$iptconf_dir,$usr_cname_spaces,$_console;
    
    if( trim($_client)=="") {
	wlog("Адрес добавляемого клиента не может быть пустым!",2,TRUE,5,FALSE); _exit();
    }
    if( !$_console) {
	if( !isadmin()) return(FALSE);
    }
    if( ($_policy=="__nopol__") or( trim($_policy)=="")) {
	wlog("Default policy for the group $_group is not specified.. ",2,TRUE,5,FALSE); _exit();
    }

    $bufch=user_chkexists($_client);

    if( !$bufch) {
    
	$defpolicy=get_usr_param($_group,"_default_policy");
	$_id=get_usr_param($_group,"group_id");
	$link=mysql_getlink();
	if( !mysql_query("INSERT INTO clients SET group_id=$_id,ip=\"$_client\",policies=\"$_policy\",cname=\"$_cname\"")) {
	    wlog("Ошибка выполнения запроса INSERT при добавлении клиента $_client!",2,TRUE,5,TRUE);
	    exit;
	}
	_loadpolicy($_client,$_policy,FALSE,TRUE);
    } else {
	print("Клиент с адресом $_client уже существует в группе ".(( !$_console) ? "<a href=\"users.php?grp=$bufch\">$bufch</a>..<br><br>" : "$bufch.")."\n");
    }
    if( !$_console) {      
        f_list_users($_group,TRUE);
	show_useradd_form();
    }
    wlog("Добавление клиента $_client в группу $_group",0,FALSE,1,FALSE);
    _exit();
}


#-------------------------------------------------------------

?>