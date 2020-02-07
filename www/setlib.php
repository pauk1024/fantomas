<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8.2
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: setlib.php
# Description: 
# Version: 2.8.2.1
###




function set_getstat($pset,$pmode=0)
{
    global $_sudo,$_ipset;
    if( $pmode==0) exit;
    $rez;
    $line="$_ipset -nL $pset";
    list($rr,$aa1)=_exec2($line);
    if( $rr>0) { wlog("������ ��������� �������� $pset � set_binding()...<br>rr .$rr. paddr .$paddr.<br> line: .$line.",2,TRUE,3,TRUE); }
    $flin=0;
    $col=0;
    foreach($aa1 as $akk1 => $avv1) {
	if( trim($avv1)=="") continue;
	if( trim($avv1)=="Members:") { $flin=1; continue; }
	if( trim($avv1)=="Bindings:") { $flin=2; continue; }
				
	if( $flin==$pmode) {
	    $col++;
	}
    }
    unset($aa1);
    return($col);

}

#--------------------------------------------------------------------

function show_setaddmember_form($pset="",$inlist=FALSE)
{
    if(trim($pset)=="") exit;
    print("<form name=\"psetaddmember\" action=\"sets.php\">\n");
    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"set_addmember\">\n");
    print("<font class=text42>����� �������:</font>\n");
    print("<table class=table5e style=\"width:400px;cellpadding:0px;margin:0px\">\n");
    print("<tr><td align=middle>Addr:</td><td align=middle><input type=\"text\" name=\"addr\" id=host size=38></td> \n");
    print("<td align=right><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"��������\"></td></tr> \n");
    print("</table>\n</form>\n");


}

#----------------------------------------------------------------

