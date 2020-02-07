<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: ff.php
# Description: 
# Version: 0.2.1
###


require("./../config.php");
require("iptlib.php");
require("iptlib2.php");

$flAdminsOnly=FALSE;
require("auth.php");

$_mode=( isset($_GET["mode"])) ? $_GET["mode"] : "";
$_func=( isset($_GET["f"])) ? $_GET["f"] : "";
$_p=( isset($_GET["p"])) ? $_GET["p"] : "";



#-------------------------------------------------------------------------


#-------------------------------------------------------------------------



print("<html>\n");
require("include/head1.php");
print("<body\n>");

if( trim($_func)=="") {
    print("");
} else {
    if( $_func=="whois") {
	if( trim($_p)=="") {
	    print("");
	} else {
	    if( !is_readable($_whois)) {
		print("<script type=\"text/javascript\">\ntop.location.replace('$rep_whoisurl$_ip'); \n </script>\n");
	    } else {
		print("<font class=top1>WHOIS Information for $_p:<br>\n<br><br>\n");
		print("<font class=text10t>\n");
		if( isset($aa0)) unset($aa0);
		list($rst,$aa0)=_exec2("$_whois $_p");
		foreach($aa0 as $key0 => $value0) print("$value0 <br>\n");
		print("<br>\n<hr>\n<br><br></font>\n");
	    }
	}
    }    
}





?>
</body>
</html>
