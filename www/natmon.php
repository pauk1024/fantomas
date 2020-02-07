<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.9
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: natmon.php
# Description: 
# Version: 2.8.1
###


$_target=( isset($_GET["t"])) ? $_GET["t"] : "";
$_timeout=( isset($_GET["w"])) ? $_GET["w"] : "";
$_mode=( isset($_GET["m"])) ? $_GET["m"] : "";

$_pn=( isset($_GET["pn"])) ? TRUE:FALSE;
$_pnn=( isset($_GET["pnn"])) ? TRUE:FALSE;
$_pp=( isset($_GET["pp"])) ? $_GET["pp"]:"";
$_psh=( isset($_GET["psh"])) ? $_GET["psh"]:"";
$_pdh=( isset($_GET["pdh"])) ? $_GET["pdh"]:"";
$_pr=( isset($_GET["pr"])) ? $_GET["pr"]:"";
$_refresh=( isset($_GET["refresh"])) ? TRUE:FALSE;


$_r=( isset($_GET["r"])) ? TRUE : FALSE;
#$_z=( isset($_GET["z"])) ? (( $_GET["z"]=="1") ? TRUE:FALSE):FALSE;


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=FALSE;
require("auth.php");

if( trim($_timeout)=="") {
    $_timeout=( $monitor_delay>0) ? "$monitor_delay" : "10";
}

print("<html>\n");
require("include/head.php");
print("<body>\n");

if( !file_exists($_netstat_nat)) {
    list($rv,$rout)=_exec2("which netstat-nat");
    if( $rv==0) foreach($rout as $rkey => $rval) if( file_exists($rval)) {
	$_netstat_nat=$rval;
	break;
    }
}