function add_set($pset,$ptype,$pmode)
{
    global $_hashsize,$_probes,$_resize,$_size,$_timeout,$_p1name,$_p2name,$_p1value,$_p2value;
    global $_sudo,$_ipset,$_grep,$iptconf_dir,$sets_dir,$_set_save_onchange,$_gc;
    if((trim($pset)=="") or (trim($ptype)=="")) exit;

    if( $pmode!="addset2") {
    
	print("<br><br><br><br><div style=\"padding-left:50px\">\n");
	
	print("<font class=top1>��������� ������ ���-�����</font><br><br>\n");
	print("<font class=text32>���: <i>$pset</i><br>\n");
	print("���: <i>$ptype</i><br><br>\n");
	if( $ptype=="ipmap") {
        
	    print("<table class=table5e style=\"width:400px;cellpadding:0px\">\n");
	    print("<form name=\"ipmapopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>�����1</td><td>\n<select name=\"p1name\">\n");
	    print("<option name=\"from\">--from\n<option name=\"network\">--network\n<option type=\"netmask\">--netmask\n");
	    print("</select></td><td><input type=\"text\" name=\"p1value\" id=p1value size=30></td></tr>\n");
	    print("<tr><td class=frm1>�����2</td><td>\n<select name=\"p1name\">\n");
	    print("<option name=\"to\">--to\n<option type=\"netmask\">--netmask\n");
	    print("</select></td><td><input type=\"text\" name=\"p1value\" id=p1value size=30></td></tr>\n");
	    print("<tr><td><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");

	} elseif( $ptype=="macipmap") {

	    print("<table class=table5e style=\"width:400px;cellpadding:0px;\">\n");
	    print("<form name=\"macipmapopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>�����1</td><td>\n<select name=\"p1name\">\n");
	    print("<option name=\"from\">--from\n<option name=\"network\">--network\n<option type=\"netmask\">--netmask\n");
	    print("</select></td><td><input type=\"text\" name=\"p1value\" id=p1value size=30></td></tr>\n");
	    print("<tr><td class=frm1>�����2</td><td>\n<select name=\"p2name\">\n");
	    print("<option name=\"to\">--to\n<option type=\"netmask\">--netmask\n");
	    print("</select></td><td><input type=\"text\" name=\"p2value\" id=p1value size=30></td></tr>\n");
	    print("<tr><td colspan=2 align=right><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");
	    
	} elseif( $ptype=="portmap") {

	    print("<table class=table5e style=\"width:400px;cellpadding:0px;margin:0px\">\n");
	    print("<form name=\"portmapopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>� �����</td><td>\n<input type=\"HIDDEN\" name=\"p1name\" value=\"from\">\n");
	    print("<input type=\"TEXT\" name=\"p1value\" id=p1value size=10></td></tr>\n");
	    print("<tr><td class=frm1>�� ����</td><td><input type=\"HIDDEN\" name=\"p2name\" value=\"to\">\n<input type=\"text\" name=\"p2value\" id=p2value size=10></td></tr>\n");
	    print("<tr><td colspan=2 align=right><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");

	} elseif( $ptype=="iphash") {

	    print("<table class=table5e style=\"width:400px;cellpadding:0px;\">\n");
	    print("<form name=\"iphashopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>Hashsize</td><td><input type=\"TEXT\" name=\"hashsize\" id=p1value size=10 value=\"1024\"></td></tr>\n");
	    print("<tr><td class=frm1>Probes</td><td><input type=\"text\" name=\"probes\" id=probes size=10 value=\"4\"></td></tr>\n");
	    print("<tr><td class=frm1>Resize</td><td><input type=\"text\" name=\"resize\" id=resize size=10 value=\"50\"></td></tr>\n");
	    print("<tr><td class=frm1>Netmask</td><td><input type=\"HIDDEN\" name=\"p1value\" id=p1value value=\"netmask\"><input type=\"text\" name=\"p2value\" id=p2value size=20></td></tr>\n");
	    print("<tr><td colspan=2 align=right><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");
	
	} elseif( $ptype=="nethash") {

	    print("<table class=table5e style=\"width:300px;\">\n");
	    print("<form name=\"iphashopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>Hashsize</td><td>\n<input type=\"TEXT\" name=\"hashsize\" id=hashsize size=10 value=\"1024\"></td></tr>\n");
	    print("<tr><td class=frm1>Probes</td><td><input type=\"text\" name=\"probes\" id=probes size=10 value=\"4\"></td></tr>\n");
	    print("<tr><td class=frm1>Resize</td><td><input type=\"text\" name=\"resize\" id=resize size=10 value=\"50\"></td></tr>\n");
	    print("<tr><td colspan=2 align=right><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");

	} elseif( ($ptype=="ipporthash") or ($ptype=="ipportiphash") or ($ptype=="ipportnethash")) {

	    print("<table class=table5e style=\"width:400px;cellpadding:0px;margin:0px\">\n");
	    print("<form name=\"ipporthashopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>�����1</td><td>\n<select name=\"p1name\">\n");
	    print("<option name=\"from\">--from\n<option name=\"network\">--network\n");
	    print("</select></td><td><input type=\"text\" name=\"p1value\" id=p1value size=30></td></tr>\n");
	    print("<tr><td class=frm1>�����2</td><td>\n<select name=\"p2name\">\n");
	    print("<option name=\"to\">--to\n<option name=\"network\">--network\n");
	    print("</select></td><td><input type=\"text\" name=\"p2value\" id=p2value size=30></td></tr>\n");
	    print("<tr><td class=frm1>Hashsize</td><td colspan=2>\n<input type=\"TEXT\" name=\"hashsize\" id=hashsize size=10 value=\"1024\"></td></tr>\n");
	    print("<tr><td class=frm1>Probes</td><td colspan=2><input type=\"text\" name=\"probes\" id=probes size=10 value=\"4\"></td></tr>\n");
	    print("<tr><td class=frm1>Resize</td><td colspan=2><input type=\"text\" name=\"resize\" id=resize size=10 value=\"50\"></td></tr>\n");
	    print("<tr><td colspan=3 align=right><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");

	    } elseif( $ptype=="iptree") {

	    print("<table class=table5e style=\"width:400px;cellpadding:0px;margin:0px\">\n");
	    print("<form name=\"iptreeopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>Timeout</td><td>\n<input type=\"TEXT\" name=\"timeout\" id=timeout size=10></td>\n");
	    print("<td><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");

	} elseif( $ptype=="iptreemap") {

	    print("<table class=table5e style=\"width:400px;cellpadding:0px;margin:0px\">\n");
	    print("<form name=\"iptreemapopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>Gc</td><td>\n<input type=\"TEXT\" name=\"gc\" id=gc size=10 value=\"300\"></td>\n");
	    print("<td><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");

	} elseif( $ptype=="setlist") {

	    print("<table class=table5e style=\"width:400px;cellpadding:0px;margin:0px\">\n");
	    print("<form name=\"setlistopt\" action=\"sets.php\">\n");
	    print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	    print("<input type=\"HIDDEN\" name=\"settype\" id=set value=\"$ptype\">\n");
	    print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"addset2\">\n");
	    print("<tr><td class=frm1>Size</td><td>\n<input type=\"TEXT\" name=\"size\" id=size size=10 value=\"8\"></td>\n");
	    print("<td><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"> </td></tr>\n");
	    print("</form>\n</table>\n");

	}
	print("</div>\n");
	print("<br><br>\n");
	web_show_back("sets.php","�� ������� (������)");
	exit;
    
    } elseif( $pmode=="addset2") {
	
	$bfrom=""; $bto=""; $bnetwork=""; $bnetmask=""; $bsize="";
	$bhashsize=""; $probes=""; $bresize=""; $btimeout=""; $bgc=""; $bsize="";
	
	if( trim($_p1name)!="") {
	    $bfrom=( trim($_p1name)=="from") ? "--from $_p1value" : $bfrom;
	    $bnetwork=( trim($_p1name)=="network") ? "--network $_p1value" : $bnetwork;
	    $bnetmask=( trim($_p1name)=="netmask") ? "--netmask $_p1value" : $bnetmask;
	}
	if( trim($_p2name)!="") {
	    $bto=( trim($_p2name)=="to") ? "--to $_p2value" : $bto;
	}
	$bhashsize=( trim($_hashsize)!="") ? "--hashsize $_hashsize" : $bhashsize;
	$bprobes=( trim($_probes)!="") ? "--probes $_probes" : $bprobes;
	$bresize=( trim($_resize)!="") ? "--resize $_resize" : $bresize;
	$btimeout=( trim($_timeout)!="") ? "--timeout $_timeout" : $btimeout;
	$bgc=( trim($_gc)!="") ? "--gc $_gc" : $bgc;
	$bsize=( trim($_size)!="") ? "--size $_size" : $bsize;



	$line="$_ipset -N $pset $ptype";
	if( $ptype=="ipmap") {
	    $line="$line $bfrom $bnetwork $bto $bnetmask";
	} elseif( $ptype=="macipmap") {
	    $line="$line $bfrom $bnetwork $bto";
	} elseif( $ptype=="portmap") {
	    $line="$line $bfrom $bto";
	} elseif( $ptype=="iphash") {
	    $line="$line $bhashsize $bprobes $bresize $bnetmask";
	} elseif( $ptype=="nethash") {
	    $line="$line $bhashsize $bprobes $bresize";
	} elseif( $ptype=="ipporthash") {
	    $line="$line $bfrom $bnetwork $bto $bhashsize $bprobes $bresize";
	} elseif( $ptype=="ipportiphash") {
	    $line="$line $bfrom $bnetwork $bto $bhashsize $bprobes $bresize";
	} elseif( $ptype=="ipportnethash") {
	    $line="$line $bfrom $bnetwork $bto $bhashsize $bprobes $bresize";
	} elseif( $ptype=="iptree") {
	    $line="$line $btimeout";
	} elseif( $ptype=="iptreemap") {
	    $line="$line $bgc";
	} elseif( $ptype=="setlist") {
	    $line="$line $bsize";
	}
	
	if( ( file_exists("$iptconf_dir/ipsetlist")) and (is_writable("$iptconf_dir/ipsetlist"))) {
	    
	    list($rr,$aa1)=_exec2($line);
	    
	} else {
	    
	    wlog("������: ���� $iptconf_dir/ipsetlist ���������� ��� ������ ��� �� ����������!",2,TRUE,5,TRUE);
	    exit;
	}
	
	if($rr>0) {
	    wlog("������ �������� �������� $pset!<br>\ncmd: $line<br>\nreturn: .$rr.\n",2,TRUE,4,TRUE);
	    print("<br><br><a href=\"sets.php\" title=\"�����\"><img src=\"icons/gtk-undo.gif\" title=\"�����\">�����</a><br>\n");
	    exit;
	} else {
	    $listfile=fopen("$iptconf_dir/ipsetlist","a");
	    fwrite($listfile,"$pset\n");
	    fclose($listfile); 
	    if( isset($aa1)) unset($aa1);
	  if( $_set_save_onchange) {
	    list($rr1,$aa1)=_exec2("$_ipset -S $pset > $sets_dir/$pset");
	    if( $rr1>0) {
		wlog("������ ���������� �������� $pset � add_set()...",2,TRUE,4,FALSE); exit;
	    }
	  }

	    print("<font class=text42>���-���� ������� ��������...</font><br><br>\n");
	}
	    
	wlog("���������� ��������: line $line",0,FALSE,1,FALSE);	
    }



}

