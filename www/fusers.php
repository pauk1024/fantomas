<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.4.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: fusers.php
# Description: 
# Version: 0.2.4.1
###




$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : (( isset($_POST["mode"])) ? $_POST["mode"] : "");
$_user=( isset($_GET["user"])) ? $_GET["user"] : (( isset($_POST["user"])) ? $_POST["user"] : "");
$_old_user=( isset($_GET["old_user"])) ? $_GET["old_user"] : (( isset($_POST["old_user"])) ? $_POST["old_user"] : "");
$_desc=( isset($_GET["desc"])) ? $_GET["desc"] : (( isset($_POST["desc"])) ? $_POST["desc"] : "");
$_isadmin=( isset($_GET["isadmin"])) ? $_GET["isadmin"] : (( isset($_POST["isadmin"])) ? $_POST["isadmin"] : "");
$_islocked=( isset($_GET["islocked"])) ? $_GET["islocked"] : (( isset($_POST["islocked"])) ? $_POST["islocked"] : "");
$_upass1=( isset($_GET["upass1"])) ? $_GET["upass1"] : (( isset($_POST["upass1"])) ? $_POST["upass1"] : "");
$_upass2=( isset($_GET["upass2"])) ? $_GET["upass2"] : (( isset($_POST["upass2"])) ? $_POST["upass2"] : "");
$_opmode=( isset($_GET["opmode"])) ? $_GET["opmode"] : (( isset($_POST["opmode"])) ? $_POST["opmode"] : "0");

if( session_id()=="") session_start();


require("./../config.php");

if( !is_readable("iptlib.php")) { print("error opening iptlib<br>"); exit(); }
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=TRUE;
if( $_opmode=="0") {
    require("auth.php");
} else {
    require("authlib.php");
    if( $_desc==crypt($iptconf_dir,session_id()."pupgrd345")) {
	$kk=( CRYPT_MD5==1) ? "$1$".substr(session_id(),0,7)."fi$" : "_J9..".substr(session_id(),0,3)."fm";
	if( $_opmode != crypt($_user,$kk)) {
	    print("An fatal error cuting buffer at 0x34A."); 
	    if( $_logs_enable) wlog("Ошибка параметров защиты апгрейда пароля!",2,FALSE,5,FALSE);
	    exit;
	}
    } else {
	print("<script type=\"text/javascript\">\n  top.location.replace('index.php');\n</script>\n") ; exit;
    }
}

print("<html>\n");
require("include/head.php");

#----------------------------------------------------------------------

function show_useradd_form() 
{
    print("<br><br><font class=top1>Новый пользователь</font><br> \n");
    print("<table class=table5e width=\"380px\">\n");
    print("<form name=\"form4365\" action=\"fusers.php\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"adduser\">\n");
    print("<tr><td> Логин: </td><td> <input type=\"text\" name=\"user\" size=25></td>\n  ");
    print("<td> <input type=\"submit\" value=\"Добавить\"> </td></tr>\n");
    print("</form>\n</table>\n");

}

#----------------------------------------------------------------------

function show_repass_form($user) 
{
    global $_SESSION;
    $def_user=( isset($_SESSION["fantomsuser"])) ? $_SESSION["fantomsuser"] : "";
    
    print("<br><br><font class=top1>Смена пароля пользователя $user</font><br> \n");
    if( $user==$def_user) print("<br><font class=text41 style=\"font-style:italic\">*** После изменения пароля откроется страница авторизации!</font><br><br><br> \n");
    print("<form name=\"form4365\" action=\"fusers.php\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"savepass\">\n");
    print("<input type=\"hidden\" name=\"user\" value=\"$user\">\n");
    print("<input type=\"hidden\" name=\"old_user\" value=\"$user\">\n");
    print("<table class=table4 width=\"450px>\"><tr><td>&nbsp</td></tr>\n");
    print("<tr><td> Новый пароль: </td><td> <input type=\"password\" name=\"upass1\" size=20 value=\"\"><br>\n");
    print("<input type=\"password\" name=\"upass2\" size=20 value=\"\">  </td>\n  ");
    print("<td> <input type=\"submit\" value=\"Сохранить\"> </td></tr>\n");
    print("<tr><td>&nbsp</td></tr>\n</table>\n");
    print("</form>\n<br><br><br><font class=text41>\n");
    print("<a href=\"fusers.php\"> <img src=\"icons/gtk-undo.gif\" alt=\"Отменить смену пароля\"> Назад >></a><i> (не сохранять изменения)\n");
}

