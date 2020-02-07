<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<?php

if( !isset($NoHeadEnd)) $NoHeadEnd=FALSE;

print("<head>\n");
print("   <meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">\n");
print("   <meta http-equiv=\"Expires\" content=\"Mon, 01 Jan 1990 00:00:00 GMT\">\n");
print("   <meta http-equiv=\"Cache-Control\" content=\"no-cache\">\n");

print("	<script type=\"text/javascript\" src=\"js/jquery.js\"></script>\n");
print("	<script type=\"text/javascript\" src=\"js/date.js\"></script>\n");
print("	<script type=\"text/javascript\" src=\"js/jquery.datePicker-2.1.2.js\"></script>\n");
print("	<script type=\"text/javascript\" src=\"js/thickbox.js\"></script>\n");
print("	<script type=\"text/javascript\" src=\"js/jquery.jeditable.js\"></script>\n");

#print("   <link rel=\"STYLESHEET\" type=\"text/css\" href=\"css/text.css\">\n");
print("	<link rel=\"stylesheet\" type=\"text/css\" href=\"css/datePicker.css\" media=\"Screen\" >\n");
print("	<link rel=\"stylesheet\" type=\"text/css\" href=\"css/text1.css\">\n");
print("	<link rel=\"stylesheet\" type=\"text/css\" href=\"css/thickbox.css\" media=\"Screen\" >\n");

print("   <BASE TARGET=\"basefrm\">\n");
print("   <title>Fantomas page</title>\n");

?>

<script type="text/javascript">
    $(function()
      {
	$('#inputDate1').datePicker();
            $('#inputDate2').datePicker({
            	startDate: '01-01-2000',
            	endDate: '01-01-2012'
            });

            $('#inputDate3').datePicker({
        	startDate: '01-01-2000',
        	endDate: '01-01-2012',
                clickInput:true
            });

            $('#d1').datePicker({
        	startDate: '01-01-2000',
        	endDate: '01-01-2012',
                clickInput:true,
            	displayClose:true
            });

            $('#d2').datePicker({
        	startDate: '01-01-2000',
        	endDate: '01-01-2012',
                clickInput:true,
            	displayClose:true
            });

                
            $('#inputDate4').datePicker({
            	createButton:false,
                clickInput:true,
            	endDate: (new Date()).addDays(365).asString() 
            });		
            		
            $('#inputDate5').datePicker({
                clickInput:true,
		endDate: (new Date()).addDays(365).asString(),
            	renderCallback:function($td, thisDate, month, year)
        	    {
            		if (thisDate.isWeekend()) {
            		    $td.addClass('weekend');
            		    $td.addClass('disabled');
            		}
            	}
            });	
                
            $('#inputDate6').datePicker({
            	displayClose:true,
            	closeOnSelect:false
	    })
            .bind(
            	'click',
            	function()
            	{
            	    alert("сработал click!");
            	}
            );
            $('#inputDate7')
            .datePicker({
            	createButton:false,
            	startDate: (new Date()).addDays(-5).asString(),
            	clickInput:true
            	}
            )
        .bind(
            'dpClosed',
            function(e, selectedDate)
            {
                alert("сработал dpClosed");	        
            }
        )
            .bind(
            	'dateSelected',
            	function(e, selectedDate, $td)
		{
		    alert("сработал dateSelected, " + selectedDate.asString());
		    $('#inputDate7').val(selectedDate.asString());
		    $('#inputDate6').dpSetSelected(selectedDate.addDays(0).asString());
		}
	    );
	    $('#inputDate8')
		.datePicker({inline:true})
		.bind('dateSelected',function(e, selectedDate, $td)
		    {
			alert(selectedDate.asString());
		    }
		);
	});
</script>

<?php 
if( !$NoHeadEnd) print("</head>\n");

?>
<link rel="icon" href="./favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