#--------------------------------------------------------------------

function set_addmember($pset="",$paddr="")
{
    global $_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange;
    if((trim($pset)=="") or (trim($paddr)=="")) exit;
    $rez="";
    $paddr=gethostbyname($paddr);
    $line="$_ipset -nL $pset | $_grep $paddr";
    list($rr,$aa1)=_exec2($line);
    if( $rr>1) { wlog("Error checking setlist in ipset1-command in set_addmember()...<br>rr .$rr. paddr .$paddr.<br> line: .$line.",2,TRUE,5,TRUE); exit; }
    if( count($aa1)>0) {
	wlog("Addr $paddr ��� ���������� � �������� $pset.",2,TRUE,4,TRUE); exit;
    }
    unset($aa1);
    list($rr,$aa1)=_exec2("$_ipset -A $pset $paddr");
    if( $rr>0) { wlog("������ ���������� $paddr � ������� $pset...",2,TRUE,5,TRUE); exit; }
    unset($aa1);
    if( $_set_save_onchange) {
	list($rr,$aa1)=_exec2("$_sudo $_ipset -S $pset > $sets_dir/$pset");
	if( $rr>0) { wlog("������ ���������� �������� $pset �� ���� � ���� $sets_dir/$pset...",2,TRUE,5,TRUE); exit; }
    }

    wlog("���������� �������� $paddr � ������� $pset",0,FALSE,1,FALSE);
}

#-----------------------------------------------------------------

function set_killpos($pset="",$paddr="",$prun="")
{
    global $_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange,$_run,$_set_submit_query,$_qptr;
    if((trim($pset)=="") or (trim($paddr)=="")) exit;
    $rez="";
    $line="$_ipset -nL $pset | $_grep $paddr";
    list($rr,$aa1)=_exec2($line);
    if( $rr>1) { print("Error checking setlist in ipset1-command in set_addmember()...<br>rr .$rr. rez .$rez. paddr .$paddr.<br> line: .$line."); exit; }
    if( count($aa1)==0) {
	wlog("������� $paddr �� ������ � �������� $pset.",2,TRUE,5,TRUE); exit;
    }
    unset($aa1);
    $paddr=trim($paddr);
    $pqptr=( trim($_qptr)!="") ? "&qptr=1" : "";
    $prun=( !$_set_submit_query) ? "1" : $prun;
    
    if( trim($prun)=="") {
	print("<br><br><table class=table1 width=\"350px\" cellpadding=\"6px\"><tr><td align=center>\n");
	print("<br>������� ������� $paddr �� ���-����� $pset?<br><br> \n");
	print("<a href=\"sets.php?set=$pset&mode=set_killpos&addr=$paddr&run=1\" class=a14>��</a>&nbsp&nbsp&nbsp<a href=\"sets.php?set=$pset&mode=listview$pqptr\" class=a14>���</a>\n");
	print("</td></tr></table>\n");
	exit;
    } else {
	
	list($rr,$aa1)=_exec2("$_ipset -D $pset $paddr");
	if( $rr>0) { wlog("������ �������� $paddr �� �������� $pset...",2,TRUE,4,TRUE); exit; }
	unset($aa1);
	if( $_set_save_onchange) {
	    list($rr,$aa1)=_exec2("$_ipset -S $pset > $sets_dir/$pset");
	    if( $rr>0) { wlog("������ ���������� �������� $pset �� ���� � ���� $sets_dir/$pset...",2,TRUE,5,TRUE); exit; }
	}
	wlog("�������� ������� $paddr �� �������� $pset",0,FALSE,1,FALSE);
    }
    
}

#-----------------------------------------------------------------