#----------------------------------------------------------------------

function list_webusers()
{
    global $mysql_host, $mysql_user, $mysql_password;
    
    $link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    
    if( !$link) { wlog("Error connecting to mysql!!!",2,FALSE,4,FALSE); exit; }
    mysql_select_db("fantomas");
    $result=mysql_query("SELECT * FROM users WHERE 1");
    wlog("Просмотр списка пользователей веб.",0,FALSE,1,FALSE);
    
    print("<br><br><font class=top1>Пользователи web <br><br>\n");
    print("<table class=table1 width=\"600px\">\n");
    print("<tr><th width=\"150px\" rowspan=2> Логин </th><th width=\"150px;\">Уровень доступа</th><th width=\"300px\" rowspan=2> Описание </th></tr>\n");
    print("</tr><tr>\n<th width=\"150px\">Статус логина</th></tr>\n ");
    while( $row=mysql_fetch_array($result)) {
    
	print("<tr><td style=\"padding-left:20px;\" rowspan=2> <font style=\" FONT: bold 12pt Arial;\">".trim($row["username"])."</font> </td>\n");
	print("<td align=center> <font color=blue><i>".(($row["isadmin"]==1) ? "Администратор" : "Пользователь")."</i> </td>\n");
	print("<td style=\"padding-left:20px;\" rowspan=2> ".trim($row["description"])." </td> \n");
	print("<td class=td3 width=\"110px\" align=middle rowspan=2> <a href=\"fusers.php?mode=edit&user=".$row["username"]."\"><img src=\"icons/gtk-edit.gif\" alt=\"Редактировать\"></a>  \n");
	print("&nbsp<a href=\"fusers.php?mode=del&user=".$row["username"]."\"><img src=\"icons/edittrash.gif\" alt=\"Удалить\"></a>  \n");
	print("&nbsp<a href=\"fusers.php?mode=repass&user=".$row["username"]."\"><img src=\"icons/seahorse.gif\" alt=\"Сменить пароль\"></a></td></tr>  \n");
	print("<tr><td align=center>".(($row["islocked"]==1) ? "<font color=red>Заблокирован" : "<font color=green>Активный")."  </font></td></tr>\n");
    
    }
    print("</table>\n");
    show_useradd_form();


}

#----------------------------------------------------------------------

