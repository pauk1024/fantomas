<?php
###
# Name: Fantomas Iptconf manager
# Version: 2.8
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: iptlib.php
# Description: 
# Version: 2.8.2.11
###



function _exit() 
{
    global $_lockfile,$iptconf_dir;
    global $_lck;
    global $_console;
    if( $_console) echo "\n";
    if( is_resource($_lck)) fclose($_lck); 
    if(file_exists($iptconf_dir."/".$_lockfile)) unlink($iptconf_dir."/".$_lockfile);
    exit;
}

#--------------------------------------------------------

function bytes2mega($_bytes="")
{
    $rez="";
    $mera=1024;
    if( trim($_bytes)=="") return("");
    if( (strlen($_bytes)>=4) and (strlen($_bytes)<=6)) {
	$_bytes=round($_bytes/1024,2);
	$rez="$_bytes kb";
    } elseif((strlen($_bytes)>6) and (strlen($_bytes)<=10)) {
	$_bytes=round(($_bytes/1024)/1024,2);
	$rez="$_bytes Mb";
    } elseif((strlen($_bytes)>10) and (strlen($_bytes)<=13)) {
	$_bytes=round((($_bytes/1024)/1024)/1024,2);
	$rez="$_bytes Gb";
    } elseif(strlen($_bytes)>13) {
	$_bytes=round(((($_bytes/1024)/1024)/1024)/1024,2);
	$rez="$_bytes Tb";
    } else {
	$rez=$_bytes." b";
    }
    return($rez);
}

#--------------------------------------------------------

function mega2bytes($_mb)
{
    $_rez="";
    $mera=1024;
    if( trim($_mb)=="") return("");
    $_mb=strtolower(str_replace(" ","",$_mb)); $_mb=str_replace("\t","",$_mb);
    if( $_mb[strlen($_mb)-1]=="m") { 
        $_qt=substr($_mb,0,-1);
        settype($_qt,"int");
        $_qt=($_qt*$mera)*$mera;
        $_rez="$_qt";
    } elseif( substr($_mb,strlen($_mb)-2)=="mb") { 
        $_qt=substr($_mb,0,-2);
        settype($_qt,"int");
        $_qt=($_qt*$mera)*$mera;
        $_rez="$_qt";
    } elseif( $_mb[strlen($_mb)-1]=="g") { 
        $_qt=substr($_mb,0,-1);
        settype($_qt,"int");
        $_qt=(($_qt*$mera)*$mera)*$mera;
        $_rez="$_qt";
    } elseif( substr($_mb,strlen($_mb)-2)=="gb") { 
        $_qt=substr($_mb,0,-2);
        settype($_qt,"int");
        $_qt=(($_qt*$mera)*$mera)*$mera;
        $_rez="$_qt";
    } elseif( $_mb[strlen($_mb)-1]=="k") { 
        $_qt=substr($_mb,0,-1);
        settype($_qt,"int");
        $_qt=$_qt*$mera;
        $_rez="$_qt";
    } elseif( substr($_mb,strlen($_mb)-2)=="kb") { 
        $_qt=substr($_mb,0,-2);
        settype($_qt,"int");
        $_qt=$_qt*$mera;
        $_rez="$_qt";
    } else {
	$_rez=$_mb;
    }
    return($_rez);
}

#--------------------------------------------------------
function getsyslog($filename)
{
    if( !($flog=fopen($filename,"a+"))) {
	print("Error creating file $filename"); exit;
    } else {
	if( !$rez1=fwrite($flog,"$_time INFO: startig new log...\n")) {
	    fclose($flog); print("Error writing to $filename file.."); exit;
	} else {
	    fclose($flog);
	}
    }	
    return($rez1);
}

#--------------------------------------------------------

function wlog($message,$mode=0,$show=TRUE,$level=3,$webed=TRUE)
{
    global $syslog, $_console, $_SESSION;
    global $_logs_enable,$_logs_level,$_logs_dir;
    global $_logs_log_maxsize;
    $webed=( $_console) ? FALSE : TRUE;
    $message=trim($message)."\n";
    if( $mode==0) {
	$_mod="INFO: ";
    } elseif( $mode==1) {
	$_mod="WARNING: ";
    } elseif( $mode==2) {
	$_mod="ERROR: ";
    }
    $_time=@strftime("%d-%m-%Y %T");
    $level=($level==5) ? 0 : $level;
    $level=($level==4) ? 1 : $level;
    $level=($level==3) ? 2 : $level;
    $level=($level==2) ? 3 : $level;
    $level=($level==1) ? 4 : $level;
    $level=($level==0) ? 5 : $level;
	if( $show) {
	    $line=($webed) ? "<font style=\"FONT: normal 10pt Tahoma;color:696969;\"><b>$_mod</b> $message</font><br>\n" : "$_mod $message";
	    print($line);
	}
	if( !$_logs_enable) return( TRUE);
	if( $_logs_level >= $level) return( TRUE);
	$logfile=$_logs_dir."/".$syslog;
	if( !file_exists($logfile)) {
	    getsyslog($logfile);
	} else {
	    if( !is_writable($logfile)) {
		print("Error write access to file $syslog"); exit;
	    }
	    if( mega2bytes($_logs_log_maxsize)<=filesize($logfile)) {
		rename($logfile,$logfile.".".strftime("%Y%m%d%H%S"));
		getsyslog($logfile);
	    }
	}
	$slog=fopen($logfile,"a");
	$puser=( isset($_SESSION["user"])) ? $_SESSION["user"] : "Unknown";
	$remoteaddr=( isset($_SERVER["REMOTE_ADDR"])) ? $_SERVER["REMOTE_ADDR"] : "N/A";
	$src=( isset($_SERVER["PHP_SELF"])) ? $_SERVER["PHP_SELF"] : "N/A";
	$line="$_time user:$puser IP:$remoteaddr Src:$src $_mod $message";
	$_result=( fwrite($slog,$line)) ? TRUE : FALSE;
	fclose($slog);
    return(( $_result != FALSE) ? TRUE : FALSE);
}

#------------------------------------------------------------------

function rebytes($_mb1)
{
    return(bytes2mega(mega2bytes($_mb1)));
}

#--------------------------------------------------------

function _trimline($str,$sep=" \t")
{
    $_fl=FALSE;
    $_ddstr=strlen($str);
    $_line="";
    $str=str_replace("\t"," ",$str);
    for($_dd=1; $_dd<=$_ddstr; $_dd++) {
	$_dd0=$_dd-1;
	if( substr_count($sep,$str[$_dd0]) !=0) {
	    if( $_dd0==0) { 
		$_line.="$str[$_dd0]"; 
	    } else {
		if( substr_count($sep,$str[$_dd0-1]) ==0) {
		    $_line.="$str[$_dd0]";
		}
	    }
	} else { $_line.="$str[$_dd0]"; }
    }

return(trim($_line));

}
#--------------------------------------------------------

function gettok($str,$pos,$sep=" \t")
{
    if( $str=="") return("");
    $_fl=FALSE;
    for( $_dd=1; $_dd <=strlen($sep); $_dd++) {
	if( substr_count($str,$sep[$_dd-1]) !=0) { $_fl=TRUE; break; }
    }
    if( ! $_fl) { 
	if( $pos==1) return($str);
    }

    $_wpos=1;
    $_word="";
    $_result="";
    $_dd=0;

    while( TRUE) {
	$_dd1=$_dd+1;
	if( substr_count($sep,$str[$_dd]) !=0) {
	    if( $_wpos==$pos) { 
		$_result=$_word; break; 
	    } else {
		$_wpos++; 
		$_word="";
	    }
	} else { $_word="$_word$str[$_dd]"; }

	if( ($_dd1)==strlen($str)) { $_result=( $_wpos==$pos) ? "$_word" : "$_result"; break; }
	$_dd++;
    }
    return(trim($_result));
}

#----------------------------------------------------------

function gettok2($str,$pos,$sep=" \t",$flnochop=FALSE)
{
    if( $str=="") return("");
    $str=trim($str);
    $rez=""; 
    if( !$flnochop) {
	if( strlen($sep)>1) {
	    $asep=str_split($sep);
	    foreach($asep as $asepkk => $asepvv) {
		if( $asepkk==0) continue;
		$str=str_replace($asepvv,$asep[0],$str);
	    }
	}
    }
    if( substr_count($str,$sep[0])==0) {
	$rez=( $pos==1) ? $str : $rez;
    } else {
	$aa=explode($sep[0],$str);
	if( $pos<=count($aa)) {
	    $rez=$aa[$pos-1];
	}
    }
    return($rez);	
}
# странно: gettok2 отрабатывает в среднем за 2-5мсек, в то время как gettok - за 0.0001мсек...
# замерял даже при закомментированном первом условии с циклом - то же самое...
# поэтому пока юзаем gettok.
#----------------------------------------------------------

function coltoks($str,$sep=" \t")
{
    $_fl=FALSE;
    for( $_dd=1; $_dd <=strlen($sep); $_dd++) {
	if( substr_count($str,$sep[$_dd-1]) !=0) { $_fl=TRUE; break; }
    }
    $_result=1;
    if( ! $_fl) return($_result);
    
    for($_dd=1; $_dd<=strlen($str); $_dd++) {
	if( substr_count($sep,$str[$_dd-1]) !=0) {
	    $_result++;
	    continue;
	}
    }

    return($_result);
}

#----------------------------------------------------------

function mysql_getlink()
{
    global $mysql_host,$mysql_user,$mysql_password,$mysql_fantomas_db;
    $link=mysql_connect($mysql_host,$mysql_user,$mysql_password)
        or die("Error connecting to mysql!");
    if( function_exists("mysql_set_charset")) mysql_set_charset("koi8r",$link);
    mysql_select_db($mysql_fantomas_db);
    return($link);
}
#-----------------------------------------------------------------------

function _exec($line1)
{
    global $_console, $_sudo, $_exec_errlevel;
    $_exec_errlevel=( !isset($_exec_errlevel)) ? 0:$_exec_errlevel;
    $line1=( !$_console) ? "$_sudo $line1" : $line1;
    $br=( !$_console) ? "<br>" : "";
    $rez=exec($line1,$out1,$rv);
    if($rv>$_exec_errlevel) {
	print("--error debug:\n$br rv .$rv.\n$br line1 .$line1. \n$br out: \n$br");
	foreach( $out1 as $kk => $vv0) print("---out1: .$vv0.\n$br");
	print("rez: .$rez.\n---end error----\n$br");
    }

    return($rv);
}
#---------------------------------------------------------

function _exec2($pline,$nosudo=FALSE,$multi=FALSE,$silent=FALSE)
{
    global $ssh_host,$ssh_port,$ssh_user,$ssh_pass,$_SESSION,$ssh_enable;
    global $_console,$_sudo,$_exec_errlevel;
    if( !$multi) {
	if( !trim($pline)) return(FALSE);
	$acmd=array(0 => $pline);
    } else {
	if( count($pline)==0) return(FALSE);
	$acmd=$pline;
	foreach($acmd as $akey => $aval) if( !trim($aval)) unset($acmd[$akey]);
    }
    $_via_ssh=( $_console) ? FALSE:$ssh_enable;
    if( $_via_ssh) {
        if( !$link=ssh2_connect($ssh_host,$ssh_port)) { 
	    wlog("Error connecting via to $ssh_host:$ssh_port",2,TRUE,5); exit; 
	}
	if( !@ssh2_auth_password($link,$ssh_user,$ssh_pass)) { 
	    wlog("SSH login/password error while connecting to $ssh_host:$ssh_port using .$ssh_user./.$ssh_pass.",2,TRUE,5); exit; 
	}
    }
    foreach($acmd as $akey => $line) {
        if( !$nosudo) $line=( !$_console) ? $_sudo." ".$line:$line;
	$br=( !$_console) ? "<br>" : "";
        if( $_via_ssh) {
    	    if(isset($buf)) unset($buf);
    	    if(isset($lstrim)) unset($lstrim);
	    if( !$lstrim=ssh2_exec($link,$line."; echo \"rv:\$?\"")) { 
		wlog("Error running command ($line) via ssh on $ssh_host:$ssh_port",2,TRUE,5); return(FALSE); 
	    }
#	    exec($_ssh." ".$line,$buf,$rv);
	    stream_set_blocking($lstrim,TRUE);
	    if( !$multi) {
		foreach($buf=explode("\n",stream_get_contents($lstrim)) as $key => $val) if( !trim($val)) unset($buf[$key]);
	    } else {
		$buf=explode("\n",stream_get_contents($lstrim));
	    }
#	    foreach($buf as $key => $val) if( !trim($val)) unset($buf[$key]);
	    $rv=str_replace("rv:","",$buf[max(array_keys($buf))]); unset($buf[max(array_keys($buf))]);
	} else {
	    exec($line,$buf,$rv);
	    if( !$multi) {
		foreach($buf as $key => $val) if( !trim($val)) unset($buf[$key]);
	    }
	}
	if(($rv>$_exec_errlevel) && ( !$silent)) {
	    print("--error debug:\n$br rv .$rv.\n$br line .$line. \n$br out: \n$br");
	    foreach($buf as $kk => $vv0) print("---buf: .$vv0.\n$br");
	    print("---end error----\n$br");
	}
    }
    return(array($rv,$buf));
	    
}
#---------------------------------------------------------
function mark_value_conv($value="")
{
#   DO NOT EDIT This one: 
    $keys="abcdefghijklmnopqrstuvwxyz";
    $value=str_replace("0x","",str_replace("/0xffffffff","",$value));
    $rez=(( !$pos=strpos($keys,$value)) ? $value : (10+$pos));
    return($rez);
}