#---------------------------------------------------------------------------------------------------------
function show_head()
{
    global $_mode,$_target,$_pn,$_pnn,$_pp,$_psh,$_pdh,$_timeout,$_pr;
    global $_netstat_nat,$_refresh;
/*
    $prefs_url="natmon.php?height=500&width=500&m=prefs&t=$_target";
    $prefs_url.=($_pn) ? "&pn=1":"";
    $prefs_url.=($_pnn) ? "&pnn=1":"";
    $prefs_url.="&pp=$_pp&psh=$_psh&pdh=$_pdh&w=$_timeout&pr=$_pr&modal=false";
*/
    $prefs_url="natmon.php?&m=prefs".(( !trim($_target)) ? "":"&t=$_target");
    $prefs_url.=($_pn) ? "&pn=1":"";
    $prefs_url.=($_pnn) ? "&pnn=1":"";
    $prefs_url.=( !trim($_pp)) ? "":"&pp=".trim($_pp);
    $prefs_url.=( !trim($_psh)) ? "":"&psh=".trim($_psh);
    $prefs_url.=( !trim($_pdh)) ? "":"&pdh=".trim($_pdh);
    $prefs_url.=( !trim($_pr)) ? "":"&pr=".trim($_pr);
    $prefs_url.=( !trim($_timeout)) ? "":"&w=".trim($_timeout);
    $prefs_url.=($_refresh) ? "&refresh=1":"";
    $prefs_url.="&inlineId=prefsfrm;height=500;width=500";


    print("<br>   \n");
    print("<table class=notable width=\"90%\"><tr>\n");
    print("<td align=left> <font class=top2>Монитор соединений NAT</font></td>\n");
    print("<td align=right>\n");


    print("<table class=notable><tr><td>\n");
    print("<a href=\"".$prefs_url."\" class=\"thickbox\"title=\"Открыть настройки\" ><img src=\"icons/seahorse-preferences.gif\" title=\"Открыть настройки\"></a> \n");
    print("</td><td>\n");
    print("<a href=\"".$prefs_url."\" class=\"thickbox\"title=\"Открыть настройки\" >Настройки</a> \n");
    print("</td></tr></table>\n");

    

    print("</td></tr>\n</table>\n");
    print("<hr size=1 align=left width=\"90%\">\n\n");
    
    if( !file_exists($_netstat_nat)) {
	print("<br>\n <font class=error>AHTUNG!</font> <font class=text33> Программа netstat-nat не найдена, список соединений не может быть получен.</font>\n");
	exit;
    }
}
#---------------------------------------------------------------------------------------------------------
function thick($line)
{
    if( !trim($line)) return(FALSE);
    print(html_entity_decode(htmlentities($line,ENT_NOQUOTES,"koi8r")));
}
#---------------------------------------------------------------------------------------------------------
function debug()
{
    global $_mode,$_target,$_pn,$_pnn,$_pp,$_psh,$_pdh,$_timeout,$_pr;
    print("mode .$_mode. <br>\n");
    print("target .$_target. <br>\n");
    print("pn .".var_dump($_pn).". <br>\n");
    print("pnn .".var_dump($_pnn).". <br>\n");
    print("pp .$_pp. <br>\n");
    print("psh .$_psh. <br>\n");
    print("pdh .$_pdh. <br>\n");
    print("pr .$_pr. <br>\n");
    print("timeout .$_timeout. <br>\n");

}
#---------------------------------------------------------------------------------------------------------
function show_prefs()
{
    global $_mode,$_target,$_pn,$_pnn,$_pp,$_psh,$_pdh,$_timeout,$_pr,$_refresh,$iframe_url;

    $line="";
    $line.="Refresh:".(( $_refresh) ? "вкл":"выкл");
    if( trim($_target)!="") $line.=(( !trim($line)) ? "":",")." target:".(trim($_target));
    if( $_pn) $line.=(( !trim($line)) ? "":",")." don't resolve hosts/ports (-n)";
    if( $_pnn) $line.=(( !trim($line)) ? "":",")." NAT-box connection info (-N)";
    if( trim($_pp)!="") $line.=(( !trim($line)) ? "":",")." proto:".(trim($_pp));
    if( trim($_psh)!="") $line.=(( !trim($line)) ? "":",")." src-host:".(trim($_psh));
    if( trim($_pdh)!="") $line.=(( !trim($line)) ? "":",")." dst-host:".(trim($_pdh));
    if( trim($_pr)!="") $line.=(( !trim($line)) ? "":",")." sort:".(trim($_pr));
    if(( $_refresh) && (trim($_timeout)!="")) $line.=(( !trim($line)) ? "":",")." timeout:".(trim($_timeout));

    print("<table class=notable width=\"90%\"><tr>\n");
    print("<td align=left style=\"FONT: normal 8pt Tahoma;color:696969;padding-left:10px;\"> $line</td>\n");
    if( !$_refresh) {
	print("<td align=right>\n");
	print("<input type=\"BUTTON\" name=\"b1\" value=\"Обновить\" style=\"FONT: normal 8pt Tahoma;\" onClick=\"javascript: document.getElementById('natstat').contentWindow.location.replace('".$iframe_url."');\">\n");
	print("</td>\n");

    }
    print("</tr>\n</table>\n");
}
#---------------------------------------------------------------------------------------------------------
function getnatstat()
{
    global $_mode,$_target,$_pn,$_pnn,$_pp,$_psh,$_pdh,$_timeout,$_pr;
    global $_netstat_nat,$_refresh;
    
    $_time1=time();
    
    $line=$_netstat_nat;
    if( $_pn) $line.=" -n";
    if( $_pnn) $line.=" -N";
    if( trim($_pp)) $line.=" -p ".trim($_pp);
    if( trim($_psh)) $line.=" -s ".trim($_psh);
    if( trim($_pdh)) $line.=" -d ".trim($_pdh);
    if( trim($_pr)) $line.=" -r ".trim($_pr);
    if( trim($_target)=="S") {
	$line.=" -S";
    } elseif( trim($_target)=="D") {
	$line.=" -D";
    } elseif( trim($_target)=="L") {
	$line.=" -L";
    } elseif( trim($_target)=="R") {
	$line.=" -R";
    }
    $line.=" -o";
    
    list($rv,$aout)=_exec2($line);
    if( $rv!=0) {
	wlog("Программа netstat-add вернула код ошибки $rv в getnatstat().",2,TRUE,5,TRUE); exit; 
    }
    if( count($aout)==0) {
	print("<br><br>Нет соединений."); return(TRUE);
    }
    
    print("<table class=table1 width=\"85%\" cellpadding=\"2px\">\n");
    if(( $_pnn) && (( $_target=="S") || ( $_target=="D"))) {
	print("<tr> <th>Proto</th> <th>NATed Address</th> <th>NAT-host Address</th> <th>Destination Address</th> <th>State</th> </tr>\n");
    } else {
	print("<tr> <th>Proto</th> <th>NATed Address</th> <th>Destination Address</th> <th>State</th> </tr>\n");
    }
    
    foreach($aout as $akey => $string) {
	if( !trim($string=_trimline($string))) continue;
	$bufproto=gettok($string,1," \t");
	$bufnated=gettok($string,2," \t");
	$bufnathost=(( $_pnn) && (( $_target=="S") || ( $_target=="D"))) ? gettok($string,3," \t"):"";
	$bufdstaddr=gettok($string,((( $_pnn) && (( $_target=="S") || ( $_target=="D"))) ? 4:3)," \t");
	$bufstate=gettok($string,((( $_pnn) && (( $_target=="S") || ( $_target=="D"))) ? 5:4)," \t");
	
	print("<tr>\n");
	print("<td style=\"padding-left:7px;\"> $bufproto </td>\n<td style=\"padding-left:7px;\"> $bufnated </td>\n");
	if(( $_pnn) && (( $_target=="S") || ( $_target=="D"))) print("<td style=\"padding-left:7px;\"> ".$bufnathost." </td>\n");
	print("<td style=\"padding-left:7px;\"> $bufdstaddr </td>\n");
	print("<td style=\"padding-left:7px;\"> $bufstate </td>\n");
	print("</tr>\n");
    }

    print("</table>");

    $_wtime=time()-$_time1;
    print("<br><font style=\"font-size:8pt; font-style: italic;\">Сформировано  в ".date("G:i:s")." за ".round(($_wtime/60),2)."мин. &nbsp ".round($_wtime,2)."сек. </font> \n");
        


}
#---------------------------------------------------------------------------------------------------------
function show_prefs_form()
{
    global $_mode,$_target,$_pn,$_px,$_pnn,$_pp,$_psh,$_pdh,$_timeout,$_pr,$_refresh;
    if( $_mode!="prefs") return(FALSE);

    thick("<span style=\"padding-left:10px;\">\n<font class=top1>Настройки монитора NAT</font>\n</span>\n<br><br>\n");

    thick("<form id=\"tbox\" name=\"tbox\" action=\"natmon.php\">\n");
    thick("<input type=\"HIDDEN\" id=\"m\" name=\"m\" value=\"list\">  \n");
    thick("<input type=\"HIDDEN\" id=\"r\" name=\"r\" value=\"1\">  \n");

    thick("<table class=table4 style=\"width:485px;padding-left:10px;\" cellpadding=\"5px\">\n");

    thick("<tr>\n<td>\n");
    thick("<font class=text33>Показывать соединения: </font></td>\n<td colspan=2>\n");
    thick("<span class=seldiv>\n<SELECT id=\"t\" name=\"t\" style=\"width:200px;\">  \n");
    thick("<option value=\"\" ".((($_target=="all") || ( $_target=="")) ? "SELECTED" : "").">Все </option>\n");
    thick("<option VALUE=\"S\" ".(($_target=="S") ? "SELECTED" : "").">SNAT  </option> \n");
    thick("<option VALUE=\"D\" ".(($_target=="D") ? "SELECTED" : "").">DNAT  </option> \n");
    thick("<option VALUE=\"L\" ".(($_target=="L") ? "SELECTED" : "").">PREROUTING (до NAT, без SNAT/DNAT)  </option> \n");
    thick("<option VALUE=\"R\" ".(($_target=="R") ? "SELECTED" : "").">POSTROUTING (после NAT, без SNAT/DNAT)  </option> \n");
    thick("</SELECT>\n</span>\n</td>\n</tr>\n");

    thick("<tr>\n<td colspan=3>\n<font class=text33>\n");
    thick("<input type=\"CHECKBOX\" name=\"pn\" id=\"pn\" value=\"1\" ".(( $_pn) ? "CHECKED":"")."><label for=\"pn\">Не определять названия хостов/портов</label>\n<br>\n");
    thick("<input type=\"CHECKBOX\" name=\"pnn\" id=\"pnn\" value=\"1\" ".(( $_pnn) ? "CHECKED":"")."><label for=\"pnn\">Отображать информацию соединения NAT</label>\n<br>\n");

    thick("</font>\n</td>\n</tr>\n <tr><td>\n");

    thick("<font class=text33>По протоколу: </font></td>\n<td colspan=2>\n");
    thick("<span class=seldiv>\n<SELECT id=\"pp\" name=\"pp\" style=\"width:200px;\">  \n");
    thick("<option value=\"\" ".((( $_pp=="all") || ( $_pp=="")) ? "SELECTED" : "").">ALL  </option>\n");
    thick("<option VALUE=\"tcp\" ".(($_pp=="tcp") ? "SELECTED" : "").">TCP  </option> \n");
    thick("<option VALUE=\"udp\" ".(($_pp=="udp") ? "SELECTED" : "").">UDP  </option> \n");
    thick("<option VALUE=\"igmp\" ".(($_pp=="udp") ? "SELECTED" : "").">IGMP  </option> \n");
    thick("</SELECT></span>\n");
    
    thick("</td>\n</tr>\n <tr><td>\n");

    thick("<font class=text33>По исходному адресу (source-host): </font></td>\n<td colspan=2>\n");
    thick("<input type=\"TEXT\" name=\"psh\" id=\"psh\" value=\"".$_psh."\" size=45>\n");

    thick("</td></tr>\n <tr><td>\n");

    thick("<font class=text33>По адресу назначения (dst-host): </font></td>\n<td colspan=2>\n");
    thick("<input type=\"TEXT\" name=\"pdh\" id=\"pdh\" value=\"".$_pdh."\" size=45>\n");

    thick("</td></tr>\n <tr><td>\n");

    thick("<font class=text33>Сортировать по: </font></td>\n<td colspan=2>\n");
    thick("<span class=seldiv>\n<SELECT id=\"pr\" name=\"pr\" style=\"width:200px;\">  \n");
    thick("<option value=\"\" ".((( $_pr=="0") || ( $_pr=="")) ? "SELECTED" : "").">без сортировки  </option>\n");
    thick("<option VALUE=\"src\" ".(($_pr=="src") ? "SELECTED" : "").">исходному адресу (src)  </option> \n");
    thick("<option VALUE=\"dst\" ".(($_pr=="dst") ? "SELECTED" : "").">адресу назначения (dst)  </option> \n");
    thick("<option VALUE=\"src-port\" ".(($_pr=="src-port") ? "SELECTED" : "").">исходному порту (src-port)  </option> \n");
    thick("<option VALUE=\"dst-port\" ".(($_pr=="dst-port") ? "SELECTED" : "").">порту назначения (dst-port)  </option> \n");
    thick("<option VALUE=\"state\" ".(($_pr=="state") ? "SELECTED" : "").">состоянию соединения (state)  </option> \n");
    thick("</SELECT>\n</span>\n");

    thick("</td></tr>\n <tr><td>\n");

    thick("<font class=text33>\n");
    thick("<input type=\"CHECKBOX\" name=\"refresh\" id=\"refresh\" value=\"1\" ".(( $_refresh) ? "CHECKED":"")." onClick=\"javascript: document.getElementById('w').disabled=(this.checked==true) ? false:true; \"><label for=\"refresh\">Обновлять через</label>: </font></td>\n<td colspan=2>");
    thick("<input type=\"text\" id=\"w\" name=\"w\" value=\"$_timeout\" size=6><font class=text33>&nbspсек. &nbsp \n ");
    thick("</td></tr>\n <tr><td colspan=3 align=right style=\"padding-right:25px;\">\n");
    thick("<input type=\"SUBMIT\"  value=\"Применить\">");
    thick("</td></tr> \n");
    thick("</table>\n </form>  \n");
    
    thick("<script type=\"text/javascript\">\n");
    thick("\tdocument.getElementById('w').disabled = (document.getElementById('refresh').checked==true) ? false:true;\n");
    thick("</script>\n");


}
#---------------------------------------------------------------------------------------------------------