function set_flush($pset="",$prun="")
{
    global $_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange;
    global $_set_submit_query;
    $prun=( !$_set_submit_query) ? "1" : $prun;
    if(trim($pset)=="") exit;
    if( trim($prun)=="") {
	print("<br><br><table class=table1 width=\"350px\" cellpadding=\"6px\"><tr><td align=center>\n");
	print("<br>�������� ���-���� $pset?<br><br> \n");
	print("<a href=\"sets.php?set=$pset&mode=set_flush&run=1\" class=a14>��</a>&nbsp&nbsp&nbsp<a href=\"sets.php?set=$pset&mode=show\" class=a14>���</a>\n");
	print("</td></tr></table>\n");
	exit;
    } else {
	
	$rez="";
	$line="$_ipset -nL $pset | $_grep Type";
	list($rr,$aa1)=_exec2($line);
	if( $rr>0) { wlog("Error checking setlist in ipset1-command in set_addmember()...<br>rr .$rr. paddr .$paddr.<br> line: .$line.",2,TRUE,5,TRUE); exit; }
	$fl=FALSE;
	foreach($aa1 as $aakey => $aaval) $fl=(substr_count($aaval,"Unknown")>0) ? TRUE:$fl;
	if( $fl) { wlog("������� $pset �� ��������...",2,TRUE,5,TRUE); exit; }
	unset($aa1);
	list($rr,$aa1)=_exec2("$_ipset -F $pset");
	if( $rr>0) { wlog("������ �������� �������� $pset...",2,TRUE,5,TRUE); exit; }
	unset($aa1);
	if( $_set_save_onchange) {
	    list($rr,$aa1)=_exec2("$_ipset -S $pset > $sets_dir/$pset");
	    if( $rr>0) { wlog("������ ���������� �������� $pset �� ���� � ���� $sets_dir/$pset...",2,TRUE,5,TRUE); exit; }

	}
	wlog("������� �������� $pset",0,FALSE,1,FALSE);
    }

}

#-----------------------------------------------------------------

function set_unload($pset="",$prun="")
{
    global $_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange;
    global $_set_submit_query,$_refer;
    if(trim($pset)=="") exit;
    $prun=( !$_set_submit_query) ? "1" : $prun;

    if( trim($prun)=="") {
	$bufpars=( ($_refer=="list") or ($_refer=="")) ? "" : "set=$pset&mode=show";
	print("<br><br><table class=table1 width=\"350px\" cellpadding=\"6px\"><tr><td align=center>\n");
	print("<br>��������� �� ������ ���-���� $pset?<br><br> \n");
	print("<a href=\"sets.php?set=$pset&mode=set_unload&run=1\" class=a14>��</a>&nbsp&nbsp&nbsp<a href=\"sets.php?$bufpars\" class=a14>���</a>\n");
	print("</td></tr></table>\n");
	exit;
    } else {
	
	$rez="";
	$line="$_ipset -nL $pset | $_grep Type";
	list($rr,$aa1)=_exec2($line);
	if( $rr>0) { wlog("������ �������� �������� � set_addmember(), �������� ������� �� ��������...<br>rr .$rr. rez .$rez. paddr .$paddr.<br> line: .$line.",2,TRUE,5,TRUE); exit; }
	$fl=FALSE;
	foreach($aa1 as $aakey => $aaval) $fl=(substr_count($aaval,"Unknown")>0) ? TRUE:$fl;
	if( $fl) { wlog("������� $pset �� ��������...",2,TRUE,5,TRUE); exit; }
	unset($aa1);
	
	$setfile=$sets_dir."/".$pset;
	if( !is_writable($setfile)) { wlog("������: ���� $setfile ���������� ��� ������!",2,TRUE,4,TRUE); exit; }
	
	if( $_set_save_onchange) {
	    list($rr,$aa1)=_exec2("$_ipset -S $pset > $setfile");
	    if( $rr>0) { wlog("������ ���������� �������� $pset...",2,TRUE,5,TRUE); exit; }
	    unset($aa1);
	}
	list($rr,$aa1)=_exec2("$_ipset -F $pset");
	if( $rr>0) { wlog("������ ������� �������� $pset...",2,TRUE,5,TRUE); exit; }
	unset($aa1);
	list($rr,$aa1)=_exec2("$_ipset -X $pset");
	if( $rr>0) { wlog("������ �������� �� ������ �������� $pset...",2,TRUE,5,TRUE); exit; }
	wlog("�������� �������� $pset",0,FALSE,1,FALSE);
    }

}

#-----------------------------------------------------------------

function set_load($pset="",$prun="")
{
    global $_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange;
    global $_set_submit_query;
    if(trim($pset)=="") exit;
    $prun=( !$_set_submit_query) ? "1" : $prun;

    if( trim($prun)=="") {
	print("<br><br><table class=table1 width=\"350px\" cellpadding=\"6px\"><tr><td align=center>\n");
	print("<br>��������� ���-���� $pset?<br><br> \n");
	print("<a href=\"sets.php?set=$pset&mode=set_load&run=1\" class=a14>��</a>&nbsp&nbsp&nbsp<a href=\"sets.php?set=$pset\" class=a14>���</a>\n");
	print("</td></tr></table>\n");
	exit;
    } else {
	
	$rez="";
	$line="$_ipset -nL $pset | $_grep Type";
	list($rr,$aa1)=_exec2($line);
	if( $rr==0) { wlog("�������� ������� �������� ������� ��� 0 - �������� ������� ��� ��������! ...<br>rr .$rr. rez .$rez. paddr .$paddr.<br> line: .$line.",2,TRUE,5,TRUE); exit; }
	unset($aa1);
	
	$setfile=$sets_dir."/".$pset;
	if( !is_readable($setfile)) { wlog("������: ���� $setfile ���������� ��� ������!",2,TRUE,5,TRUE); exit; }
	
	list($rr,$aa1)=_exec2("$_ipset -R < $setfile");
	if( $rr>0) { wlog("������ �������� �������� $pset �� ����� $setfile...",2,TRUE,5,TRUE); exit; }
	unset($aa1);
	wlog("�������� �������� $pset",0,FALSE,1,FALSE);
    }

}