#---------------------------------------------------------
function kidpolicy($policy,$retparsed=FALSE,$_group="")
{
    global $users_dir;
    global $iptconf_dir;
    $_multiport=FALSE; $_flinv=FALSE; $_proto=""; $_ports=""; $_quota=""; $_manglemark="";
    
    if( !trim($policy)) return(FALSE);
    
    if( ! file_exists("$iptconf_dir/policies")) { wlog("Policies config is not found",2,TRUE); exit; }
    if( ! is_readable("$iptconf_dir/policies")) { wlog("Error opening file policies in findpolicy()",2,TRUE); exit; }

	

    $_pfile=fopen("$iptconf_dir/policies","r");
    $_flpl=FALSE; $_flacc=FALSE;

#	так, где наша политика?
    $fl=FALSE;
    $flin=FALSE;
    $strnum=0;
    $_string="";
    $_multiport1=FALSE;
    $_flinv1=FALSE;
    $_quota1="";
    $_proto1="";
    $_ports1="";
    $_manglemark1="";

    while( TRUE) {
      if( feof($_pfile)) break;
      $_str1=strtolower(trim(fgets($_pfile))); $strnum++;
      if( !trim($_str1)) continue;
      if( $_str1[0] =="#") continue;	
      $_word1=gettok($_str1,1," \t");
      $_word2=gettok($_str1,2," \t");
      if( !$flin) {
        if( $_word1 =="policy") { 
	    if( $_word2 == $policy) { $flin=TRUE; continue; }
        } 
      } else {

	$buf1=coltoks($_str1," \t");

	if( $_word1 =="}") {
	    $flin=FALSE; break;
	} elseif($_word1 =="accept") {
	    $_proto1=gettok($_str1,2," \t");
		
	    for($_kk=3; $_kk<=$buf1; $_kk++) {
		$_tmp2=gettok($_str1,$_kk," \t");
		if( ($_tmp2 =="dst") or ($_tmp2 =="src")) {
#			похуй, дирекшн не учавствует в поиске политики, но переписывать влом
		} elseif( $_tmp2=="quota") {
		    $_kk++;
		    $_quota1=gettok($_str1,$_kk," \t");
		} elseif( ($_tmp2=="to") or ($_tmp2=="from")) {
		    $_kk++;
		} else {
		    if( $_tmp2[0]=="(") { 
			$_multiport1=TRUE;
			$_tmp2=str_replace("(","",$_tmp2);
			$_ports1=trim(str_replace(")","",$_tmp2));
		    } else { $_ports1=$_tmp2; }
		    if( $_ports1[0]=="!") {
			$_flinv1=TRUE;
			$_ports1=trim(substr($_ports1,1));
		    }
		}
	    }
	} elseif( ($_word1=="in") or ($_word1=="out")) {
	    $_dir1=$_word1;
	}
      }
    }
    $_manglemark1=get_policy_param($policy,"manglemark");
		
    $_tmp01=str_replace(",","",$_ports1);
    $_tmp01=( $_flinv) ? "!$_tmp01" : "$_tmp01";
    if( !$retparsed) { $_rez="$policy$_proto1$_tmp01"; } else {
	$_rez=array("policyname" => $policy, "proto" => $_proto1, "ports" => ((!$_flinv1) ? "" : "! ")."$_ports1", "quota" => $_quota1, "manglemark" => $_manglemark1);
    }
    fclose($_pfile);
    return($_rez);


}


#---------------------------------------------------------
function findpolicy($str,$ip,$retparsed=FALSE,$_group="",$policy="")
{
    global $users_dir;
    global $iptconf_dir;
    $_multiport=FALSE; $_flinv=FALSE; $_proto=""; $_ports=""; $_quota=""; $_manglemark="";

    if( $ip=="") return("");
    if( $ip=="0.0.0.0/0") return("");
    if( $str =="") return(""); 

    if( ! file_exists("$iptconf_dir/policies")) { wlog("Policies config is not found",2,TRUE); exit; }
    if( ! is_readable("$iptconf_dir/policies")) { wlog("Error opening file policies in findpolicy()",2,TRUE); exit; }

    if( !$policy) {
	
	$link=mysql_getlink();
	$line="SELECT clients.ip, clients.group_id, clients.policies, clients.cname, (SELECT groups.name FROM groups WHERE groups.id=clients.group_id) AS grpname, (SELECT groups.default_policy FROM groups WHERE groups.id=clients.group_id) AS defpolicy FROM clients WHERE clients.ip=\"$ip\"";
	if( !$res=mysql_query($line)) {
	    wlog("Error quering groups list in findpolicy()!",2); exit;
	}
	if( mysql_num_rows($res)==0) {
	    wlog("Client with IP $ip is not found!",2,TRUE,5,TRUE); return(FALSE);
	}
    
	$_pols=array(); $_flok=FALSE; $_defpolicy="";
	$row=mysql_fetch_array($res);
	$_defpolicy=$row["defpolicy"];
	$_id=$row["group_id"];
	$_pols=( !trim($row["policies"])) ? array("0" => $_defpolicy):explode(",",trim($row["policies"]));

    } else { 
	$_pols[0]=$policy; 
    }


    $_proto=gettok($str,4," \t");
    $_tmp10=gettok($str,10," \t");
    $_tmp11=gettok($str,11," \t");
    $_tmp12=gettok($str,12," \t");
    $_tmp13=gettok($str,13," \t");
    $_tmp13=gettok($str,14," \t");
	
    $_rez="";
    if( $_tmp10=="multiport") { 
	$_multiport=TRUE;
	if( gettok($str,12," \t")=="!") {
	    $_flinv=TRUE;
	    $_ports=gettok($str,13," \t");
	    if( gettok($str,14," \t")=="quota:") $_quota=gettok($str,15," \t");
	} else { 
	    $_ports=gettok($str,12," \t"); 
	    if( $_ports[0]=="!") {
		$_flinv=TRUE;
		$_ports=substr($_ports,1);
	    }
	    if( gettok($str,13," \t")=="quota:") $_quota=gettok($str,14," \t");
	}

	$_tmpports=$_ports;
    } elseif( $_tmp10==$_proto) {
	$_tmp21=gettok($str,11," \t");
	$_tmp22=gettok($_tmp21,1,":");
	if( ($_tmp22=="dpt") or ($_tmp22=="spt")) {
	    $_ports=gettok($_tmp21,2,":");
	    if( $_ports[0]=="!") {
		$_flinv=TRUE;
		$_ports=substr($_ports,1);
	    }
	    $_tmp12=$_ports;
	    $_tmp13=$_ports;
	}
	if( gettok($str,12," \t")=="quota:") $_quota=gettok($str,13," \t");
    } elseif( $_tmp10=="quota:") {
	$_quota=gettok($str,11," \t");
    }

    $_tmpstr=explode(" ",_trimline($str," \t"));
    foreach($_tmpstr as $_tmpstrkk => $_tmpstrvv) {
	if( $_tmpstrvv == "ports") {
	    $_flinv=( $_tmpstr[$_tmpstrkk+1]=="!") ? TRUE : $_flinv;
	    $_ports=( $_tmpstr[$_tmpstrkk+1]=="!") ? $_tmpstr[$_tmpstrkk+2] : $_tmpstr[$_tmpstrkk+1];
	}
	if( $_tmpstrvv == "quota:") {
	    $_quota=$_tmpstr[$_tmpstrkk+1];
	} elseif(( strtolower($_tmpstrvv) == "mark") or ( strtolower($_tmpstrvv) == "connmark")) {
	    $_manglemark=mark_value_conv($_tmpstr[$_tmpstrkk+2]);
	}
	    
    }


    foreach($_pols as $_ii0 => $_vv0) {
	
	$_pfile=fopen("$iptconf_dir/policies","r");
	$_flpl=FALSE; $_flacc=FALSE;
	$_pol="";

#	так, где наша политика?
	$fl=FALSE;
	$strnum=0;
	$_string="";
	while( TRUE) {
	    if( feof($_pfile)) break 1;
	    $_string=strtolower(trim(fgets($_pfile))); $strnum++;
	    if( strlen($_string) ==0) continue 1;
	    if( $_string[0] =="#") continue 1;
	    if( coltoks($_string," \t") > 1) {
		if( gettok($_string,1," \t") !="policy") { continue 1; } else {
		    if( gettok($_string,2," \t") == $_vv0) { $fl=TRUE; break 1; } else { 
			continue 1; }
		}
	    }
	}
	if( ! $fl) { wlog("Policy for client $ip is not found!",2); _exit(); }
#       это ж надо умудриться проебать политику :) ладно, поехали дальше)
	while( ! feof($_pfile)) {
	    $_str1=trim(strtolower(fgets($_pfile)));
	    $buf1=coltoks($_str1," \t");
	    $_word1=gettok($_str1,1," \t");
	    $_pol=$_vv0;
	    $_multiport1=FALSE;
	    $_flinv1=FALSE;
	    $_quota1="";
	    $_proto1="";
	    $_ports1="";
	    $_manglemark1="";

	    if( $_word1 =="}") {
		break 1;
	    } elseif($_word1 =="accept") {
		$_proto1=gettok($_str1,2," \t");
		
		for($_kk=3; $_kk<=$buf1; $_kk++) {
		    $_tmp2=gettok($_str1,$_kk," \t");
		    if( ($_tmp2 =="dst") or ($_tmp2 =="src")) {
#			похуй, дирекшн не учавствует в поиске политики, но переписывать влом
		    } elseif( $_tmp2=="quota") {
			$_kk++;
			$_quota1=gettok($_str1,$_kk," \t");
		    } elseif( ($_tmp2=="to") or ($_tmp2=="from")) {
			$_kk++;
		    } else {
			if( $_tmp2[0]=="(") { 
			    $_multiport1=TRUE;
			    $_tmp2=str_replace("(","",$_tmp2);
			    $_ports1=trim(str_replace(")","",$_tmp2));
			} else { $_ports1=$_tmp2; }
			if( $_ports1[0]=="!") {
			    $_flinv1=TRUE;
			    $_ports1=trim(substr($_ports1,1));
			}
		    }
		}
	    } elseif( ($_word1=="in") or ($_word1=="out")) {
		$_dir1=$_word1;
	    }

	    $_manglemark1=get_policy_param($_pol,"manglemark");;
		$ir=0;
		if( $_multiport==$_multiport1) $ir++; 
#		if( $_manglemark1!="") {
#		    if( $_manglemark==$_manglemark1) $ir++;
#		} else { $ir++; }
		$ir++;
		if( $_proto==$_proto1) $ir++;
		if( $_flinv==$_flinv1) $ir++;
		if( $_ports==$_ports1) $ir++;
		if( trim($_quota)!="") {
		    if(trim($_quota1)!="") $ir++;
		} else { $ir++; }
		


		
		if( $ir==6) {
		    $_flacc=TRUE; 
		    $_tmp01=str_replace(",","",$_ports);
		    $_tmp01=( $_flinv) ? "!$_tmp01" : "$_tmp01";
		    if( !$retparsed) { $_rez="$_pol$_proto$_tmp01"; } else {
			$_rez=array("policyname" => $_pol, "proto" => $_proto, "ports" => ((!$_flinv) ? "" : "! ")."$_ports", "quota" => $_quota, "manglemark" => $_manglemark);
		    }
		    break;
		}

	}
	fclose($_pfile);
    }
    return($_rez);


}

#----------------------------------------------------------

function parse_line_counts($pline,$nopolicy=FALSE,$grp="",$pol="")
{
    $traf=array();
    $traf["pkts"]=gettok($pline,1," \t"); 
    $traf["bytes"]=gettok($pline,2," \t");
    $traf["src"]=gettok($pline,8," \t");
    $traf["dst"]=gettok($pline,9," \t");
    $traf["policy"]=( !$nopolicy) ? findpolicy($pline,((($traf["dst"]=="0.0.0.0/0") or ($traf["dst"]=="")) ? $traf["src"] : $traf["dst"]),TRUE,$grp,$pol) : "";
	
    return($traf);
}