$iframe_url="natmon.php?m=getnatstat".(( !trim($_target)) ? "":"&t=$_target");
$iframe_url.=($_pn) ? "&pn=1":"";
$iframe_url.=($_pnn) ? "&pnn=1":"";
$iframe_url.=( !trim($_pp)) ? "":"&pp=".trim($_pp);
$iframe_url.=( !trim($_psh)) ? "":"&psh=".trim($_psh);
$iframe_url.=( !trim($_pdh)) ? "":"&pdh=".trim($_pdh);
$iframe_url.=( !trim($_pr)) ? "":"&pr=".trim($_pr);
$iframe_url.=( !trim($_timeout)) ? "":"&w=".trim($_timeout);
$iframe_url.=($_refresh) ? "&refresh=1":"";

if(( !trim($_mode)) || ( $_mode=="list")) {

    show_head();
    show_prefs();
    

    print("<iframe id=\"natstat\" src=\"".$iframe_url."\" frameborder=\"0\" name=\"natstat\" scrolling=\"no\" width=\"90%\" height=\"500px\" vspace=\"0\" hspace=\"0\">\n");
    print("Ваш браузер не поддерживает фреймв. Да. Вот..\n");
    print("</iframe>\n");
    

} elseif( $_mode=="prefs") {

    show_prefs_form();

} elseif( $_mode=="getnatstat") {

  if( !$_r) {
    
    show_load($_SERVER["REQUEST_URI"]."&r=1","Чтение спика соединений");

  } else {

    print("<div id=\"pantus\" style=\"FONT: normal 8pt Tahoma; color:696969;\"> &nbsp </div>\n");
    print("<br>\n");

    getnatstat();
    
    if( $_refresh) {

	print("<script type=\"text/javascript\">\n");

	print("function tikdown(sec_)\n");
	print("{\n");
	print("\tif( sec_ > 0 ) {\n");
	print("\t\t document.getElementById('pantus').innerHTML = \"Страница обновится через \"+sec_+\" сек.\"; \n");
        print("\t\t setTimeout(\"tikdown(\" + (sec_-1)+ \");\",990); \n");
	print("\t} else {\n");
	print("\t\t document.location.replace('".$iframe_url."'); \n");
	print("\t}\n\n");
	print("}\n\n");

	print("\ttop.frames['basefrm'].document.getElementById('natstat').height = document.body.scrollHeight+55+'px'; \n");

	print("\ttikdown(".$_timeout."); \n");
	
	
	print("\t</script>\n");
    } else {
	print("<script type=\"text/javascript\">\n");

	print("\ttop.frames['basefrm'].document.getElementById('natstat').height = document.body.scrollHeight+55+'px'; \n");
	
	print("\t</script>\n");

    }

  }

} elseif( $_mode=="debug") {

    show_head();
    debug();

}

?>
</body>
</html>