<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.7
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: authlib.php
# Description: authlib
# Version: 0.2.4.7
###

#--------------------------------------------------------------------
function encrypt0($pstring)
{
    global $_passw_md5_enable,$_passw_crypt_key;
    if( trim($pstring)=="") return("");
    if( session_id()=="") session_start();
    $sesskey=substr(session_id(),0,24);
    if( trim($sesskey)=="") { 
	wlog("Ошибка хеширование строки...",2,TRUE,4,TRUE);
	exit;
    }

    if( $_passw_md5_enable) {
	if( CRYPT_MD5!=1) {
	    wlog("Внимание! Шифрование MD5 недоступно...",2,TRUE,4,TRUE); 
	    exit;
	}
	$rez=md5($pstring);
    } else { 
	$bufsalt="_J9..".substr($_passw_crypt_key,0,4);
	$rez=crypt($pstring,$bufsalt);
    }
    return($rez);

}

#--------------------------------------------------------------------
function encrypt1($pstring)
{
    global $_passw_crypt_key,$_passw_md5_enable;
    if( trim($_passw_crypt_key)=="") return("");
    if( trim($pstring)=="") return("");
    $bufsalt="";
    if( $_passw_md5_enable) {
	if( CRYPT_MD5!=1) {
	    wlog("Внимание! Шифрование MD5 недоступно...",2,TRUE,4,TRUE); exit;
	}
	if( strlen($_passw_crypt_key)>9) {
	    $bufsalt="$1$".substr($_passw_crypt_key,0,9)."$";
	} elseif( strlen($_passw_crypt_key)<9) {
	    $bufsalt="$1$".$_passw_crypt_key;
	    for($ii=strlen($_passw_crypt_key);$ii<9;$ii++) $bufsalt=$bufsalt."F";
	} elseif( strlen($_passw_crypt_key)==9) {
	    $bufsalt="$1$".$_passw_crypt_key;
	}
    } else { 
	$bufsalt="_J9..".substr($_passw_crypt_key,0,4);
    }
    $rez=crypt($pstring,$bufsalt);
    return($rez);

}

#--------------------------------------------------------------------

function auth_get_user_clevel($puser="")
{
    global $mysql_host, $mysql_user, $mysql_password, $mysql_fantomas_db;

    $link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
    if( !$link) { wlog("Ошибка соединения с mysql!!!",2,TRUE,5,TRUE); exit(); }
    mysql_select_db($mysql_fantomas_db);
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    $line="SELECT username,v FROM users WHERE MD5(username)=\"$puser\"";
    $result=mysql_query("$line");
    if( mysql_num_rows($result)>0) {
	$row=mysql_fetch_array($result);
	$rez=$row["v"];
    } else {
	$rez=99;
    }
    mysql_close($link);
    return($rez);

}
#--------------------------------------------------------------------

function auth_user_passw_upgrade($puser="",$psessid="")
{
    global $mysql_host, $mysql_user, $mysql_password, $mysql_fantomas_db, $iptconf_dir;
    
    if( session_id()=="") session_start();
    if( trim($puser)=="") { wlog("Username не может быть пустым...",2,TRUE,4,TRUE); exit; }
    if( $psessid != encrypt0(session_id())) {
	wlog("Wrong session id...",2,TRUE,5,TRUE); exit; 
    } else {
	print("<html>\n");
	require("include/head1.php");
	print("<body>\n");
	print(" \n");
	print("<br><br><br>\n<font class=top1>Необходим апгрейд пароля.<br><br></font><font class=text32>Пожалуйста, введите свой пароль два раза:</font><br>\n");
	print("<form name=\"form345\" action=\"fusers.php\"> \n");
	print("<input type=\"hidden\" name=\"user\" value=\"$puser\"> \n");
	print("<input type=\"hidden\" name=\"mode\" value=\"savepass\"> \n");
	print("<input type=\"hidden\" name=\"desc\" value=\"".crypt($iptconf_dir,session_id()."pupgrd345")."\"> \n");
	$kk=( CRYPT_MD5==1) ? "$1$".substr(session_id(),0,7)."fi$" : "_J9..".substr(session_id(),0,3)."fm";
	print("<input type=\"hidden\" name=\"opmode\" value=\"".crypt($puser,$kk)."\"> \n");
	print("<table class=table4 cellpadding=\"6px\"> \n");
	print("<tr><td> Пароль: </td><td> <input type=\"password\" name=\"upass1\" size=20 value=\"\"> </td></tr> \n");
	print("<tr><td> Еще раз: </td><td> <input type=\"password\" name=\"upass2\" size=20 value=\"\"> </td></tr> \n");
	print("<tr><td colspan=2> <input type=\"submit\" name=\"sbmt345\" value=\"Установить\"></td></tr> \n");
	print("</table></form>\n");
	print("</font>\n</html>\n");
	exit;
    
    }

}