#-------------------------------------------------------------
function parseComment($pline,$fmode=1)
{
    $ct=coltoks($pline," \t"); $sin=FALSE; $bufpline=""; $bufcomment="";
    for($ii=1;$ii<=$ct;$ii++) {
	$bufword=trim(gettok($pline,$ii," \t"));
	if( $bufword=="/*") { 
	    $sin=TRUE; continue; 
	} elseif( $bufword=="*/") {
	    $sin=FALSE; continue; 
	} else {
	    if( !$sin) { 
		$bufpline=$bufpline." ".$bufword;
	    } else {
		$bufcomment=(( $bufcomment) ? $bufcomment:"").$bufword;
	    }
	}
    }
    if( $fmode==1) {
	return($bufcomment);
    } elseif( $fmode==2) {
	return($bufpline);
    } elseif( $fmode==3) {
	return( array( "line" => $bufpline, 
			"comment" => $bufcomment,
			"client" => trim(substr($bufcomment,strrpos($bufcomment,"_")+1)),
			"policy" => trim(substr($bufcomment,0,strrpos($bufcomment,"_")))
			));
    }
}
#-------------------------------------------------------------
function process_counters($ip="",$grp="")
{
    global $iptconf_dir,$_iptables,$_grep,$_sudo,$_console;
    
#    $line1=(( !$_console) ? "$_sudo " : "")."$_iptables -t mangle -nL COUNT_IN -v -x".(($ip!="") ? " | awk '\$0 ~ /".$ip."[ \t]/ { print \$0 }'" : "");
#    $line2=(( !$_console) ? "$_sudo " : "")."$_iptables -t mangle -nL COUNT_OUT -v -x".(($ip!="") ? " | awk '\$0 ~ /".$ip."[ \t]/ { print \$0 }'" : "");

    $line1="$_iptables -t mangle -nL COUNT_IN -v -x".(($ip!="") ? " | awk '\$0 ~ /".$ip."[ \t]/ { print \$0 }'" : "");
    $line2="$_iptables -t mangle -nL COUNT_OUT -v -x".(($ip!="") ? " | awk '\$0 ~ /".$ip."[ \t]/ { print \$0 }'" : "");
    list($rin,$tmp_in)=_exec2($line1);
    list($rout,$tmp_out)=_exec2($line2);
    if( ($rin!=0) or ($rout!=0)) { 
	return(FALSE); 
    }
    
    
    $in_str=($ip=="") ? 2 : 0; 
    $out_str=($ip=="") ? 2 : 0; 
    $inline=array(); $outline=array(); 
    $reztraf=array();
    $num=0;
    $flfeof=(($in_str==count($tmp_in)) or ($out_str==count($tmp_out))) ? TRUE : FALSE;
    while( !$flfeof ) {
    
#	Читаем строки из вывода команд, вырезаем комментарии
	 
	while( $in_str<=count($tmp_in)) {
	$bufcomment1="";
	    $_string1=_trimline($tmp_in[$in_str]," \t");
	    $ct=coltoks($_string1," \t"); $sin=FALSE; $bufstring1="";
	    for($ii=1;$ii<=$ct;$ii++) {
		$bufword=trim(gettok($_string1,$ii," \t"));
		if( $bufword=="/*") { 
		    $sin=TRUE; continue; 
		} elseif( $bufword=="*/") {
		    $sin=FALSE; continue; 
		} else {
		    if( !$sin) { 
			$bufstring1=$bufstring1." ".$bufword;
		    } else {
			$bufcomment1=(( $bufcomment1) ? $bufcomment1:"").$bufword;
		    }
		}
	    }
	    $_string1=_trimline($bufstring1," \t");
	    $in_str++;
	    if(trim($_string1)=="") continue 1;
	    if($_string1[0] == "#") continue 1;
	    break 1;
	}

	while( $out_str<=count($tmp_out)) {
	$bufcomment2="";
	    $_string2=_trimline($tmp_out[$out_str]," \t"); 
	    $ct=coltoks($_string2," \t"); $sin=FALSE; $bufstring1="";
	    for($ii=1;$ii<=$ct;$ii++) {
		$bufword=trim(gettok($_string2,$ii," \t"));
		if( $bufword=="/*") { 
		    $sin=TRUE; continue; 
		} elseif( $bufword=="*/") {
		    $sin=FALSE; continue; 
		} else {
		    if( !$sin) {
			$bufstring1=$bufstring1." ".$bufword;
		    } else {
			$bufcomment2=(( $bufcomment2) ? $bufcomment2:"").$bufword;
		    }
		}
	    }
	    $_string2=_trimline($bufstring1," \t");
	    $out_str++;
	    if(trim($_string2)=="") continue 1;
	    if($_string2{0} == "#") continue 1;
	    break 1;
	}
	
	if( count($inline)!=0) for( $ii=0; $ii<=count($inline); $ii++) unset($inline[$ii]);
	if( count($outline)!=0) for( $ii=0; $ii<=count($outline); $ii++) unset($outline[$ii]);
	
	list($bufrv,$bufout)=_exec2("cat $iptconf_dir/policies | grep 'policy ".($commpol=substr($bufcomment1,0,strrpos($bufcomment1,"_")))." '");
#	if( !$bufret=exec("$_sudo cat $iptconf_dir/policies | grep 'policy ".($commpol=substr($bufcomment1,0,strrpos($bufcomment1,"_")))." '")) {
	if( $bufrv==0) {
	    $inline=parse_line_counts($_string1,FALSE,$grp);
	} else {
	    $inline=parse_line_counts($_string1,FALSE,$grp,$commpol);
	    
	}
	$outline=parse_line_counts($_string2,TRUE,$grp);
	
	if( (trim($inline["dst"])=="0.0.0.0/0") and (trim($inline["src"])=="0.0.0.0/0")) continue;
	if( (trim($outline["dst"])=="0.0.0.0/0") and (trim($outline["src"])=="0.0.0.0/0")) continue;
	
	if($inline["dst"] != $outline["src"]) { wlog("IN/OUT statictics compare error, perhaps an some error in COUNTs chains rules.",1); }


	$reztraf[$num]=array("in_pkts" => $inline["pkts"], "in_bytes" => $inline["bytes"], "out_pkts" => $outline["pkts"], "out_bytes" => $outline["bytes"], "ip" => $inline["dst"], "policy" => $inline["policy"]);
	$num++;
	if( ($in_str==count($tmp_in)) or ($out_str==count($tmp_out))) { $flfeof=TRUE; break; }
    }


    return($reztraf);

}

#-------------------------------------------------------------

function open_new_period()
{
    global $_iptables,$_awk;
    global $statfile;
    global $mysql_host,$mysql_user,$mysql_password;
    if ( !isset($statfile)) $statfile="counters";
    
    $link1=mysql_connect($mysql_host,$mysql_user,$mysql_password);
    if( !$link1) { wlog("Error connecting to mysql in iptlib.php/open_new_period()..",2,TRUE,5,TRUE); exit; }
    mysql_select_db("fantomas");
    
    counts_export();
    if( file_exists($statfile)) { 
	rename($statfile,strftime("counters%Y%m%d%H%M.bkp"));
    } else { wlog("Error accessing file $statfile",2); _exit(); }
    _exec2(array("$_iptables -t mangle -Z PREROUTING",
		"$_iptables -t mangle -Z FORWARD",
		"$_iptables -t mangle -Z COUNT_IN",
		"$_iptables -t mangle -Z COUNT_OUT"),FALSE,TRUE);
    
    $rez1=mysql_query("UPDATE fantomas SET p_start_date=CURRENT_TIMESTAMP()  ");
    mysql_close($link1);
    wlog("Открытие нового периода через open_new_period()",0,FALSE,1,FALSE);

}
#-----------------------------------------------------------



function counts_export($flweb=FALSE,$flsilent=FALSE)
{
    global $_iptables,$_awk;
    global $_tmpbuf; 
    global $_console;
    global $iptconf_dir;
    global $backup_dir;
    
    
    if( getcwd()!=$iptconf_dir) if( !chdir($iptconf_dir)) { 
	wlog("Error changing directory to $iptconf_dir in fillconf()..",2,TRUE,5,TRUE); exit; 
    }
    
    $statfile="counters";
    if( file_exists($statfile)) {
	$tfname="counters".strftime("%Y%m%d%H%M").".bkp";
	_exec2("mv $iptconf_dir/$statfile $backup_dir/$tfname");
    }
    $stat=fopen($statfile,"w");
    
    if( ! $flsilent) {
	if( $_console) echo "Saving counters...";
    }
    
    list($rin,$tmp_in)=_exec2("$_iptables -t mangle -nL COUNT_IN -v -x");
    list($rout,$tmp_out)=_exec2("$_iptables -t mangle -nL COUNT_OUT -v -x");
    if( ($rin!=0) or ($rout!=0)) { wlog("Error running iptables command in counts_export()",2,TRUE,5,TRUE); exit; }
    
    $in_str=2; $out_str=2;
    $flfeof=(($in_str==count($tmp_in)) or ($out_str==count($tmp_out))) ? TRUE : FALSE;
    while( !$flfeof ) {
	while( $in_str<count($tmp_in)) {
	    $_string1=_trimline($tmp_in[$in_str]," "); $in_str++;
	    if(strlen($_string1) == 0) continue 1;
	    if($_string1[0] == "#") continue 1;
	    break;
	}
	while( $out_str<count($tmp_out)) {
	    $_string2=_trimline($tmp_out[$out_str]," "); $out_str++;
	    if(strlen($_string2) == 0) continue 1;
	    if($_string2{0} == "#") continue 1;
	    break;
	}
	if( ($in_str==count($tmp_in)) or ($out_str==count($tmp_out))) { $flfeof=TRUE; break; }
	$statstr="";
	$buf_pkts=""; $buf_bytes=""; $buf_src=""; $buf_dst="";
	$buf_pkts=gettok($_string1,1," \t");
	$buf_bytes=gettok($_string1,2," \t");
	$buf_src=gettok($_string1,8," \t");
	$buf_dst=gettok($_string1,9," \t");;
	if( (trim($buf_dst)=="0.0.0.0/0") and (trim($buf_src)=="0.0.0.0/0")) continue;
	$_bufdst=$buf_dst;
	if( isset($aastr)) unset($aastr);
	$aastr=parseComment($_string1,3);
	if(( !$aastr["client"]) or ( !$aastr["policy"])) {
	    $_policy=findpolicy($_string1,$buf_dst);
	} else {
	    $buf_dst=$aastr["client"];
	    $_policy=findpolicy($_string1,$aastr["client"],FALSE,"",$aastr["policy"]);
	}
	$statstr="$buf_dst; policy:$_policy; in($buf_pkts/$buf_bytes);";
	$buf_pkts=""; $buf_bytes=""; $buf_src=""; $buf_dst="";
	$buf_pkts=gettok($_string2,1," \t");
	$buf_bytes=gettok($_string2,2," \t");
	$buf_src=gettok($_string2,8," \t");
	$buf_dst=gettok($_string2,9," \t");;
	if( (trim($buf_dst)=="0.0.0.0/0") and (trim($buf_src)=="0.0.0.0/0")) continue;
	
	if($_bufdst <> $buf_src) { 
	    wlog("debug: <br> string1: .$_string1. <br>string2 .$_string2.<br> buf_src $buf_src _bufdst $_bufdst<br>\nIN/OUT statictics compare error, perhaps an some error in COUNTs chains rules.",2,TRUE,5,TRUE); exit; 
	}
	$statstr="$statstr out($buf_pkts/$buf_bytes);\n";
#	if( ! fwrite($stat,$statstr)) { wlog("Can't write to $statfile, exiting.",2,TRUE,5,TRUE); exit; }
	if( ! fwrite($stat,$statstr)) { wlog("Can't write to $statfile..",2,TRUE,5,TRUE); }
    }

    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM networks WHERE 1")) {
    	wlog("Error loading networks list...",2,TRUE,5,FALSE); exit; 
    }
    $anets=array();

    while( $row=mysql_fetch_array($res)) {
    	$bufnet=$row["addr"];
        if( isset($aa)) unset($aa);
	$cmd="$_iptables -t mangle -nL -v -x | $_awk '$0~/_".gettok($bufnet,1,"/")."_/&&/RETURN/ {print($1\",\"$2\",\"$11);}'";
	list($rr,$aa)=_exec2($cmd,FALSE,FALSE);
	    	        	    
	if(( $rr!=0) || ( count($aa)==0))
	    continue;

	$all_pkts_in=""; $all_bytes_in=""; $all_pkts_out=""; $all_bytes_out="";
	$loc_pkts_in=""; $loc_bytes_in=""; $loc_pkts_out=""; $loc_bytes_out="";
	$fwd_pkts_in=""; $fwd_bytes_in=""; $fwd_pkts_out=""; $fwd_bytes_out="";
	foreach($aa as $line) {
	    $buf1=gettok($line,3,",");
	    $target=gettok($buf1,1,"_");
	    $dir=gettok($buf1,3,"_");
	    $bufnet=gettok($buf1,2,"_");
	    if( $target=="CNTNET") {
		if($dir=="DST") {
		    $all_pkts_in=gettok($line,1,",");
		    $all_bytes_in=gettok($line,2,",");
		} elseif($dir=="SRC") {
		    $all_pkts_out=gettok($line,1,",");
		    $all_bytes_out=gettok($line,2,",");
		}
	    } elseif( $target=="CNTNETF") {
		if($dir=="DST") {
		    $fwd_pkts_in=gettok($line,1,",");
		    $fwd_bytes_in=gettok($line,2,",");
		} elseif($dir=="SRC") {
		    $fwd_pkts_out=gettok($line,1,",");
		    $fwd_bytes_out=gettok($line,2,",");
		}
	    } elseif( $target=="CNTNETL") {
		if($dir=="DST") {
		    $loc_pkts_in=gettok($line,1,",");
		    $loc_bytes_in=gettok($line,2,",");
		} elseif($dir=="SRC") {
		    $loc_pkts_out=gettok($line,1,",");
		    $loc_bytes_out=gettok($line,2,",");
		}
	    }
	}
	$anets[]=array(
		"net" => $bufnet,
		"CNTNET" => array(
			"pkts_dst" => $all_pkts_in,
			"bytes_dst" => $all_bytes_in,
			"pkts_src" => $all_pkts_out,
			"bytes_src" => $all_bytes_out
		    ),
		"CNTNETF" => array(
			"pkts_dst" => $fwd_pkts_in,
			"bytes_dst" => $fwd_bytes_in,
			"pkts_src" => $fwd_pkts_out,
			"bytes_src" => $fwd_bytes_out
		    ),
		"CNTNETL" => array(
			"pkts_dst" => $loc_pkts_in,
			"bytes_dst" => $loc_bytes_in,
			"pkts_src" => $loc_pkts_out,
			"bytes_src" => $loc_bytes_out
		    )
	);
    }
    foreach($anets as $akey => $aaitem) {
	$anet=$aaitem["net"];
	$statstr=trim($anet)."; policy:CNTNET; in(".$aaitem["CNTNET"]["pkts_dst"]."/".$aaitem["CNTNET"]["bytes_dst"].") out(".$aaitem["CNTNET"]["pkts_src"]."/".$aaitem["CNTNET"]["bytes_src"].");\n";
	if( ! fwrite($stat,$statstr)) { wlog("Can't write to $statfile..",2,TRUE,5,TRUE); }
	$statstr=trim($anet)."; policy:CNTNETF; in(".$aaitem["CNTNETF"]["pkts_dst"]."/".$aaitem["CNTNETF"]["bytes_dst"].") out(".$aaitem["CNTNETF"]["pkts_src"]."/".$aaitem["CNTNETF"]["bytes_src"].");\n";
	if( ! fwrite($stat,$statstr)) { wlog("Can't write to $statfile..",2,TRUE,5,TRUE); }
	$statstr=trim($anet)."; policy:CNTNETL; in(".$aaitem["CNTNETL"]["pkts_dst"]."/".$aaitem["CNTNETL"]["bytes_dst"].") out(".$aaitem["CNTNETL"]["pkts_src"]."/".$aaitem["CNTNETL"]["bytes_src"].");\n";
	if( ! fwrite($stat,$statstr)) { wlog("Can't write to $statfile..",2,TRUE,5,TRUE); }
    }
    
    if( ! $flsilent) {
	if( $_console) echo "....Ok\n";
    }
    wlog("Экспорт счетчиков",0,FALSE,1,FALSE);

}

