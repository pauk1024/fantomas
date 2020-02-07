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

 <FRAMESET rows="*,100" onResize="if (navigator.family == 'nn4') window.location.reload()" border=1> 
<FRAME src="sysstat.php?p=topmon&t=chart" name="chartframe">
  <FRAME src="sysstat.php?p=topmon&t=toolbox" name="toolframe"> 
 </FRAMESET> 

</HTML>
