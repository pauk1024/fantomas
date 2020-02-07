<HTML>


 <HEAD>
  <TITLE>Fantomas web interface</TITLE>

  <SCRIPT>
   function op() {
     // This function is for folders that do not open pages themselves.
     // See the online instructions for more information.
   }
  </SCRIPT>

 </HEAD>

 <!-- You may make other changes, but do not change the names  -->
 <!-- of the frames (treeframe and basefrm).                   -->

 <FRAMESET rows="*,130" onResize="if (navigator.family == 'nn4') window.location.reload()" border=1> 
<?php

require("./../config.php");

print("<FRAME src=\"trafmon.php?t=chart&grp=".trim($monitor_def_grp)."&w=$monitor_delay\" name=\"chart\" id=\"chart\"> \n");


?>

  <FRAME src="trafmon.php?t=toolbox" name="toolbox" id=\"toolbox\"> 
 </FRAMESET> 

</HTML>