#-------------------------------------------------------------

function getprov($prname,$par)
{
    global $iptconf_dir;

    if( !trim($prname)) return(FALSE);
    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM providers WHERE name=\"$prname\"")) {
	wlog("Provider link $prname is not found!",2); _exit(); 
    }
    if( !$row=mysql_fetch_array($res)) return(FALSE);
    
    return(( isset($row[$par])) ? $row[$par]:FALSE);
}



#----------------------------------------------------------
function checkmarks($act,$mark,$line)
{
    global $iptconf_dir;
    $_rez=FALSE;
    if( file_exists("$iptconf_dir/tmp_nat")) {
	$_file=fopen("$iptconf_dir/tmp_nat","r+");
	$_str="";
	while( ! feof($_file)) {
	    $_str=trim(fgets($_file));
	    if( strlen($_str)==0) continue;
	    if( $_str[0]=="#") continue;
	    if( (gettok($_str,1,"@") == $act) and (gettok($_str,2,"@") == $mark) and (gettok($_str,3,"@"))) { $_rez=TRUE; break; }
	}
    } else {
	$_file=fopen("$iptconf_dir/tmp_nat","w");
	fwrite($_file,"#\n");
    }
    fclose($_file);
    if( ! $_rez) {
	$_file=fopen("$iptconf_dir/tmp_nat","a");
	$_rez=fwrite($_file,"$act@$mark@$line\n");
	fclose($_file);
    }
}
#----------------------------------------------------------

function getcounts($_ip,$_policykey,$_mode)
{
    global $_console,$iptconf_dir;
    $_rez="";
    $_ip1=""; $_policy1=""; $_pkts=""; $_bytes="";
    if( ! file_exists("$iptconf_dir/counters")) { wlog("Counters file is not found...",2); _exit(); }
    if( ! is_readable("$iptconf_dir/counters")) { wlog("Error opening counters.",2); _exit(); }
    if(($_ip=="") or ($_policykey=="")) return("");
    $_file=fopen("$iptconf_dir/counters","r");
    while( ! feof($_file)) {
	$_string=trim(fgets($_file));
	if( trim($_string)=="") continue;
	if( $_string[0]=="#") continue;
	$_ip1=str_replace(";","",gettok($_string,1," \t"));
	$_policy1=str_replace(";","",gettok(gettok($_string,2," \t"),2,":"));
	if( ( $_ip1 ==$_ip) and ( $_policy1 == $_policykey)) {
	    if( $_mode==1) {
		$_tmp1=gettok($_string,3," \t");
		$_tmp1=str_replace(")","",str_replace("in(","",$_tmp1));
	    } elseif( $_mode==2) {
		$_tmp1=gettok($_string,4," \t");
		$_tmp1=str_replace(")","",str_replace("out(","",$_tmp1));
	    }
	    $_tmp1=str_replace(";","",$_tmp1);
	    $_pkts=gettok($_tmp1,1,"/");
	    $_pkts=(( !trim($_pkts)) || ( $_pkts=="0")) ? "1":$_pkts; 
	    $_bytes=gettok($_tmp1,2,"/");
	    $_bytes=(( !trim($_bytes)) || ( $_bytes=="0")) ? "1":$_bytes;
	    $_rez="$_pkts $_bytes";
	    break;
	}
    }
    fclose($_file);
    return($_rez);
}