#-----------------------------------------------------------------

function set_rename($pset="",$prun="")
{
    global $iptconf_dir,$_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange,$_p1name,$_p1value;
    if(trim($pset)=="") exit;

    if( trim($prun)=="") {
	print("<br><br><font class=top1>�������������� ���-����� $pset</font>\n");
	print("<table class=table1 width=\"350px\" cellpadding=\"6px\">\n");
	print("<form name=\"setrename1\" action=\"sets.php\">\n");
	print("<input type=\"HIDDEN\" name=\"p1name\" id=p1name value=\"set_newnameof_$pset\">\n");
	print("<input type=\"HIDDEN\" name=\"run\" id=run value=\"1\">\n");
	print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"set_rename\">\n");
	print("<tr><td>����� ���: </td><td> <input type=\"TEXT\" name=\"p1value\" size=30></td></tr> \n");
	print("<tr><td colspan=2><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������������\"></td></tr>\n");
	print("</table>\n <form>\n");
	print("<br><br>\n");
	web_show_back("sets.php","�� ������� (������)");
	exit;
    } else {
    
	if( trim($_p1name)!="set_newnameof_$pset") {
	    wlog("������ �������� ������������������ ������� ��������� set_rename!<br><br>debug: p1name .$_p1name. p1value .$_p1value. pset .$pset.",2,TRUE,5,TRUE); exit;
	}
	$_p1value=trim($_p1value);
	if( $_p1value=="") {
	    wlog("����� ��� �������� �� ����� ���� ������..",2,TRUE,5,TRUE); exit;
	}
	
	$rez="";
	$line="$_ipset -nL $pset | $_grep Type";
	list($rr,$aa1)=exec($line);
	if( $rr>0) { wlog("������ �������� �������� � set_rename(), �������� ������� �� ��������...<br>rr .$rr. rez .$rez. paddr .$paddr.<br> line: .$line.",1,TRUE,3,TRUE); }
	if( count($aa1)==0) { wlog("������� $pset �� ��������...",2,TRUE,5,TRUE); exit; }
	unset($aa1);

	$line="$_ipset -E $pset $_p1value";
	list($rr,$aa1)=_exec2($line);
	if( $rr>0) { wlog("������ �������������� �������� $pset � $_p1value...<br><br>debug: line .$line.",2,TRUE,5,TRUE); exit; }
	unset($aa1);

	if( $_set_save_onchange) {
	    $line="$_ipset -S $_p1value > $sets_dir/$_p1value";
	    list($rr,$aa1)=_exec2($line);
	    if( $rr>0) { wlog("������ ������ �������� $_p1value...<br><br>debug: line .$line.",2,TRUE,5,TRUE); exit; }
	    unset($aa1);

	    $oldsetfile=$sets_dir."/".$pset;
	    $newsetfile=$sets_dir."/".$_p1value;

	    if( file_exists($oldsetfile)) {
		if( !is_writable($oldsetfile)) {
		    wlog("���� $oldsetfile ���������� ��� ������ - �� ������� ��� ������������� � .bak!",1,TRUE,3,TRUE);
		} else {
		    rename($oldsetfile,$oldsetfile.".bak");
		}
	    }
	    $setlistfile=$iptconf_dir."/ipsetlist";
	    if( !is_writable($setlistfile)) {
		wlog("���� $setlistfile ���������� ��� ������!!!! �� ������� ������ ���� ����� ��� ��������.",1,TRUE,3,TRUE);
	    } else {
		rename($setlistfile,"$setlistfile.bak");
		$aslnew=fopen($setlistfile,"a");
		if( !$aslnew) {
		    wlogt("������ �������� ����� $setlistfile!! ��������� ����� ������������ httpd �� ����� $iptconf_dir!",2,TRUE,3,TRUE);
		    rename($setlistfile.".bak",$setlistfile);
		} else {
		    $asetlist=file("$setlistfile.bak");
		    foreach($asetlist as $aslkk => $aslvv) {
			if(trim($aslvv)=="") continue;
			if( trim($aslvv)==trim($pset)) {
			    continue;
			} else {
			    fwrite($aslnew,"$aslvv");
			}
		    }
		    fwrite($aslnew,"$_p1value\n");
		}
	    }
	}
	wlog("�������������� �������� $pset � $_p1value",0,FALSE,1,FALSE);

    }

}

#-----------------------------------------------------------------

function set_save($pset="")
{
    global $_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange;
    if(trim($pset)=="") exit;
	
    $rez="";
    $line="$_ipset -nL $pset | $_grep Type";
    list($rr,$aa1)=exec($line);
    if( $rr>0) { wlog("������ �������� �������� � set_addmember(), �������� ������� �� ��������...<br>rr .$rr. rez .$rez. paddr .$paddr.<br> line: .$line.",2,TRUE,5,TRUE); exit; }
    $fl=FALSE;
    foreach($aa1 as $aakey => $aaval) $fl=(substr_count($aaval,"Unknown")>0) ? TRUE:$fl;
    if( $fl) { wlog("������� $pset �� ��������...",2,TRUE,5,TRUE); exit; }
    unset($aa1);
	
    $setfile=$sets_dir."/".$pset;
    if( !is_writable($setfile)) { wlog("������: ���� $setfile ���������� ��� ������!",2,TRUE,5,TRUE); exit; }
    
    if( file_exists($setfile.".bak")) unlink($setfile.".bak");
    if( file_exists($setfile)) rename($setfile,$setfile.".bak");
    
    list($rr,$aa1)=_exec2("$_ipset -S $pset > $setfile");
    if( $rr>0) { wlog("������ ���������� �������� $pset...",2,TRUE,5,TRUE); exit; }
    unset($aa1);
    wlog("���-���� $pset ������� �������� � ���� $setfile.<br>",0,TRUE,1,FALSE);

}