#--------------------------------------------------------------------

function ShowPassForm($passkey="",$passname="",$psessid="") 
{
    global $_crypt_passform;
    
    if( session_id()=="") session_start();
    $sesskey=substr(session_id(),0,24);
    print("<html>\n");
    print("<body>\n<center>\n");
    print("<script type=\"text/javascript\">\n");
    if( $_crypt_passform) {
	require("js/des.js");
	print("function hhb() \n{\n");
	print("	var pword = document.getElementById('userpass').value;\n");
	print("	var hword = des('$sesskey', pword, 1, 0, null);\n");
	print("	var hword = stringToHex(hword);\n");
	print("	document.getElementById('userpass').value = hword;\n");
	print("	var pword = document.getElementById('username').value;\n");
	print("	var hword = des('$sesskey', pword, 1, 0, null);\n");
	print("	var hword = stringToHex(hword);\n");
	print("	document.getElementById('username').value = hword;\n");
	print("	return true;\n");
	print("}\n");
    }
#    print("<script type=\"text/javascript\">\n");
    print("function mOver(itemId)\n");
    print("{\n");
    print("	document.getElementById(itemId).className = \"divbtnon\";\n");
    print("}\n");
    print("function mOut(itemId)\n");
    print("{\n");
    print("	document.getElementById(itemId).className = \"divbtnout\";\n");
    print("}\n");
    print("</script>\n");
        
    print("\n<br><br><br>\n");
    print("<div class=logo>");
    print("<div>Fantomas Iptconf</div>\n");
    print("<img src=\"icons/tux50_916.jpg\" />\n");
    print("</div>\n");
    print("<br>\n");

    print("<table class=table4 width=\"350px\" cellpadding=\"8px\" style=\"margin-top:5px;\"\"> \n");
    if( $_crypt_passform) {
	print("<form name=\"form2\" id=\"form2\" action=\"index.php\" method=\"POST\" onSubmit=\"return hhb();\" style=\"padding:0px; margin:0px;\">  \n");
    } else {
	print("<form name=\"form2\" id=\"form2\" action=\"index.php\" method=\"POST\" style=\"padding:0px; margin:0px;\">  \n");
    }
    if($passkey!="") print("<input type=\"HIDDEN\" name=\"$passname\" value=\"$passkey\">\n");
    print("<input type=\"HIDDEN\" name=\"passed\" value=\"1\">\n");
    print("<input type=\"HIDDEN\" name=\"sessid\" value=\"$psessid\">\n");
    print("<tr><td>Логин: </td><td><input type=\"TEXT\" name=\"username\" id=\"username\" border=\"1px\" tabindex=\"1\" size=29 /></td></tr>  \n");
    print("<tr><td>Пароль: </td><td><input type=\"PASSWORD\" id=\"userpass\" name=\"userpass\" border=\"1px\" tabindex=\"2\" size=30 /></td></tr>  \n");
    print("<tr><td colspan=2 align=\"right\">\n");
#    print("<input type=\"SUBMIT\" id=\"sbmt\" value=\" Ok \" style=\"visibility:hidden;position:absolute;\">\n");
    print("<input type=\"SUBMIT\" id=\"sbmt\" name=\"sbmt\" value=\" Ok \">\n");
#    print("<div id=\"btnOk\" class=divbtnout onMouseOver=\"javascript:mOver('btnOk');\" onMouseOut=\"javascript:mOut('btnOk');\" style=\"width:80px;\"> <a href=\"#\" title=\"Вход в программу\" OnClick=\"javascript: document.getElementById('form2').submit();\"> Войти </a> </div>\n");

    print("</td></tr>\n</form></table>\n");
    
}