#----------------------------------------------------------
function _loadpolicy($_ip,$_policyname,$_remove=FALSE,$flinsert=FALSE,$return_lines=FALSE)
{
    global $_iptables,$_sudo,$_awk;
    global $_ipset;
    global $_console;
    global $_local_eth;
    global $iptconf_dir;
    global $keep_counts_at_polunload;
    
    wlog((( !$_remove) ? "Загрузка" : "Выгрузка")." политики $_policyname для клиента $_ip",0,FALSE,1,FALSE);
    
    if( !$_remove) {
	$acmd=(!$flinsert) ? "-A" : "-I"; 
	$icmd="-I";
    } else {
	$acmd="-D";
	$icmd="-D";
    }
    if( $_remove) {
	if( $keep_counts_at_polunload) counts_export(TRUE,TRUE);
    }
    
    if( ! file_exists("$iptconf_dir/policies")) { wlog("Policies config is not found...",2); _exit(); }
    if( ! is_readable("$iptconf_dir/policies")) { wlog("Error opening policies config.",2); _exit(); }
    if(($_ip=="") or ($_policyname=="")) return("");
    $aalines1=array();
    
    $_pfile=fopen("$iptconf_dir/policies","r");
#   так, где наша политика?
    $fl=FALSE;
    $strnum=0;
    while( TRUE) {
	if( feof($_pfile)) break;
	$_string=strtolower(trim(fgets($_pfile))); $strnum++;
	if( strlen($_string) ==0) continue;
	if( $_string[0] =="#") continue;
	if( coltoks($_string," \t") > 1) {
	    if( gettok($_string,1," \t") !="policy") { continue; } else {
		if( gettok($_string,2," \t") == $_policyname) { $fl=TRUE; break; } else { 
		    continue; }
	    }
	}
    }
    if( ! $fl) { wlog("Policy $_policyname for client $_ip is not found in _loadpolicy()!",2); _exit(); }
#   это ж надо умудриться проебать политику :) ладно, поехали дальше)
    $f_end=FALSE;
    $_accpt[0]=""; $_act=""; $_act_dst=""; $_inout=""; $_prov=""; 
    $_flCount=FALSE; $_manglemark=""; $_quote=""; $_blacklist[0]="";
    $_rjct[0]=""; $_flsmtpout=FALSE; $_dsport="";
    $_title=""; $_accpt_direct=""; $_flNolog=FALSE; $_allow_only=""; $flCount_local=FALSE;
#   отсюда начинаем собсна парсить политику
    $_ff=0;
    $_rr=0;
    $_multiport=FALSE;
    while( TRUE) {
	if( feof($_pfile)) break;
	$_string=strtolower(trim(fgets($_pfile))); $strnum++;
	if( $_string[0] =="#") continue;
	if( strlen($_string) ==0) continue;
	$buf1=coltoks($_string," \t");
	if( $buf1 ==1) {
	    if( $_string ==chr(125)) { $f_end=TRUE; break; 
	    } elseif( $_string=="out") {
		$_inout=$_string;
	    } elseif( $_string =="count") {
		$_flCount=TRUE;
		if( gettok($_string,2," \t")=="nolog") $_flNolog=TRUE;
	    } elseif( $_string =="allow_smtp_out") {
		$_flsmtpout=TRUE;
	    } else {
		wlog("Sintax error in Policies config at line $strnum.",1);
		continue;
	    }
	} elseif( $buf1 > 1) {
		$_tmp1=gettok($_string,1," \t");
		if($_tmp1 == "accept") {
		    $_tmp2=gettok($_string,2," \t");
		    if( ($_tmp2 !="tcp") and ($_tmp2 !="udp") and ($_tmp2 !="icmp") and ($_tmp2 !="all")) { 
			wlog("Sintax error in Policies config at line $strnum",1); continue; }
		    $buf2="$_tmp2";
		    $_f_ports=""; $_f_dst=""; $_f_quota=""; $_f_accpt_direct="";
		    if($buf1 > 2) {
			for($_kk=3; $_kk<=$buf1; $_kk++) {
			    $_tmp2=gettok($_string,$_kk," \t");
			    if( ($_tmp2 =="dst") or ($_tmp2 =="src")) {
				$_f_dst=( $_tmp2 =="dst") ? "--dport" : "$_f_dst";
				$_f_dst=( $_tmp2 =="src") ? "--sport" : "$_f_dst";
			    } elseif( $_tmp2=="quota") {
				$_kk++;
				$_tmp21=gettok($_string,$_kk," \t");
				$_f_quota=( trim($_tmp21) !="") ? "$_tmp21" : "$_f_quota";
			    } elseif( ($_tmp2=="from") or ($_tmp2=="to")) {
				$_kk++;
				$_tmp21=gettok($_string,$_kk," \t");
				$_tmp21=gethostbyname($_tmp21);
				$_f_accpt_direct=$_tmp2."-".$_tmp21; 
			    } else {
				$_f_ports="$_tmp2";
			    }
			}
		    }
		    $_accpt[$_ff]="$buf2:$_f_ports:$_f_dst:$_f_quota:$_f_accpt_direct"; $_ff++;
		} elseif($_tmp1 == "reject") {
		    $_tmp2=gettok($_string,2," \t");
		    if( ($_tmp2 !="tcp") and ($_tmp2 !="udp") and ($_tmp2 !="icmp") and ($_tmp2 !="all")) { 
			wlog("Sintax error in Policies config at line $strnum",1); continue; }
		    $buf2="$_tmp2";
		    if($buf1 > 2) {
			$_tmp2=gettok($_string,3," \t");
			if( $_tmp2[0] == "(") $_multiport=TRUE; 
			$_tmp2=( $_tmp2[0] == "(") ? substr($_tmp2,1) : $_tmp2;
			$_tmp2=( $_tmp2[strlen($_tmp2)-1] == ")") ? substr($_tmp2,0,-1) : $_tmp2;
			$buf2="$buf2:$_tmp2";
			$_tmp2=gettok($_string,4," \t");
			if( ($_tmp2 =="dst") or ($_tmp2 =="src")) {
			    $buf2=( $_tmp2 =="dst") ? "$buf2:--dport" : "$buf2";
			    $buf2=( $_tmp2 =="src") ? "$buf2:--sport" : "$buf2";
			}
		    }
		    $_rjct[$_rr]=$buf2; $_rr++;
		} elseif( $_tmp1 == "action") {
		    $_tmp2=gettok($_string,2," \t");
		    if( ($_tmp2 !="snat") and ($_tmp2 !="dnat") and ($_tmp2 !="masquerade") and ($_tmp2 !="input")) {
			wlog("Invalid action target in Policies config at line $strnum",1); continue; }
		    $_act=$_tmp2;
/*
		    $_act_dst=( $_act =="dnat") ? gettok($_string,3," \t") : $_act_dst;
		    if( coltoks($_act_dst,":")==2) {
			$chk_dst=gettok($_act_dst,1,":");
			$chk_port=gettok($_act_dst,2,":");
			if( substr_count($chk_dst,".")!=3) {
			    $chk_dst=gethostbyname($chk_dst);
			    $_act_dst="$chk_dst:$chk_port";
			}
		    }
*/
		    if( $_act_dst=gettok($_string,3," \t")) {
			$chk_dst=gettok($_act_dst,1,":");
			$chk_port=gettok($_act_dst,2,":");
			if( substr_count($chk_dst,".")!=3) {
			    $chk_dst=gethostbyname($chk_dst);
			    $_act_dst="$chk_dst:$chk_port";
			}
		    }

		} elseif( ($_tmp1== "out") or ($_tmp1== "in")) {
		    $_inout=$_tmp1;
		    $_prov=gettok($_string,2," \t");
		} elseif( $_tmp1== "manglemark") {
		    $_manglemark=gettok($_string,2," \t");
		    $_manglemark=( substr($_manglemark,0,2)=="0x") ? $_manglemark:"0x".$_manglemark;
		} elseif( ($_tmp1== "quote") or ($_tmp1== "quota")) {
		    $_quote=gettok($_string,2," \t");
		} elseif( $_tmp1== "blacklist") {
		    $_tmp2=gettok($_string,2," \t");
		    if( $_tmp2[0]=="(") {
			$_tmp2=str_replace("(","",$_tmp2);
			$_tmp2=str_replace(")","",$_tmp2);
			for($_tmp2_i=1; $_tmp2_i<=coltoks($_tmp2,","); $_tmp2_i++) {
			    $_tmplinebl=gettok($_tmp2,$_tmp2_i,",");
			    $_blacklist[$_tmp2_i-1]="$_tmplinebl";
			}
		    } else { $_blacklist[0]="$_tmp2"; }
		} elseif( $_tmp1== "allow_only") {
		    $_allow_only=gettok($_string,2," \t");
		} elseif( ($_tmp1=="title") or ($_tmp1=="name")) {
		    $_title=_trimline(str_replace($_tmp1,"",$_string)," ");
		    $_title=($_title[0]=="\"") ? substr($_title,1) : $_title;
		    $_title=($_title[strlen($_title)-1]=="\"") ? substr($_title,0,-1) : $_title;
		} elseif ( $_tmp1=="count") {
		    $_flCount=TRUE;
		    $colt1=coltoks($_string," \t");
		    for( $cc=2; $cc<=$colt1; $cc++) {
			$bufcc=gettok($_string,$cc," \t");
			if( $bufcc=="nolog") { 
			    $_flNolog=TRUE;
			} elseif(( $bufcc=="local") or ( $bufcc=="localtraffic")) {
			    $_flCount_local=TRUE;
			}
		    }	
		}
	}
    }
#   ну, глубоко вдохнули.... выдохнули..
    if( $_prov !="") {
	$_eth=getprov($_prov,"ifname");
	$_ext_ip=getprov($_prov,"ip");
    }
    $fl_mm=FALSE;
    if( $_quote !="") {
	$_quote=mega2bytes($_quote);
    }

    if( $_act_dst !="") {
	if( substr_count($_act_dst,".")!=3) {
	    $_addr=gethostbyname($_act_dst);
	    $_act_dst=( $_addr != $_act_dst) ? $_addr : $_act_dst; }
    }
    $_comment=" -m comment --comment \"".$_policyname."_".$_ip."\"";
    
#    if( isset($aalines1)) unset($aalines1);
#    $aalines1=array();

    if( $_blacklist[0] !="") {
        for($_i2=1; $_i2<=count($_blacklist); $_i2++) {
	    $_i21=$_i2-1;
	    $_tmp2=$_blacklist[$_i21]; 
	    $_line="$_iptables $icmd FORWARD -s $_ip -m set";
	    if( $_tmp2[0]=="!") {
		$_line="$_line !";
		$_blacklist[$_i21]=str_replace("!","",$_blacklist[$_i21]);
	    }
	    $_line="$_line --set $_blacklist[$_i21] dst -j DROP $_comment";
	    $aalines1[]=$_line;
	    $_line="$_iptables $icmd FORWARD -d $_ip -m set";
	    if( $_tmp2[0]=="!") {
		$_line="$_line !";
	    }
	    $_line="$_line --set $_blacklist[$_i21] src -j DROP $_comment";
	    $aalines1[]=$_line;
	}
    }

    if( ($_flsmtpout) and ( !$_remove)) {
	$_line="$_iptables -A FORWARD -p tcp -m tcp --dport 25 -m set --match-set locals src -m set ! --match-set locals dst -j ACCEPT $_comment";
	$aalines1[]=$_line;
    }

# генерируем реджекты
    
    foreach($_rjct as $_rr => $_rjctvv) {
	if( strlen($_rjct[$_rr]) ==0) continue;
	$_dspt="--dport";
	if( coltoks($_rjct[$_rr],":")>1) {
	    $_proto=gettok($_rjct[$_rr],1,":");
	    $_ports=gettok($_rjct[$_rr],2,":");
	    $_bufdspt=gettok($_rjct[$_rr],3,":");
	    $_dspt=( trim($_bufdspt)=="") ? $_dspt : $_bufdspt;
	} elseif( coltoks($_rjct[$_rr],":")==1) {
	    $_proto=$_rjct[$_rr];
	    $_ports="";
	}
	$fl_inv=FALSE;
	if( $_ports[0]=="!") { $_ports=substr($_ports,1); $fl_inv=TRUE; }
	$_line="$_iptables $acmd FORWARD -s $_ip";
	if( $_proto=="all") {
	    $_line="$_line";
	} elseif( $_proto=="icmp") {
	    $_line="$_line -p icmp";
	} else {
	    $_line="$_line ".((trim($_eth)=="") ? "" : "-i $_eth")." -p $_proto";
	    $_line=( $_multiport) ? "$_line -m multiport" : "$_line -m $_proto";
	    $_line=( $fl_inv) ? "$_line ! " : $_line;
	    $_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports"; 
	}
	$_line="$_line $_comment -j REJECT";
	$aalines1[]=$_line;
    
    }

# ассепты

    foreach($_accpt as $_ff => $_accptvv) {

	if( trim($_accpt[$_ff]) =="") continue;
	if( trim($_act)=="") continue;
	$_dspt="--dport";
	$_accpt_quota="";
	$_accpt_direct="";
	$_kol=coltoks($_accpt[$_ff],":");


	$fl_inv=FALSE;
	if( $_kol >1) {
	    $_proto=gettok($_accpt[$_ff],1,":");
	    $_tmp1=gettok($_accpt[$_ff],2,":");
	    if( strlen(trim($_tmp1))>0) {
		$_multiport=($_tmp1[0]=="(") ? TRUE : FALSE; 
		$_tmp1=( $_tmp1[0] == "(") ? substr($_tmp1,1) : $_tmp1;
		$_tmp1=( $_tmp1[strlen($_tmp1)-1] == ")") ? substr($_tmp1,0,-1) : $_tmp1;
		if( $_tmp1[0]=="!") { $_tmp1=substr($_tmp1,1); $fl_inv=TRUE; }
	    }
	    $_ports=$_tmp1;

	    $_bufdspt=gettok($_accpt[$_ff],3,":");
	    $_dspt=( trim($_bufdspt)=="") ? $_dspt : $_bufdspt;
	    $_tmp1=gettok($_accpt[$_ff],4,":");
	    if( $_tmp1 !="") {
		$_accpt_quota=mega2bytes($_tmp1);
	    }
	    $_tmp1=gettok($_accpt[$_ff],5,":");
	    $_accpt_direct=( trim($_tmp1)!="") ? $_tmp1 : $_accpt_direct;
	} elseif( coltoks($_accpt[$_ff],":")==1) {
	    $_proto=$_accpt[$_ff];
	    $_ports="";
	}

	if(( $_manglemark != "") and ( $_act!="input")) {

	    $_line="$_iptables -t mangle $acmd PREROUTING ";
	    if( $_inout =="in") {

		if(( $_act !="dnat") and ( $_act!="input")) { wlog("Target $_act cannot have inbound direction.",2); _exit(); }
		$_t_direct=((trim($_accpt_direct)!="") ? "-s ".gettok($_accpt_direct,2,"-") : "" );
		$_t_direct=$_t_direct." -d $_ext_ip";
		if( $_proto=="all") { 
		    $_line="$_line ".((trim($_eth)=="") ? "" : "-i $_eth")." $_t_direct";
		} else {
		    if( trim($_ports)=="") {
			$_line="$_line -i $_eth $_t_direct -p $_proto";
		    } else {
			$_line="$_line -i $_eth $_t_direct -p $_proto -m $_proto";
			$_line=( $fl_inv) ? "$_line ! " : $_line;
			$_line="$_line $_dspt $_ports";
		    }
			
		}

		if( trim($_allow_only)!="") {
		    $_line=$_line." -m set --match-set $_allow_only src";
		}
		if( $_act=="dnat") {
		    $_line="$_line -j CONNMARK --set-mark $_manglemark";
		} else {
		    $_line="$_line -j MARK --set-mark $_manglemark";
		}
		$_line="$_line -m comment --comment \"".$_policyname."_".$_ip."\"";


	    } elseif( $_inout =="out") {

		$_t_direct=((trim($_accpt_direct)!="") ? "-d ".gettok($_accpt_direct,2,"-") : "" );
		if( $_proto=="all") {
		    $_line="$_line -s $_ip $_t_direct";
		} else {
		    if( trim($_ports)=="") {
			$_line="$_line -s $_ip $_t_direct -p $_proto";
		    } else {
			$_line="$_line -s $_ip $_t_direct -p $_proto";
			$_line=( $_multiport) ? "$_line -m multiport" : "$_line -m $_proto";
			$_line=( $fl_inv) ? "$_line !" : $_line;
			$_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports"; 
		    }

		}
		if( trim($_allow_only)!="") {
		    $_line=$_line." -m set --match-set $_allow_only dst";
		}

		if($_t_direct=="") {
		    $_line="$_line -m set ! --set locals dst";
		}
		$_line="$_line -j MARK --set-mark $_manglemark";
		$_line="$_line -m comment --comment \"".$_policyname."_".$_ip."\"";
	    }
	    $aalines1[]=$_line; 
	    $fl_mm=TRUE;
	    $_line="$_iptables -t nat $acmd POSTROUTING -m mark --mark $_manglemark";

	    if( $_act =="snat") {
		$_line="$_line -o $_eth -j SNAT --to-source $_ext_ip";
	    } elseif( $_act =="masquerade") {
		$_line="$_line -j MASQUERADE";
	    } elseif( $_act =="dnat") {
		$_line="$_iptables -t nat $acmd PREROUTING -m connmark --mark $_manglemark -j DNAT --to-destination $_act_dst";
	    }

#	    if(!$_remove) checkmarks($_act,$_manglemark,$_line);
    	    $_line="[ ".((( $acmd=="-A") || ( $acmd=="-I")) ? "":"!")." -z \"\$($_sudo $_iptables -t nat -nL | $_awk '\$0~/mark match $_manglemark/&&/".strtoupper($_act)."/')\" ] && $_sudo ".$_line;
	    $aalines1[]=$_line;

	} elseif(( !trim($_manglemark)) and ( $_act!="input")) {
#   дальше без макировки

	    $_line="$_iptables -t nat $acmd POSTROUTING";
	    if( $_act =="snat") {

		$_t_direct=((trim($_accpt_direct)!="") ? "-d ".gettok($_accpt_direct,2,"-") : "" );
		if( $_proto=="all") {
		    $_line=="$_line -s $_ip $_t_direct";
		} else {
		    if( trim($_ports)=="") {
			$_line="$_line -s $_ip $_t_direct -p $_proto";
		    } else {
			$_line="$_line -s $_ip $_t_direct -p $_proto";
			$_line=( $_multiport) ? "$_line -m multiport" : "$_line -m $_proto";
			$_line=( $fl_inv) ? "$_line !" : $_line;
			$_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports";
		    }
		    
		}
		if( trim($_allow_only)!="") {
		    $_line=$_line." -m set --match-set $_allow_only dst";
		}

		if( $_t_direct=="") {
		    $_line="$_line -m set ! --set locals dst";
		    
		}
		$_line="$_line -j SNAT --to-source $_ext_ip";
		$_line="$_line -m comment --comment \"".$_policyname."_".$_ip."\"";

	    } elseif( $_act =="masquerade") {

		$_t_direct=((trim($_accpt_direct)!="") ? "-d ".gettok($_accpt_direct,2,"-") : "" );
		if( $_proto=="all") {
		    $_line="$_line -s $_ip $_t_direct";
		} else {
		    if( trim($_ports)=="") {
			$_line="$_line -s $_ip $_t_direct -p $_proto";
		    } else {
			$_line="$_line -s $_ip $_t_direct -p $_proto";
			$_line=( $_multiport) ? "$_line -m multiport" : "$_line -m $_proto";
			$_line=( $fl_inv) ? "$_line !" : $_line;
			$_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports";
		    }

		}
		if( trim($_allow_only)!="") {
		    $_line=$_line." -m set --match-set $_allow_only dst";
		}

		if( $_t_direct=="") {
		    $_line="$_line -m set ! --set locals dst";
		}
		$_line="$_line -j MASQUERADE";
		$_line="$_line -m comment --comment \"".$_policyname."_".$_ip."\"";

	    } elseif( $_act =="dnat") {

#		$_line="$_iptables -t nat $acmd PREROUTING -i $_eth";
		$_line="$_iptables -t nat $acmd PREROUTING".(( !trim($_ext_ip)) ? " -i $_eth":"");;
		$_t_direct=((trim($_accpt_direct)!="") ? "-s ".gettok($_accpt_direct,2,"-") : "" );
		$_t_direct="-d $_ext_ip ".$_t_direct;
		if( $_proto=="all") {
		    $_line="$_line $_t_direct";
		} else {
		    if( trim($_ports)=="") {
			$_line="$_line $_t_direct -p $_proto";
		    } else {
			$_line="$_line $_t_direct -p $_proto";
			$_line=( $_multiport) ? "$_line -m multiport" : "$_line -m $_proto";
			$_line=( $fl_inv) ? "$_line !" : $_line;
			$_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports";
		    }
		    
		}
		if( trim($_allow_only)!="") {
		    $_line=$_line." -m set --match-set $_allow_only src";
		}

		$_line="$_line -j DNAT --to-destination $_act_dst";
		$_line="$_line -m comment --comment \"".$_policyname."_".$_ip."\"";

	    }
	    $aalines1[]=$_line;
	}

	$_policykey="";
        if( ($_proto !="icmp") and ($_proto !="all")) {
	    $_tmp01=str_replace(",","",$_ports);
	    $_tmp01=( $fl_inv) ? "!$_tmp01" : "$_tmp01";
	    $_policykey="$_policyname$_proto$_tmp01";
	} else { 
	    $_policykey="$_policyname$_proto"; }

# цепочки счетчиков

	if( $_flCount) {

	    $_comment=" -m comment --comment \"".$_policyname."_".$_ip."\"";
	    if( $_manglemark !="") {
		$_countmark="-m ".(( $_act=="dnat") ? "connmark" : "mark")." --mark $_manglemark";
	    } else {
		$_countmark="";
	    }
	    $_countmark_in=( $_inout=="in") ? $_countmark:"";
	    $_countmark_out=( $_inout=="out") ? $_countmark:"";

	    $_line="$_iptables -t mangle $acmd COUNT_IN";
	    if( $_act=="dnat") {
		$_line=$_line." -d $_ip";
		$_dspt="--dport";
	    } elseif( $_act=="input") {
		$_line=$_line." -i $_eth -d $_ext_ip";
		$_dspt="--dport";
	    } else {
		$_line=$_line." -d $_ip";
		$_dspt="--sport";
	    }
	    $_line="$_line -p $_proto";
	    $_tcounts=getcounts($_ip,$_policykey,1);
	    $_tmpbytes=( trim($_tcounts)!="") ? gettok($_tcounts,2,"/ \t") : "";

	    if( (trim($_quote)!="") and (trim($_tmpbytes)!="")) $_quote=( $_quote<=$_tmpbytes) ? "1" : ($_quote-$_tmpbytes);
	    if( (trim($_accpt_quota)!="") and (trim($_tmpbytes)!="")) $_accpt_quota=( $_accpt_quota<=$_tmpbytes) ? "1" : ($_accpt_quota-$_tmpbytes);

	    if( $acmd!="-D") {
		$_line=( trim($_tcounts) !="") ? "$_line --set-counters $_tcounts" : "$_line";
	    }
	    if( trim($_ports)!="") {
		$_line=( $_multiport) ? "$_line -m multiport" : "$_line -m $_proto";
		$_line=( $fl_inv) ? "$_line !" : $_line;
		$_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports";
	    }
	    if(( trim($_allow_only)!="") and ( $_act!="input")) {
		$_line=$_line." -m set --match-set $_allow_only src";
	    }

	    if( $_accpt_quota=="") {
		$_line=( $_quote !="") ? "$_line -m quota --quota $_quote -j ACCEPT" : "$_line -j RETURN";
	    } else {
		$_line="$_line -m quota --quota $_accpt_quota -j ACCEPT";
	    }
	    $_line="$_line $_countmark_in $_comment";
#	    $_line="$_line $_countmark $_comment";
	    
	    $aalines1[]=$_line;
	    
	    if( $_act=="dnat") {
		$_line="$_iptables -t mangle $acmd FORWARD";
		$_line=$_line." -d $_ip";
		$_dspt="--dport";
	    } elseif( $_act=="input") {
		$_line="$_iptables -t mangle $acmd INPUT";
		$_line=$_line." -i $_eth -d $_ext_ip";
		$_dspt="--dport";
	    } else {
		$_line="$_iptables -t mangle $acmd FORWARD";
		$_line=$_line." -d $_ip";
		$_dspt="--sport";
	    }
	    
	    if( trim($_ports)!="") {
		$_line=( $_multiport) ? "$_line -p $_proto -m multiport" : "$_line -p $_proto -m $_proto";
		$_line=( $fl_inv) ? "$_line !" : $_line;
		$_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports";
	    } else {
		$_line="$_line -p $_proto";
	    }

	    if(( trim($_allow_only)!="") and ( $_act!="input")) {
		$_line=$_line." -m set --match-set $_allow_only src";
	    }

	    $_line=$_line.(( $_act!="input") ? " -m set ! --set locals src " : "");
	  if( !$_flNolog) $aalines1[]="$_line -j ULOG $_countmark_in $_comment";
	    $aalines1[]="$_line -j COUNT_IN $_countmark_in $_comment";

	    $_line="$_iptables -t mangle $acmd COUNT_OUT";
	    if( $_act=="dnat") {  
		$_line=$_line." -s $_ip";
		$_dspt="--sport";
	    } elseif( $_act=="input") {
		$_line=$_line." -s $_ext_ip";
		$_dspt="--sport";
	    } else {
		$_line=$_line." -s $_ip";
		$_dspt="--dport";
	    }
	    if( $_proto=="all") {
		$_line="$_line -p all";
	    } else {
		$_line="$_line -p $_proto";
		$_tcounts=getcounts($_ip,$_policykey,2);
		if( $acmd!="-D") {
		    $_line=( trim($_tcounts) !="") ? "$_line --set-counters $_tcounts" : "$_line";
		}		    
		if( trim($_ports)!="") {
		    $_line=( $_multiport) ? "$_line -m multiport" : "$_line -m $_proto";
		    $_line=( $fl_inv) ? "$_line !" : $_line;
		    $_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports";
		}
	    }
	    if(( trim($_allow_only)!="") and ( $_act!="input")) {
		$_line=$_line." -m set --match-set $_allow_only dst";
	    }
	    $_line="$_line -j RETURN $_countmark_out $_comment";
	    $aalines1[]=$_line;
	    
	    if( $_act=="dnat") {
		$_line="$_iptables -t mangle $acmd FORWARD";
		$_line=$_line." -s $_ip";
		$_dspt="--sport";
	    } elseif( $_act=="input") {
		$_line="$_iptables -t mangle $acmd OUTPUT";
		$_line=$_line." -s $_ext_ip";
		$_dspt="--sport";
	    } else {
		$_line="$_iptables -t mangle $acmd FORWARD";
		$_line=$_line." -s $_ip";
		$_dspt="--dport";
	    }
	    $_line="$_line -p $_proto";
	    if( trim($_ports)!="") {
		$_line=( $_multiport) ? "$_line -m multiport" : "$_line -m $_proto";
		$_line=( $fl_inv) ? "$_line !" : $_line;
		$_line=( $_multiport) ? "$_line --ports $_ports" : "$_line $_dspt $_ports";
	    }
	    if(( trim($_allow_only)!="") and ( $_act!="input")) {
		$_line=$_line." -m set --match-set $_allow_only dst";
	    }
	    
	    $_line=$_line.(( $_act!="input") ? " -m set ! --set locals dst ":"");
	    
	    if( !$_flNolog) $aalines1[]="$_line -j ULOG $_countmark_out $_comment";
	    $_line="$_line -j COUNT_OUT $_countmark_out $_comment";
	    $aalines1[]=$_line;
	}
    }
    if( $return_lines==FALSE) {
	_exec2($aalines1,FALSE,TRUE);
    } else {
	return($aalines1);
    }
}