#-----------------------------------------------------------------

function set_binding($pset="",$paddr="",$prun="")
{
    global $iptconf_dir,$_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange,$_p1name,$_p1value;
    global $_set_submit_query;
    if(trim($pset)=="") exit;
    $rez="";
    $line="$_ipset -nL $pset | $_grep Type";
    list($rr,$aa1)=exec($line);
    if( $rr>0) wlog("������ �������� �������� � set_binding(), �������� ������� �� ��������...<br>rr .$rr. paddr .$paddr.<br> line: .$line.",2,TRUE,3,TRUE);
    if( count($aa1)==0) { wlog("������� $pset �� ��������...",2,TRUE,4,TRUE); exit; }
    unset($aa1);

    if( trim($prun)=="") {
	print("<br><br><font class=top1>�������� ��������<br><br> �������� ���-����: $pset</font>\n");
	print("<form name=\"setrename1\" action=\"sets.php\">\n");
	print("<input type=\"HIDDEN\" name=\"run\" id=run value=\"1\">\n");
	print("<input type=\"HIDDEN\" name=\"set\" id=set value=\"$pset\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"set_binding\">\n");
	print("<table class=table1 width=\"450px\" cellpadding=\"6px\">\n");
	print("<tr><td>�������:</td><td>");

	if( trim($paddr)=="") {
	    $rez="";
	    $line="$_ipset -nL $pset";
	    list($rr,$aa1)=exec($line);
	    if( $rr>0) { wlog("������ ��������� �������� $pset � set_binding()...<br>rr .$rr. paddr .$paddr.<br> line: .$line.",2,TRUE,3,TRUE); }
	    $flin=FALSE;
	    $aa2=array();
	    foreach($aa1 as $akk1 => $avv1) {
		if( trim($avv1)=="Members:") { $flin=TRUE; continue; }
		if( trim($avv1)=="Bindings:") { $flin=FALSE; continue; }
				
		if( $flin) {
		    $aa2[count($aa2)+1]=$avv1;
		}
	    }
	    unset($aa1);
	    print("<select name=\"addr\">\n");
	    foreach($aa2 as $akk2 => $avv2) {
		print("<option value=\"$avv2\">$avv2\n");
	    }
	    print("</select>\n");
	    unset($aa2);
	} else {
	    print("$paddr <input type=\"HIDDEN\" name=\"addr\" id=addr value=\"$paddr\">\n");
	}
	print("</td></tr>\n");
	print("<tr><td>���-���� ����������: </td><td> ");

	$setlistfile=$iptconf_dir."/ipsetlist";
	if( !is_readable($setlistfile)) { 
	    wlog("���� $setlistfile ���������� ��� ������!",2,TRUE,4,TRUE); exit;
	}
	$asets=file($setlistfile);
	$aaloaded=array();
	foreach($asets as $askk => $asvv) {
	    $asvv=trim($asvv);
	    if( ($asvv==trim($pset)) or ($asvv=="locals")) continue;
	    $rez="";
	    $line="$_ipset -nL $asvv | $_grep Type";
	    list($rv,$aa3)=exec($line);
	    if( $rv==0) { 
		foreach($aa3 as $aa3kk => $aa3vv) {
		    $rez=trim(str_replace("Type:","",$aa3vv));
		    $aaloaded["$asvv"]="$asvv &nbsp&nbsp&nbsptype:$rez";
		}
	    }
	    unset($aa3); unset($rez); unset($rr);
	}
	if( count($aaloaded)>0) {
	    print("<select name=\"p1value\">\n");
	    foreach($aaloaded as $aaldkk => $aaldvv) {
		print("<option value=\"$aaldkk\">$aaldvv\n");
	    }
	    print("</select>\n");
	} else {
	    wlog("��� ����������� ���-������! ",2,TRUE,3,TRUE); exit;
	}
	unset($asets);
	unset($aaloaded);
	print("</td></tr>\n");
	print("<tr><td colspan=2 align=right><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"�������\"></td></tr>\n");
	print("</table>\n");
	print("<br><br><br><div class=text33 style=\"padding-left:380px\"><a href=\"sets.php?set=$pset&mode=show\" title=\"�����\"><img src=\"icons/gtk-undo.gif\" title=\"�����\">�����</a></div>\n");
	exit;
    } else {
    
	$_p1value=trim($_p1value);
	$paddr=trim($paddr);
	if( $_p1value=="") {
	    wlog("��� �������� ���������� �� ����� ���� ������..",2,TRUE,3,TRUE); exit;
	}
	if( $paddr=="") {
	    wlog("������� �������� �� ����� ���� ������..",2,TRUE,3,TRUE); exit;
	}
	
	$line="$_ipset -B $pset $paddr -b $_p1value";
	list($rr,$aa1)=_exec2($line);
	if( $rr>0) { wlog("������ �������� �������� ��� �������� $paddr �� ������� $_p1value...<br><br>debug: line .$line.<br>rr .$rr.",2,TRUE,5,TRUE); exit; }
	unset($aa1);

	if( $_set_save_onchange) {
	    $line="$_ipset -S $pset > $sets_dir/$pset";
	    list($rr,$aa1)=_exec2($line);
	    if( $rr>0) { wlog("������ ������ �������� $pset...<br><br>debug: line .$line.<br>rr .$rr.",2,TRUE,5,TRUE); exit; }
	    unset($aa1);
	}
	print("������� ������� ������...<br>");
	wlog("�������� �������� �������� � �������� $pset ��� $paddr",0,FALSE,1,FALSE);
    }

}

