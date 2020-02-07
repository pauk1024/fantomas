<html>
<body bgcolor="FFFBF0">
<?php
###
# Name: Fantomas Iptconf manager
# Version: 0.2.1
# Copyright 2009 Coreit! group
# Author: Andrey Makarov (pauk)
# Email: admin@coreit.ru
# Web: http://coreit.ru/fantomas/
# 
# Scriptname: chkf.php
# Description: inline element
# Version: 0.2.1
###

$p=( isset($_GET["p"])) ? $_GET["p"] : "";

if( $p=="") { 
    exit;
} else {
    $img=( file_exists($p)) ? "icons/apply16.gif" : "icons/cancel16.gif";
    $answ=( file_exists($p)) ? "" : "НЕ ";
    $type=( is_file($p)) ? "Файл" : "Каталог";
    print("<img src=\"$img\" title=\"$type $answ существует\" border=0 hspace=0 vspace=0 bgcolor:transparent;>");
}


?>
</body>
</html>