#----------------------------------------------------------

function shaper_loadconf()
{
    global $iptconf_dir;
    global $_ifname,$_ifbifname;
    if( !$fshaperconf=fopen("$iptconf_dir/shaperconf","r")) {
	$ret=FALSE;
    } else {
	$ret=array();
	while( !feof($fshaperconf)) {
	    $str=trim(fgets($fshaperconf));
	    if( strlen($str)==0) continue;
	    if( $str[0]=="#") continue;
	    if( substr_count($str,"device")>0) {
		$ic=coltoks($str," \t");
		$bufifbdev=""; $bufifname="";
		for($i=1;$i<=$ic;$i++) {
		    $bufi=gettok($str,$i," \t");
		    $buf1=gettok($bufi,$i,":=");
		    if( $buf1=="device") {
			$bufifbdev=gettok($bufi,2,":=");
		    } elseif( $buf1=="ifname") {
			$bufifname=gettok($bufi,2,":=");
		    }
		}
		$ret["device".count($ret)]=array( "type" => "device",
						"ifbdev" => $bufifbdev,
						"hwdev" => $bufifname
					    );
	    } else {
		
		$bufclient=""; $bufgrp=""; $bufratein=""; $bufrateout=""; $bufifname=""; $bufifbifname=""; $bufstatus="";
		$ic=coltoks($str," \t");
		for($i=1;$i<=$ic;$i++) {
		    $bufi=gettok($str,$i," \t");
		    $buf1=gettok($bufi,1,"=:");
		    if( $buf1=="grp") {
			$bufgrp=gettok($bufi,2,":=");
		    } elseif( $buf1=="client") {
			$bufclient=gettok($bufi,2,":=");
		    } elseif( $buf1=="ratein") {
			$bufratein=gettok($bufi,2,":=");
		    } elseif( $buf1=="rateout") {
			$bufrateout=gettok($bufi,2,":=");
		    } elseif( $buf1=="ifname") {
			$bufifname=gettok($bufi,2,":=");
		    } elseif( $buf1=="ifbifname") {
			$bufifbifname=gettok($bufi,2,":=");
		    } elseif( $buf1=="status") {
			$bufstatus=gettok($bufi,2,":=");
		    }
		}
		$ret["client".count($ret)]=array( "type" => "client",
					"client" => $bufclient, 
					"grp" => $bufgrp, 
					"ratein" => $bufratein, 
					"rateout" => $bufrateout,
					"ifname" => $bufifname,
					"ifbifname" => $bufifbifname,
					"status" => $bufstatus
				    );
	    }
	}
    }
    return($ret);

}
#-----------------------------------------------------------------