#-----------------------------------------------------------------

function set_unbind($pset="",$paddr="",$prun="")
{
    global $iptconf_dir,$_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange,$_p1name,$_p1value;
    global $_set_submit_query;
    if(trim($pset)=="") exit;
    if(trim($paddr)=="") exit;
    $rez="";
    $line="$_ipset -nL $pset | $_grep Type";
    list($rr,$aa1)=exec($line);
    wlog("debug: line .$line.<br>aa1 ".count($aa1)." rr .$rr. <br>\n ",1,FALSE,3,FALSE);
    if( $rr>0) wlog("������ �������� �������� � set_binding(), �������� ������� �� ��������...<br>rr .$rr. rez .$rez. paddr .$paddr.<br> line: .$line.",1,TRUE,3,TRUE);
    if( count($aa1)==0) { wlog("������� $pset �� ��������...",2,TRUE,5,TRUE); exit; }
    unset($aa1);

    $prun=( !$_set_submit_query) ? "1" : $prun;

    if( trim($prun)=="") {
	print("<br><br>\n");

	print("<br><br><br><div style=\"padding-left:90px\">\n");
	print("<table class=table1 width=\"350px\" cellpadding=\"6px\"><tr><td align=center>\n");
	print("<br>������� ������� ��� $paddr �� ���-���� $_p1value?<br><br> \n");
	print("<a href=\"sets.php?set=$pset&mode=set_unbind&addr=$paddr&p1value=$_p1value&run=1\" class=a14>��</a>&nbsp&nbsp&nbsp<a href=\"sets.php?set=$pset&mode=show\" class=a14>���</a>\n");
	print("</td></tr></table>\n");

	exit;
    } else {
    
	$_p1value=trim($_p1value);
	$paddr=trim($paddr);

	if( $paddr=="") {
	    wlog("������� �������� �� ����� ���� ������..",2,TRUE,5,TRUE); exit;
	}
	
	$line="$_ipset -U $pset $paddr";
	list($rr,$aa1)=exec($line);
	if( $rr>0) { wlog("������ �������� �������� ��� �������� $paddr �� ������� $_p1value...<br><br>debug: line .$line.<br>rr .$rr.",2,TRUE,5,TRUE); exit; }
	unset($aa1);

	if( $_set_save_onchange) {
	    $line="$_ipset -S $pset > $sets_dir/$pset";
	    list($rr,$aa1)=_exec2($line);
	    if( $rr>0) { wlog("������ ������ �������� $pset...<br><br>debug: line .$line.<br>rr .$rr.",2,TRUE,5,TRUE); exit; }
	    unset($aa1);
	}
	print("������� ������� ������...<br>");
	wlog("�������� �������� � �������� $pset � $paddr",0,FALSE,1,FALSE);
    }

}

#-----------------------------------------------------------------
function show_addset_form()
{
    global $_mode;
    $atypes=array("nethash","ipmap","macipmap","portmap","ipporthash","ipportiphash","ipportnethash","iptree","iptreemap","setlist");
    print("<br><br>  \n");
    print("<table class=table5e cellpadding=\"2px\" width=\"350px\">\n");
    print("<form name=\"form324\" action=\"sets.php\">  \n");
    print("<input type=\"hidden\" name=\"mode\" value=\"addset\">  \n");
    print("<tr><td colspan=2><font class=top1>����� ���-���� </font></td></tr> \n");
    print("<tr><td>���:</td><td> <input type=\"text\" name=\"set\" size=20> </td></tr> \n");
    print("<tr><td>���:</td><td> <select name=\"settype\">\n");
    print("<option value=\"iphash\" SELECTED>iphash\n");
    foreach($atypes as $atkk => $atvv) print("<option value=\"$atvv\">$atvv\n");
    print("</select></td></tr>\n");
    print("<tr><td colspan=2> <input type=\"submit\" value=\"�����\"> </td></tr>  \n");
    print("</form>\n</table>\n ");
    print("<br> \n");
    if($_mode!="ipset") { 
	print("<br><hr width=\"400px\" align=left><br>");
	web_show_back("sets.php");
    }
}

#-------------------------------------------------------------------------


function sets_search($pstring="",$prun="")
{
    global $_ipset,$_sudo,$_grep,$iptconf_dir,$_mode;
    if( trim($prun)=="") {
	print("<br><br>  \n");
	print("<table class=table5e cellpadding=\"2px\" width=\"350px\"> \n");
	print("<form name=\"form324\" action=\"sets.php\">  \n");
	print("<input type=\"hidden\" name=\"mode\" value=\"lst_search\">  \n");
	print("<input type=\"hidden\" name=\"run\" value=\"1\">  \n");
	print("<tr><td colspan=2 class=top1>����� �� ���-������ </td></tr> \n");
	print("<tr><td class=td2>������:</td><td> <input type=\"text\" name=\"p1value\" size=30> </td></tr> \n");
	print("<tr><td colspan=2> <input type=\"submit\" value=\"�����\"> </td></tr>  \n");
	print(" </form>\n</table>\n");
	print("<br>  \n");
	if( $_mode!="ipset") { 
	    print("<hr width=\"400px\" align=left><br><br>");
	    web_show_back("sets.php");
	}
    } else {
	$setlistfile=$iptconf_dir."/ipsetlist";
	$aast=file($setlistfile);
	print("<br><br><br><font class=top1>���������� ������ \"$pstring\":<br><br></font><font class=text32>");
	$ccount=0;
	$arez=array();
	foreach($aast as $aastkk => $aastvv) {
	    if( trim($aastvv)=="") continue;
	    $rez="";
	    list($rv,$aa1)=exec("$_ipset -nL $aastvv");
	    if( $rv>0 ) { continue; } else {
		foreach($aa1 as $aa1kk => $aa1vv) {
		    if( substr_count($aa1vv,trim($pstring))>0) {
			$arez[$aa1kk]=trim($aastvv);
			$ccount++;
		    }
		}
	    }
	    unset($aa1);
	}
	print("������� ��������� - <b>$ccount</b>: <br><br>\n");
	foreach($arez as $arezkk => $arezvv) {
	    print("���-���� $arezvv, ������ $arezkk: <a href=\"sets.php?set=$arezvv&mode=listview&ref=show\">�������&#8594</a><br>\n");
	}
	
    
    
    }

}

