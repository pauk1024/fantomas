<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: index.php
# Description: 
# Version: 0.2.8
###


session_start();
$sesskey=substr(session_id(),0,24);

require("./../config.php");

if( file_exists($iptconf_dir."/1stconf")) header("Location: conf.php");

require("iptlib.php");
require("authlib.php");
require("include/des.php");
require("include/head1.php");

$context="index.php";

$passed=( isset($_POST["passed"])) ? $_POST["passed"] : "";
$sessid=( isset($_POST["sessid"])) ? $_POST["sessid"] : "";

$ltimeout=( isset($lndex_login_timeout)) ? $lndex_login_timeout : 0;

$username=( isset($_POST["username"])) ? $_POST["username"] : "";

if( trim($username)!="") {
    if( $_crypt_passform) {
	$bufusername=trim(des($sesskey,hexToString($username),0,0,null));
	$username=md5(trim(des($sesskey,hexToString($username),0,0,null)));
#	$_SESSION["user"]=trim(des($sesskey,hexToString($_POST["username"]),0,0,null));
	$_SESSION["user"]=$bufusername;
    } else {
	$bufusername=$username;
	$_SESSION["user"]=$bufusername;
	$username=md5(trim($username));
    }
    $user_clevel=auth_get_user_clevel($username);
    if( $user_clevel==99) {
        print("Неверное имя пользователя... <br><br><a href=\"index.php\">Попробовать  снова</a>\n");
	exit;
    }
}
$userpass=( isset($_POST["userpass"])) ? $_POST["userpass"] : "";


if( $_crypt_passform) {
    $userpass=trim(des($sesskey,hexToString($userpass),0,0,null));
}
if(isset($user_clevel)) {
    if( $user_clevel>1) {
	$userpass=encrypt0($userpass);
    } else {
	$userpass=crypt($userpass,$_passw_crypt_key);
    }
}
    

$logout=( isset( $_GET["logout"])) ? TRUE : FALSE;
$_q=( isset( $_GET["q"])) ? 1 : 0;

$sess_user=( isset($_SESSION["fantomsuser"])) ? $_SESSION["fantomsuser"] : "";
$sess_password=( isset($_SESSION["fantomsuserpass"])) ? $_SESSION["fantomsuserpass"] : "";
$sess_sessid=( isset($_SESSION["fantomssessid"])) ? $_SESSION["fantomssessid"] : "";

if( isset($user_clevel)) {
    if( $user_clevel<2) {
	if( !CheckPassword($username,$userpass,FALSE,encrypt0(session_id())) ) {
	    $userpass=encrypt1(trim(des($sesskey,hexToString($_POST["userpass"]),0,0,null)));
	    if( !CheckPassword($username,$userpass,FALSE,encrypt0(session_id())) ) {
		session_destroy();
		print("Неверный логин... <br><br><a href=\"index.php\">Попробовать  снова</a>\n");
		exit;
	    } else {
		auth_user_passw_upgrade($username,encrypt0(session_id()));
	    }
	} else {
	    auth_user_passw_upgrade($username,encrypt0(session_id()));
	}
    }
}

$flLogin=( (trim($sess_user)=="") or (trim($sess_password)=="") or (trim($sess_sessid)=="")) ? FALSE : TRUE;