function shapers_load()
{
    global $iptconf_dir,$_shaper_default_ifbifname,$_shaper_default_ifname;
    
    if( !function_exists("shape_getstatus")) {
	wlog("Function shape_getstatus() is not found()",2,TRUE,5,TRUE); return(FALSE);
    }
    if( shape_getstatus()) { 
	wlog("Шейпер уже загружен!",2,TRUE,4,TRUE); exit;
    } else {
	$aconf=shaper_loadconf();
	$scol=0;
	foreach($aconf as $akey => &$aitem) {
	    if( substr_count($akey,"device")==0) continue;
	    $aitem["ifbdev"]=( !trim($aitem["ifbdev"])) ? $_shaper_default_ifbifname:$aitem["ifbdev"];
	    $aitem["hwdev"]=( !trim($aitem["hwdev"])) ? $_shaper_default_ifname:$aitem["hwdev"];
	    shaper_up($aitem["ifbdev"],$aitem["hwdev"]);
	    $scol++;
	}
	if( !$scol) {
	    shaper_up($_shaper_default_ifbifname,$_shaper_default_ifname);
	}
	foreach($aconf as $akey => &$aclient) {
	    if( substr_count($akey,"client")==0) continue;
	    if( trim($aclient["status"])=="1") continue;

	    if( !$_client=trim($aclient["client"])) continue;
	    shaper_save_client($aclient,TRUE);
	}
    }

    wlog("Загрузка конфигурации шейперов...",0,FALSE,1,FALSE);
}
#----------------------------------------------------------
function shapers_unload()
{
    global $_sudo,$_ifconfig,$_grep;
#    $adevs=explode("\n",exec("$_sudo $_ifconfig | $_grep 'Link encap:' | awk '$0 ~ /^ifb/ { print($1); }'"));
    list($rv,$adevs)=_exec2("$_ifconfig | $_grep 'Link encap:' | awk '$0 ~ /^ifb/ { print($1); }'");
    if( count($adevs)>0) {
	foreach($adevs as $adev) shaper_down($adev);
    }
    wlog("Выгрузка всех доступных шейперов...",0,FALSE,1,FALSE);
}
#----------------------------------------------------------
function fillconf($flweb=FALSE)
{
    global $_iptables;
    global $_ipset,$_sudo, $_grep,$_echo;
    global $_service;
    global $_local_eth;
    global $_console;
    global $_localifs;
    global $_extifs;
    global $sets_dir;
    global $iptconf_dir;
    global $users_dir;
    global $_portfilter_enable;
    global $_shaper_enable;
    
    wlog("!!!ЗАПУСК fillconf()!!!",0,FALSE,1,FALSE);
    
    if( getcwd()!=$iptconf_dir) if( !chdir($iptconf_dir)) { print("Error changing directory to $iptconf_dir in fillconf().."); exit; }
    
    if( ! file_exists("$iptconf_dir/ports")) { wlog("Ports config is not found...",2); _exit(); }
    if( ! file_exists("$iptconf_dir/policies")) { wlog("Policies config is not found...",2); _exit(); }

    if( $_console) echo "Loading configuration...";

    if( file_exists("tmp_nat")) unlink("tmp_nat");
#     поехали...

    $link=mysql_getlink();

    $aalines=array(
	    "$_sudo $_iptables -P OUTPUT ACCEPT",
	    "$_sudo $_iptables -P INPUT ACCEPT",
	    "$_sudo $_iptables -P FORWARD DROP",
	    "$_sudo $_iptables -F",
	    "$_sudo $_iptables -t mangle -F",
	    "$_sudo $_iptables -t nat -F",
	    "[ -z \"\$($_sudo $_iptables -t mangle -L | $_grep 'Chain COUNT_IN')\" ] && $_sudo $_iptables -t mangle -N COUNT_IN",
	    "[ -z \"\$($_sudo $_iptables -t mangle -L | $_grep 'Chain COUNT_OUT')\" ] && $_sudo $_iptables -t mangle -N COUNT_OUT",
	    "[ -z \"\$($_sudo $_iptables -t mangle -L | $_grep 'Chain COUNT_NETS')\" ] && $_sudo $_iptables -t mangle -N COUNT_NETS",
	    "[ -z \"\$($_sudo $_iptables -t mangle -L | $_grep 'Chain COUNT_NETS_LOC')\" ] && $_sudo $_iptables -t mangle -N COUNT_NETS_LOC",
	    "[ -z \"\$($_sudo $_iptables -t mangle -L | $_grep 'Chain COUNT_NETS_FWD')\" ] && $_sudo $_iptables -t mangle -N COUNT_NETS_FWD",
	    "[ -z \"\$($_sudo $_iptables -t mangle -L | $_grep 'Chain PORTFILTER')\" ] && $_sudo $_iptables -t mangle -N PORTFILTER",
	    );
    _exec2($aalines,TRUE,TRUE);
    unset($aalines); $aalines=array();
    
#   выгружаем шейперы
    shapers_unload();

#   сначала загрузим сети
    if( !$res=mysql_query("SELECT * FROM networks WHERE 1")) {
	wlog("Error quering networks list",2); _exit(); 
    }

    $strnum=0;
    $_i_nets=0; $_count_nets[0]="";
    $t_ln=array();
    $_string="";
    while($row=mysql_fetch_array($res)) {
	$strnum++;
	$flCount=TRUE;
	$buf_net=$row["addr"];
	$flnoallfwd=( $row["notallfwd"]=="1") ? TRUE:FALSE;
	if( $row["local"]=="1") {
	    $t_ln[]=$buf_net; 
	}
	if( $flCount) { $_count_nets[$_i_nets]=$buf_net; $_i_nets++; }
	if( !$flnoallfwd) {
	    $aalines[]="$_iptables -A INPUT -s $buf_net -j ACCEPT";
	    $aalines[]= "$_iptables -A INPUT -d $buf_net -j ACCEPT";
	    $aalines[]="$_iptables -A FORWARD -s $buf_net -j ACCEPT";
	    $aalines[]="$_iptables -A FORWARD -d $buf_net -j ACCEPT";
	}

	$bufnet0=gettok($buf_net,1,"/");
	$res2=mysql_query("SELECT * FROM providers WHERE LOCATE(\"".str_replace(gettok($bufnet0,4,"."),"",$bufnet0)."\",ip)>0",$link);
	$row2=mysql_fetch_array($res2);
	$bufip=$row2["ip"];
	$sbufip=(!trim($bufip)) ? "":"-s $bufip";
	$dbufip=(!trim($bufip)) ? "":"-d $bufip";
	mysql_free_result($res2);
	if( !$tcounts_dst=getcounts($bufnet0,"CNTNET",1)) $tcounts_dst="";
	$cntdst=( !trim($tcounts_dst)) ? "":"--set-counters $tcounts_dst";
	if( !$tcounts_src=getcounts($bufnet0,"CNTNET",2)) $tcounts_src="";
	$cntsrc=( !trim($tcounts_src)) ? "":"--set-counters $tcounts_src";
	$aalines[]="$_iptables -t mangle -A PREROUTING -s $buf_net $cntsrc -j COUNT_NETS -m comment --comment \"CNTNET_".$bufnet0."_SRC\"";
	$aalines[]="$_iptables -t mangle -A POSTROUTING -d $buf_net $cntdst -j COUNT_NETS -m comment --comment \"CNTNET_".$bufnet0."_DST\"";
	$aalines[]="$_iptables -t mangle -A COUNT_NETS -s $buf_net $cntsrc -j RETURN -m comment --comment \"CNTNET_".$bufnet0."_SRC\"";
	$aalines[]="$_iptables -t mangle -A COUNT_NETS -d $buf_net $cntdst -j RETURN -m comment --comment \"CNTNET_".$bufnet0."_DST\"";

	if( !$tcounts_dst=getcounts($bufnet0,"CNTNETL",1)) $tcounts_dst="";
	$cntdst=( !trim($tcounts_dst)) ? "":"--set-counters $tcounts_dst";
	if( !$tcounts_src=getcounts($bufnet0,"CNTNETL",2)) $tcounts_src="";
	$cntsrc=( !trim($tcounts_src)) ? "":"--set-counters $tcounts_src";
	$aalines[]="$_iptables -t mangle -A OUTPUT $sbufip -d $buf_net $cntsrc -j COUNT_NETS_LOC -m comment --comment \"CNTNETL_".$bufnet0."_SRC\"";
	$aalines[]="$_iptables -t mangle -A INPUT $dbufip -s $buf_net $cntdst -j COUNT_NETS_LOC -m comment --comment \"CNTNETL_".$bufnet0."_DST\"";
	$aalines[]="$_iptables -t mangle -A COUNT_NETS_LOC $sbufip -d $buf_net $cntsrc -j RETURN -m comment --comment \"CNTNETL_".$bufnet0."_SRC\"";
	$aalines[]="$_iptables -t mangle -A COUNT_NETS_LOC $dbufip -s $buf_net $cntdst -j RETURN -m comment --comment \"CNTNETL_".$bufnet0."_DST\"";

	if( !$tcounts_dst=getcounts($bufnet0,"CNTNETF",1)) $tcounts_dst="";
	$cntdst=( !trim($tcounts_dst)) ? "":"--set-counters $tcounts_dst";
	if( !$tcounts_src=getcounts($bufnet0,"CNTNETF",2)) $tcounts_src="";
	$cntsrc=( !trim($tcounts_src)) ? "":"--set-counters $tcounts_src";
	$aalines[]="$_iptables -t mangle -A FORWARD -s $buf_net $cntsrc -j COUNT_NETS_FWD -m comment --comment \"CNTNETF_".$bufnet0."_SRC\"";
	$aalines[]="$_iptables -t mangle -A FORWARD -d $buf_net $cntdst -j COUNT_NETS_FWD -m comment --comment \"CNTNETF_".$bufnet0."_DST\"";
	$aalines[]="$_iptables -t mangle -A COUNT_NETS_FWD -s $buf_net $cntsrc -j RETURN -m comment --comment \"CNTNETF_".$bufnet0."_SRC\"";
	$aalines[]="$_iptables -t mangle -A COUNT_NETS_FWD -d $buf_net $cntdst -j RETURN -m comment --comment \"CNTNETF_".$bufnet0."_DST\"";

    }
    $aalines[]="$_iptables -t mangle -A COUNT_NETS -j RETURN";
    $aalines[]="$_iptables -t mangle -A COUNT_NETS_LOC -j RETURN";
    $aalines[]="$_iptables -t mangle -A COUNT_NETS_FWD -j RETURN";

    if( count($t_ln)>0) {
	$aalines[]="$_ipset -F locals &> /dev/null";
	$aalines[]="$_ipset -X locals &> /dev/null";
	$aalines[]="$_ipset -N locals nethash";
	foreach($t_ln as $ti => $tlnvv) {
	    if( trim($t_ln[$ti])=="") continue;
	    $aalines[]="$_ipset -A locals $t_ln[$ti]";
	}
	$aalines[]="$_ipset -S locals > $sets_dir/locals";
	if( file_exists("$iptconf_dir/ipsetlist")) { 
	    _exec2("mv $iptconf_dir/ipsetlist $iptconf_dir/ipsetlist.1");
	}
	_exec2("$_echo 'locals' | $_sudo tee -a $iptconf_dir/ipsetlist >/dev/null",TRUE);
	if( file_exists("$iptconf_dir/ipsetlist.1")) {
	    _exec2("cat $iptconf_dir/ipsetlist.1 | grep -v locals | $_sudo tee -a $iptconf_dir/ipsetlist >/dev/null",TRUE);
	    _exec2("rm -f $iptconf_dir/ipsetlist.1");
	}
    }
    mysql_free_result($res);
    unset($row);

    _exec2($aalines,FALSE,TRUE);
    unset($aalines); $aalines=array();


#   теперь сетлисты
    $_fpols=fopen("policies","r");
    $_bls=array(); $_blpos=0;
    while( !feof($_fpols)) {
	$_string=fgets($_fpols);
	$_bword1=trim(gettok($_string,1," \t"));
	if(( $_bword1=="blacklist") or ( $_bword1=="allow_only")) {
	    $_tmpbl=gettok($_string,2," \t");
	    if( $_tmpbl[0]=="(") {
		$_tmpbl=str_replace("(","",$_tmpbl);
		$_tmpbl=str_replace(")","",$_tmpbl);
		for($_i2=1; $_i2<=coltoks($_tmpbl); $_i2++) {
		    $_tmpbl2=gettok($_tmpbl,$_i2,",");
		    $_flbl=FALSE;
		    for($_i3=0; $_i3<=count($_bls); $_i3++) {
			if( trim($_bls[$_i3])==trim($_tmpbl2)) { $_flbl=TRUE; }
		    }
		    if( !$_flbl) { $_bls[$_blpos]=$_tmpbl2; $_blpos++; }
		}
	    } else {
		$_flbl=FALSE;
		foreach($_bls as $_i3 => $_blsvv) {
		    if( trim($_bls[$_i3])==trim($_tmpbl)) { $_flbl=TRUE; }
		}
		if( !$_flbl) { $_bls[$_blpos]=gettok($_string,2," \t"); $_blpos++; }
	    }
	} else {
	    continue;
	}
    }
    
    foreach($_bls as $_i21 => $bvv0) {
	if( trim($_bls[$_i21])=="") continue;
	if( ! file_exists("$sets_dir/$_bls[$_i21]")) { wlog("IPSet blacklist file $_bls[$_i21] is not found.",2); _exit(); }
	if( ! is_readable("$sets_dir/$_bls[$_i21]")) { wlog("Error opening ipset blacklist file $_bls[$_i21].",2); _exit(); }
	$flst=FALSE; $fin=FALSE;

	$aalines[]="$_ipset -F ".trim($bvv0)." &> /dev/null";
	$aalines[]="$_ipset -X ".trim($bvv0)." &> /dev/null";
	$aalines[]="$_ipset -R < $sets_dir/".trim($bvv0);
	$aalines[]="$_echo '".trim($bvv0)."' >> $iptconf_dir/ipsetlist";

    }
    fclose($_fpols);
    _exec2($aalines,FALSE,TRUE);
    unset($aalines); $aalines=array();

#   читаем произвольные правила
    if( file_exists("inits")) {
	$strnum=0;
	$_inits=fopen("inits","r");
	if( ! $_inits) { wlog("Error opening file inits",2); _exit(); }
	$_string="";
	while( !feof($_inits)) {
	    $_string=trim(fgets($_inits)); $strnum++;
	    if(strlen($_string) == 0) continue;
	    if($_string[0] == "#") continue;
	    $aalines[]="$_iptables $_string";
	}
	fclose($_inits);
    }
    _exec2($aalines,FALSE,TRUE);
    unset($aalines); $aalines=array();

#   порты
    $strnum=0;
    $_ports=fopen("ports","r");
    $_string="";
    if( ! $_ports) { wlog("Error opening file ports",2); _exit(); }
    $aalines[]="$_iptables -A INPUT -p icmp --icmp-type echo-request -j ACCEPT";
  if( $_portfilter_enable) {
    $aalines[]="$_iptables -t mangle -I PREROUTING -j PORTFILTER";
    $_string="";
    while( ! feof($_ports)) {
	$_string=strtolower(trim(fgets($_ports))); $strnum++;
	if(strlen($_string) == 0) continue;
	if($_string[0] == "#") continue;
	$bufproto=""; $bufport=""; $bufdest="";
	$bufproto=gettok($_string,1," :\t");
	if( coltoks($_string,":") >1) {
	    $_tmp1=gettok($_string,2," :\t");
		if( ! settype($_tmp1,"int")) {
		    wlog("Error parsing line $strnum of Ports config.",2); _exit(0);
		} else {
		    $bufport=$_tmp1;
		}
	} else {
	    continue;
	}
	$bufcolt=coltoks($_string," \t");
	$bufdest_src=FALSE; $bufdest_dst=FALSE; $bufdest_local=FALSE;
	$bufdstpath=""; $bufsrcpath=""; $bufdirect="both";
	$bufsrcinv=""; $bufdstinv="";
	if( $bufcolt>1) {
	    for( $iicol=2; $iicol<=$bufcolt; $iicol++) {
		$_tmp1=trim(gettok($_string,$iicol," \t"));
		$_tmp00=gettok($_tmp1,1,":");
		if( $_tmp00 =="dst") {
		    $buf00=trim(gettok($_tmp1,2,":"));
		    $bufdstpath=(( $buf00=="") or ( $buf00=="0.0.0.0/0")) ? "" : $buf00;
		    $bufdstinv=( $bufdstpath[0]=="!") ? "!" : "";
		    $bufdstpath=( $bufdstpath[0]=="!") ? substr($bufdstpath,1) : $bufdstpath;
		    $bufdest_dst=TRUE;
		} elseif ( $_tmp00 =="src") {
		    $buf00=trim(gettok($_tmp1,2,":"));
		    $bufsrcpath=(( $buf00=="") or ( $buf00=="0.0.0.0/0")) ? "" : $buf00;
		    $bufsrcinv=( $bufsrcpath[0]=="!") ? "!" : "";
		    $bufsrcpath=( $bufsrcpath[0]=="!") ? substr($bufsrcpath,1) : $bufsrcpath;
		    $bufdest_src=TRUE;
		} elseif ( $_tmp1 =="local") {
		    $bufdest_local=TRUE;
		} elseif( $_tmp00 =="direction") {
		    $bufdirect=trim(gettok($_tmp1,2,":"));
		} else {
		    wlog("Error parsing line $strnum of Ports config. tmp1 $_tmp1",2,TRUE,3,TRUE); 
		}
	    } 
	}
	$_line="$_iptables -t mangle -A PORTFILTER -p $bufproto"; 

	if( $bufdirect=="sport") {
	    $_line="$_iptables -t mangle -A PORTFILTER"; 
	    $_line=( trim($bufsrcpath)!="") ? "$_line $bufsrcinv -s $bufsrcpath" : "$_line";
	    $_line=( trim($bufdstpath)!="") ? "$_line $bufdstinv -d $bufdstpath" : "$_line";
	    $_line="$_line -p $bufproto".(( $bufport !="") ? " --sport $bufport" : "");
	    $_line="$_line -j RETURN";
	    $aalines[]=$_line;
	}
	if( $bufdirect=="dport") {
	    $_line="$_iptables -t mangle -A PORTFILTER"; 
	    $_line=( trim($bufsrcpath)!="") ? "$_line $bufsrcinv -s $bufsrcpath" : "$_line";
	    $_line=( trim($bufdstpath)!="") ? "$_line $bufdstinv -d $bufdstpath" : "$_line";
	    $_line="$_line -p $bufproto".(( $bufport !="") ? " --dport $bufport" : "");
	    $_line="$_line -j RETURN";
	    $aalines[]=$_line;
	}
	if( $bufdirect=="both") {
	    $_line="$_iptables -t mangle -A PORTFILTER"; 
	    $_line=( trim($bufsrcpath)!="") ? "$_line $bufsrcinv -s $bufsrcpath" : "$_line";
	    $_line=( trim($bufdstpath)!="") ? "$_line $bufdstinv -d $bufdstpath" : "$_line";
	    $_line="$_line -p $bufproto".(( $bufport !="") ? " -m multiport --port $bufport" : "");
	    $_line="$_line -j RETURN";
	    $aalines[]=$_line;
	}
	$_local_line="$_iptables -A INPUT -p $bufproto"; 
	if( $bufport !="") {
	    $aalines[]="$_local_line --sport $bufport -j ACCEPT";
	    $aalines[]="$_local_line --dport $bufport -j ACCEPT";
	} elseif( $bufport=="") {
	    $aalines[]="$_local_line -j ACCEPT";
	} 



    }
    fclose($_ports);
    $aalines[]="$_iptables -t mangle -A PORTFILTER -s 127.0.0.1 -j RETURN";
    $aalines[]="$_iptables -t mangle -A PORTFILTER -d 127.0.0.1 -j RETURN";
    $aalines[]="$_iptables -t mangle -A PORTFILTER -j DROP";
  }
    _exec2($aalines,FALSE,TRUE);
    unset($aalines); $aalines=array();


#    поехали по юзерам

    if( !$res=mysql_query("SELECT * FROM groups WHERE 1")) return(FALSE);
    if( mysql_num_rows($res)==0) { wlog("Unable to find Users config files.",2); _exit(); }
    
    while( $row=mysql_fetch_array($res)) {
	$fldefpolicy=FALSE;
	$_defpolicy=$row["default_policy"];
	$_pols[0]="";
	$buf_ip="";
	$strnum=0;
	$line="SELECT * FROM clients WHERE group_id=".$row["id"]."";
	if( !$rescl=mysql_query($line)) {
	    wlog("Error quering clients list for group ".$row["name"]." in fillconf()!",2); exit;
	}
	
	while( $rowcl=mysql_fetch_array($rescl)) {
	    $strnum++;
	    if( $rowcl["islocked"]>0) continue;
	    $buf_ip=$rowcl["ip"];
	    $_pols=( !trim($rowcl["policies"])) ? array("0" => $_defpolicy):explode(",",$rowcl["policies"]);
	    $_cname=$rowcl["cname"];

	    if( count($_pols) == 1) { 
		if( isset($aabuf)) unset($aabuf);
		if( $aabuf=_loadpolicy($buf_ip,$_pols[0],FALSE,FALSE,TRUE)) {
		    if( count($aabuf)>0) foreach($aabuf as $aabkk => $aaline) $aalines[]=$aaline;
		}
		unset($aabuf);
	    } else {
		foreach($_pols as $_ee => $_eevv) {
		    if( !isset($_pols[$_ee])) continue;
		    if( $_eevv !="") { 
			$_tty=$_eevv; 
			if( isset($aabuf)) unset($aabuf);
			if( $aabuf=_loadpolicy($buf_ip,$_eevv,FALSE,FALSE,TRUE)) {
			    if( count($aabuf)>0) foreach($aabuf as $aabkk => $aaline) $aalines[]=$aaline;
			}
			unset($aabuf);
			$_pols[$_ee]=""; 
		    }
		}
	    }
	}
    }
    $aalines[]="$_iptables -t mangle -A COUNT_IN -j DROP";
    _exec2($aalines,FALSE,TRUE);
    unset($aalines); $aalines=array();


#   теперь добавим маркированные наты
    if( file_exists("tmp_nat")) {
	$_file=fopen("tmp_nat","r");
	$strnum=0;
	$_string="";
	while( ! feof($_file)) {
	    $_string=trim(fgets($_file)); $strnum++;
	    if( strlen($_string)==0) continue;
	    if( $_string[0]=="#") continue;
	    $_line=gettok($_string,3,"@");
	    if( gettok($_line,1," \t") !=$_iptables) { wlog("Some shit happens in tmp_nat at line $strnum!",1); continue; }
	    $aalines[]=$_line;
	}
	fclose($_file);
	unlink("tmp_nat");
    }
    $aalines[]="$_iptables -A FORWARD -p tcp -m tcp --dport 25 -m set --match-set locals src -m set ! --match-set locals dst -j REJECT";
    $aalines[]="$_service iptcldr save";
    
    _exec2($aalines,FALSE,TRUE);
    
    if( $_console) echo "....Ok\n";
    
#   загружаем шейперы
    shapers_load();

    wlog("!!!КОНЕЦ fillconf()!!!",0,FALSE,1,FALSE);
}
#-------------------------------------------------------------------
function verid($v)
{
    if( !trim($v)) return(0);
    $cc=coltoks($v,"."); $nn=0;
    for($i=1;$i<=$cc;$i++) $nn+=trim(gettok($v,$i,"."));
    return($nn);
}
#-------------------------------------------------------------------
function getversion()
{
    global $iptconf_dir,$_sudo,$_grep,$_SESSION;
    if( isset($_SESSION["procinfo"])) unset($_SESSION["procinfo"]);
    $_SESSION["procinfo"]=array("name" => "Fantomas Iptconf", "version" => "", "ver" => "");
    $ver="";
    if( file_exists("$iptconf_dir/VERSION")) {
	$vfile=fopen("$iptconf_dir/VERSION","r");
	while( !feof($vfile)) {
	    $string=trim(fgets($vfile));
	    $buf1=trim(gettok($string,1,":="));
	    $_SESSION["procinfo"]["name"]=( $buf1=="name") ? gettok($string,2,":=") : $_SESSION["procinfo"]["name"];
	    $_SESSION["procinfo"]["version"]=( $buf1=="version") ? "версия: ".($ver=gettok($string,2,":=")) : $_SESSION["procinfo"]["version"];
	    $_SESSION["procinfo"]["ver"]=$ver;
	}
	fclose($vfile);
    } else {
	if( $fileid=fopen("$iptconf_dir/www/iptlib.php","r")) {
	    while( !feof($fileid)) {
		$code=( substr($code=str_replace(" ","",_trimline(strtolower(fgets($fileid)))),0,1)=="#") ? trim(substr($code,0,1)) : $code;
		if( gettok($code,1,":")=="version") { $_SESSION["procinfo"]["version"]=gettok($code,2,":"); break; }
	    }
	    fclose($fileid);
	}
    }
    if( $ret=trim(exec("ping -c2 -w3 ".(( ($addr=gethostbyname("coreit.ru"))=="coreit.ru") ? "78.107.237.91":$addr)." | $_grep 'bytes from'",$a0,$r))) {
	if( $aa=explode("\n",file_get_contents("http://coreit.ru/updates.php?p=fantomas&v=$ver&q=all"))) {
	    $aa1=array();
	    foreach($aa as &$av) {
		if( gettok(strtolower($av),1," \t")=="version:") $aa1["version"]=trim(gettok($av,2," \t")); 
		if( gettok(strtolower($av),1," \t")=="download:") $aa1["download"]=trim(gettok($av,2," \t")); 
		if( gettok(strtolower($av),1," \t")=="changelog:") $aa1["changelog"]=trim(gettok($av,2," \t")); 
		if( gettok(strtolower($av),1," \t")=="fullversion:") $aa1["fullversion"]=trim(gettok($av,2," \t")); 
	    }
	    if( verid($aa1["version"])>verid($ver)) {
		$info="<br><br><font class=top1><b>Внимание!</b></font><br>\n<font class=text32>\n";
		$info=$info."Доступна более новая версия программы - ".$aa1["fullversion"]." <br>\n";
		$info=$info."<a href=\"".$aa1["download"]."\" title=\"Скачать дистрибутив (в новом окне)\" class=text42s><font class=text42s target=_blank>Download</font></a> \n";
		$info=$info."<a href=\"".$aa1["changelog"]."\" title=\"Перейти на страницу изменений (в новом окне)\" class=text42s target=_blank><font class=text42s>Changelog</font></a> \n";
		$info=$info."</font><br>\n";
		if( isset($_SESSION["updinfo"])) unset($_SESSION["updinfo"]);
		$_SESSION["updinfo"]=$info;
	    }
	}
    }
    
    return(TRUE);
}