function edit_user($user,$pnew=FALSE)
{
    global $mysql_host, $mysql_user, $mysql_password,$_SESSION;
    
    $def_user=( isset($_SESSION["fantomsuser"])) ? $_SESSION["fantomsuser"] : "";

    if( !$pnew) {
    
	$link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
	if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
	if( !$link) { wlog("Error connecting to mysql!!!",2,TRUE,4,TRUE); exit; }
	mysql_select_db("fantomas");
	$result=mysql_query("SELECT * FROM users WHERE username=\"$user\"");
	$row=mysql_fetch_array($result);
	
	$frm_user=$row["username"];
	$frm_isadmin=$row["isadmin"];
	$frm_islocked=$row["islocked"];
	$frm_desc=$row["description"];
	$frm_mode="save";
    } else {
	$frm_user=$user;
	$frm_isadmin=0;
	$frm_islocked=0;
	$frm_desc="";
	$frm_mode="newsave";
    }
    print("<br><br><font class=top1>Логин $user <br><br>\n");
    if( $user==$def_user) print("<font class=text41 style=\"font-style:italic\">*** При смене имени логина откроется страница авторизации!</font><br><br> \n");
    print("<form name=\"form436\" action=\"fusers.php\">\n");
    print("<input type=\"hidden\" name=\"mode\" value=\"$frm_mode\">");
    print("<input type=\"hidden\" name=\"old_user\" value=\"$frm_user\">");
    print("<table class=table4 width=\"500px\"><tr><td colspan=2>&nbsp</td></tr>\n");

    print("<tr><td> Имя логина</td><td> <input type=\"text\" size=20 name=\"user\" value=\"".$frm_user."\"> </td></tr> \n");
    print("<tr><td colspan=2> <input type=\"checkbox\" id=\"isadmin\" name=\"isadmin\" ".(($frm_isadmin==1) ? "CHECKED" : "")."><label for=\"isadmin\"> Административный доступ</label></td></tr> \n");
    print("<tr><td colspan=2> <input type=\"checkbox\" id=\"islocked\" name=\"islocked\" ".(($frm_islocked==1) ? "CHECKED" : "")."><label for=\"islocked\"> Заблокировать</label></td></tr> \n");
    print("<tr><td> Описание</td><td> <textarea name=\"desc\" rows=3 cols=40>".$frm_desc."</textarea> </td></tr> \n");
    if( $pnew) print("<tr><td> Пароль: </td><td> <input type=\"password\" size=20 name=\"upass1\" value=\"\"><br>\n<input type=\"password\" size=20 name=\"upass2\" value=\"\"> </td></tr> \n");
    print("<tr><td colspan=2> <input type=\"submit\" value=\"Сохранить\"></td></tr> \n");
    print("<tr><td colspan=2>&nbsp</td></tr>\n</table>\n<br><br><br><font class=text41>\n");
    print("<a href=\"fusers.php\"> <img src=\"icons/gtk-undo.gif\" alt=\"Отменить редактирование\"> Назад к списку >></a><i> (не сохранять изменения)\n");



}

#----------------------------------------------------------------------

function save_pass($opmode="0")
{
    global $_user, $_desc, $_isadmin, $_islocked, $_upass1, $_upass2, $_old_user;
    global $mysql_host, $mysql_user, $mysql_password, $_passw_crypt_key;
    global $_passw_md5_enable,$_SESSION;


    if( trim($_user)=="") { print("Cannot operate with empty username, stop..."); exit; }
    
    if( trim($_upass1)!=trim($_upass2)) { print("Input passwords is different..."); exit; }
    if( ( trim($_upass1)=="") and ( trim($_upass2)=="")) { print("Password cannot be blank."); exit; }
    
    $def_user=( isset($_SESSION["fantomsuser"])) ? $_SESSION["fantomsuser"] : "";
    if( $opmode=="0") {
	if( trim($def_user)=="") { print("Cannot operate with empty logged on user!!!"); exit; }
    } else {
	$kk=( CRYPT_MD5==1) ? "$1$".substr(session_id(),0,7)."fi$" : "_J9..".substr(session_id(),0,3)."fm";
	if( $opmode != crypt($_user,$kk)) {
	    print("PHP fault error: sintax error at declaring function type at line 178."); exit;
	}
    }
    
    $vmode=( $_passw_md5_enable) ? 3 : 2;
    $link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    if( !$link) { wlog("Error connecting to mysql: ".mysql_error(),2,TRUE,4,TRUE); exit(); }
    mysql_select_db("fantomas");

    $line1="UPDATE users SET userpass=\"".encrypt0($_upass1)."\",v=$vmode WHERE username=\"$_user\"";
    $rez=mysql_query($line1);
    if( !$rez) { wlog("Error quering MySQL in save_pass(), error: ".mysql_error(),2,TRUE,4,TRUE); exit; }
    
    mysql_close($link);

    wlog("Сохранение пароля для логина $_user",0,FALSE,1,FALSE);    
    if( $opmode=="0") {
	if( $_user==$def_user) { 
	    logoff();
	} else {
	    list_webusers();
	}
    } else {
	print("<script type=\"text/javascript\">\n top.location.replace('index.php');\n</script>\n");
    }

}