#-------------------------------------------------------------------------


function set_addto($pset="",$paddr="",$prun="")
{
    global $iptconf_dir,$_ipset,$_grep,$_sudo,$sets_dir,$_set_save_onchange,$_p1name,$_p1value;
    global $_set_submit_query;

    if( trim($prun)=="") {
	print("<div style=\"padding-left:50px\">\n");
	print("<br><br><font class=top1>�������� � ���-����<br><br>\n");
	print("<form name=\"setaddto1\" action=\"sets.php\">\n");
	print("<input type=\"HIDDEN\" name=\"run\" id=run value=\"1\">\n");
	print("<input type=\"HIDDEN\" name=\"mode\" id=mode value=\"set_addto\">\n");
	print("<table class=table5d width=\"450px\" cellpadding=\"6px\">\n");
	print("<tr><td>�����:</td><td><input type=\"TEXT\" name=\"addr\" size=35 value=\"$paddr\"></td></tr>\n");
	print("<tr><td>���-����:</td><td>\n");

	$setlistfile=$iptconf_dir."/ipsetlist";
	if( !is_readable($setlistfile)) { 
	    wlog("���� $setlistfile ���������� ��� ������!",2,TRUE,5,TRUE); exit;
	}
	$asets=file($setlistfile);
	$aaloaded=array();
	foreach($asets as $askk => $asvv) {
	    $asvv=trim($asvv);
	    if( ($asvv==trim($pset)) or ($asvv=="locals")) continue;
	    $rez="";
	    $line="$_ipset -nL $asvv | $_grep Type";
	    list($rv,$aa3)=_exec2($line);
	    if( $rv==0) { 
		foreach($aa3 as $aa3kk => $aa3vv) {
		    $rez=trim(str_replace("Type:","",$aa3vv));
		    $aaloaded["$asvv"]="$asvv &nbsp&nbsp&nbsptype:$rez";
		}
	    }
	    unset($aa3); unset($rez); unset($rr);
	}
	if( count($aaloaded)>0) {
	    print("<select name=\"set\">\n");
	    foreach($aaloaded as $aaldkk => $aaldvv) {
		print("<option value=\"$aaldkk\">$aaldvv\n");
	    }
	    print("</select>\n");
	} else {
	    wlog("��� ����������� ���-������! ",1,TRUE,3,FALSE); exit;
	}
	unset($asets);
	unset($aaloaded);
	print("</td></tr>\n");
	print("<tr><td colspan=2 align=right><input type=\"SUBMIT\" name=\"sbmit\" id=sbmit value=\"��������\"></td></tr>\n");
	print("</table>\n</div>\n");
	print("<br><br><br><div class=text33 style=\"padding-left:380px\"><a href=\"javascript:window.close()\" onclick=\"window.close()\"><img src=\"icons/gtk-delete.gif\" title=\"�������\">�������</a></div>\n");
	exit;
    } else {

    if(trim($pset)=="") exit;
    
    $rez="";
    $line="$_ipset -nL $pset | $_grep Type";
    list($rr,$aa1)=_exec2($line);
    if( $rr>0) wlog("������ �������� �������� � set_binding(), �������� ������� �� ��������...<br>rr .$rr. rez .$rez. paddr .$paddr.<br> line: .$line.",1,TRUE,3,TRUE);
    if( count($aa1)==0) { wlog("������� $pset �� ��������...",2,TRUE,5,TRUE); exit; }
    unset($aa1);
    
	$paddr=trim($paddr);
	if( $paddr=="") {
	    wlog("������� �������� �� ����� ���� ������..",2,TRUE,4,TRUE); exit;
	}
	
	$line="$_ipset -A $pset $paddr";
	list($rr,$aa1)=_exec2($line);
	if( $rr>0) { wlog("������ ���������� �������� $paddr � ������� $pset...<br><br>debug: line .$line.<br>rr .$rr.",2,TRUE,5,TRUE); exit; }
	unset($aa1);

	if( $_set_save_onchange) {
	    $line="$_ipset -S $pset > $sets_dir/$pset";
	    list($rr,$aa1)=_exec2($line);
	    if( $rr>0) { wlog("������ ������ �������� $pset...<br><br>debug: line .$line.<br>rr .$rr.",2,TRUE,5,TRUE); exit; }
	    unset($aa1);
	}
	print("����� ������� ��������...<br><br><br>\n");
	wlog("���������� $paddr � ������� $pset",0,FALSE,1,FALSE);
	print("<br><br><br><div class=text33 style=\"padding-left:380px\"><a href=\"javascript:window.close()\" onclick=\"window.close()\"><img src=\"icons/gtk-delete.gif\" title=\"�������\">�������</a></div>\n");	

    }

}

#-----------------------------------------------------------------

?>