#--------------------------------------------------------------------
function CheckPassword($usr,$pass,$fladmonly=FALSE,$psessid,$noindex=FALSE)
{
    global $iptconf_dir, $_passw_crypt_key;
    global $mysql_host, $mysql_user, $mysql_password, $mysql_fantomas_db;
    global $_logs_enable,$_logs_logged_checkpass_nolog,$_SESSION;
    $rez=FALSE;

#wlog("debug: 1:: _SESSION[user] .".$_SESSION["user"].". _SESSION[username] .".$_SESSION["user"].".",0,FALSE,1,FALSE);

    if( trim($psessid)=="") {
	$rez=FALSE;
    } elseif( $psessid!=encrypt0(session_id())) {
	$rez=FALSE;
    } else {
	$link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
	if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
	if( !$link) { wlog("Ошибка соединения с mysql!!!",2,TRUE,5,TRUE); exit(); }
	mysql_select_db($mysql_fantomas_db);
	$line="SELECT MD5(username) as username, userpass,isadmin,islocked,v FROM users WHERE MD5(username)=\"$usr\"";
	$result=mysql_query($line);
	$row=mysql_fetch_array($result);
	$cpass=encrypt0($pass);
	$logline="Проверка пароля:";
	if($row["userpass"]==$pass) $rez=TRUE;
	$logline=$logline.(( $rez) ? " успешно" : " пароль неверен");
	if( $fladmonly) $rez=( $row["isadmin"]==1) ? TRUE : FALSE;
	$logline=$logline.(( $rez) ? "" : ", нет администраторских прав");
	if( $rez) $rez=( $row["islocked"]==0) ? TRUE : FALSE;
	$logline=$logline.(( $rez) ? "" : ", логин отключен.");
	mysql_close($link);
    }
    $logline=$logline.(( $rez) ? ". Доступ разрешен." : ". В доступе отказано.");
    if( $_logs_enable) {
	if(( !$_logs_logged_checkpass_nolog) and ( !$noindex)) 	wlog($logline,0,FALSE,1,FALSE);
    }
#wlog("debug: 2:: _SESSION[user] .".$_SESSION["user"].". _SESSION[username] .".$_SESSION["user"].".",0,FALSE,1,FALSE);

    return($rez);
}

#---------------------------------------------------------------------


function isadmin() 
{
    global $mysql_host, $mysql_user, $mysql_password, $_passw_crypt_key;
    
    $user=( isset($_SESSION["fantomsuser"])) ? $_SESSION["fantomsuser"] : "";
    $pass=( isset($_SESSION["fantomsuserpass"])) ? $_SESSION["fantomsuserpass"] : "";
    $psessid=( isset($_SESSION["fantomssessid"])) ? $_SESSION["fantomssessid"] : "";
    $rez=FALSE;
    
    if( (trim($user)=="") or (trim($pass)=="") or (trim($psessid)=="")) return(FALSE);
    
    $link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
    if( !$link) { wlog("Error connecting to mysql!!!",2,TRUE,5,TRUE); exit(); }
    mysql_select_db("fantomas");
    $result=mysql_query("SELECT MD5(username) as username,userpass,v,isadmin,islocked FROM users WHERE MD5(username)=\"$user\"");
    $row=mysql_fetch_array($result);
    if($row["userpass"]!=$pass) return(FALSE);
    $rez=( $row["isadmin"]==1) ? TRUE : FALSE;
    mysql_close($link);
    return($rez);


}

#---------------------------------------------------------------------

function logoff($q=0)
{
    global $_SESSION,$pollist_poltemp_exitwarn;
    if( !$pollist_poltemp_exitwarn) $q=1;
    if( $q==0) {
	if( isset($_SESSION["temp_pollist"])) {
	    if( count($_SESSION["temp_pollist"])>0) {
#		if( $pollist_poltemp_exitwarn) {
		    print("<br><br><font class=text42s>В буферной памяти сессии обнаружено <b>".count($_SESSION["temp_pollist"])."</b> временных записей:<br><br>\n");
		    print("<table class=table5 style=\"border-collapse:collapse;margin-left:30px;\">\n");
		    foreach($_SESSION["temp_pollist"] as $ssvalue) print("<tr><td class=td41ye style=\"padding-left:15px;\">policy_tmp_$ssvalue </td></tr>\n");
		    print("</table>\n");
		    print("<br>\n При выходе из программы эти записи будут уничтожены.<br><br>\n");
		    print("<a href=\"index.php?logout=logout&q=1\" title=\"Выйти с удалением временных данных\"><img src=\"icons/system-log-out.gif\" title=\"Выйти с удалением временных данных\"> <b>Выйти с удалением этих данных?</b></a><br>\n");
		    exit;
#		}
	    }
	}
    } 
    unset($_SESSION["fantomsuser"]);
    unset($_SESSION["fantomsuserpass"]);
    unset($_SESSION["fantomssessid"]);
    unset($_SESSION["user"]);
    session_destroy();
    print("<html>");
    print("<body>\n");
    print("<script type=\"text/javascript\">\n");
    print(" \n");
    print("   if (top.frames.length!=0)\n");
    print("   { if(window.location.href.replace)\n");
    print("        top.location.replace(self.location.href);\n");
    print("     else \n");
    print("        top.location.href=self.document.href;\n");
    print("} \n");
    print("top.location.replace('index.php');");
    print(" </script>\n");
    print("</body></html>");
    wlog("Выход из программы",0,FALSE,1,FALSE);


}

#----------------------------------------------------------------------
?>