#-------------------------------------------------------------------

function load_ifs($fmode=1)
{
    global $iptconf_dir;
    global $_localifs;
    global $_extifs;
    if( $fmode==3) {
	if( isset($aprovs)) unset($aprovs);
	$aprovs=array();
    }
    if( count($_localifs)>1) { 
	foreach($_localifs as $lkey => $lv) unset($_localifs[$lkey]); 
	$_localifs=array(); 
    }
    if( count($_extifs)>1) { 
	foreach($_extifs as $ekey => $ev) unset($_extifs[$ekey]); 
	$_extifs=array();
    }

    $link=mysql_getlink();
    if( !$res=mysql_query("SELECT * FROM providers WHERE 1")) return(FALSE);
    while( $row=mysql_fetch_array($res)) {
	if( $fmode==1) {
	    if( $row["local"]=="1") $_localifs[]=$row["ifname"];
	} elseif( $fmode==2) {
	    if( trim($row["ip"])!="") $_extifs[]=$row["ip"];
	} elseif( $fmode==3) {
	    $aprovs[]=$row["name"];
	}
    }
    if( $fmode==3) {
	return($aprovs);
    }
}

#------------------------------------------------------------------

function get_policy_param($pname,$par,$parn="",$retall=0,$self=FALSE)
{
    global $iptconf_dir;
    if( (trim($pname)=="") or (trim($par)=="")) return(FALSE);
    $ppath="$iptconf_dir/policies";
    if( ( !file_exists($ppath)) or ( !is_readable($ppath))) {
	wlog("Error opening $ppath file in get_policy_param()",2); _exit(); 
    }
    $fins=FALSE;
    $pfile=fopen($ppath,"r");
    $strnum=0; $ri=0; $_rez[0]="";
    while( !feof($pfile)) {
	$_string=_trimline(fgets($pfile)," "); $strnum++;
	$_tmp1=gettok($_string,1," \t");
	if( $_tmp1=="policy") {
	    if( $fins) { wlog("Duplicate policy creating section or error closing previous policy definition in $ppath at line $strnum.",2); _exit(); }
	    $fins=( gettok($_string,2," \t")==$pname) ? TRUE : FALSE;
	    continue;
	} elseif( $_tmp1=="}") {
	    if( $fins) { $fins=FALSE; break; }
	} else {
	    if( $fins) {
		if( $_tmp1==$par) {
		    $_tmpp1=_trimline(str_replace($_tmp1,"",$_string)," ");
		    if( $parn!="") { 
			if( substr_count($_tmpp1,$parn)==0) continue;
			$ic1=coltoks($_tmpp1," \t");
			for( $ii1=1; $ii1<=$ic1; $ii1++) {
			    $tmp0=gettok($_tmpp1,$ii1," \t");
			    if( $tmp0==$parn) {
				$_tmpp1=( !$self) ? gettok($_tmpp1,$ii1+1," \t") : $tmp0;
				break 1;
			    }
			}
		    } else { $_tmpp1=( !$self) ? _trimline(str_replace($_tmp1,"",$_string)," ") : $_tmp1; }
		    $_tmpp1=($_tmpp1[0]=="\"") ? substr($_tmpp1,1) : $_tmpp1;
		    $_rez[$ri]=($_tmpp1[strlen($_tmpp1)-1]=="\"") ? substr($_tmpp1,0,-1) : $_tmpp1;
		    $ri++;
		}
	    }
	}
    }
    return(($retall==0) ? $_rez[0] : $_rez );
}


#-------------------------------------------------------------------------


function get_usr_param($grname,$par,$parn="",$retparsed=FALSE,$sep=" \t")
{
    global $iptconf_dir;
    global $users_dir;
    if( (trim($grname)=="") or (trim($par)=="")) return(FALSE);
    
    $_rez="";
    
    $link=mysql_getlink();
    $line="SELECT * FROM groups WHERE name=\"$grname\"";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса к списку групп в процедуре get_usr_param()!<br> ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    $row=mysql_fetch_array($res);
    $_id=$row["id"];
    $grptitle=$row["title"];
    $defpolicy=$row["default_policy"];
    if( $par=="title") {
	return($grptitle);
    } elseif( $par=="_default_policy") {
	return($defpolicy);
    } elseif( $par=="group_id") {
	return($_id);
    } elseif( $par=="islocked") {
	return($row["islocked"]);
    }
    mysql_free_result($res);
    unset($row);

    $line="SELECT * FROM clients WHERE (group_id=\"$_id\") && (ip=\"$par\")";
    if( !$res=mysql_query($line)) {
	wlog("Ошибка выполнения запроса к списку клиентов в процедуре get_usr_param()!<br> ".mysql_error($link),2,TRUE,5,TRUE);
	exit;
    }
    if( mysql_num_rows($res)==0) return(FALSE);
    $row=mysql_fetch_array($res);
    $_rez=( isset($row[$parn])) ? $row[$parn]:FALSE;
    
    if( $retparsed) { 
	$_rez=str_replace("$parn","",$_rez); 
	$_rez=str_replace("(","",$_rez); 
	$_rez=str_replace(")","",$_rez); 
	$_rez=str_replace(":","",$_rez); 
	$_rez=str_replace("=","",$_rez); 
	$_rez=str_replace("\"","",$_rez); 
    }
    return($_rez );
}


#-----------------------------------------------------------------------

function makeID()
{
    global $_SESSION;
    if(( !trim(session_id())) || ( !isset($_SESSION["fantomssessid"]))) return(FALSE);
    $bufid=trim(date("dmyHis"));
    if( isset($_SESSION["lastID"])) $bufid=( $_SESSION["lastID"]==$bufid) ? ($bufid+1):$bufid;
    $_SESSION["lastID"]=$bufid;
    return($bufid);
}

#-----------------------------------------------------------------------

function policy2array($ppolicy,$nockoi=FALSE,$nopnkey=FALSE)
{

global $iptconf_dir;

if($ppolicy=="") return(FALSE);

if( ( !file_exists("$iptconf_dir/policies")) or ( ! is_readable("$iptconf_dir/policies"))) {
    print("Error opening policies file...<br>  \n"); exit; 
}
    $pfile1=fopen("$iptconf_dir/policies","r");
    $open=FALSE;
    $aa1=array();
    $strnum=0;
    $pstrnum=0;
    $bufkey="";
    while( !feof($pfile1)) {
	$string=_trimline(strtolower(fgets($pfile1))," \t"); $strnum++;
	if( trim($string)=="") continue;
	if( $string[0]=="#") continue;
	$buf1=gettok($string,1," \t");
	$buf2=gettok($string,2," \t");
	if( !$open) {
	    if( $buf1=="policy") {
		if( $buf2==$ppolicy) { $open=TRUE; continue; }
	    }
	} else {
	    if( $buf1=="}") { $open=FALSE; break; }
	    $bufkey=( !$nopnkey) ? "$ppolicy$pstrnum" : "$pstrnum";
	    if( !$nockoi) {
		$buf0=htmlentities(str_replace($buf1,"",$string),ENT_NOQUOTES,"koi8r");
		$buf1=htmlentities($buf1,ENT_NOQUOTES,"koi8r");
		$aa1[$bufkey]="<b>$buf1</b>&nbsp <i>$buf0</i>"; 
	    } else {
		$aa1[$bufkey]="$string"; 
	    }
	    $pstrnum++;
	}
    }
    fclose($pfile1);
    
    return(( count($aa1)>0) ? $aa1:FALSE);
}


#--------------------------------------------------------------------

function is_policy_loaded($policy,$ip,$_group="")
{
    global $users_dir,$iptconf_dir,$_iptables;
    $_rez=FALSE;
    if( (trim($policy)=="") or (trim($ip)=="")) return($_rez);

    list($rr,$out1)=_exec2("$_iptables -t mangle -nL -v -x | grep -w $ip");
    if(count($out1)>0) { 
	foreach($out1 as $okk => $ovv) {
	    if( isset($arr)) unset($arr);
	    if( !$arr=findpolicy(_trimline($ovv),$ip,TRUE,$_group)) continue;
	    if( count($arr)>0) {
		if( $arr["policyname"]==$policy ) { return(TRUE); }
	    }
	}
    }
    if( $_rez) return($_rez);
    unset($out1); 
    list($rr,$out1)=_exec2("$_iptables -t nat -nL -v -x | grep -w $ip");
    if(count($out1)>0) {
	foreach($out1 as $okk => $ovv) {
	    if( isset($arr)) unset($arr);
	    $arr=findpolicy(_trimline($ovv),$ip,TRUE,$_group);
	    if( count($arr)>0) {
		if( $arr["policyname"]==$policy) return(TRUE);
	    } 
	}
    }
    return($_rez);

}
#--------------------------------------------------------------------

?>