#----------------------------------------------------------------------

function delete_user()
{
    global $_user,$_SESSION;
    global $mysql_host, $mysql_user, $mysql_password, $_passw_crypt_key;
    
    if( trim($_user)=="") { wlog("Cannot operate with empty username, stop...",2,TRUE,5,TRUE); exit; }
    
    
    $def_user=( isset($_SESSION["fantomsuser"])) ? $_SESSION["fantomsuser"] : "";
    if( trim($def_user)=="") { wlog("Cannot operate with empty logged on user!!!",2,TRUE,5,TRUE); exit; }
    
    $link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    if( !$link) { wlog("Error connecting to mysql!!!",2,TRUE,4,TRUE); exit(); }
    mysql_select_db("fantomas");
    
    mysql_query("DELETE FROM users WHERE username=\"$_user\"");
    wlog("Удаление логина $_user",0,FALSE,1,FALSE);
    
    mysql_close($link);
    
    list_webusers();

}




#-----------------------------------------------------------------------




function save_user($pnew=FALSE)
{
    global $_user, $_desc, $_isadmin, $_islocked, $_upass1, $_upass2, $_old_user;
    global $mysql_host, $mysql_user, $mysql_password, $_passw_crypt_key,$_SESSION;
    
    if( trim($_user)=="") { wlog("Cannot operate with empty username, stop...",2,TRUE,5,TRUE); exit; }
    
    if( $pnew) {
	if( trim($_upass1)!=trim($_upass2)) { wlog("Введенные пароли не совпадают!...",2,TRUE,4,TRUE); exit; }
	if( ( trim($_upass1)=="") and ( trim($_upass2)=="")) { wlog("Пароли не могут быть пустыми!",2,TRUE,4,TRUE); exit; }
	
    }
    
    $def_user=( isset($_SESSION["fantomsuser"])) ? $_SESSION["fantomsuser"] : "";
    if( trim($def_user)=="") { wlog("Cannot operate with empty logged on user!!!",2,TRUE,4,TRUE); exit; }
    
    $link=mysql_connect($mysql_host, $mysql_user, $mysql_password);
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    if( !$link) { wlog("Error connecting to mysql!!!",2,TRUE,4,TRUE); exit(); }
    mysql_select_db("fantomas");
    
    $line1=(( !$pnew) ? "UPDATE" : "INSERT INTO")." users SET username=\"$_user\",isadmin=".((trim($_isadmin)!="") ? "1" : "0").",islocked=".((trim($_islocked)!="") ? "1" : "0").",description=\"".$_desc."\" ".(( !$pnew) ? "WHERE username=\"$_old_user\"" : "");

    $result=mysql_query($line1);
    
    if( $pnew) mysql_query("UPDATE users SET userpass=\"".encrypt0($_upass1)."\",v=".((CRYPT_MD5==1) ? "3" : "2")." WHERE username=\"$_user\"");
    
    mysql_close($link);
    wlog("Сохранение данных логина $_user",0,FALSE,1,FALSE);
    
    if( ($_old_user==$def_user) and ($_old_user!=$_user)) { 
	logoff(); 
    } else {
	list_webusers();
    }

}



#-----------------------------------------------------------------------

print("<body>\n<br><br><br>");

if( $_user=="") { list_webusers(); } else {

    if( $_mode=="edit") {
	
	edit_user($_user);
	
    } elseif( $_mode=="save") {
	save_user();
    } elseif( $_mode=="newsave") {
	save_user(TRUE);
    } elseif( $_mode=="adduser") {
	edit_user($_user,TRUE);
    } elseif( $_mode=="savepass") {
	save_pass($_opmode);
    } elseif( $_mode=="repass") {
	show_repass_form($_user);
    } elseif( $_mode=="del") {
	delete_user();
    }

}


?>
</body>
</html>