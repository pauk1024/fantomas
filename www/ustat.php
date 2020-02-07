<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: ustat.php
# Description: 
# Version: 2.8.1
###



$_date1=""; $_date2="";
$_query_ptr=FALSE;
$_group="";

$_client=( isset($_GET["client"])) ? $_GET["client"] : "";
$_group=( isset($_GET["grp"])) ? $_GET["grp"] : $_group;    
$_date1=( isset($_GET["d1"])) ? $_GET["d1"] : $_date1;
$_date2=( isset($_GET["d2"])) ? $_GET["d2"] : $_date2;
$_fldirect=( isset($_GET["fd"])) ? $_GET["fd"] : "";
$_query_ptr=( isset($_GET["query_ptr"])) ? TRUE : $_query_ptr;
$_proto=( isset($_GET["proto"])) ? $_GET["proto"] : "";
$_ports=( isset($_GET["ports"])) ? $_GET["ports"] : "";
$_dir=( isset($_GET["dir"])) ? $_GET["dir"] : "";
$_policy=( isset($_GET["policy"])) ? $_GET["policy"] : "";
$_groupby=( isset($_GET["groupby"])) ? $_GET["groupby"] : "1";
$_procents=( isset($_GET["procents"])) ? TRUE : FALSE;
$_show=( isset($_GET["s"])) ? TRUE : FALSE;
$_shost=( isset($_GET["shost"])) ? $_GET["shost"] : "";    


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

require("auth.php");


$NoHeadEnd=TRUE;

print("<html>\n");
require("include/head.php");

if( trim($_groupby)=="3") {
    print("<base target=_self>\n");
}

?>
<script type="text/javascript">

function OnGroupByChange(val)
{
    var qptr = document.getElementById("query_ptr");
    var procents = document.getElementById("procents");
    
    qptr.checked = ( val == "2") ? false : true;
    qptr.disabled = ( val == "2") ? true : false;
    procents.checked = ( val == "2") ? false : false;
    procents.disabled = ( val == "2") ? true : false;
}

</script>
</head>
<body>
<?php



#-----------------------------------------------------------------------

function show_ustat_form() 
{
    global $_client,$_proto,$_ports,$_dir,$_policy;
    global $mysql_host,$mysql_user,$mysql_password,$_group;
    print("<br>\n<font class=top3>Детализация трафика</font><br><br>\n");
    print("<table class=table4 cellpadding=\"3px\"><tr><td>");

    print("<form name=\"params1\" action=\"ustat.php\">\n");
    if( trim($_client)=="") {
	print("<font class=top2>По всем клиентам </font><br>\n");
	print("<input type=\"HIDDEN\" name=\"client\" id=client value=\"\" />\n");
    } else {
	print("<font class=top2>По клиенту </font><span class=seldiv><SELECT name=\"client\">\n");
	$chost=gethostbyaddr($_client);
	print("<option value=\"$_client\"> $_client ".((trim($chost)!="") ? "($chost)" : "")." \n<option value=\"\">по всем клиентам\n</SELECT></span><br><br>\n");
    }
    
    print("<input type=\"HIDDEN\" name=\"grp\" id=grp value=\"$_group\" />\n\n");
    print("<input type=\"HIDDEN\" name=\"run\" id=run value=\"1\" />\n\n");
    if( (trim($_ports)!="") or (trim($_proto)!="") or (trim($_dir)!="") or (trim($_policy)!="")) {
	print("<i>Параметры:</i><br>\n");
	print("<input type=\"HIDDEN\" name=\"proto\" id=proto value=\"$_proto\" /> with proto $_proto<br>\n");
	print("<input type=\"HIDDEN\" name=\"ports\" id=ports value=\"$_ports\" /> with ports $_ports<br>\n");
	print("<input type=\"HIDDEN\" name=\"dir\" id=dir value=\"$_dir\" /> with direction $_dir<br>\n");
	print("<input type=\"HIDDEN\" name=\"policy\" id=policy value=\"$_policy\" /> with policy $_policy<br>\n");
    }

    $bufdate1=date("d-m-Y");
    $bufdate2=date("d-m-Y");
    print("<br><font class=top2>За период \n</td></tr><tr><td colspan=1><font class=text42>\n");

    print("<dl><font class=text1><b> C &nbsp&nbsp&nbsp</b></font><input id=\"d1\" value=\"$bufdate1\" name=\"d1\"  style=\"border:1px;border-color:e9b78f; border-style:solid;\" size=20 /></dl> \n");

    print("<dl><font class=text1><b> По &nbsp</b></font><input id=\"d2\" value=\"$bufdate2\" name=\"d2\" style=\"border:1px;border-color:e9b78f; border-style:solid;\" size=20 /></dl>  \n");

    print("<input type=\"checkbox\" id=query_ptr name=\"query_ptr\" value=\"on\" CHECKED><label for=\"query_ptr\">Определять имена хостов</label> <font style=\"FONT: italic 8pt;\">(DNS PTR lookup's)</font><br> \n");
    print("<font style=\"FONT: italic 8pt Arial\">Внимание! Это может занять некоторое время, дождитесь появления страницы!</font><br><br>\n");
    print("<input type=\"checkbox\" id=procents name=\"procents\" value=\"on\"><label for=\"procents\">Рассчитывать проценты позиций от общей суммы</label></font><br> \n");
    print("<table class=notable1 style=\"padding:5px;margin:5px;\" cellpadding=\"5px\"><tr><td>\n");
    print("<font class=text42> Группировать &nbsp</font> </td><td> <span class=seldiv><SELECT name=\"groupby\" id=groupby onChange=\"OnGroupByChange(this.value)\">\n");
    print("<option value=\"1\" SELECTED>по посещенным сайтам\n<option value=\"2\">по дням (по дате)\n<option value=\"3\">по клиентам (суммарно по каждому)\n</SELECT></span></td></tr>\n");
    print("<tr><td> \n <font class=text42> Выбирать &nbsp</font> </td><td> <span class=seldiv><SELECT name=\"fd\" id=fd>\n");
    print("<option value=\"1\">Входящий трафик</option>\n <option value=\"2\">Исходящий трафик</option>\n </SELECT></span>\n</td></tr>\n");
    print("</table>\n");
    print("<input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"Сформировать\">\n</form>\n");

    print("</font></td></tr></table>\n");

}

#-------------------------------------------------------------------




if( isset($_GET["run"]) ) {
    if( !$_show) {

	show_load("ustat.php?client=$_client&grp=$_group&d1=$_date1&d2=$_date2&query_ptr=$_query_ptr&proto=$_proto&ports=$_ports&dir=$_dir&policy=$_policy&groupby=$_groupby&procents=$_procents&shost=$_shost&s=1&run=1");
	
    } else {
	get_query1($_client,$_date1,$_date2,$_query_ptr,$_proto,$_ports,$_dir,$_policy,$_group,$_fldirect);
    }
    exit;

} else {
    show_ustat_form();
}


?>

</body>
</html>