if( $flLogin) {


    if( !CheckPassword($sess_user,$sess_password,FALSE,$sess_sessid)) {
	session_destroy();
	if( isset($_SESSION["fantomsuser"])) unset($_SESSION["fantomsuser"]);
	if( isset($_SESSION["fantomsuserpass"])) unset($_SESSION["fantomsuserpass"]);
	if( isset($_SESSION["fantomssessid"])) unset($_SESSION["fantomssessid"]);
	$flLogin=FALSE;
    } else {
	if( $logout) { 
	    logoff($_q);
	} else {

	    require("include/index_base.php");
	    exit;
	}
    }
} else {

    $_remoteaddr=( isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "");
    $_passkey=( isset($_GET[$index_freename])) ? $_GET[$index_freename] : (( isset($_POST[$index_freename])) ? $_POST[$index_freename] : "");

    if( $_passkey!=$index_freepass) {

	if( $filter_web_access==1) {

	    if( $_remoteaddr!="") {
		$flok=FALSE;
		$col_allowed=coltoks($allowed_ip,", \t");
		for($i=1; $i<=$col_allowed; $i++) {
		    $tmpaddr=gettok($allowed_ip,$i,", \t");
		    $_tmpremote=$_remoteaddr;
		    if( substr_count($tmpaddr,".")!=3) continue;
		    if( gettok($tmpaddr,4,".")=="0") {
			$_tmpremote=str_replace(gettok($_tmpremote,4,"."),"",$_tmpremote);
			$tmpaddr=str_replace(gettok($tmpaddr,4,"."),"",$tmpaddr);
		    }
		    if( $_tmpremote==$tmpaddr) { $flok=TRUE; break; }
		}
	    }

	} else { $flok=TRUE; }

    } else { 
	$flok=TRUE; 
	wlog("Служебный режим: вход без проверки по списку исходных IP-адресов.",0,FALSE,1,FALSE);
    }

    if( !$flok) {
	$site_to_redirect=( trim($site_to_redirect)=="") ? "http://bash.org.ru" : $site_to_redirect;
	print("<html>\n<frameset border=\"0\" frameborder=\"0\" cols=\"*\" rows=\"*\"> \n");
	print("<frame src=\"$site_to_redirect\" scrolling=\"no\" noresize>\n");
	print("</frameset></html>");
	wlog("Попытка входа с неразрешенного IP-адреса, включен редирект.",0,FALSE,1,FALSE);
	exit;
    } else {
	if( isset($_SESSION["fantomsuser"])) unset($_SESSION["fantomsuser"]);
	if( isset($_SESSION["fantomsuserpass"])) unset($_SESSION["fantomsuserpass"]);
	if( isset($_SESSION["fantomssessid"])) unset($_SESSION["fantomssessid"]);
	
	$sessid=encrypt0(session_id());


	if( trim($passed)=="") {
	    
	    ShowPassForm($_passkey,$index_freename,$sessid);
	} else {
	    if( !CheckPassword($username,$userpass,FALSE,$sessid)) {
		$userpass=encrypt1(trim(des($sesskey,hexToString($_POST["userpass"]),0,0,null)));
		if( !CheckPassword($username,$userpass,FALSE,$sessid)) {
		    unset($_SESSION["fantomsuser"]);
		    unset($_SESSION["fantomsuserpass"]);
		    unset($_SESSION["fantomssessid"]);
		    session_regenerate_id(TRUE);
#			$sessid=session_id();
		    $sessid=encrypt0(session_id());
		    print("<font class=text1>Неверно указаны логин/пароль.</font>\n");
		    ShowPassForm($_passkey,$index_freename,$sessid);
		} else {
		    auth_user_passw_upgrade($username,encrypt0(session_id()));
		}
	    } else {
		$ctimeout=($ltimeout==0) ? 0 : time()+$ltimeout*60;
#		$ctimeout=time()+$ltimeout*60;
		unset($_SESSION["fantomsuser"]);
		unset($_SESSION["fantomsuserpass"]);
		unset($_SESSION["fantomssessid"]);
		$_SESSION["fantomsuser"]=$username;
		$_SESSION["fantomsuserpass"]=$userpass;
		$_SESSION["fantomssessid"]=$sessid;
		getversion();
		wlog("Успешный вход в программу. _SESSION[username] .".$_SESSION["user"].".",0,FALSE,1,FALSE);

		print("<script type=\"text/javascript\">\n top.location.replace('index.php');\n</script>\n");
	    }
	}
    }
}


?>